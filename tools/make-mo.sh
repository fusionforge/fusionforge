#! /bin/sh

locales="eu bg ca zh_TW nl en eo fr de el he id it ja ko la nb pl pt_BR pt ru zh_CN es sv th"

for l in $locales ; do
    echo "Processing $l..."
    mkdir -p locales/$l/LC_MESSAGES
    msgfmt -o locales/$l/LC_MESSAGES/gforge.mo translations/$l.po
done
