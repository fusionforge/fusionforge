#!/usr/bin/perl
#
#  Convert SQL user database to LDIF format (for SourceForge LDAP schema)
#  by pfalcon@users.sourceforge.net 2000-10-17
#
#  ./sql2ldif.pl	: Dump only top-level ou map
#  ./sql2ldif.pl --full : Dump full database (ouch!)
#
#  $Id: sql2ldif.pl,v 1.13 2001/03/26 20:38:01 pfalcon Exp $
# 

use DBI;

#require("base64.pl");  # Include all the predefined functions
require("include.pl");  # Include all the predefined functions
&db_connect;


sub homedir {
	my ($user) = @_;
	return "/home/users/".substr($user,0,1)."/".substr($user,0,2)."/$user";
}

dump_header();

if (!($#ARGV+1)) {
	exit;
}

#
#  Dump user entries (ou=People)
#

# We give user maximum of privileges assigned to one by groups ;-(
my $query = "
SELECT user_name,realname,shell,unix_pw,unix_uid,MAX(cvs_flags)
FROM users,user_group
WHERE unix_status='A'
      AND users.user_id=user_group.user_id
GROUP BY user_name,realname,shell,unix_pw,unix_uid
";
my $rel = $dbh->prepare($query);
$rel->execute();

#print "$sys_ldap_host\n";
#print "$sys_ldap_base_dn\n";

@cvs_flags2shell=('/dev/null','/bin/cvssh','/bin/bash');

#
# Note: unix uid = db uxix_uid + $uid_add
#
while(my ($username, $realname, $shell, $pw, $uid, $cvs_flags) = $rel->fetchrow()) {
	$uid+=$uid_add;
	print "dn: uid=$username,ou=People,$sys_ldap_base_dn\n";
	print "uid: $username\n";
	if (!$realname) { $realname='?'; }
	$realname=~tr#\x80-\xff#?#;  # it should be UTF-8 encoded, we just drop non-ascii chars
	print "cn: $realname\n";
	print "objectClass: account
objectClass: posixAccount
objectClass: top
objectClass: shadowAccount
objectClass: x-sourceforgeAccount
";
	print "userPassword: {crypt}$pw
shadowLastChange: 1
shadowMax: 99999
shadowWarning: 7
loginShell: $shell
x-cvsShell: $cvs_flags2shell[$cvs_flags]
uidNumber: $uid
gidNumber: 100
homeDirectory: ".homedir($username)."
gecos: $realname

";
}

#
#  Dump group entries (ou=Group)
#

my $query = "
SELECT groups.group_id,unix_group_name,user_name
FROM groups,users,user_group
WHERE groups.status='A'
AND groups.group_id=user_group.group_id
AND user_group.user_id=users.user_id
ORDER BY groups.group_id
";
my $rel = $dbh->prepare($query);
$rel->execute();

#
# Note: unix gid = db group_id + $gid_add
#
$last_gid=-1;
while(my ($gid, $groupname, $member) = $rel->fetchrow()) {
	$gid+=$gid_add;

	if ($gid != $last_gid) {
		print "\ndn: cn=$groupname,ou=Group,$sys_ldap_base_dn
objectClass: posixGroup
objectClass: top
cn: $groupname
userPassword: {crypt}x
gidNumber: $gid
";
		$last_gid=$gid;
	}

	print "memberUid: $member\n";
}

#
#  Dump CVS group entries (ou=cvsGroup)
#

my $query = "
SELECT groups.group_id,unix_group_name,user_name,cvs_flags
FROM groups,users,user_group
WHERE groups.status='A'
AND groups.group_id=user_group.group_id
AND user_group.user_id=users.user_id
ORDER BY groups.group_id
";
# we need cvsGroup even if no member has permission
#AND user_group.cvs_flags > 0
my $rel = $dbh->prepare($query);
$rel->execute();

$last_gid=-1;
while(my ($gid, $groupname, $member, $cvs) = $rel->fetchrow()) {
	$gid+=$gid_add;

	if ($gid != $last_gid) {

		# virtual member for anoncvs access
		print "\ndn: uid=anoncvs_$groupname,ou=People,$sys_ldap_base_dn\n";
		print "uid: anoncvs_$groupname\n";
		print "cn: anoncvs\n";
		print "objectClass: account
objectClass: posixAccount
objectClass: top
objectClass: shadowAccount
objectClass: x-sourceforgeAccount
";
		print "userPassword: {crypt}x
shadowLastChange: 1
shadowMax: 99999
shadowWarning: 7
loginShell: /bin/false
x-cvsShell: /bin/false
";
		print "uidNumber: ",$gid+$anoncvs_add;
		print "
gidNumber: $gid
homeDirectory: ".homedir("anoncvs_$groupname")."
gecos: anoncvs
";
		# CVS group itself
		print "\ndn: cn=$groupname,ou=cvsGroup,$sys_ldap_base_dn
objectClass: posixGroup
objectClass: top
cn: $groupname
userPassword: {crypt}x
gidNumber: $gid
memberUid: anoncvs_$groupname
";
		$last_gid=$gid;
	}

	if ($cvs>0) {
		print "memberUid: $member\n";
	}
}

#
#  Auxilary functions
#

sub dump_header {
	print "dn: $sys_ldap_base_dn
dc: sourceforge
objectClass: top
objectClass: domain
objectClass: domainRelatedObject
associatedDomain: $sys_default_domain

dn: ou=Hosts,$sys_ldap_base_dn
ou: Hosts
objectClass: top
objectClass: organizationalUnit
objectClass: domainRelatedObject
associatedDomain: $sys_default_domain

dn: ou=People,$sys_ldap_base_dn
ou: People
objectClass: top
objectClass: organizationalUnit
objectClass: domainRelatedObject
associatedDomain: $sys_default_domain

dn: ou=Aliases,$sys_ldap_base_dn
ou: Aliases
objectClass: top
objectClass: organizationalUnit
objectClass: domainRelatedObject
associatedDomain: $sys_default_domain

dn: ou=Group,$sys_ldap_base_dn
ou: Group
objectClass: top
objectClass: organizationalUnit
objectClass: domainRelatedObject
associatedDomain: $sys_default_domain

dn: ou=cvsGroup,$sys_ldap_base_dn
ou: cvsGroup
objectClass: top
objectClass: organizationalUnit
objectClass: domainRelatedObject
associatedDomain: $sys_default_domain

dn: cn=Replicator,dc=sourceforge,dc=net
cn: Replicator
sn: Replicator the Robot
description: empty
objectClass: top
objectClass: person
userPassword: {crypt}x

";
}
