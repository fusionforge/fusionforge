#! /bin/sh
# 
# $Id$
#
# Configure LDAP for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [  $(id -u) != 0 -a  "x$1" != "xlist" ] ; then
	echo "You must be root to run this, please enter passwd"
	exec su -c "$0 $1"
fi

PATH=$PATH:/usr/sbin

# Should I do something for /etc/pam_ldap.conf ?
modify_pam_ldap(){
	echo "Nothing to do"
}

# Check/Modify /etc/libnss-ldap.conf
configure_libnss_ldap(){
    cp -a /etc/libnss-ldap.conf /etc/libnss-ldap.conf.sourceforge-new
	dn=$1
	# Check if DN is correct
	if ! grep -q "^base[ 	]*$dn" /etc/libnss-ldap.conf.sourceforge-new ; then
		echo "WARNING: Probably incorrect base line in /etc/libnss-ldap.conf"
		grep "^base" /etc/libnss-ldap.conf
		echo "Should be: base $dn"
	fi
	# Check bindpw
	# Should contain the secret
	# All users can see ldap stored gid/uid
	chmod 644 /etc/libnss-ldap.conf.sourceforge-new
# It doesn't seem to be necessary, only rootbinddn is necessary
#	if ! grep -q "^bindpw" /etc/libnss-ldap.conf ; then
#		echo "# Next line added by Sourceforge install" >>/etc/libnss-ldap.conf
#		echo "bindpw secret" >>/etc/libnss-ldap.conf
#	fi
	# Check rootbinddn
	# This seems to be necessary to display uid/gid
	# Should be cn=admin,dc=...
	if ! grep -q "^rootbinddn" /etc/libnss-ldap.conf.sourceforge-new ; then
		echo "# Next line added by Sourceforge install" >>/etc/libnss-ldap.conf.sourceforge-new
		echo "rootbinddn cn=admin,$dn" >>/etc/libnss-ldap.conf.sourceforge-new
	fi
}

# Purge /etc/libnss-ldap.conf
purge_libnss_ldap(){
    cp -a /etc/libnss-ldap.conf /etc/libnss-ldap.conf.sourceforge-new
	perl -pi -e "s/^# Next line added by Sourceforge install\n/#SF#/g" /etc/libnss-ldap.conf.sourceforge-new
	perl -pi -e "s/^#SF#.*\n//g" /etc/libnss-ldap.conf.sourceforge-new
}

# Modify /etc/ldap/slapd.conf
configure_slapd(){
	dn=$1

	if [ ! -e /etc/ldap/slapd.conf ] ; then
	    echo "ERROR: You don't have a /etc/ldap/slapd.conf file."
	    echo "Please make sure your slapd package is correctly configured."
	    exit 1
	fi
    cp -a /etc/ldap/slapd.conf /etc/ldap/slapd.conf.sourceforge-new
	
	# Maybe should comment referral line too
	echo "WARNING: Please check referal line in /etc/ldap/slapd.conf"
	
	# Debian config by default only include core schema
	if ! grep -q "Sourceforge" /etc/ldap/slapd.conf.sourceforge-new ; then
		rm -f /etc/ldap/slapd.conf.sourceforge
		for schema in /etc/ldap/schema/core.schema \
			/etc/ldap/schema/cosine.schema \
			/etc/ldap/schema/inetorgperson.schema \
			/etc/ldap/schema/nis.schema \
			/etc/sourceforge/sourceforge.schema
		do
			if ! grep -q "^include[ 	]*$schema" /etc/ldap/slapd.conf.sourceforge-new ; then
				echo "include	$schema	#Added by Sourceforge install" >>/etc/ldap/slapd.conf.sourceforge
				echo "Adding $schema"
			else
				echo "Commenting $schema"
				export schema
				perl -pi -e "s/^include[ 	]*\$schema/#Comment by Sourceforge install#include	\$schema/g" /etc/ldap/slapd.conf.sourceforge-new
				echo "include	$schema	#Added by Sourceforge install" >>/etc/ldap/slapd.conf.sourceforge
				echo "Adding $schema"
			fi
		done
		cat /etc/ldap/slapd.conf.sourceforge-new >>/etc/ldap/slapd.conf.sourceforge
		mv /etc/ldap/slapd.conf.sourceforge /etc/ldap/slapd.conf.sourceforge-new

		# Then write access for SF_robot
		perl -pi -e "s/access to attribute=userPassword/# Next second line added by Sourceforge install
access to attribute=userPassword
	by dn=\"cn=SF_robot,$dn\" write/" /etc/ldap/slapd.conf.sourceforge-new

		perl -pi -e "s/access to \*/# Next lines added by Sourceforge install
access to dn=\".*,ou=People,$dn\"		
	by dn=\"cn=admin,$dn\" write	
	by dn=\"cn=SF_robot,$dn\" write		
	by * read				
access to dn=\"ou=People,$dn\"		
	by dn=\"cn=admin,$dn\" write	
	by dn=\"cn=SF_robot,$dn\" write		
	by * read				
access to dn=\"ou=Group,$dn\"		
	by dn=\"cn=admin,$dn\" write	
	by dn=\"cn=SF_robot,$dn\" write		
	by * read				
access to dn=\"ou=cvsGroup,$dn\"		
	by dn=\"cn=admin,$dn\" write	
	by dn=\"cn=SF_robot,$dn\" write		
	by * read				
# End of sourceforge add
access to */" /etc/ldap/slapd.conf.sourceforge-new

		# invoke-rc.d slapd restart
	fi	
}

