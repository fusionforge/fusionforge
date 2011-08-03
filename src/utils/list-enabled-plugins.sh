#! /bin/sh

if [ -e plugins ] ; then
    cd .
elif [ -e ../src/plugins ] ; then
    cd ../src
else
    echo "Couldn't find source directory..."
    exit 1
fi

e=""
d=""

for name in plugins/*/NAME ; do 
    dir=${name%%/NAME}
    plugin=${dir##plugins/}
    if [ -e $dir/packaging/control/[1-9][0-9][0-9]plugin-$plugin ] \
	&& ([ ! -e $dir/etc/$plugin.ini ] || [ "$(confget -f $dir/etc/$plugin.ini plugin_status)" = valid ]) ; then
	e="$e $plugin"
    else
	d="$d $plugin"
    fi
done

if [ "$1" = "--disabled" ] ; then
    echo $d
else
    echo $e
fi
