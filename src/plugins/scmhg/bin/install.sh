#!/bin/bash -e
# hgweb post-install

source $(forge_get_config source_path)/post-install.d/common/service.inc

plugindir=$(forge_get_config plugins_path)/scmhg
hgwebcgi=$(ls /usr/share/doc/mercurial-*/hgweb.cgi 2>/dev/null | tail -1)
hgwebdir=$(ls -d /usr/share/doc/mercurial-* 2>/dev/null | tail -1)

case "$1" in
    configure)
	# hgweb
	if [ -z "$hgwebcgi" -o -z "$hgwebdir" ]; then echo "Cannot find gitweb"; exit 1; fi
	mkdir -p -m 755 $plugindir/cgi-bin/
	ln -nfs $hgwebcgi                 $plugindir/cgi-bin/
	;;
    remove)
	rm -rf $plugindir/cgi-bin/
	find $plugindir/www/ -type l -print0 | xargs -r0 rm
	;;
    *)
        echo "Usage: $0 {configure|remove}"
        exit 1
esac
