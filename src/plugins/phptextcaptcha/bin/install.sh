#!/bin/bash -e
# phptextcaptcha post-install

source $(forge_get_config source_path)/post-install.d/common/service.inc

datadir=$(forge_get_config data_path)/plugins/phptextcaptcha
apache_user=$(forge_get_config apache_user)

case "$1" in
    configure)
	chown $apache_user: $datadir
	;;
    remove)
	find $datadir -type l -print0 | xargs -r0 rm
	;;
    *)
        echo "Usage: $0 {configure|remove}"
        exit 1
esac
