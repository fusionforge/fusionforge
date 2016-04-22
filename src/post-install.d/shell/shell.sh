#! /bin/bash
# Configure NSS+PostgreSQL shell access
#
# Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
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

. $(forge_get_config source_path)/post-install.d/common/service.inc

db_name=$(forge_get_config database_name)
db_user=$(forge_get_config database_user)
db_host=$(forge_get_config database_host)
# homedir_prefix, e.g. /home/users/ (with trailing slash)
homedir_prefix=$(forge_get_config homedir_prefix | sed -e 's:[^/]$:&/:')
system_user_ssh_akc=$(forge_get_config system_user_ssh_akc)

db_user_nss=${db_user}_nss


# Distros may want to install new conffiles using tools such as ucf(1)
DESTDIR=$3
mkdir -m 755 -p $DESTDIR/etc/

# Check/Modify /etc/libnss-pgsql.conf
configure_libnss_pgsql(){
    hostconf=''
    case "$db_host" in
	127.*|localhost.*|localhost) ;; # 'local'
	*) hostconf="host=$db_host"  ;; # 'host'
    esac
    if [ ! -s $DESTDIR/etc/nss-pgsql.conf ]; then
	gid=$(forge_get_config users_default_gid)
	cat > $DESTDIR/etc/nss-pgsql.conf <<EOF
### NSS Configuration for FusionForge

#----------------- DB connection
# Use 'trust' authentication, cf. https://bugs.debian.org/551389
connectionstring = user=$db_user_nss dbname=$db_name $hostconf


#----------------- NSS queries
getpwnam        = SELECT login AS username,passwd,gecos,('$homedir_prefix' || login) AS homedir,shell,uid,$gid FROM nss_passwd WHERE login = \$1
getpwuid        = SELECT login AS username,passwd,gecos,('$homedir_prefix' || login) AS homedir,shell,uid,$gid FROM nss_passwd WHERE uid = \$1
#allusers        = SELECT login AS username,passwd,gecos,('$homedir_prefix' || login) AS homedir,shell,uid,$gid FROM nss_passwd
getgroupmembersbygid = SELECT login AS username FROM nss_passwd WHERE $gid = \$1
getgrnam = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups WHERE name = \$1
getgrgid = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups WHERE gid = \$1
#allgroups = SELECT name AS groupname,'x',gid,ARRAY(SELECT user_name FROM nss_usergroups WHERE nss_usergroups.gid = nss_groups.gid) AS members FROM nss_groups 
groups_dyn = SELECT ug.gid FROM nss_usergroups ug, nss_passwd p WHERE ug.uid = p.uid AND p.login = \$1 AND ug.gid <> \$2
EOF
    fi
    if [ ! -s $DESTDIR/etc/nss-pgsql-root.conf ]; then
	cat > $DESTDIR/etc/nss-pgsql-root.conf <<EOF
### NSS Configuration for FusionForge

#----------------- DB connection
shadowconnectionstring = user=$db_user_nss dbname=$db_name $hostconf

#----------------- NSS queries
shadowbyname    = SELECT login AS shadow_name, passwd AS shadow_passwd, 14087 AS shadow_lstchg, 0 AS shadow_min, 99999 AS shadow_max, 7 AS shadow_warn, '' AS shadow_inact, '' AS shadow_expire, '' AS shadow_flag FROM nss_passwd WHERE login = \$1
shadow          = SELECT login AS shadow_name, passwd AS shadow_passwd, 14087 AS shadow_lstchg, 0 AS shadow_min, 99999 AS shadow_max, 7 AS shadow_warn, '' AS shadow_inact, '' AS shadow_expire, '' AS shadow_flag FROM nss_passwd
EOF
    fi

    chmod 644 $DESTDIR/etc/nss-pgsql.conf
    chmod 600 $DESTDIR/etc/nss-pgsql-root.conf
    chown root:root $DESTDIR/etc/nss-pgsql-root.conf
}

purge_libnss_pgsql(){
    rm -f /etc/nss-pgsql.conf /etc/nss-pgsql-root.conf
}

