#!/bin/sh
if [ -e src/translations/fusionforge.pot ] ; then        # We're in the parent dir
    cd src
elif [ -e ../src/translations/fusionforge.pot ] ; then   # We're in tools/ or src/
    cd ../src
elif [ -e ../translations/fusionforge.pot ] ; then       # In a subdir of src/
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
