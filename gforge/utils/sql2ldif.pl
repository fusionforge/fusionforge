#!/usr/bin/perl
#
#  Convert SQL user database to LDIF format (for SourceForge LDAP schema)
#  by pfalcon@users.sourceforge.net 2000-10-17
#
#  ./sql2ldif.pl	: Dump only top-level ou map
#  ./sql2ldif.pl --full : Dump full database (ouch!)
#
#  $Id: sql2ldif.pl,v 1.8 2000/12/10 23:07:31 pfalcon Exp $
# 

use DBI;

#require("base64.pl");  # Include all the predefined functions
require("include.pl");  # Include all the predefined functions
&db_connect;

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

while(my ($username, $realname, $shell, $pw, $uid, $cvs_flags) = $rel->fetchrow()) {
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
shadowLastChange: 10879
shadowMax: 99999
shadowWarning: 7
loginShell: $shell
x-cvsShell: $cvs_flags2shell[$cvs_flags]
uidNumber: $uid
gidNumber: 100
homeDirectory: /home/users/$username
gecos: $realname

";
}

#
#  Dump group entries (ou=Group)
#

my $query = "
SELECT group_id,unix_group_name
FROM groups
WHERE status='A'
";
my $rel = $dbh->prepare($query);
$rel->execute();

while(my ($gid, $groupname) = $rel->fetchrow()) {
	my $query = "
SELECT user_name
FROM users,user_group
WHERE group_id=$gid
      AND users.user_id=user_group.user_id
";
	my $rel = $dbh->prepare($query);
	$rel->execute();

	print "dn: cn=$groupname,ou=Group,$sys_ldap_base_dn
objectClass: posixGroup
objectClass: top
cn: $groupname
userPassword: {crypt}x
gidNumber: $gid
";

	while(my ($username) = $rel->fetchrow()) {
		print "memberUid: $username\n";
	}
	print "\n";
}

#
#  Dump CVS group entries (ou=cvsGroup)
#

my $query = "
SELECT group_id,unix_group_name
FROM groups
WHERE status='A'
";
my $rel = $dbh->prepare($query);
$rel->execute();

while(my ($gid, $groupname) = $rel->fetchrow()) {
	my $query = "
SELECT user_name
FROM users,user_group
WHERE group_id=$gid
      AND users.user_id=user_group.user_id
      AND user_group.cvs_flags > 0
";
	my $rel = $dbh->prepare($query);
	$rel->execute();

	print "dn: cn=$groupname,ou=cvsGroup,$sys_ldap_base_dn
objectClass: posixGroup
objectClass: top
cn: $groupname
userPassword: {crypt}x
gidNumber: $gid
";

	while(my ($username) = $rel->fetchrow()) {
		print "memberUid: $username\n";
	}
	print "\n";
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

";
}
