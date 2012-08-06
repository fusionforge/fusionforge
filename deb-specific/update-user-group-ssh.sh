#!/bin/sh
if [ $(id -u) != 0 ] 
then
	echo "You must be root to run this, please enter passwd"
	su -c $0
else
    LOCK=/var/lock/gforge-update-user-group-cvs
    if ! lockfile-create --retry 2 $LOCK ; then
	echo "$0 locked, please try again later."
	exit 1
    fi
    lockfile-touch $LOCK &
    LOCKPID=$!
    trap "kill $LOCKPID ; lockfile-remove $LOCK" exit
        
	# Fill ldap tables
	# Should be safe to comment this soon
	# Be sure the system user are created before creating homes
	# when using nss-ldap
#	[ -x /usr/share/gforge/bin/install-ldap.sh ] && \
#		/usr/share/gforge/bin/install-ldap.sh update > /dev/null 2>&1

	[ -d /var/lib/gforge/dumps ] || \
	mkdir /var/lib/gforge/dumps && \
	chown gforge:gforge /var/lib/gforge/dumps

	/usr/share/gforge/bin/user_dump_update.pl
	/usr/share/gforge/bin/group_dump_update.pl
	/usr/share/gforge/bin/ssh_dump_update.pl
	/usr/share/gforge/bin/mailfwd_update.pl
	#[ -f /usr/share/gforge/bin/cvs_dump.pl ] && su -s /bin/sh gforge -c /usr/share/gforge/bin/cvs_dump.pl || true
	#[ -f /usr/share/gforge/bin/cvs_update.pl ] && /usr/share/gforge/bin/cvs_update.pl || true

	#CB#su gforge -c /usr/share/gforge/bin/dump_database.pl -s /bin/sh
	#CB#su gforge -c /usr/share/gforge/bin/ssh_dump.pl -s /bin/sh

	# Create user, groups and cvs archives
	#CB#/usr/share/gforge/bin/new_parse.pl

	# Fill ssh authorized_keys
	#CB#/usr/share/gforge/bin/ssh_create.pl
fi
