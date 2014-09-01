#!/bin/bash -x
# MediaWiki post-install

config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)
plugindir=$(forge_get_config source_path)/plugins/mediawiki

mediawikidir=$((ls -d /usr/share/mediawiki* 2>/dev/null || echo '/usr/share/mediawiki') | tail -1)
# Debian: /usr/share/mediawiki/
# CentOS6: /usr/share/mediawiki119/

upgrade_mediawikis () {
    # Upgrade Mediawiki database schemas
    $(forge_get_config binary_path)/list-projects-using-plugin.php mediawiki | while read i ; do
	$(forge_get_config plugins_path)/mediawiki/bin/mw-wrapper.php $i update.php --quick
    done
}

case "$1" in
    configure)
        ln -nfs $mediawikidir/api.php              $plugindir/www/api.php
        ln -nfs $mediawikidir/extensions           $plugindir/www/extensions
        ln -nfs $mediawikidir/img_auth.php         $plugindir/www/img_auth.php
        ln -nfs $mediawikidir/includes             $plugindir/www/includes
        ln -nfs $mediawikidir/index.php            $plugindir/www/index.php
        ln -nfs $mediawikidir/languages            $plugindir/www/languages
        ln -nfs $mediawikidir/load.php             $plugindir/www/load.php
        ln -nfs $mediawikidir/maintenance          $plugindir/www/maintenance
        ln -nfs $mediawikidir/opensearch_desc.php  $plugindir/www/opensearch_desc.php
        ln -nfs $mediawikidir/profileinfo.php      $plugindir/www/profileinfo.php
        ln -nfs $mediawikidir/redirect.php         $plugindir/www/redirect.php
        ln -nfs $mediawikidir/thumb.php            $plugindir/www/thumb.php

	ln -nfs $mediawikidir/skins $plugindir/www/skins
	ln -nfs $mediawikidir/skins/monobook/headbg.jpg $plugindir/www/themes/css/mw-headbg.jpg

	ln -nfs $mediawikidir $data_path/plugins/mediawiki/master
	ln -nfs $plugindir/mediawiki-skin/FusionForge.php $mediawikidir/skins/FusionForge.php
	;;
    triggered)
	case $2 in
	/usr/share/mediawiki*) upgrade_mediawikis ;;
	esac
	;;
    remove)
	;;
    *)
        echo "Usage: $0 {configure|triggered|remove}"
        exit 1
esac