# Purge /etc/ldap/slapd.conf
purge_slapd(){
	if [ ! -e /etc/ldap/slapd.conf ] ; then
	    echo "ERROR: You don't have a /etc/ldap/slapd.conf file."
	    echo "Please make sure your slapd package is correctly configured."
	    exit 1
	fi
    cp -a /etc/ldap/slapd.conf /etc/ldap/slapd.conf.sourceforge-new
	
	perl -pi -e "s/^.*#Added by Sourceforge install\n//" /etc/ldap/slapd.conf.sourceforge-new
	perl -pi -e "s/#Comment by Sourceforge install#//" /etc/ldap/slapd.conf.sourceforge-new
if grep -q "# Next second line added by Sourceforge install" /etc/ldap/slapd.conf.sourceforge-new
then
	vi -e /etc/ldap/slapd.conf.sourceforge-new <<-FIN
/# Next second line added by Sourceforge install
:d
/SF_robot
:d
:w
:x
FIN
fi
if grep -q "Next lines added by Sourceforge install" /etc/ldap/slapd.conf.sourceforge-new
then
	vi -e /etc/ldap/slapd.conf.sourceforge-new <<-FIN
/# Next lines added by Sourceforge install
:ma a
/# End of sourceforge add
:ma b
:'a,'bd
:w
:x
FIN
fi

}

# Modify /etc/nsswitch.conf
configure_nsswitch()
{
    cp -a /etc/nsswitch.conf /etc/nsswitch.conf.sourceforge-new
    # This is sensitive file
    # By security i let priority to files
    # Should maybe enhance this to take in account nis
    # Maybe ask the order db/files/nis/ldap
    if ! grep -q '^passwd:.*ldap' /etc/nsswitch.conf.sourceforge-new ; then
	perl -pi -e "s/^(passwd:[^#\n]*)([^\n]*)/\1 ldap \2#Added by Sourceforge install\n#Comment by Sourceforge install#\1\2/gs" /etc/nsswitch.conf.sourceforge-new
    fi
    if ! grep -q '^group:.*ldap' /etc/nsswitch.conf.sourceforge-new ; then
	perl -pi -e "s/^(group:[^#\n]*)([^\n]*)/\1 ldap \2#Added by Sourceforge install\n#Comment by Sourceforge install#\1\2/gs" /etc/nsswitch.conf.sourceforge-new
    fi
    if ! grep -q '^shadow:.*ldap' /etc/nsswitch.conf.sourceforge-new ; then
	perl -pi -e "s/^(shadow:[^#\n]*)([^\n]*)/\1 ldap \2#Added by Sourceforge install\n#Comment by Sourceforge install#\1\2/gs" /etc/nsswitch.conf.sourceforge-new
    fi
}

