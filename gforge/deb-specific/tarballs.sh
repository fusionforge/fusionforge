#!/bin/sh
CVSROOT=/var/lib/sourceforge/chroot/cvsroot
CVSTARDIR=/var/lib/sourceforge/cvstarballs
if [ $# != 1 ] 
then 
	$0 default
else
	target=$1
	if [  $(id -u) != 0 -a  "x$target" != "xlist" ] #-a "x$target" != "xclean"  ]
	then
	        echo "You must be root to run this, please enter passwd"
	        su -c "$0 $target"
	else
		case "$target" in
			default|configure)
				cd $CVSROOT
				ls | while read dir
				do
					tar czf $CVSTARDIR/${dir}-cvsroot.tar.gz.new ${dir}
					mv $CVSTARDIR/${dir}-cvsroot.tar.gz.new $CVSTARDIR/${dir}-cvsroot.tar.gz
				done
				;;
			update)
				;;
			purge)
				;;
		esac
	fi
fi
