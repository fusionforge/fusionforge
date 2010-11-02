#! /bin/bash
#
# UPDATE LDAP FROM GFORGE DATABASE
# Copy and Past Coding from install-ldap.sh

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
    admin_passwd=$(grep ^admin_password= /etc/fusionforge/fusionforge.conf | cut -d= -f2-)
    robot_cryptedpasswd=`slappasswd -s "$robot_passwd" -h {CRYPT}`
    # TODO: ask the user for the main (slapd) password
    # Probably only do that when needed (when inserting the robot account)
    [ -f /etc/ldap.secret ] && slapd_admin_passwd=$(cat /etc/ldap.secret) || slapd_admin_passwd=$robot_passwd

    cryptedpasswd=`slappasswd -s "$slapd_admin_passwd" -h {CRYPT}`

    tmpfile_pattern=/tmp/$(basename $0).XXXXXX
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



# Load ldap database from gforge database
load_ldap(){
sys_ldap_base_dn="dc=fb14srv1,dc=hpi,dc=uni-potsdam,dc=de";

    # First, let's make sure our base DN exists
    if ! exists_dn $gforge_base_dn ; then
	tmpldif=$(mktemp $tmpfile_pattern)
	tmpldifadd=$(mktemp $tmpfile_pattern)
	tmpldifmod=$(mktemp $tmpfile_pattern)
	dc=$(echo $gforge_base_dn | cut -d, -f1 | cut -d= -f2)
#dc: $dc
	cat >> $tmpldif <<EOF
dn: $sldap_base_dn
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
    tmpladd=$(mktemp $tmpfile_pattern)
    tmplmod=$(mktemp $tmpfile_pattern)
    tmpldifadd=$(mktemp $tmpfile_pattern)
    tmpldifmod=$(mktemp $tmpfile_pattern)
    dc=$(echo $gforge_base_dn | cut -d, -f1 | cut -d= -f2)
    su -s /bin/sh gforge -c /usr/lib/gforge/bin/sql2ldifmod.pl >> $tmplmod
    su -s /bin/sh gforge -c /usr/lib/gforge/bin/sql2ldifadd.pl >> $tmpladd
    # echo "Filling LDAP with database"
    if ! eval "ldapadd -r -c -D '$slapd_admin_dn' -x -w'$admin_passwd' -f $tmpladd > $tmpldifadd 2>&1" ; then
	# Some entries could not be added (already there)
        # Therefore, we have to modify them
	if ! eval "ldapmodify -r -c -D '$slapd_admin_dn' -x -w'$admin_passwd' -f $tmplmod > $tmpldifmod 2>&1" ; then
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
    rm -f $tmpldif $tmpldifadd $tmpldifmod $tmpladd $tmplmod
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

# Main
setup_vars
check_server
load_ldap

