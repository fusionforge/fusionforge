#!/bin/sh
if [ $(id -u) != 0 ] 
then
	echo "You must be root to run this, please enter passwd"
	su -c $0
else
	# Fill ldap tables
	/usr/lib/gforge/bin/install-ldap.sh update > /dev/null 2>&1

	[ -d /var/lib/gforge/dumps ] || \
	mkdir /var/lib/gforge/dumps && \
	chown gforge:gforge /var/lib/gforge/dumps
	su gforge -c /usr/lib/gforge/bin/dump_database.pl -s /bin/sh
	su gforge -c /usr/lib/gforge/bin/ssh_dump.pl -s /bin/sh

	# Create user, groups and cvs archives
	/usr/lib/gforge/bin/new_parse.pl

	# Fill ssh authorized_keys
	/usr/lib/gforge/bin/ssh_create.pl
fi
