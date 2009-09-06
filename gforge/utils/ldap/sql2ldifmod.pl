#!/usr/bin/perl
#
#  Convert SQL user database to LDIF format (for SourceForge LDAP schema)
#  to modify entries.
#  by pfalcon@users.sourceforge.net 2000-10-17
#
#  ./sql2ldif.pl	: Dump only top-level ou map
#  ./sql2ldif.pl --full : Dump full database (ouch!)
#

use DBI;


require("/usr/lib/gforge/lib/include.pl");  # Include all the predefined functions
$chroot="/var/lib/gforge/chroot";


$dbh ||= DBI->connect("DBI:Pg:dbname=$sys_dbname","$sys_dbuser","$sys_dbpasswd");
die "Cannot connect to database: $!" if ( ! $dbh );

#
#  Dump user entries (ou=People)
#

# We give user maximum of privileges assigned to one by groups ;-(
my $query = "
	SELECT 
		nss_passwd.login,gecos,shell,nss_shadow.passwd,uid,gid,email
	FROM 
		nss_passwd,nss_shadow,mta_users
	WHERE 
		nss_passwd.login=mta_users.login AND nss_passwd.login=nss_shadow.login
		GROUP BY nss_passwd.login,gecos,shell,nss_shadow.passwd,uid,gid,email";


my $rel = $dbh->prepare($query);
$rel->execute();


while(my ($username, $realname, $shell, $pw, $uid, $gid, $email) = $rel->fetchrow()) {
if (!$realname) { $realname='?'; }
$realname=~tr#\x80-\xff#?#;  # it should be UTF-8 encoded, we just drop non-ascii chars


print "dn: uid=$username,ou=People,$sys_ldap_base_dn
changetype: modify
replace: uid
uid: $username
-
replace: cn
cn: $realname
-
replace: userPassword
userPassword: {crypt}$pw
-
replace: loginShell
loginShell: $shell
-
replace: uidNumber
uidNumber: $uid
-
replace: gidNumber
gidNumber: $gid
-
replace: homeDirectory
homeDirectory: $chroot/home/users/$username
-
replace: gecos
gecos: $realname
-
replace: debGforgeForwardEmail
debGforgeForwardEmail: $email \n\n

";
}

#
#  Dump group entries (ou=Group)
#

my $query = "SELECT gid,name FROM nss_groups";

my $rel = $dbh->prepare($query);
$rel->execute();

while(my ($gid, $groupname) = $rel->fetchrow()) {
	my $query = "SELECT user_name FROM nss_usergroups
			WHERE gid=$gid";

	my $rel = $dbh->prepare($query);
	$rel->execute();

print "dn: cn=$groupname,ou=Group,$sys_ldap_base_dn
changetype: modify
replace: cn
cn: $groupname
-
replace: userPassword
userPassword: {crypt}x
-
replace: gidNumber
gidNumber: $gid
";
print "-
replace: memberUid\n";

while(my ($username) = $rel->fetchrow()) {
print "memberUid: $username\n";
}
print "\n";
}

#
#  Dump mailing-lists entries (ou=mailingList)
#

$query = "SELECT list_name,
		post_address,
		owner_address,
		request_address,
		admin_address,
		bounces_address,
		confirm_address,
		join_address,
		leave_address,
		subscribe_address,
		unsubscribe_address
          FROM mta_lists" ;

$rel = $dbh->prepare($query);
$rel->execute();

while(my ($listname, $post, $owner, $request, $admin, $bounces, $confirm, $join, $leave, $subscribe, $unsubscribe) = $rel->fetchrow()) {

print "dn: cn=$listname,ou=mailingList,$sys_ldap_base_dn
changetype: modify
replace: cn
cn: $listname
-
replace: ou
ou: mailingList
-
replace: debGforgeListPostAddress
debGforgeListPostAddress: \"$post\"
-
replace: debGforgeListOwnerAddress
debGforgeListOwnerAddress: \"$owner\"
-
replace: debGforgeListRequestAddress
debGforgeListRequestAddress: \"$request\"
-
replace: debGforgeListAdminAddress
debGforgeListAdminAddress: \"$admin\"
-
replace: debGforgeListBouncesAddress
debGforgeListBouncesAddress: \"$bounces\"
-
replace: debGforgeListConfirmAddress
debGforgeListConfirmAddress: \"$confirm\"
-
replace: debGforgeListJoinAddress
debGforgeListJoinAddress: \"$join\"
-
replace: debGforgeListLeaveAddress
debGforgeListLeaveAddress: \"$leave\"
-
replace: debGforgeListSubscribeAddress
debGforgeListSubscribeAddress: \"$subscribe\"
-
replace: debGforgeListUnsubscribeAddress
debGforgeListUnsubscribeAddress: \"$unsubscribe\"

";



#
#  Auxilary functions
#

sub dump_header {
        my $dc=$sys_ldap_base_dn;
        $dc =~ s/,.*// ;
        $dc =~ s/.*=// ;
	print "dn: ou=Hosts,$sys_ldap_base_dn
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

dn: ou=mailingList,$sys_ldap_base_dn
ou: mailingList
objectClass: top
objectClass: organizationalUnit
objectClass: domainRelatedObject
associatedDomain: $sys_lists_host

dn: uid=dummy,ou=People,$sys_ldap_base_dn
uid: dummy
cn: Dummy User
objectClass: account
objectClass: posixAccount
objectClass: top
objectClass: shadowAccount
objectClass: debGforgeAccount
userPassword: {crypt}x
shadowLastChange: 10879
shadowMax: 99999
shadowWarning: 7
loginShell: /bin/false
debGforgeCvsShell: /bin/false
uidNumber: $dummy_uid
gidNumber: $dummy_uid
homeDirectory: $chroot/home/users/dummy
gecos: Dummy User

";
}
}
