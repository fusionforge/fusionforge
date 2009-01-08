#!/bin/sh
#
# %NAME% startup script
# MUST BE RUN BY USER %NAME%

# Source configuration
. %SYSCONFDIR%/sysconfig/%NAME%

RETVAL=1
PIDFILE=$1

if [ "`whoami`" != "%NAME%" ] ; then
	echo "This script must be run by user %NAME%"
else
	if [ -z "$PIDFILE" ] ; then
		echo "PID filename is missing"
	else
		if [ -f $PIDFILE -a -w $PIDFILE ] ; then
			RETVAL=0
		else
			echo "PID file '$PIDFILE' is missing or not writeable"
		fi
	fi
fi
if [ $RETVAL -eq 0 ] ; then
	umask 0027
	sed \
		-e "s/%HTTP_PORT%/$HTTP_PORT/g" \
		%DATADIR%/%NAME%/templates/plexus.xml > %LOCALSTATEDIR%/lib/%NAME%/conf/plexus.xml
	$JAVA_HOME/bin/java \
		-Xmx${JAVA_HEAP}m \
		-classpath %DATADIR%/%NAME%/core/boot/plexus-classworlds-*.jar \
		-Dclassworlds.conf=%LOCALSTATEDIR%/lib/%NAME%/conf/classworlds.conf  \
		-Djava.io.tmpdir=%LOCALSTATEDIR%/lib/%NAME%/temp \
		-Dtools.jar=$JAVA_HOME/lib/tools.jar \
		-Dplexus.home=%LOCALSTATEDIR%/lib/%NAME% \
		-Dplexus.core=%DATADIR%/%NAME%/core \
		-Dplexus.system.path="$PATH" \
		-Dappserver.base=%LOCALSTATEDIR%/lib/%NAME% \
		-Dappserver.home=%LOCALSTATEDIR%/lib/%NAME% \
		org.codehaus.plexus.classworlds.launcher.Launcher >> %LOCALSTATEDIR%/lib/%NAME%/logs/startup.log 2>&1 &
	if [ $? -eq 0 ] ; then
		echo $! > $PIDFILE
	else
		RETVAL=1
	fi
fi
exit $RETVAL
