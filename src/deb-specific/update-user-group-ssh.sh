#!/bin/sh
if [ $(id -u) != 0 ] 
then
	echo "You must be root to run this, please enter passwd"
	su -c $0
else
    LOCK=/var/lock/gforge-update-user-group-ssh
    if ! lockfile-create --retry 2 $LOCK ; then
	echo "$0 locked, please try again later."
	exit 1
    fi
    lockfile-touch $LOCK &
    LOCKPID=$!
    trap "kill $LOCKPID ; lockfile-remove $LOCK" exit

	[ -d $(forge_get_config data_path)/dumps ] || \
	mkdir $(forge_get_config data_path)/dumps && \
	chown gforge:gforge $(forge_get_config data_path)/dumps

	$(forge_get_config binary_path)/user_dump_update.pl
	$(forge_get_config binary_path)/group_dump_update.pl
	$(forge_get_config binary_path)/ssh_dump_update.pl
	$(forge_get_config binary_path)/mailfwd_update.pl
fi
