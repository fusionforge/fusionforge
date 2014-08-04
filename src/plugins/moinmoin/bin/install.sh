#! /bin/sh

data_path=$(forge_get_config data_path)
prefix=$data_path/plugins/moinmoin/wikidata

case "$1" in
    configure)
	for i in data underlay ; do
	    if ! [ -e $prefix/$i ] ; then
		cp -r /usr/share/moin/$i $prefix/
		chown -R gforge:gforge $prefix/$i
	    fi
	done
	chown gforge $(forge_get_config config_path)/config.ini.d/debian-install-secrets.ini
	if ! [ -e $dataprefix/moinmoin.log ] ; then
	    touch $dataprefix/moinmoin.log
	    chown gforge $dataprefix/moinmoin.log
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
