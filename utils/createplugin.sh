#!/bin/sh

# TODO missing copyright

#set -x

path=$(dirname $0)
modelfullname=HelloWorld
modelminus=`echo $modelfullname | tr '[A-Z]' '[a-z]'`
modelplugdir=$path/../plugins/$modelminus
dopackage=0

usage() {
	echo Usage: $0 [--dopackage] PluginName
}

echo "Plugin template creator"
if [ $# -eq 0 ] 
then
	usage
else
	case $1 in 
		--dopackage)
			dopackage=1
			shift
			;;
		*)
			;;
	esac
	fullname=$1
	minus=`echo $1 | tr '[A-Z]' '[a-z]'`
	plugdir=$minus
	current_dir=`pwd`
	if [ "`basename $current_dir`" != "plugins" ]; then
	    echo "Please launch the script from withing the src/plugins/ dir"
	    exit 1
	fi
	[ ! -d $modelplugdir/debian/fusionforge-plugin-$modelminus ] || (cd $modelplugdir ; debclean)
	echo "Creating $1 plugin"
	echo "Creating directory $plugdir"
	[ ! -d $plugdir ] && mkdir $plugdir
	(cd $modelplugdir;find bin;find etc;find common;find include;find www;find utils;find db;find cronjobs;find tests; find translations; find README; find NAME)|sort|while read debfile
	do
		if [ -d $modelplugdir/$debfile ]
		then
			newdebdir=`echo $debfile | sed "s/$modelminus/$minus/g"`
			if [ ! -d $plugdir/$newdebdir ]
			then
				echo "Making directory $plugdir/$newdebdir" ; mkdir $plugdir/$newdebdir
			fi
		else
			newdebfile=`echo $debfile | sed "s/$modelminus/$minus/g"`
			if [ ! -f $plugdir/$newdebfile ]
			then
				echo "Creating $plugdir/$newdebfile"
				cat $modelplugdir/$debfile | \
					sed "s/$modelminus/$minus/g" | \
					sed "s/$modelfullname/$fullname/g" > \
				$plugdir/$newdebfile
			fi
		fi
	done

	if [ $dopackage -ne 0 ]
	then
		echo "Doing package"
		chmod +x $plugdir/utils/*
		chmod +x $plugdir/bin/*
		(cd $modelplugdir;find debian;find packaging)|sort|while read debfile
		do
			if [ -d $modelplugdir/$debfile ]
			then
				newdebdir=`echo $debfile | sed "s/$modelminus/$minus/g"`
				[ -d $plugdir/$newdebdir ] || (echo "Making directory $plugdir/$newdebdir" ; mkdir $plugdir/$newdebdir)
			else
				newdebfile=`echo $debfile | sed "s/$modelminus/$minus/g"`
				if [ ! -f $plugdir/$newdebfile ]
				then
					echo "Creating $plugdir/$newdebfile"
					cat $modelplugdir/$debfile | \
						sed "s/$modelminus/$minus/g" | \
						sed "s/$modelfullname/$fullname/g" > \
					$plugdir/$newdebfile
				fi
			fi
		done
	fi
fi

