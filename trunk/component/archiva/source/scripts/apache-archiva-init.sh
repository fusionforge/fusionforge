#!/bin/sh
#
# %NAME%	Startup script for the Apache Archiva Server
#
# chkconfig:	- 85 15
# description:	Apache Archiva is an extensible repository management software
# pidfile:	%LOCALSTATEDIR%//run/%NAME%.pid


# Source function library
. %INITRDDIR%/functions

prog=%NAME%
PIDFILE=%LOCALSTATEDIR%/run/$prog.pid
ARCHIVA_USER=%NAME%
ARCHIVA_GROUP=%NAME%
RETVAL=0

start() {
	echo -n $"Starting $prog: "
	RETVAL=0
	if [ -f $PIDFILE ] ; then
		ARCHIVA_PID=`cat $PIDFILE`
		if [ -n "$ARCHIVA_PID" -a -d /proc/$ARCHIVA_PID ] ; then
			echo "$prog is already running"
			failure $"$prog startup"
			RETVAL=1
		else
			rm -f $PIDFILE
		fi
	fi
	if [ $RETVAL -eq 0 ] ; then
		touch $PIDFILE
		chmod 644 $PIDFILE
		chown $ARCHIVA_USER.root $PIDFILE
		touch %LOCALSTATEDIR%/lib/%NAME%/logs/startup.log
		chown $ARCHIVA_USER.$ARCHIVA_GROUP %LOCALSTATEDIR%/lib/%NAME%/logs/startup.log
		chmod 0640 %LOCALSTATEDIR%/lib/%NAME%/logs/startup.log
		su - $ARCHIVA_USER -c "%DATADIR%/%NAME%/bin/startup $PIDFILE" >> %LOCALSTATEDIR%/lib/%NAME%/logs/startup.log 2>&1
		RETVAL=$?
		if [ $RETVAL -eq 0 ] ; then
			chown root.root $PIDFILE
			success $"$prog startup"
		else
			rm -f $PIDFILE
			failure $"$prog startup"
		fi
	fi
	echo
}

stop() {
	echo -n $"Stopping $prog: "
	killproc $prog
	echo
	RETVAL=$?
}

status() {
	echo -n $"Status of $prog: "
	if [ -f $PIDFILE ] ; then
		ARCHIVA_PID=`cat $PIDFILE`
		if [ -n "$ARCHIVA_PID" -a -d /proc/$ARCHIVA_PID ] ; then
			echo "running with PID $ARCHIVA_PID"
		else
			echo "not running, but PID file '$PIDFILE' exists"
		fi
	else
		echo "not running, or PID file '$PIDFILE' has been removed"
	fi
	RETVAL=0
}

case "$1" in
	start)
		start
		;;
	stop)
		stop
		;;
	restart)
		stop
		start
		;;
	status)
		status
		;;
	*)
		echo "Usage: $0 {start|stop|restart|status}"
		RETVAL=1
esac
exit $RETVAL
