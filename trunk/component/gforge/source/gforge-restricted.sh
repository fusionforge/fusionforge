#!/bin/sh
if [ $# -ne 1  ]; then
	echo 1>&2 Usage: $0  gforge.company.com
	exit 127
fi

GFORGE_OPT=~/gforge/opt
GFORGE_DATA=~/gforge/data
GFORGE_CFG=~/gforge/etc

JPGRAPH_DIR=~/jpgraph

#validate hostname
echo "$1" | egrep '^([[:alnum:]._-])*$' -q
found_host=$?
if [ $found_host -ne 0 ]; then
	echo 1>&2 "invalid hostname"
	exit 2
fi


mkdir -p $GFORGE_CFG
if [ ! -d $GFORGE_CFG ]; then
	echo 1>&2 "$GFORGE_CFG didn't exist - error - make sure you've got permission"
	exit 2
fi

mkdir -p $GFORGE_OPT
if [ ! -d $GFORGE_OPT ]; then
	echo 1>&2 "$GFORGE_OPT didn't exist - error - make sure you've got permission"
	exit 2
fi
mkdir -p $GFORGE_DATA
if [ ! -d $GFORGE_DATA ]; then
	echo 1>&2 "$GFORGE_DATA didn't exist - error - make sure you've got permission"
	exit 2
fi

cp -r * $GFORGE_OPT

cd $GFORGE_DATA
mkdir uploads
mkdir jpgraph
mkdir scmtarballs
mkdir scmsnapshots
mkdir localizationcache

#Create default location for SVN repositories
mkdir svnroot

cd $GFORGE_OPT

#Create default gforge config files
cp etc/local.inc.example $GFORGE_CFG/local.inc
cp etc/gforge-httpd.conf.example $GFORGE_CFG/httpd.conf

mkdir $GFORGE_CFG/plugins

#copy the scmcvs plugin config to /etc/gforge/
#if [ ! -d $GFORGE_CFG/plugins/scmcvs ]; then
#	mkdir -p $GFORGE_CFG/plugins/scmcvs
#fi
#cp plugins/scmcvs/etc/plugins/scmcvs/config.php $GFORGE_CFG/plugins/scmcvs/config.php

#copy the scmsvn config files to /etc/gforge/
if [ ! -d $GFORGE_CFG/plugins/scmsvn ]; then
	mkdir -p $GFORGE_CFG/plugins/scmsvn
fi
cp plugins/scmsvn/etc/plugins/scmsvn/config.php $GFORGE_CFG/plugins/scmsvn/config.php

#copy the svntracker config files to /etc/gforge/
if [ ! -d $GFORGE_CFG/plugins/svntracker ]; then
	mkdir $GFORGE_CFG/plugins/svntracker
fi
cp plugins/svntracker/etc/plugins/svntracker/config.php $GFORGE_CFG/plugins/svntracker/config.php


#symlink plugin www's
cd $GFORGE_OPT/www

if [ ! -d plugins/ ]; then
	/bin/mkdir plugins
fi

cd plugins

if [ ! -d scmsvn ]; then
	ln -s ../../plugins/scmsvn/www/ scmsvn
fi
if [ ! -d svntracker ]; then
	ln -s ../../plugins/svntracker/www/ svntracker
fi

# create symlink for fckeditor
if [ ! -d fckeditor ]; then
	ln -s ../../plugins/fckeditor/www/ fckeditor
fi

cd $GFORGE_OPT

find $GFORGE_OPT -type d | xargs chmod 700
find $GFORGE_OPT -type f | xargs chmod 600
chmod 711 $GFORGE_OPT
find $GFORGE_OPT/www $GFORGE_OPT/plugins -type d | xargs chmod 711
find $GFORGE_OPT/www $GFORGE_OPT/plugins/*/www -type f | xargs chmod 744
find $GFORGE_OPT/www $GFORGE_OPT/plugins/*/www \( -name \*.php -o -name \*.class.php \) | xargs chmod 700

if [ ! -d $GFORGE_CFG ]; then
	echo 1>&2 "$GFORGE_CFG didn't exist - error - make sure you've got permission"
	exit 2
fi
find $GFORGE_CFG -type d | xargs chmod 700
find $GFORGE_CFG -type f | xargs chmod 600
find $GFORGE_CFG -type f -exec perl -pi -e "s/gforge\.company\.com/$1/" {} \;
find $GFORGE_CFG -type f -exec perl -pi -e "s/192\.168\.100\.100/$2/" {} \;

# echo "noreply:        /dev/null" >> /etc/aliases
