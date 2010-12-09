#!/bin/sh

modelfullname=HelloWorld
modelminus=`echo $modelfullname | tr '[A-Z]' '[a-z]'`
modelplugdir=$modelminus

usage() {
	echo Usage: $0 PluginName
	echo
	echo " where 'PluginName' should be in CamelCase : mix of upper and lower-case characters"
}

echo "Plugin template creator"
if [ "$#" != "1" ] 
then
	usage
else
	fullname=$1
	minus=`echo $1 | tr '[A-Z]' '[a-z]'`
	plugdir=$minus
	echo "Creating $1 plugin"
	echo "Creating directory $plugdir"
	[ ! -d $plugdir ] && mkdir $plugdir
	[ ! -d $plugdir/bin ] && mkdir $plugdir/bin
	[ ! -d $plugdir/etc/plugins/$minus ] && mkdir -p $plugdir/etc/plugins/$minus
	[ ! -d $plugdir/common/languages ] && mkdir -p $plugdir/common/languages
	[ ! -d $plugdir/www ] && mkdir $plugdir/www

	if [ ! -f $plugdir/common/${fullname}Plugin.class.php ]
	then
		echo Creating $plugdir/common/${fullname}Plugin.class.php
		cat $modelplugdir/common/${modelfullname}Plugin.class.php | \
		sed "s/$modelminus/$minus/g" | \
		sed "s/$modelfullname/$fullname/g" > \
		$plugdir/common/${fullname}Plugin.class.php
	fi
	if [ ! -f $plugdir/common/$minus-init.php ]
	then
		echo Creating $plugdir/common/$minus-init.php
		cat $modelplugdir/common/$modelminus-init.php | \
		sed "s/$modelminus/$minus/g" | \
		sed "s/$modelfullname/$fullname/g" > \
		$plugdir/common/$minus-init.php
	fi
	if [ ! -f $plugdir/www/index.php ]
	then
		echo Creating $plugdir/www/index.php
		cat $modelplugdir/www/index.php | \
		sed "s/$modelminus/$minus/g" | \
		sed "s/$modelfullname/$fullname/g" > \
		$plugdir/www/index.php
	fi
	if [ ! -f $plugdir/INSTALL ]
	then
		echo Creating $plugdir/INSTALL
		cat $modelplugdir/INSTALL | \
		sed "s/$modelminus/$minus/g" > \
		$plugdir/INSTALL
	fi
	if [ ! -f $plugdir/etc/plugins/$minus/config.php ]
	then
		echo Creating $plugdir/etc/plugins/$minus/config.php
		cp $modelplugdir/etc/plugins/$modelminus/config.php $plugdir/etc/plugins/$minus/config.php
	fi
#	if [ ! -f $plugdir/common/languages/Base.tab ]
#	then
#		echo Creating $plugdir/common/languages/Base.tab
#		cat $modelplugdir/common/languages/Base.tab | \
#		sed "s/$modelminus/$minus/g" | \
#		sed "s/$modelfullname/$fullname/g" > \
#		$plugdir/common/languages/Base.tab
#	fi
fi
