#! /bin/sh
### BEGIN INIT INFO
# Provides:          @OLDPACKAGE@-plugin-scmbzr
# Required-Start:    $local_fs $remote_fs $network
# Required-Stop:     $local_fs $remote_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
### END INIT INFO
#
# Init script for @OLDPACKAGE@-plugin-scmbzr Debian package.
# Based on the script provided by loggerhead.

PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
DESC="Loggerhead Bazaar Branch Browser for @FORGENAME@"
NAME=@OLDPACKAGE@-plugin-scmbzr
PIDFILE=/var/run/$NAME.pid
SCRIPTNAME=/etc/init.d/$NAME

# Gracefully exit if the package has been removed.
[ -x /usr/bin/serve-branches ] || exit 0

# Check if configuration file is present
[ ! -f /etc/@OLDPACKAGE@/plugins/scmbzr/serve-branches.conf ] && exit 0

. /etc/@OLDPACKAGE@/plugins/scmbzr/serve-branches.conf

#
#	Function that starts the daemon/service.
#
d_start() {
    start-stop-daemon -p $PIDFILE -S --startas /usr/bin/serve-branches --chuid loggerhead --make-pidfile --background --chdir $served_branches -- --prefix=$prefix --port=$port --log-folder /var/log/loggerhead 2>/dev/null
}

#
#	Function that stops the daemon/service.
#
d_stop() {
	start-stop-daemon -p $PIDFILE -K
}


case "$1" in
  start)
	echo -n "Starting $DESC: $NAME"
	d_start
	echo "."
	;;
  stop)
	echo -n "Stopping $DESC: $NAME"
	d_stop
	echo "."
	;;
  restart|force-reload)
	echo -n "Restarting $DESC: $NAME"
	d_stop
	sleep 1
	d_start
	echo "."
	;;
  *)
	echo "Usage: $SCRIPTNAME {start|stop|restart|force-reload}" >&2
	exit 1
	;;
esac

exit 0