# Purge /etc/nsswitch.conf
purge_nsswitch()
{
    cp -a /etc/nsswitch.conf /etc/nsswitch.conf.sourceforge-new
    perl -pi -e "s/^[^\n]*#Added by Sourceforge install\n//" /etc/nsswitch.conf.sourceforge-new
    perl -pi -e "s/#Comment by Sourceforge install#//" /etc/nsswitch.conf.sourceforge-new
}

# Load ldap database from sourceforge database
load_ldap(){
	naming_context=$1
	secret=$2
	if [ "x$secret" != "x" ] 
	then
		# This load the ldap database
		echo "Distinguished Name is $naming_context"
		echo "Creating ldif file from database"
		tmpldif="/tmp/ldif$$"
		/usr/lib/sourceforge/bin/sql2ldif.pl >$tmpldif
		echo "Filling LDAP with database"
		# Only if the ldap server is local
		# Maybe ask for the password, but will simple athentication
		# Be allowed on remote server ?
		# VERBOSE=-v
		# -v Use  verbose mode, with many diagnostics written to
		# standard output.
		# -c Continuous  operation  mode. Errors are reported,
		# but ldapmodify will  continue  with  modifications.
		# The default is to exit after reporting an error.
		# -x Use simple authentication instead of SASL.
		# -w passwd Use passwd as the password for  simple
		# authentication.
		# -r Replace existing values by default.
		# add with -r don't modify and modify don't add so i do add and modify
 		ldapadd $VERBOSE -r -c -D "cn=admin,$naming_context" -x -w"$secret" -f $tmpldif > /dev/null 2>&1 || true
 		ldapmodify $VERBOSE -r -c -D "cn=admin,$naming_context" -x -w"$secret" -f $tmpldif > /dev/null 2>&1 || true
		rm -f $tmpldif
	else
		echo "WARNING: Can't load ldap table without /etc/lapd.secret file"
		echo "AFAIK  : This file should be installed by libpam-ldap"
	fi
}

print_ldif_default(){
	dn=$1
	dc=`echo $1 | sed 's/dc=\(.[^,]*\),.*/\1/'`
	cryptedpasswd=$2
	cat <<-FIN
dn: $dn
objectClass: dcObject
dc: $dc

dn: cn=admin,$dn
objectClass: organizationalRole
objectClass: simpleSecurityObject
cn: admin
userPassword: $cryptedpasswd
description: LDAP administrator

dn: ou=People,$dn
objectClass: organizationalUnit
ou: People

dn: ou=Roaming,$dn
objectCLass: organizationalUnit
ou: Roaming
FIN
}

