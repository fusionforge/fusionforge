#! /bin/bash
#
# $Id$
#
# Configure LDAP for GForge
# Christian Bayle, Roland Mas
# Initially written for debian-sf (Sourceforge for Debian)
# Adapted as time went by for Gforge

set -e

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
    db_host=$(grep ^db_host= /etc/gforge/gforge.conf | cut -d= -f2-)
    db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
    db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
    db_password=$(grep ^db_password= /etc/gforge/gforge.conf | cut -d= -f2-)

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

# Should I do something for /etc/pam_pgsql.conf ?
modify_pam_pgsql(){
    echo -n
    # echo "Nothing to do"
}

# Check/Modify /etc/libnss-ldap.conf
configure_libnss_pgsql(){
    # All users can see ldap stored gid/uid
    cat > /etc/nss-pgsql.conf.gforge-new <<EOF
host            = $db_host
port            = 5432
database        = $db_name
login           = gforge_nss
passwd          = ''
passwdtable     = nss_passwd
grouptable      = nss_groups
groupmembertable = nss_passwd JOIN nss_usergroups ON nss_passwd.uid=nss_usergroups.uid JOIN nss_groups ON nss_usergroups.gid=nss_groups.gid

passwd_name     = login
passwd_passwd   = passwd
passwd_uid      = uid
passwd_dir      = homedir
passwd_shell    = shell
passwd_gecos    = gecos
passwd_gid      = gid

group_name      = name
group_passwd    = passwd
group_gid       = gid
group_member    = login
EOF
    chmod 644 /etc/nss-pgsql.conf.gforge-new
}

# Purge /etc/nss-pgsql.conf
purge_libnss_pgsql(){
    cp -a /etc/nss-pgsql.conf /etc/nss-pgsql.conf.gforge-new
    perl -pi -e "s/^# Next line added by GForge install\n/#SF#/g" /etc/nss-pgsql.conf.gforge-new
    perl -pi -e "s/^#SF#.*\n//g" /etc/nss-pgsql.conf.gforge-new
}

# Modify /etc/nsswitch.conf
configure_nsswitch()
{
    cp -a /etc/nsswitch.conf /etc/nsswitch.conf.gforge-new
    # This is sensitive file
    # By security i let priority to files
    # Should maybe enhance this to take in account nis
    # Maybe ask the order db/files/nis/pgsql
    if ! grep -q '^passwd:.*pgsql' /etc/nsswitch.conf.gforge-new ; then
	perl -pi -e "s/^(passwd:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf.gforge-new
    fi
    if ! grep -q '^group:.*pgsql' /etc/nsswitch.conf.gforge-new ; then
	perl -pi -e "s/^(group:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf.gforge-new
    fi
    if ! grep -q '^shadow:.*pgsql' /etc/nsswitch.conf.gforge-new ; then
	perl -pi -e "s/^(shadow:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf.gforge-new
    fi
}

# Purge /etc/nsswitch.conf
purge_nsswitch()
{
    cp -a /etc/nsswitch.conf /etc/nsswitch.conf.gforge-new
    perl -pi -e "s/^[^\n]*#Added by GForge install\n//" /etc/nsswitch.conf.gforge-new
    perl -pi -e "s/#Comment by GForge install#//" /etc/nsswitch.conf.gforge-new
}

# Main
case "$1" in
    configure-files)
	setup_vars
	# echo "Modifying /etc/nss-pgsql.conf"
	configure_libnss_pgsql
	# echo "Modifying /etc/nsswitch.conf"
	configure_nsswitch
	;;
    configure)
	;;
    purge-files)
	setup_vars
	# echo "Purging /etc/nsswitch.conf"
	purge_nsswitch
	# echo "Purging /etc/nss-pgsql.conf"
	purge_libnss_pgsql
	;;
    test|check)
	setup_vars
	show_vars
	check_server
	;;
    setup)
    	$0 configure-files
	$0 configure
	cp /etc/nss-pgsql.conf /etc/nss-pgsql.conf.gforge-old
	cp /etc/nsswitch.conf.gforge /etc/nsswitch.conf.gforge-old
	mv /etc/nss-pgsql.conf.gforge-new /etc/nss-pgsql.conf
	mv /etc/nsswitch.conf.gforge-new /etc/nsswitch.conf
	;;
    cleanup)
	$0 purge-files
	cp /etc/nss-pgsql.conf /etc/nss-pgsql.conf.gforge-old
	cp /etc/nsswitch.conf.gforge /etc/nsswitch.conf.gforge-old
	mv /etc/nss-pgsql.conf.gforge-new /etc/nss-pgsql.conf
	mv /etc/nsswitch.conf.gforge-new /etc/nsswitch.conf
	;;
    *)
	echo "Usage: $0 {configure|configure-files|purge-files|test|setup|cleanup}"
	exit 1
	;;
esac
