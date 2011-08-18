#! /bin/bash
#
# Configure NSS for PostGreSQL for GForge
# Christian Bayle, Roland Mas
# Initially written for debian-sf (Sourceforge for Debian)
# Adapted as time went by for Gforge then for FusionForge

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
    db_name=$(/usr/share/gforge/bin/forge_get_config database_name)
    db_user=$(/usr/share/gforge/bin/forge_get_config database_user)
    db_host=$(/usr/share/gforge/bin/forge_get_config database_host)
    
    db_user_nss=${db_user}_nss

    tmpfile_pattern=/tmp/$(basename $0).XXXXXX
}

# Should I do something for /etc/pam_pgsql.conf ?
modify_pam_pgsql(){
    echo -n
    # echo "Nothing to do"
}

# Check/Modify /etc/libnss-pgsql.conf
configure_libnss_pgsql(){
    cat > /etc/nss-pgsql.conf.gforge-new <<EOF
### NSS Configuration for Gforge

#----------------- DB connection
connectionstring = user=$db_user_nss dbname=$db_name hostaddr=127.0.0.1


#----------------- NSS queries
getpwnam        = SELECT login AS username,passwd,gecos,('/var/lib/gforge/chroot/home/users/' || login) AS homedir,shell,uid,gid FROM nss_passwd WHERE login = \$1
getpwuid        = SELECT login AS username,passwd,gecos,('/var/lib/gforge/chroot/home/users/' || login) AS homedir,shell,uid,gid FROM nss_passwd WHERE uid = \$1
#allusers        = SELECT login AS username,passwd,gecos,('/var/lib/gforge/chroot/home/users/' || login) AS homedir,shell,uid,gid FROM nss_passwd
getgroupmembersbygid = SELECT login AS username FROM nss_passwd WHERE gid = \$1
getgrnam = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups WHERE name = \$1
getgrgid = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups WHERE gid = \$1
#allgroups = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups 
groups_dyn = SELECT ug.gid FROM nss_usergroups ug, nss_passwd p WHERE ug.uid = p.uid AND p.login = \$1 AND ug.gid <> \$2
EOF
    cat > /etc/nss-pgsql-root.conf.gforge-new <<EOF
### NSS Configuration for Gforge

#----------------- DB connection
shadowconnectionstring = user=$sys_dbuser_nss dbname=$db_name hostaddr=127.0.0.1

#----------------- NSS queries
shadowbyname    = SELECT login AS shadow_name, passwd AS shadow_passwd, 14087 AS shadow_lstchg, 0 AS shadow_min, 99999 AS shadow_max, 7 AS shadow_warn, '' AS shadow_inact, '' AS shadow_expire, '' AS shadow_flag FROM nss_passwd WHERE login = \$1
shadow          = SELECT login AS shadow_name, passwd AS shadow_passwd, 14087 AS shadow_lstchg, 0 AS shadow_min, 99999 AS shadow_max, 7 AS shadow_warn, '' AS shadow_inact, '' AS shadow_expire, '' AS shadow_flag FROM nss_passwd
EOF

    chmod 644 /etc/nss-pgsql.conf.gforge-new
    chmod 600 /etc/nss-pgsql-root.conf.gforge-new
    chown root:root /etc/nss-pgsql-root.conf.gforge-new
}

# Purge /etc/nss-pgsql.conf
purge_libnss_pgsql(){
    echo -n > /etc/nss-pgsql.conf.gforge-new
    echo -n > /etc/nss-pgsql-root.conf.gforge-new
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
	# echo "Modifying /etc/nss-pgsql.conf and /etc/nss-pgsql-root.conf"
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
	# echo "Purging /etc/nss-pgsql.conf and /etc/nss-pgsql-root.conf"
	purge_libnss_pgsql
	;;
    test|check)
	setup_vars
	check_server
	;;
    setup)
    $0 configure-files
	$0 configure
	if [ -f /etc/nss-pgsql.conf ] ; then
		cp /etc/nss-pgsql.conf /etc/nss-pgsql.conf.gforge-old
	fi
	if [ -f /etc/nss-pgsql-root.conf ] ; then
		cp /etc/nss-pgsql-root.conf /etc/nss-pgsql-root.conf.gforge-old
	fi
	if [ -f /etc/nsswitch.conf ] ; then
		cp /etc/nsswitch.conf /etc/nsswitch.conf.gforge-old
	fi
	mv /etc/nss-pgsql.conf.gforge-new /etc/nss-pgsql.conf
	mv /etc/nss-pgsql-root.conf.gforge-new /etc/nss-pgsql-root.conf
	mv /etc/nsswitch.conf.gforge-new /etc/nsswitch.conf
	;;
    cleanup)
	$0 purge-files
	cp /etc/nss-pgsql.conf /etc/nss-pgsql.conf.gforge-old
	cp /etc/nss-pgsql-root.conf /etc/nss-pgsql-root.conf.gforge-old
	cp /etc/nsswitch.conf.gforge /etc/nsswitch.conf.gforge-old
	mv /etc/nss-pgsql.conf.gforge-new /etc/nss-pgsql.conf
	mv /etc/nss-pgsql-root.conf.gforge-new /etc/nss-pgsql-root.conf
	mv /etc/nsswitch.conf.gforge-new /etc/nsswitch.conf
	;;
    *)
	echo "Usage: $0 {configure|configure-files|purge-files|test|setup|cleanup}"
	exit 1
	;;
esac
