#
# Regular cron jobs for the helloworld package
#
0 4	* * *	root	[ -x /usr/bin/helloworld_maintenance ] && /usr/bin/helloworld_maintenance
