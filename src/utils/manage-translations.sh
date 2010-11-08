#!/bin/sh -e

if [ -e src/translations/fusionforge.pot ] ; then        # We're in the parent dir
    cd src
elif [ -e translations/fusionforge.pot ] ; then             # probably in src/ (or a renamed src/)
    cd . # do nothing, but shell syntax requires an instruction in a then-block
elif [ -e ../src/translations/fusionforge.pot ] ; then   # in tools/ or tests/ or something
    cd ../src
elif [ -e ../translations/fusionforge.pot ] ; then       # In a subdir of src/
    cd ..
else
    echo "Couldn't find translations directory..."
    exit 1
fi

locales=$(cd translations; ls *.po | sed 's/.po$//' | sort)

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
	rm translations/fusionforge.pot
	
	find . -type f \( -name \*.php -or -name users -or -name projects \) \
	    | grep -v -e {arch} -e svn-base \
	    | grep -v ^./plugins/wiki \
	    | LANG=C sort \
	    | xargs xgettext -d fusionforge -o translations/fusionforge.pot -L PHP --from-code=utf-8
	    
	for l in $locales ; do
	    echo "Processing $l..."
	    msgmerge -U translations/$l.po translations/fusionforge.pot
	done
	;;
    build)
	for l in $locales ; do
	    mkdir -p locales/$l/LC_MESSAGES
	    msgfmt -o locales/$l/LC_MESSAGES/fusionforge.mo translations/$l.po
	done
	;;
    *)
	echo "Unknown operation"
	exit 1
	;;
esac
