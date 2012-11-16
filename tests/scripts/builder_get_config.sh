#! /bin/sh

# Setup Env
relativepath=`dirname $0`
absolutetestspath=`cd $relativepath/..; pwd`
cd $absolutetestspath

# Read defaults and hostname specific
. config/default
if [ -f config/`hostname` ]
then
      	. config/`hostname`
fi

case $1 in
	BUILDERDIR)
		BUILDERDIR=${BUILDERDIR:-$HOME/builder/}
		echo $BUILDERDIR
		;;
	REPOPATH)
		WORKDIR=`cd $absolutetestspath/../..; pwd`
		# Jenkins will set WORKSPACE
		WORKSPACE=${WORKSPACE:-$WORKDIR}
		REPOPATH=$WORKSPACE/build
		echo $REPOPATH
		;;
esac