configure_pam() {
    # Collaborative umask 0022 -> 0002
    if ! grep -q '^session\s*optional\s*pam_umask.so.*' /etc/pam.d/sshd; then
	echo 'session    optional     pam_umask.so umask=002  # FusionForge' >> /etc/pam.d/sshd
    fi
}

remove_pam() {
    sed -i -e '/.* # FusionForge/d' /etc/pam.d/sshd
}

# Modify /etc/nsswitch.conf
# Not using UCF since we're sed-ing an existing file
configure_nsswitch()
{
    if ! grep -q '^passwd:.*pgsql' /etc/nsswitch.conf ; then
	perl -pi -e "s/^(passwd:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by FusionForge install\n#Comment by FusionForge install#\1\2/gs" /etc/nsswitch.conf
    fi
    if ! grep -q '^group:.*pgsql' /etc/nsswitch.conf ; then
	perl -pi -e "s/^(group:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by FusionForge install\n#Comment by FusionForge install#\1\2/gs" /etc/nsswitch.conf
    fi
    if ! grep -q '^shadow:.*pgsql' /etc/nsswitch.conf ; then
	perl -pi -e "s/^(shadow:[^#\n]*)([^\n]*)/\1 pgsql \2#Added by FusionForge install\n#Comment by FusionForge install#\1\2/gs" /etc/nsswitch.conf
    fi
}

# Revert /etc/nsswitch.conf
remove_nsswitch()
{
    sed -i -e '/^.*#Added by FusionForge install/d' /etc/nsswitch.conf
    sed -i -e 's/#Comment by FusionForge install#//' /etc/nsswitch.conf
}

configure_nscd()
{
    if [ -e /etc/redhat-release ]; then
	chkconfig nscd on
	service nscd start
    fi
}

configure_sshd()
{
    if ! getent passwd ${system_user_ssh_akc} >/dev/null; then
	useradd ${system_user_ssh_akc} -s /bin/false -M -d /nonexistent
    fi
    
    # Deal with CentOS 6's early patch
    user_cmd=AuthorizedKeysCommandUser
    if [ -f /etc/redhat-release ]; then
	os_version=$(rpm -q --qf "%{VERSION}" $(rpm -q --whatprovides redhat-release))
	if [ "$os_version" = "6" ]; then
	    user_cmd=AuthorizedKeysCommandRunAs
	fi
    fi
    # Add placeholder if necessary
    if ! grep -qw '^AuthorizedKeysCommand' /etc/ssh/sshd_config; then
	echo 'AuthorizedKeysCommand replace_me' >> /etc/ssh/sshd_config
    fi
    if ! grep -qw "^$user_cmd" /etc/ssh/sshd_config; then
	echo "$user_cmd replace_me" >> /etc/ssh/sshd_config
    fi
    # Configure SSH daemon
    cmd=$(forge_get_config source_path)/bin/ssh_akc.php
    sed -i -e "s,^AuthorizedKeysCommand .*,AuthorizedKeysCommand $cmd," /etc/ssh/sshd_config
    sed -i -e "s,^$user_cmd .*,$user_cmd ${system_user_ssh_akc}," /etc/ssh/sshd_config

    chown ${system_user_ssh_akc} \
	$(forge_get_config config_path)/config.ini.d/post-install-secrets-ssh_akc.ini

    # Fix "Unsafe AuthorizedKeysCommand: bad ownership or modes for directory /usr/local/share"
    dir=$cmd
    while [ "$dir" != '/' ]; do
	dir=$(dirname $dir)
	if [ -n "$(find $dir -maxdepth 0 -perm -g+w)" ]; then chmod g-w $dir; fi
    done

    service $(forge_get_config ssh_service) restart
}

remove_sshd()
{
    sed -i -e "/^AuthorizedKeysCommand.*/d" /etc/ssh/sshd_config
    userdel $system_user_ssh_akc
}


# Main
case "$1" in
    configure)
	$(dirname $0)/upgrade-conf.sh $2
	configure_libnss_pgsql
	configure_nsswitch
	configure_nscd
	configure_pam
	configure_sshd
	;;
    remove)
	remove_nsswitch
	remove_pam
	remove_sshd
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
