#! /bin/sh

config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)
prefix=$data_path/plugins/moinmoin/wikidata
wsgi_user=$(forge_get_config wsgi_user moinmoin)

case "$1" in
    configure)
	for i in data underlay ; do
	    if ! [ -e $prefix/$i ] ; then
		cp -r /usr/share/moin/$i $prefix/
		chown -R $wsgi_user: $prefix/$i
	    fi
	done
	chown $wsgi_user $config_path/config.ini.d/post-install-secrets.ini  # Ewww...
	if ! [ -e $dataprefix/moinmoin.log ] ; then
	    touch $dataprefix/moinmoin.log
	    chown $wsgi_user $dataprefix/moinmoin.log
	fi
	;;
    purge)
	for i in data underlay ; do
	    rm -rf $prefix/$i
	done
	;;
    *)
        echo "Usage: $0 {configure|purge}"
        exit 1
esac
