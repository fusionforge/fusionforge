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

setup_vars() {
    sf_ldap_base_dn=$(grep ^ldap_base_dn= /etc/sourceforge/sourceforge.conf | cut -d= -f2-)
    # [ "x$sf_ldap_base_dn" == "x" ] && sf_ldap_base_dn=`grep suffix /etc/ldap/slapd.conf | cut -d\" -f2`
    sf_ldap_admin_dn="cn=admin,${sf_ldap_base_dn}"
    sf_ldap_bind_dn="cn=SF_robot,${sf_ldap_base_dn}"
    sf_ldap_passwd=$(grep ^ldap_web_add_password= /etc/sourceforge/sourceforge.conf | cut -d= -f2-)
    sf_cryptedpasswd=`slappasswd -s "$sf_ldap_passwd" -h {CRYPT}`
    sf_ldap_host=$(grep ^ldap_host= /etc/sourceforge/sourceforge.conf | cut -d= -f2-)

    [ -f /etc/ldap.secret ] && ldap_passwd=$(cat /etc/ldap.secret) || ldap_passwd=$sf_ldap_passwd
    cryptedpasswd=`slappasswd -s "$ldap_passwd" -h {CRYPT}`
    ldap_suffix=$(grep suffix /etc/ldap/slapd.conf | cut -d\" -f2)

    tmpfile_pattern=/tmp/$(basename $0).XXXXXX

    if [ "$DEBSFDEBUG" != 1 ] ; then
	DEVNULL12="> /dev/null 2>&1"
	DEVNULL2="2> /dev/null"
    else
	set -x
    fi
}

show_vars () {
    echo "sf_ldap_base_dn = '$sf_ldap_base_dn'"
    echo "sf_ldap_admin_dn = '$sf_ldap_admin_dn'"
    echo "sf_ldap_bind_dn = '$sf_ldap_bind_dn'"
    echo "sf_ldap_passwd = '$sf_ldap_passwd'"
    echo "sf_cryptedpasswd = '$sf_cryptedpasswd'"
    echo "sf_ldap_host = '$sf_ldap_host'"
    echo "ldap_passwd = '$ldap_passwd'"
    echo "cryptedpasswd = '$cryptedpasswd'"
    echo "ldap_suffix = '$ldap_suffix'"
    echo "tmpfile_pattern = '$tmpfile_pattern'"
}

# Check Server
check_server() {
    answer=$(eval "ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts $DEVNULL2" | grep "namingContexts:" | cut -d" " -f2)
    if [ "x$answer" == "x" ] ; then 
	eval "invoke-rc.d slapd restart $DEVNULL12" && sleep 5
	answer=$(eval "ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts $DEVNULL2" \
	    | grep "namingContexts:" \
	    | cut -d" " -f2)
    fi
    if [ "x$answer" == "x" ] ; then
	echo "LDAP Server dead"
	exit 1
    # else
	# echo "LDAP Server OK: suffix=$answer"
    fi
}

# Should I do something for /etc/pam_ldap.conf ?
modify_pam_ldap(){
    echo -n
    # echo "Nothing to do"
}

# Check/Modify /etc/libnss-ldap.conf
configure_libnss_ldap(){
    cp -a /etc/libnss-ldap.conf /etc/libnss-ldap.conf.sourceforge-new
    # Check if DN is correct
    if ! grep -q "^base[ 	]*$sf_ldap_dn" /etc/libnss-ldap.conf.sourceforge-new ; then
	echo "WARNING: Probably incorrect base line in /etc/libnss-ldap.conf"
	grep "^base" /etc/libnss-ldap.conf
	echo "Should be: base $sf_ldap_base_dn"
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
	echo "rootbinddn cn=admin,$sf_ldap_base_dn" >>/etc/libnss-ldap.conf.sourceforge-new
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
	tmpfile=$(mktemp $tmpfile_pattern)
	for schema in /etc/ldap/schema/core.schema \
	    /etc/ldap/schema/cosine.schema \
	    /etc/ldap/schema/inetorgperson.schema \
	    /etc/ldap/schema/nis.schema \
	    /etc/sourceforge/sourceforge.schema
	  do
	  if ! grep -q "^include[ 	]*$schema" /etc/ldap/slapd.conf.sourceforge-new ; then
	      echo "include	$schema	#Added by Sourceforge install" >> $tmpfile
	      # echo "Adding $schema"
	  else
	      # echo "Commenting $schema"
	      perl -pi -e "s/^include[ 	]*\$schema/#Comment by Sourceforge install#include	\$schema/g" /etc/ldap/slapd.conf.sourceforge-new
	      echo "include	$schema	#Added by Sourceforge install" >> $tmpfile
	      # echo "Adding $schema"
	  fi
	done

	cat /etc/ldap/slapd.conf.sourceforge-new >> $tmpfile
	cat $tmpfile > /etc/ldap/slapd.conf.sourceforge-new
	rm -f $tmpfile

	# Then write access for SF_robot
	perl -pi -e "s/access to attribute=userPassword/# Next second line added by Sourceforge install
access to attribute=userPassword
	by dn=\"cn=SF_robot,$sf_ldap_base_dn\" write/" /etc/ldap/slapd.conf.sourceforge-new

	perl -pi -e "s/access to \*/# Next lines added by Sourceforge install
access to dn=\".*,ou=People,$sf_ldap_base_dn\"
	by dn=\"cn=admin,$sf_ldap_base_dn\" write
	by dn=\"cn=SF_robot,$sf_ldap_base_dn\" write
        by dn=\"cn=admin,$ldap_suffix\" write
	by * read
access to dn=\"ou=People,$sf_ldap_base_dn\"
	by dn=\"cn=admin,$sf_ldap_base_dn\" write
	by dn=\"cn=SF_robot,$sf_ldap_base_dn\" write
        by dn=\"cn=admin,$ldap_suffix\" write
	by * read
access to dn=\"ou=Group,$sf_ldap_base_dn\"
	by dn=\"cn=admin,$sf_ldap_base_dn\" write
	by dn=\"cn=SF_robot,$sf_ldap_base_dn\" write
        by dn=\"cn=admin,$ldap_suffix\" write
	by * read
access to dn=\"ou=cvsGroup,$sf_ldap_base_dn\"
	by dn=\"cn=admin,$sf_ldap_base_dn\" write
	by dn=\"cn=SF_robot,$sf_ldap_base_dn\" write
        by dn=\"cn=admin,$ldap_suffix\" write
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
    if grep -q "# Next second line added by Sourceforge install" /etc/ldap/slapd.conf.sourceforge-new ; then
	vi -e /etc/ldap/slapd.conf.sourceforge-new <<-FIN
/# Next second line added by Sourceforge install
:d
/SF_robot
:d
:w
:x
FIN
    fi
    if grep -q "Next lines added by Sourceforge install" /etc/ldap/slapd.conf.sourceforge-new ; then
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
    if [ "x$ldap_passwd" != "x" ] ; then
        # This loads the ldap database
        # echo "Our base DN is $sf_ldap_base_dn"
        # echo "Creating ldif file from database"
	tmpldif=$(mktemp $tmpfile_pattern)
	dc=$(echo $sf_ldap_base_dn | cut -d, -f1 | cut -d= -f2)
	/usr/lib/sourceforge/bin/sql2ldif.pl >> $tmpldif
        # echo "Filling LDAP with database"
	if ! eval "ldapadd -r -c -D 'cn=admin,$ldap_suffix' -x -w'$ldap_passwd' -f $tmpldif $DEVNULL12" ; then
            # Some entries could not be added (already there)
            # Therefore, we have to modify them
	    eval "ldapmodify -r -c -D 'cn=admin,$ldap_suffix' -x -w'$ldap_passwd' -f $tmpldif $DEVNULL12"
	fi
	rm -f $tmpldif
    else
	echo "It seems the admin password is not known to me."
	echo "I can't fill the LDAP directory without it."
	echo "Normally, libpam-ldap stores this password in /etc/ldap.secret."
	echo "Please check that file."
	exit 1
    fi
}

print_ldif_default(){
    dc=`echo $ldap_suffix | sed 's/dc=\(.[^,]*\),.*/\1/'`
    cat <<-FIN
dn: $ldap_suffix
objectClass: dcObject
dc: $dc

dn: cn=admin,$ldap_suffix
objectClass: organizationalRole
objectClass: simpleSecurityObject
cn: admin
userPassword: $cryptedpasswd
description: LDAP administrator

dn: ou=People,$ldap_suffix
objectClass: organizationalUnit
ou: People

dn: ou=Roaming,$ldap_suffix
objectCLass: organizationalUnit
ou: Roaming
FIN
}

# Setup SF_robot Passwd
setup_robot() {
    setup_vars
    
    # The first account is only used in a multiserver SF
    check_server
    echo "Adding robot accounts"
    
    { eval "ldapadd -r -c -D 'cn=admin,$ldap_suffix' -x -w'$ldap_passwd' $DEVNULL12" || true ; } <<-FIN
dn: cn=Replicator,$sf_ldap_base_dn
description: Replicator the Robot
objectClass: organizationalRole
objectClass: simpleSecurityObject
userPassword: {CRYPT}xxxxx
cn: Replicator

dn: cn=SF_robot,$sf_ldap_base_dn
description: SF the Robot
objectClass: organizationalRole
objectClass: simpleSecurityObject
userPassword: {CRYPT}xxxxx
cn: SF_robot
FIN
    check_server

    eval "ldapmodify -v -c -D 'cn=admin,$ldap_suffix' -x -w'$ldap_passwd' $DEVNULL12" <<-FIN
dn: cn=SF_robot,$sf_ldap_base_dn
changetype: modify
replace: userPassword
userPassword: $sf_cryptedpasswd
FIN
    check_server
    # echo "Testing LDAP"
    # echo "Changing dummy cn using SF_robot account"
    eval "ldapmodify -v -c -D 'cn=SF_Robot,$sf_ldap_base_dn' -x -w'$sf_ldap_passwd' $DEVNULL12" <<-FIN
dn: uid=dummy,ou=People,$sf_ldap_base_dn
changetype: modify
replace: cn
cn: Dummy User (Tested)
FIN
}

# Main
case "$1" in
    configure-files)
	setup_vars
	# echo "Modifying /etc/ldap/slapd.conf"
	configure_slapd
	# echo "Modifying /etc/libnss-ldap.conf"
	configure_libnss_ldap
	# echo "Modifying /etc/nsswitch.conf"
	configure_nsswitch
	;;
    configure)
	setup_vars
	# Restarting ldap 
	invoke-rc.d slapd restart
	sleep 5		# Sometimes it takes a bit of time to get out of bed
	check_server
	# echo "Load ldap"
	load_ldap
	# echo "Setup SF_robot account"
	setup_robot
	;;
    update)
	setup_vars
	check_server
	load_ldap
	;;
    purge-files)
	setup_vars
	# echo "Purging /etc/ldap/slapd.conf"
	purge_slapd
	# echo "Purging /etc/nsswitch.conf"
	purge_nsswitch
	# echo "Purging /etc/libnss-ldap.conf"
	purge_libnss_ldap
	;;
    purge)
	$0 empty
	;;
    list)
	setup_vars
	check_server
	# Display what is now in the database
	ldapsearch -x -b "$ldap_suffix" '(objectclass=*)' 
	;;
    empty)
	setup_vars
	check_server
	admin_regexp=$(echo $sf_ldap_base_dn | sed 's/, */, */g')
	admin_regexp="^cn=admin, *$admin_regexp"
	get_our_entries () {
	    {		# List candidates...
		/usr/lib/sourceforge/bin/sql2ldif.pl \
		    | grep "^dn:" \
		    | sed 's/^dn: *//' \
		    | grep -v "^dc=" \
		    | grep -v "^ou=" \
		    | grep -v "$admin_regexp"
		/usr/lib/sourceforge/bin/sql2ldif.pl \
		    | grep "^dn:" \
		    | sed 's/^dn: *//' \
		    | grep -v "^dc=" \
		    | grep -v "^ou=People," \
		    | grep -v "^ou=Roaming," \
		    | grep -v "$admin_regexp"
		echo cn=Replicator,$sf_ldap_base_dn
		echo cn=SF_robot,$sf_ldap_base_dn
	    } | sort -u # ...then uniquify that list
	}
	get_our_entries | eval "ldapdelete -D 'cn=admin,$ldap_suffix' -x -w'$ldap_passwd' -c $DEVNULL12" || true
	;;
    reset)
	setup_vars
	invoke-rc.d slapd stop
	rm -f /var/lib/ldap/*.dbb
	invoke-rc.d slapd start
	tmpldif=$(mktemp $tmpfile_pattern)
	print_ldif_default $sf_ldap_base_dn $cryptedpasswd > $tmpldif
	slapadd -l $tmpldif
	rm -f $tmpldif
	;;
    test|check)
	setup_vars
	show_vars
	check_server
	;;
    *)
	echo "Usage: $0 {configure|configure-files|update|purge|purge-files|list|empty|reset|test}"
	exit 1
	;;
esac
