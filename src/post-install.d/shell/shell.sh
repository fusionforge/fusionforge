#! /bin/bash
# Configure NSS+PostgreSQL shell access
#
# Copyright (C) 2014  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

set -e

db_name=$(forge_get_config database_name)
db_user=$(forge_get_config database_user)
db_host=$(forge_get_config database_host)
# homedir_prefix, e.g. /home/users/ (with trailing slash)
homedir_prefix=$(forge_get_config homedir_prefix | sed -e 's:[^/]$:&/:')

db_user_nss=${db_user}_nss

# Distros may want to install new conffiles using tools such as ucf(1)
DESTDIR=$2
mkdir -m 755 -p $DESTDIR/etc/

# Check/Modify /etc/libnss-pgsql.conf
configure_libnss_pgsql(){
    hostconf=''
    case "$db_host" in
	127.*|localhost.*|localhost) ;; # 'local'
	*) hostconf="host=$db_host"  ;; # 'host'
    esac
    if [ -e $DESTDIR/etc/nss-pgsql.conf ]; then return; fi
    cat > $DESTDIR/etc/nss-pgsql.conf <<EOF
### NSS Configuration for FusionForge

#----------------- DB connection
# Use 'trust' authentication, cf. https://bugs.debian.org/551389
connectionstring = user=$db_user_nss dbname=$db_name $hostconf


#----------------- NSS queries
getpwnam        = SELECT login AS username,passwd,gecos,('$homedir_prefix' || login) AS homedir,shell,uid,gid FROM nss_passwd WHERE login = \$1
getpwuid        = SELECT login AS username,passwd,gecos,('$homedir_prefix' || login) AS homedir,shell,uid,gid FROM nss_passwd WHERE uid = \$1
#allusers        = SELECT login AS username,passwd,gecos,('$homedir_prefix' || login) AS homedir,shell,uid,gid FROM nss_passwd
getgroupmembersbygid = SELECT login AS username FROM nss_passwd WHERE gid = \$1
getgrnam = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups WHERE name = \$1
getgrgid = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups WHERE gid = \$1
#allgroups = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups 
groups_dyn = SELECT ug.gid FROM nss_usergroups ug, nss_passwd p WHERE ug.uid = p.uid AND p.login = \$1 AND ug.gid <> \$2
EOF
    if [ -e $DESTDIR/etc/nss-pgsql-root.conf ]; then return; fi
    cat > $DESTDIR/etc/nss-pgsql-root.conf <<EOF
### NSS Configuration for FusionForge

#----------------- DB connection
shadowconnectionstring = user=$db_user_nss dbname=$db_name $hostconf

#----------------- NSS queries
shadowbyname    = SELECT login AS shadow_name, passwd AS shadow_passwd, 14087 AS shadow_lstchg, 0 AS shadow_min, 99999 AS shadow_max, 7 AS shadow_warn, '' AS shadow_inact, '' AS shadow_expire, '' AS shadow_flag FROM nss_passwd WHERE login = \$1
shadow          = SELECT login AS shadow_name, passwd AS shadow_passwd, 14087 AS shadow_lstchg, 0 AS shadow_min, 99999 AS shadow_max, 7 AS shadow_warn, '' AS shadow_inact, '' AS shadow_expire, '' AS shadow_flag FROM nss_passwd
EOF

    chmod 644 $DESTDIR/etc/nss-pgsql.conf
    chmod 600 $DESTDIR/etc/nss-pgsql-root.conf
    chown root:root $DESTDIR/etc/nss-pgsql-root.conf
}

purge_libnss_pgsql(){
    rm -f /etc/nss-pgsql.conf /etc/nss-pgsql-root.conf
}

# Modify /etc/nsswitch.conf
# Not using UCF since we're sed-ing an existing file
configure_nsswitch()
{
    if ! grep -q '^passwd:.*pgsql' /etc/nsswitch.conf ; then
	perl -pi -e "s/^(passwd:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf
    fi
    if ! grep -q '^group:.*pgsql' /etc/nsswitch.conf ; then
	perl -pi -e "s/^(group:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf
    fi
    if ! grep -q '^shadow:.*pgsql' /etc/nsswitch.conf ; then
	perl -pi -e "s/^(shadow:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by GForge install\n#Comment by GForge install#\1\2/gs" /etc/nsswitch.conf
    fi
}

# Revert /etc/nsswitch.conf
remove_nsswitch()
{
    sed -i -e '/^.*#Added by GForge install/d' /etc/nsswitch.conf
    sed -i -e 's/#Comment by GForge install#//' /etc/nsswitch.conf
}

configure_nscd()
{
    if [ -e /etc/redhat-release ]; then
	chkconfig nscd on
	service nscd start
    fi
}

# Main
case "$1" in
    configure)
	configure_libnss_pgsql
	configure_nsswitch
	configure_nscd
	;;
    remove)
	remove_nsswitch
	;;
    purge)
	# note: can't be called from Debian's postrm - rely on ucfq(1)
	purge_libnss_pgsql
	;;
    *)
	echo "Usage: $0 {configure|remove|purge}"
	exit 1
	;;
esac
