#!/bin/bash -e
# scmhg post-install

source $(forge_get_config source_path)/post-install.d/common/service.inc

plugindir=$(forge_get_config plugins_path)/scmhg
hgwebcgi=$(ls -1 /usr/share/doc/mercurial-*/hgweb.cgi /usr/share/doc/mercurial/examples/hgweb.cgi 2>/dev/null | tail -1)
hgtemplatesdir=$(ls -1d /usr/lib*/python*/site-packages/mercurial/templates /usr/share/mercurial/templates 2>/dev/null | tail -1)

case "$1" in
    configure)
	# hgweb
	if [ -z "$hgwebcgi" -o -z "$hgtemplatesdir" ]; then echo "Cannot find required directories"; exit 1; fi
	ln -nfs $hgwebcgi                 $plugindir/cgi-bin/
	ln -nfs $plugindir/etc/fflog.tmpl $hgtemplatesdir
	;;
    remove)
	rm -rf $plugindir/cgi-bin/
	rm -rf $hgtemplatesdir/fflog.tmpl
	find $plugindir/www/ -type l -print0 | xargs -r0 rm
	;;
    *)
        echo "Usage: $0 {configure|remove}"
        exit 1
esac
