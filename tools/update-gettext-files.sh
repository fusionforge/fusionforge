#! /bin/sh

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
    utils/manage-translations.sh stats
else
    utils/manage-translations.sh refresh
fi
