#!/bin/sh
if [ $(id -u) != 0 ] 
then
	echo "You must be root to run this, please enter passwd"
	su -c $0
else
	[ -d /var/lib/sourceforge/dumps ] || \
	mkdir /var/lib/sourceforge/dumps && \
	chown sourceforge:sourceforge /var/lib/sourceforge/dumps
	chsh -s /bin/bash sourceforge
	su sourceforge -c /usr/lib/sourceforge/bin/dump_database.pl
	su sourceforge -c /usr/lib/sourceforge/bin/ssh_dump.pl
	chsh -s /bin/false sourceforge
	# Create user, groups and cvs archives
	/usr/lib/sourceforge/bin/new_parse.pl
	# Fill ssh authorized_keys
	/usr/lib/sourceforge/bin/ssh_create.pl
	# Fill ldap tables
	/usr/lib/sourceforge/bin/install-ldap.sh update
fi

