#! /bin/sh

locales="eu bg ca zh_TW nl en eo fr de el he id it ja ko la nb pl pt_BR pt ru zh_CN es sv th"

for l in $locales ; do
    printf "* %5s: " $l
    msgfmt --statistics -o /dev/null translations/$l.po
done
