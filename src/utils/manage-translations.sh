#!/bin/sh -e
if [ -e src/translations/gforge.pot ] ; then        # We're in the parent dir
    cd src
elif [ -e translations/gforge.pot ] ; then             # probably in src/ (or a renamed src/)
    cd . # do nothing, but shell syntax requires an instruction in a then-block
elif [ -e ../src/translations/gforge.pot ] ; then   # in tools/ or tests/ or something
    cd ../src
elif [ -e ../translations/gforge.pot ] ; then       # In a subdir of src/
    cd ..
else
    echo "Couldn't find translations directory..."
    exit 1
fi

locales=$(ls translations/*.po \
    | xargs -n1 -iFILE basename FILE .po \
    | egrep '^[a-z][a-z](_[A-Z][A-Z]$)?' \
    | sort)

print_stats () {
    for l in $(echo $locales | xargs -n 1 | sort) ; do
	printf "* %5s: " $l
	msgfmt --statistics -o /dev/null translations/$l.po
    done
}

check_syntax () {
    for l in $(echo $locales | xargs -n 1 | sort) ; do
	msgfmt -c -o /dev/null translations/$l.po
    done
}

case $1 in
    stats)
	print_stats
	;;
    check)
	check_syntax
	;;
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
    build)
	for l in $locales ; do
	    mkdir -p locales/$l/LC_MESSAGES
	    msgfmt -o locales/$l/LC_MESSAGES/gforge.mo translations/$l.po
	done
	;;
    *)
	echo "Unknown operation"
	exit 1
	;;
esac
