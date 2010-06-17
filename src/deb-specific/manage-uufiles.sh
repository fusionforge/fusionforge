#! /bin/sh

case "$1" in
    clean)
	UUFILES=$(find -name *.uu)
	for i in $UUFILES ; do
	    d=$(dirname $i)
	    j=$(basename $i .uu)
	    if [ -e $d/$j ] ; then
		rm $d/$j
	    fi
	done
	;;
    decode)
	UUFILES=$(find -name *.uu)
	for i in $UUFILES ; do
	    d=$(dirname $i)
	    j=$(basename $i .uu)
	    uudecode -o $d/$j $i
	done
	;;
    *)
	echo "Usage: $0 {decode|clean}"
	exit 1
	;;
esac
