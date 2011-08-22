#! /bin/sh

# Lists which plugins are enabled or disabled.

# Takes into account the 'plugin_status = valid' values if the plugin's etc/pluginname.ini file exists

if [ -e plugins ] ; then
    cd .
elif [ -e ../src/plugins ] ; then
    cd ../src
else
    echo "Couldn't find source directory..."
    exit 1
fi

enabled=""
disabled=""

for name in plugins/*/NAME ; do 
    dir=${name%%/NAME}
    plugin=${dir##plugins/}
    if [ -e $dir/packaging/control/[1-9][0-9][0-9]plugin-$plugin ] ; then
	if [ ! -e $dir/etc/$plugin.ini ] ; then
	    enabled="$enabled $plugin"
	else
	    # confget returns litteral semi-colons after values, so get rid of comments
	    if [ "$(confget -f $dir/etc/$plugin.ini plugin_status | sed -r 's/[ ^t]*;.*//g')" = "valid" ] ; then
		enabled="$enabled $plugin"
	    else
		disabled="$disabled $plugin"
	    fi
	fi
    else
	disabled="$disabled $plugin"
    fi
done

if [ "$1" = "--disabled" ] ; then
    echo $disabled
else
    echo $enabled
fi
