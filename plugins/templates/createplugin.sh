#!/bin/sh

modelfullname=HelloWorld
modelminus=`echo $modelfullname | tr '[A-Z]' '[a-z]'`
modelplugdir=$modelminus
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
	[ ! -d $modelplugdir/debian/fusionforge-plugin-$modelminus ] || (cd $modelplugdir ; debclean)
	echo "Creating $1 plugin"
	echo "Creating directory $plugdir"
	[ ! -d $plugdir ] && mkdir $plugdir
	(cd $modelplugdir;find bin;find etc;find common;find include;find www;find utils;find db;find cronjobs;find tests; find translations)|sort|while read debfile
	do
		if [ -d $modelminus/$debfile ]
		then
			newdebdir=`echo $debfile | sed "s/$modelminus/$minus/g"`
			[ -d $plugdir/$newdebdir ] || (echo "Making directory $plugdir/$newdebdir" ; mkdir $plugdir/$newdebdir)
		else
			newdebfile=`echo $debfile | sed "s/$modelminus/$minus/g"`
			if [ ! -f $plugdir/$newdebfile ]
			then
				echo "Creating $plugdir/$newdebfile"
				cat $modelminus/$debfile | \
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
			if [ -d $modelminus/$debfile ]
			then
				newdebdir=`echo $debfile | sed "s/$modelminus/$minus/g"`
				[ -d $plugdir/$newdebdir ] || (echo "Making directory $plugdir/$newdebdir" ; mkdir $plugdir/$newdebdir)
			else
				newdebfile=`echo $debfile | sed "s/$modelminus/$minus/g"`
				if [ ! -f $plugdir/$newdebfile ]
				then
					echo "Creating $plugdir/$newdebfile"
					cat $modelminus/$debfile | \
						sed "s/$modelminus/$minus/g" | \
						sed "s/$modelfullname/$fullname/g" > \
					$plugdir/$newdebfile
				fi
			fi
		done
	fi
fi

