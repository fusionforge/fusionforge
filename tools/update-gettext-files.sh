#! /bin/sh

locales="eu bg ca zh_TW nl en eo fr de el he id it ja ko la nb pl pt_BR pt ru zh_CN es sv th"

# xgettext -j -d gforge -o translations/gforge.pot -L PHP --from-code=iso-8859-1 $(find -name \*.php -or -name \*.class | grep -v -e {arch} -e svn-base)

rm translations/gforge.pot
xgettext -d gforge -o translations/gforge.pot -L PHP --from-code=iso-8859-1 $(find -name \*.php -or -name \*.class | grep -v -e {arch} -e svn-base)

for l in $locales ; do
    echo "Processing $l..."
    msgmerge -U translations/$l.po translations/gforge.pot
done
