#! /bin/bash
#
# Configure LDAP for GForge
# Christian Bayle, Roland Mas
# Initially written for debian-sf (Sourceforge for Debian)
# Adapted as time went by for Gforge

set -e

# This is purely for compatibility, and will be removed sometime
if [ "$DEBSFDEBUG" = 1 ] ; then
    GFORGEDEBUG=1
fi

if [ "$GFORGEDEBUG" != 1 ] ; then
    DEVNULL12="> /dev/null 2>&1"
    DEVNULL2="2> /dev/null"
else
    set -x
fi

if [  $(id -u) != 0 -a  "x$1" != "xlist" ] ; then
	echo "You must be root to run this, please enter passwd"
	exec su -c "$0 $1"
fi

PATH=$PATH:/usr/sbin

setup_vars() {
    ldap_host=$(grep ^ldap_host= /etc/fusionforge/fusionforge.conf | cut -d= -f2-)

    gforge_base_dn=$(grep ^ldap_base_dn= /etc/fusionforge/fusionforge.conf | cut -d= -f2-)
    gforge_admin_dn="cn=admin,$gforge_base_dn"
    slapd_base_dn=$(grep ^suffix /etc/ldap/slapd.conf | cut -d\" -f2)
    slapd_admin_dn="cn=admin,$slapd_base_dn"
    robot_dn="cn=SF_robot,$gforge_base_dn"

    robot_passwd=$(grep ^ldap_web_add_password= /etc/fusionforge/fusionforge.conf | cut -d= -f2-)
    robot_cryptedpasswd=`slappasswd -s "$robot_passwd" -h {CRYPT}`
    # TODO: ask the user for the main (slapd) password
    # Probably only do that when needed (when inserting the robot account)
    [ -f /etc/ldap.secret ] && slapd_admin_passwd=$(cat /etc/ldap.secret) || slapd_admin_passwd=$robot_passwd

    cryptedpasswd=`slappasswd -s "$slapd_admin_passwd" -h {CRYPT}`

    tmpfile_pattern=/tmp/$(basename $0).XXXXXX
}

show_vars() {
    echo "slapd_base_dn      = '$slapd_base_dn'"
    echo "gforge_base_dn     = '$gforge_base_dn'"
    echo "slapd_admin_dn     = '$slapd_admin_dn'"
    echo "slapd_admin_passwd = '$slapd_admin_passwd'"
    echo "cryptedpasswd      = '$cryptedpasswd'"
    echo "tmpfile_pattern    = '$tmpfile_pattern'"
}

check_base_dn() {
    server_base_dn=$(eval "ldapsearch -x -b '' -s base '(objectclass=*)' namingContexts $DEVNULL2" | grep "namingContexts:" | cut -d" " -f2)
    echo "gforge_base_dn = $gforge_base_dn"
    echo "server_base_dn = $server_base_dn"
    if echo $gforge_base_dn | grep -q "$server_base_dn\$" ; then
	echo Gforge base DN is under the existing server base DN -- OK
    else
	echo Gforge base DN is *not* under the existing server base DN -- fail
	exit 2
    fi

    addon=$(echo $gforge_base_dn | sed "s/$server_base_dn\$//")
    echo "addon = $addon"
    if [ -z "$addon" ] ; then
	echo Gforge base DN is equal to server base DN -- OK
	return 0
    elif [ -z $(echo $addon | cut -d, -f2-) ] ; then
	echo Gforge base DN is just a level under the server base DN -- OK
	return 0
    else
	echo Gforge base DN is at least two levels under the server base DN -- continuing investigations
    fi

    needednc=$(echo $gforge_base_dn | cut -d, -f2-)
    if slapcat | grep -q "dn: $needednc" ; then
	echo Found existing object in which to create our directory -- OK
    else
	echo No existing object in which to create our directory -- fail
	exit 3
    fi
}

exists_dn() {
    my_dn=$1
    r=$(
	nr=$(ldapsearch -LLL -x -b "$my_dn" -s base '' dn 2> /dev/null | grep ^dn: | wc -l)
	echo "$nr:${PIPESTATUS[0]}"
	exit 0
    )
    nr=$(echo $r | cut -d: -f1)
    p=$(echo $r | cut -d: -f2)
    if [ $p == 32 ] || [ $nr = 0 ] ; then
	return 1
    else
	return 0
    fi
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

# Check admin password
check_password() {
    tmpcheckpwd=$(mktemp $tmpfile_pattern)
    if ldapsearch -D $slapd_admin_dn -x -w$slapd_admin_passwd -n $slapd_admin_dn > $tmpcheckpwd 2>&1 ; then
	echo "Password checked OK." > /dev/null
    else
	if grep -q "ldap_bind: Invalid credentials" $tmpcheckpwd ; then
	    rm $tmpcheckpwd
	    exit 5		# Wrong password
	else
	    rm $tmpcheckpwd
	    exit 99		# Unknown error
	fi
    fi
}

# Should I do something for /etc/pam_ldap.conf ?
modify_pam_ldap(){
    echo -n
    # echo "Nothing to do"
}

# Check/Modify /etc/libnss-ldap.conf
configure_libnss_ldap(){
    cp -a /etc/libnss-ldap.conf /etc/libnss-ldap.conf.gforge-new
    # Check if DN is correct
    if ! grep -q "^base[ 	]*$sf_ldap_dn" /etc/libnss-ldap.conf.gforge-new ; then
	echo "WARNING: Probably incorrect base line in /etc/libnss-ldap.conf"
	grep "^base" /etc/libnss-ldap.conf
	echo "Should be: base $gforge_base_dn"
    fi
    # Check bindpw
    # Should contain the secret
    # All users can see ldap stored gid/uid
    chmod 644 /etc/libnss-ldap.conf.gforge-new
    # It doesn't seem to be necessary, only rootbinddn is necessary
    #	if ! grep -q "^bindpw" /etc/libnss-ldap.conf ; then
    #		echo "# Next line added by GForge install" >>/etc/libnss-ldap.conf
    #		echo "bindpw secret" >>/etc/libnss-ldap.conf
    #	fi
    # Check rootbinddn
    # This seems to be necessary to display uid/gid
    # Should be cn=admin,dc=...
    if ! grep -q "^rootbinddn" /etc/libnss-ldap.conf.gforge-new ; then
	echo "# Next line added by GForge install" >>/etc/libnss-ldap.conf.gforge-new
	echo "rootbinddn $gforge_admin_dn" >>/etc/libnss-ldap.conf.gforge-new
    fi
}

# Purge /etc/libnss-ldap.conf
purge_libnss_ldap(){
    cp -a /etc/libnss-ldap.conf /etc/libnss-ldap.conf.gforge-new
    perl -pi -e "s/^# Next line added by GForge install\n/#SF#/g" /etc/libnss-ldap.conf.gforge-new
    perl -pi -e "s/^#SF#.*\n//g" /etc/libnss-ldap.conf.gforge-new
}

# Modify /etc/ldap/slapd.conf
configure_slapd(){
    if [ ! -e /etc/ldap/slapd.conf ] ; then
	echo "ERROR: You don't have a /etc/ldap/slapd.conf file."
	echo "Please make sure your slapd package is correctly configured."
	exit 1
    fi
    cp -a /etc/ldap/slapd.conf /etc/ldap/slapd.conf.gforge-new
    purge_slapd_new

    # Maybe should comment referral line too
    echo "WARNING: Please check referal line in /etc/ldap/slapd.conf"

    # Debian config by default only include core schema
    if ! grep -q "GForge" /etc/ldap/slapd.conf.gforge-new ; then
	tmpfile=$(mktemp $tmpfile_pattern)
	for schema in /etc/ldap/schema/core.schema \
	    /etc/ldap/schema/cosine.schema \
	    /etc/ldap/schema/inetorgperson.schema \
	    /etc/ldap/schema/nis.schema \
	    /etc/gforge/gforge.schema
	  do
	  if ! grep -q "^include[ 	]*$schema" /etc/ldap/slapd.conf.gforge-new ; then
	      echo "include	$schema	#Added by GForge install" >> $tmpfile
	      # echo "Adding $schema"
	  else
	      # echo "Commenting $schema"
	      perl -pi -e "s(^include[ 	]*$schema)(#Comment by GForge install#include	$schema)g" /etc/ldap/slapd.conf.gforge-new
	      echo "include	$schema	#Added by GForge install" >> $tmpfile
	      # echo "Adding $schema"
	  fi
	done

	cat /etc/ldap/slapd.conf.gforge-new >> $tmpfile
	cat $tmpfile > /etc/ldap/slapd.conf.gforge-new
	rm -f $tmpfile

	# Then write access for SF_robot
	perl -pi -e "s/access to attribute=userPassword/# Next second line added by GForge install
access to attribute=userPassword
	by dn=\"$robot_dn\" write/" /etc/ldap/slapd.conf.gforge-new

	# odd looking regex makes sure it doesnt match the comment 'access to *'
	perl -pi -e "s/(?<!')access to \*(?!')/# Next lines added by GForge install
access to dn.subtree=\"ou=People,$gforge_base_dn\"
	by dn=\"$gforge_admin_dn\" write
	by dn=\"$robot_dn\" write
        by dn=\"$slapd_admin_dn\" write
	by * read
access to dn.subtree=\"ou=Group,$gforge_base_dn\"
	by dn=\"$gforge_admin_dn\" write
	by dn=\"$robot_dn\" write
        by dn=\"$slapd_admin_dn\" write
	by * read
access to dn.subtree=\"ou=mailingList,$gforge_base_dn\"
	by dn=\"$gforge_admin_dn\" write
	by dn=\"$robot_dn\" write
        by dn=\"$slapd_admin_dn\" write
	by * read
access to dn.subtree=\"ou=cvsGroup,$gforge_base_dn\"
	by dn=\"$gforge_admin_dn\" write
	by dn=\"$robot_dn\" write
        by dn=\"$slapd_admin_dn\" write
	by * read
# End of gforge add
access to */" /etc/ldap/slapd.conf.gforge-new

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
    cp -a /etc/ldap/slapd.conf /etc/ldap/slapd.conf.gforge-new
    purge_slapd_new
}

purge_slapd_new(){
    perl -pi -e "s/^.*#Added by GForge install\n//" /etc/ldap/slapd.conf.gforge-new
    perl -pi -e "s/#Comment by GForge install#//" /etc/ldap/slapd.conf.gforge-new
    if grep -q "# Next second line added by GForge install" /etc/ldap/slapd.conf.gforge-new ; then
	vi -e /etc/ldap/slapd.conf.gforge-new <<-FIN
/# Next second line added by GForge install
:d
/SF_robot
:d
:w
:x
FIN
    fi
    if grep -q "Next lines added by GForge install" /etc/ldap/slapd.conf.gforge-new ; then
	vi -e /etc/ldap/slapd.conf.gforge-new <<-FIN
/# Next lines added by GForge install
:ma a
/# End of gforge add
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
    cp -a /etc/nsswitch.conf /etc/nsswitch.conf.gforge-new
    # This is sensitive file
    # By security i let priority to files
    # Should maybe enhance this to take in account nis
    # Maybe ask the order db/files/nis/ldap
    if ! grep -q '^passwd:.*ldap' /etc/nsswitch.conf.gforge-new ; then
	perl -pi -e "s/^(passwd:[^#\n]*)([^\n]*)/\1 ldap \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf.gforge-new
    fi
    if ! grep -q '^group:.*ldap' /etc/nsswitch.conf.gforge-new ; then
	perl -pi -e "s/^(group:[^#\n]*)([^\n]*)/\1 ldap \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf.gforge-new
    fi
    if ! grep -q '^shadow:.*ldap' /etc/nsswitch.conf.gforge-new ; then
	perl -pi -e "s/^(shadow:[^#\n]*)([^\n]*)/\1 ldap \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf.gforge-new
    fi
}

# Purge /etc/nsswitch.conf
purge_nsswitch()
{
    cp -a /etc/nsswitch.conf /etc/nsswitch.conf.gforge-new
    perl -pi -e "s/^[^\n]*#Added by GForge install\n//" /etc/nsswitch.conf.gforge-new
    perl -pi -e "s/#Comment by GForge install#//" /etc/nsswitch.conf.gforge-new
}

# Load ldap database from gforge database
load_ldap(){
    # First, let's make sure our base DN exists
    if ! exists_dn $gforge_base_dn ; then
	tmpldif=$(mktemp $tmpfile_pattern)
	tmpldifadd=$(mktemp $tmpfile_pattern)
	tmpldifmod=$(mktemp $tmpfile_pattern)
	dc=$(echo $gforge_base_dn | cut -d, -f1 | cut -d= -f2)
#dc: $dc
	cat >> $tmpldif <<EOF
dn: $sys_ldap_base_dn
objectClass: top
objectClass: domain
objectClass: domainRelatedObject
associatedDomain: $sys_default_domain
EOF
        # echo "Filling LDAP with database"
	if ! eval "ldapadd -r -c -D '$robot_dn' -x -w'$robot_passwd' -f $tmpldif > $tmpldifadd 2>&1" ; then
            # Some entries could not be added (already there?)
            # Therefore, we try to modify them
	    if ! eval "ldapmodify -r -c -D '$robot_dn' -x -w'$robot_passwd' -f $tmpldif > $tmpldifmod 2>&1" ; then
		echo "WARNING WARNING WARNING Something wrong happened in ldapmodify"
		echo "please check and report following error"
		echo ========================================================================================
		cat $tmpldifmod | perl -pi -e 's/^\n//' | perl -pi -e 's/modifying.*\"\n//'
		echo ========================================================================================
		echo SEE ALSO result of ldapadd in:
		echo $tmpldifadd
		echo AND result of ldapmodify in:
		echo $tmpldifmod
		echo AND ldif file in:
		echo $tmpldif
		echo ========================================================================================
		exit 99
	    fi
	fi
	rm -f $tmpldif $tmpldifadd $tmpldifmod
    fi

    # CLEANUP: should be done with the robot
    # This loads the ldap database
    # echo "Our base DN is $gforge_base_dn"
    # echo "Creating ldif file from database"
    tmpldif=$(mktemp $tmpfile_pattern)
    tmpldifadd=$(mktemp $tmpfile_pattern)
    tmpldifmod=$(mktemp $tmpfile_pattern)
    dc=$(echo $gforge_base_dn | cut -d, -f1 | cut -d= -f2)
    su -s /bin/sh gforge -c /usr/share/gforge/bin/sql2ldif.pl >> $tmpldif
    # echo "Filling LDAP with database"
    if ! eval "ldapadd -r -c -D '$robot_dn' -x -w'$robot_passwd' -f $tmpldif > $tmpldifadd 2>&1" ; then
        # Some entries could not be added (already there)
        # Therefore, we have to modify them
	if ! eval "ldapmodify -r -c -D '$robot_dn' -x -w'$robot_passwd' -f $tmpldif > $tmpldifmod 2>&1" ; then
	    echo "WARNING WARNING WARNING Something wrong happened in ldapmodify"
	    echo "please check and report following error"
	    echo ========================================================================================
	    cat $tmpldifmod | perl -pi -e 's/^\n//' | perl -pi -e 's/modifying.*\"\n//'
	    echo ========================================================================================
	    echo SEE ALSO result of ldapadd in:
	    echo $tmpldifadd
	    echo AND result of ldapmodify in:
	    echo $tmpldifmod
	    echo AND ldif file in:
	    echo $tmpldif
	    echo ========================================================================================
	    exit 99
	fi
    fi
    rm -f $tmpldif $tmpldifadd $tmpldifmod
}

print_ldif_default(){
    dc=`echo $slapd_base_dn | sed 's/dc=\(.[^,]*\),.*/\1/'`
#dc: $dc
    cat <<-FIN
dn: $slapd_base_dn
objectClass: dcObject
objectClass: domain

dn: cn=admin,$slapd_base_dn
objectClass: organizationalRole
objectClass: simpleSecurityObject
cn: admin
userPassword: $cryptedpasswd
description: LDAP administrator

dn: ou=People,$slapd_base_dn
objectClass: organizationalUnit
ou: People

dn: ou=Roaming,$slapd_base_dn
objectCLass: organizationalUnit
ou: Roaming
FIN
}

# Setup SF_robot
setup_robot() {
    setup_vars

    # The first account is only used in a multiserver SF
    check_server
    if ! exists_dn "$robot_dn" || ! exists_dn "ou=People,$gforge_base_dn" ; then
	check_password
	echo "Adding robot accounts and sub-trees"
	dc=$(echo $gforge_base_dn | cut -d, -f1 | cut -d= -f2)
	tmpldif=$(mktemp $tmpfile_pattern)
	tmpldifadd=$(mktemp $tmpfile_pattern)
	tmpldifmod=$(mktemp $tmpfile_pattern)
#dc: $dc
	cat > $tmpldif <<-FIN
dn: $gforge_base_dn
objectClass: organization

dn: ou=People,$gforge_base_dn
ou: People
objectClass: organizationalUnit

dn: ou=Aliases,$gforge_base_dn
ou: Aliases
objectClass: organizationalUnit

dn: ou=Group,$gforge_base_dn
ou: Group
objectClass: organizationalUnit

dn: ou=cvsGroup,$gforge_base_dn
ou: cvsGroup
objectClass: organizationalUnit

dn: ou=mailingList,$gforge_base_dn
ou: mailingList
objectClass: organizationalUnit

dn: cn=Replicator,$gforge_base_dn
description: Replicator the Robot
objectClass: organizationalRole
objectClass: simpleSecurityObject
userPassword: {CRYPT}xxxxx
cn: Replicator

dn: $robot_dn
description: SF the Robot
objectClass: organizationalRole
objectClass: simpleSecurityObject
userPassword: $robot_cryptedpasswd
cn: SF_robot

dn: uid=dummy,ou=People,$gforge_base_dn
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
uidNumber: 9999
gidNumber: 9999
homeDirectory: /tmp
gecos: Dummy User

FIN
	
	if ! eval "ldapadd -r -c -D '$slapd_admin_dn' -x -w'$slapd_admin_passwd' -f $tmpldif > $tmpldifadd 2>&1" ; then
	    if ! eval "ldapmodify -r -c -D '$slapd_admin_dn' -x -w'$slapd_admin_passwd' -f $tmpldif > $tmpldifadd 2>&1" ; then
		echo "WARNING WARNING WARNING Something wrong happened when setting up the robot"
		echo "please check and report following error"
		echo ========================================================================================
		cat $tmpldifmod | perl -pi -e 's/^\n//' | perl -pi -e 's/modifying.*\"\n//'
		echo ========================================================================================
		echo SEE ALSO result of ldapadd in:
		echo $tmpldifadd
		echo AND result of ldapmodify in:
		echo $tmpldifmod
		echo AND ldif file in:
		echo $tmpldif
		echo ========================================================================================
		exit 99
	    fi
	fi
    else
	echo "Robot accounts already present, not adding"
    fi

    check_server
    # echo "Testing LDAP"
    if ! exists_dn uid=dummy,ou=People,$gforge_base_dn ; then
	# echo "Adding dummy user"
	eval "ldapadd -v -c -D '$robot_dn' -x -w'$robot_passwd' $DEVNULL12" <<-FIN
dn: uid=dummy,ou=People,$gforge_base_dn
objectClass: posixAccount
objectClass: debGforgeAccount
objectClass: account
cn: Dummy User (untested)
uid: dummy
uidNumber: 9999
gidNumber: 9999
homeDirectory: /tmp
loginShell: /bin/false
FIN
    fi
    # echo "Changing dummy cn using SF_robot account"
    eval "ldapmodify -v -c -D '$robot_dn' -x -w'$robot_passwd' $DEVNULL12" <<-FIN
dn: uid=dummy,ou=People,$gforge_base_dn
changetype: modify
replace: cn
cn: Dummy User (tested)
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
    	# Don't try to use ldap if config file is not changed
    	if grep -q "GForge" /etc/ldap/slapd.conf ; then
		setup_vars
		# Restarting ldap
		invoke-rc.d slapd restart
		sleep 5		# Sometimes it takes a bit of time to get out of bed
		check_server
		check_base_dn
		# echo "Setup SF_robot account"
		setup_robot
		# echo "Load ldap"
		load_ldap
	else
		echo "Not configuring until slapd is configured"
	fi
	;;
    update)
    	# Don't try to use ldap if config file is not changed
    	if grep -q "GForge" /etc/ldap/slapd.conf ; then
		setup_vars
		check_server
		load_ldap
	else
		echo "Not updating until slapd is configured"
	fi
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
	ldapsearch -x -b "$slapd_base_dn" '(objectclass=*)'
	;;
    empty)
	setup_vars
	check_server
	admin_regexp=$(echo $gforge_base_dn | sed 's/, */, /g')
	admin_regexp="^cn=admin, *$admin_regexp"
	get_our_entries () {
	    {		# List candidates...
		/usr/share/gforge/bin/sql2ldif.pl \
		    | grep "^dn:" \
		    | sed 's/^dn: *//' \
		    | grep -v "^dc=" \
		    | grep -v "^ou=" \
		    | grep -v "$admin_regexp"
		/usr/share/gforge/bin/sql2ldif.pl \
		    | grep "^dn:" \
		    | sed 's/^dn: *//' \
		    | grep -v "^dc=" \
		    | grep -v "^ou=People," \
		    | grep -v "^ou=Roaming," \
		    | grep -v "$admin_regexp"
		echo cn=Replicator,$gforge_base_dn
		echo $robot_dn
	    } | sort -u # ...then uniquify that list
	}
	check_password
	get_our_entries | eval "ldapdelete -D '$slapd_admin_dn' -x -w'$slapd_admin_passwd' -c $DEVNULL12" || true
	;;
    reset)
	setup_vars
	invoke-rc.d slapd stop
	rm -f /var/lib/ldap/*.dbb
	invoke-rc.d slapd start
	tmpldif=$(mktemp $tmpfile_pattern)
	print_ldif_default $gforge_base_dn $cryptedpasswd > $tmpldif
	slapadd -l $tmpldif
	rm -f $tmpldif
	;;
    test|check)
	setup_vars
	show_vars
	check_server
	;;
    setup)
    	$0 configure-files
	$0 configure
	cp /etc/ldap/slapd.conf /etc/ldap/slapd.conf.gforge-old
	cp /etc/libnss-ldap.conf /etc/libnss-ldap.conf.gforge-old
	cp /etc/nsswitch.conf.gforge /etc/nsswitch.conf.gforge-old
	mv /etc/ldap/slapd.conf.gforge-new /etc/ldap/slapd.conf
	mv /etc/libnss-ldap.conf.gforge-new /etc/libnss-ldap.conf
	mv /etc/nsswitch.conf.gforge-new /etc/nsswitch.conf
	;;
    cleanup)
	$0 purge-files
	$0 purge
	cp /etc/ldap/slapd.conf /etc/ldap/slapd.conf.gforge-old
	cp /etc/libnss-ldap.conf /etc/libnss-ldap.conf.gforge-old
	cp /etc/nsswitch.conf.gforge /etc/nsswitch.conf.gforge-old
	mv /etc/ldap/slapd.conf.gforge-new /etc/ldap/slapd.conf
	mv /etc/libnss-ldap.conf.gforge-new /etc/libnss-ldap.conf
	mv /etc/nsswitch.conf.gforge-new /etc/nsswitch.conf
	;;
    *)
	echo "Usage: $0 {configure|configure-files|update|purge|purge-files|list|empty|reset|test|setup|cleanup}"
	exit 1
	;;
esac
