#!/bin/bash -e
# gitweb post-install

plugindir=$(forge_get_config source_path)/plugins/scmgit

gitwebcgi=$(ls /var/www/git/gitweb.cgi /var/www/gitweb-caching/gitweb.cgi \
    /usr/lib/cgi-bin/gitweb.cgi /usr/share/gitweb/gitweb.cgi 2>/dev/null | tail -1)
gitwebdir=$(ls -d /var/www/git /var/www/gitweb-caching /usr/share/gitweb/static 2>/dev/null | tail -1)
# CentOS: /var/www/git/
# Debian, openSUSE: /usr/share/gitweb/

case "$1" in
    configure)
	if [ -z "$gitwebcgi" -o -z "$gitwebdir" ]; then echo "Cannot find gitweb"; exit 1; fi
	mkdir -p -m 755 $plugindir/cgi-bin/
	ln -nfs $gitwebcgi                 $plugindir/cgi-bin/
	ln -nfs $gitwebdir/git-favicon.png $plugindir/www/
	ln -nfs $gitwebdir/git-logo.png    $plugindir/www/
	ln -nfs $gitwebdir/gitweb.css      $plugindir/www/
	ln -nfs $gitwebdir/gitweb.js       $plugindir/www/
	;;
    remove)
	rm -rf $plugindir/cgi-bin/
	find $plugindir/www/ -type l -print0 | xargs -r0 rm
	;;
    *)
        echo "Usage: $0 {configure|remove}"
        exit 1
esac
