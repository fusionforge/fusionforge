#! /bin/sh

locales="eu bg ca zh_TW nl en eo fr de el he id it ja ko la nb pl pt_BR pt ru zh_CN es sv th"

if [ -e gforge/translations/gforge.pot ] ; then        # We're in the parent dir
    cd gforge
elif [ -e ../gforge/translations/gforge.pot ] ; then   # We're in tools/ or gforge/
    cd ../gforge
elif [ -e ../translations/gforge.pot ] ; then          # In a subdir of gforge
    cd ..
else
    echo "Couldn't find translations directory..."
    exit 1
fi

if [ "$1" = --stats ] ; then
    mode=stats
else
    mode=refresh
fi

locales=$(echo $locales | xargs -n 1 | sort)

case $mode in
    refresh)
	rm translations/gforge.pot
	
	find -type f -\( -name \*.php -or -name users -or -name projects -\) \
	    | grep -v -e {arch} -e svn-base \
	    | grep -v ^./plugins/wiki \
	    | LANG=C sort \
	    | xargs xgettext -d gforge -o translations/gforge.pot -L PHP --from-code=iso-8859-1    
	    
	    for l in $locales ; do
		echo "Processing $l..."
		msgmerge -U translations/$l.po translations/gforge.pot
	    done
	    ;;

    stats)
	for l in $(echo $locales | xargs -n 1 | sort) ; do
	    printf "* %5s: " $l
	    msgfmt --statistics -o /dev/null translations/$l.po
	done
	;;
esac