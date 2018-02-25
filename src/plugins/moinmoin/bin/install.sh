#!/bin/bash -e

config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)
wikidataprefix=$data_path/plugins/moinmoin/wikidata
wsgi_user=$(forge_get_config wsgi_user moinmoin)

case "$1" in
    configure)
	for i in data underlay ; do
	    if ! [ -e $wikidataprefix/$i ] ; then
		cp -r /usr/share/moin/$i $wikidataprefix/
		chown -R $wsgi_user: $wikidataprefix/$i
	    fi
	done
	if [ -e /etc/centos-release ] ; then
	    ln -sf /usr/lib/python2.7/site-packages/MoinMoin/web/static/htdocs /usr/share/moin/
	fi
	chown $wsgi_user $config_path/config.ini.d/post-install-secrets.ini  # Ewww...
	if ! [ -e $wikidataprefix/moinmoin.log ] ; then
	    touch $wikidataprefix/moinmoin.log
	    chown $wsgi_user $wikidataprefix/moinmoin.log
	fi
	;;
    remove)
	for i in data underlay ; do
	    rm -rf $wikidataprefix/$i
	done
	;;
    *)
        echo "Usage: $0 {configure|remove}"
        exit 1
esac