setup_vars() {
	sys_ldap_base_dn=$(grep sys_ldap_base_dn /etc/sourceforge/local.inc | cut -d\" -f2)
	[ "x$sys_ldap_base_dn" == "x" ] && sys_ldap_base_dn=`grep suffix /etc/ldap/slapd.conf | cut -d\" -f2`
	echo "=====>sys_ldap_base_dn=$sys_ldap_base_dn"
	sys_ldap_admin_dn=$(grep sys_ldap_admin_dn /etc/sourceforge/local.inc | cut -d\" -f2)
	echo "=====>sys_ldap_admin_dn=$sys_ldap_admin_dn"
	sys_ldap_bind_dn=$(grep sys_ldap_bind_dn /etc/sourceforge/local.inc | cut -d\" -f2)
	echo "=====>sys_ldap_bind_dn=$sys_ldap_bind_dn"
	sys_ldap_passwd=$(grep sys_ldap_passwd /etc/sourceforge/database.inc | cut -d\" -f2)
	#echo "=====>sys_ldap_passwd=$sys_ldap_passwd"
	[ -f /etc/ldap.secret ] && secret=$(cat /etc/ldap.secret) || secret=$sys_ldap_passwd
	cryptedpasswd=`slappasswd -s "$secret" -h {CRYPT}`
	#echo "=====>$cryptedpasswd"
}
# Check Server
check_server() {
	naming_context=$(ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts | grep "namingContexts:" | cut -d" " -f2)
	[ "x$naming_context" == "x" ] && invoke-rc.d slapd restart && sleep 5 && naming_context=$(ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts | grep "namingContexts:" | cut -d" " -f2)
	[ "x$naming_context" == "x" ] && echo "LDAP Server KO" || echo "LDAP Server OK : dn=$naming_context"
}

# Setup SF_robot Passwd
setup_robot() {
	setup_vars

	# The first account is only used in a multiserver SF
	echo "Adding robot accounts"

	{ ldapadd -r -c -D "$sys_ldap_admin_dn" -x -w"$secret" || true ; } <<-FIN
dn: cn=Replicator,$sys_ldap_base_dn
description: Replicator the Robot
objectClass: organizationalRole
objectClass: simpleSecurityObject
userPassword: {CRYPT}xxxxx
cn: Replicator

dn: cn=SF_robot,$sys_ldap_base_dn
description: SF the Robot
objectClass: organizationalRole
objectClass: simpleSecurityObject
userPassword: {CRYPT}xxxxx
cn: SF_robot
FIN
	check_server
	echo "Changing SF_robot passwd using admin account"
	ldapmodify -v -c -D "$sys_ldap_admin_dn" -x -w"$secret" <<-FIN
dn: $sys_ldap_bind_dn
changetype: modify
replace: userPassword
userPassword: $cryptedpasswd
FIN
	check_server
	echo "Testing LDAP"
	echo "Changing dummy cn using SF_robot account"
	ldapmodify -v -c -D "$sys_ldap_bind_dn" -x -w"$secret" <<-FIN
dn: uid=dummy,ou=People,$sys_ldap_base_dn
changetype: modify
replace: cn
cn: Dummy User Tested
FIN
}

# Main
case "$1" in
	configure-files)
		dn=$(grep sys_ldap_base_dn /etc/sourceforge/local.pl | cut -d\' -f2)
		setup_vars
		echo "Modifying /etc/ldap/slapd.conf"
		# purge_slapd
		configure_slapd $dn
		echo "Modifying /etc/libnss-ldap.conf"
		configure_libnss_ldap $dn
		echo "Modifying /etc/nsswitch.conf"
		configure_nsswitch
		;;
	configure)
		dn=$(grep sys_ldap_base_dn /etc/sourceforge/local.pl | cut -d\' -f2)
		setup_vars
		# Restarting ldap 
		invoke-rc.d slapd restart
		sleep 5		# Sometimes it takes a bit of time to get out of bed
		echo "Load ldap"
		load_ldap $dn "$secret"
		echo "Setup SF_robot account"
		setup_robot
		;;
	update)
		dn=$(grep sys_ldap_base_dn /etc/sourceforge/local.pl | cut -d\' -f2)
		setup_vars
		load_ldap $dn "$secret"
		# [ -f /etc/ldap.secret ] && secret=$(cat /etc/ldap.secret) && load_ldap $dn $secret &>/dev/null
		# [ -f /etc/ldap.secret ] || load_ldap $dn $secret
		;;
	purge-files)
		echo "Purging /etc/ldap/slapd.conf"
		purge_slapd
		echo "Purging /etc/nsswitch.conf"
		purge_nsswitch
		echo "Purging /etc/libnss-ldap.conf"
		purge_libnss_ldap
		;;
	purge)
		$0 empty
		;;
	list)
		naming_context=$(ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts | grep "namingContexts:" | cut -d" " -f2)
		# Display what is now in the database
		ldapsearch -x -b "$naming_context" '(objectclass=*)' 
		;;
	empty)
	        setup_vars
		naming_context=$(ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts | grep "namingContexts:" | cut -d" " -f2)
		admin_regexp=$(echo $sys_ldap_base_dn | sed 's/, */, */g')
		admin_regexp="^cn=admin, *$admin_regexp"
		get_our_entries () {
		    slapcat \
			| grep "^dn:" \
			| sed 's/^dn: *//' \
			| grep -v "^dc=" \
			| grep -v "^ou=" \
			| grep -v "$admin_regexp"
		    slapcat \
			| grep "^dn:" \
			| sed 's/^dn: *//' \
			| grep -v "^dc=" \
			| grep -v "^ou=People," \
			| grep -v "^ou=Roaming," \
			| grep -v "$admin_regexp"
		}
		get_our_entries || true
		get_our_entries | ldapdelete -D "cn=admin,$sys_ldap_base_dn" -x -w"$secret" > /dev/null 2>&1 || true
		;;
	reset)
		setup_vars
		invoke-rc.d slapd stop
		rm -f /var/lib/ldap/*.dbb
		invoke-rc.d slapd start
		print_ldif_default $sys_ldap_base_dn $cryptedpasswd > /tmp/ldif$$ 
		slapadd -l /tmp/ldif$$
		rm -f /tmp/ldif$$
		;;
	check)	
		check_server
		;;
	test)	
		setup_robot
		;;
	*)
		echo "Usage: $0 {configure|update|purge|list|empty|reset}"
		exit 1
		;;
esac

# Ancient ldaptest follow

# All info found in /usr/share/doc/openldap-guide

# This is testing local ldap server
##echo "============ LDAP SEARCH ==================="
##ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts
##echo "============ LDAP SEARCH ==================="

# Then you need LDIF file and run ldapadd
# To fill this you need to get your namingContexts
# This do this and should be used a the sourceforge base DN
##naming_context=$(ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts | grep "namingContexts:" | cut -d" " -f2)
##echo "Naming Context is: ===>$naming_context<=="

# Un fichier ldif d'exemple
##echo "============ Example ldif file =============="
##tee /tmp/example.ldif <<-FIN
##dn: cn=Bob Smith,ou=People,$naming_context
##objectClass: person
##cn: Bob Smith
##sn: Smith
##FIN
##echo "============ Example ldif file =============="
##echo "============ Adding this to the database ===="
#/usr/sbin/slapadd -v -d2 -l /tmp/example.ldif
#ldapadd -U admin -D "cn=admin,ou=People,$naming_context" -W -f /tmp/example.ldif
#ldapadd -v -D "cn=admin,ou=People,$naming_context" -X u:admin  -f /tmp/example.ldif
##ldapadd -v -D "cn=admin,ou=People,$naming_context" -x -W -f /tmp/example.ldif
##echo "============ Checking the database =========="
##ldapsearch -x -b "$naming_context" '(objectclass=*)'

##Un ACL exemple pour la partie web
#access to dn=".*,ou=People,dc=dragoninc,dc=on,dc=ca" 
#attr=userpassword,ntpassword,lmpassword 
#        by dn="uid=root,ou=People,dc=dragoninc,dc=on,dc=ca" write 
#        by * none 
#
#access to dn=".*,ou=Group,dc=dragoninc,dc=on,dc=ca" attr=userpassword 
#        by dn="uid=root,ou=People,dc=dragoninc,dc=on,dc=ca" write 
#        by * none
#
# La mine d'or http://www.bayour.com/LDAPv3-HOWTO.html
# http://www.ameritech.net/users/mhwood/ldap-sec-setup.html
# A lire /usr/share/doc/openssl/README.Debian
# /usr/share/doc/libsasl7/sysadmin.html
# 
# To create the certificate that OpenLDAP will use, we issue the command openssl like this:
# openssl req -new -x509 -nodes -out server.pem -keyout server.pem -days 365
# openssl x509 -in server.pem -text
#
#
# Until this work:  ldapsearch -b "dc=g-tt,dc=rd,dc=francetelecom,dc=fr" '(objectclass=*)'
