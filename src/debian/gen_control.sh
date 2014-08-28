#!/bin/bash -e
(
    cat debian/control.in
    echo
    for i in $(sed -n 's/^Package: fusionforge-plugin-//p' debian/plugins); do
	sed -n -e '/^#/d' -e "/^Package: fusionforge-plugin-$i/,/^$/p" debian/plugins \
	    | grep -v ^$ \
	    | sed 's/Depends:\(.*\)/Depends: fusionforge-common (=${source:Version}),\1, ${misc:Depends}/'
	echo "Architecture: all"
	php utils/plugin_pkg_desc.php $i deb
	echo
    done
) > debian/control
