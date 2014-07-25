#! /bin/sh
# 
# Configure Loggerhead for FusionForge
# Roland Mas

set -e

if [ `id -u` != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

configfile=~gforge/.bazaar/bazaar.conf
cachedir=/var/cache/gforge/loggerhead

case "$1" in
    configure)
	PATH=$(forge_get_config binary_path):$PATH
	repos_path=$(forge_get_config repos_path scmbzr)
	web_host=$(forge_get_config web_host)
	url_prefix=$(forge_get_config url_prefix)
	use_ssl=$(forge_get_config use_ssl)

	if [ -z "$use_ssl" ] || [ "$use_ssl" = no ] ; then
	    http_user_prefix=http://${web_host}${url_prefix}scm/loggerhead
	else
	    http_user_prefix=https://${web_host}${url_prefix}scm/loggerhead
	fi
	a2enmod wsgi
	if [ ! -e $configfile ] ; then
	    mkdir -p $(dirname $configfile)
	    cat > $configfile <<EOF
# Directory to serve bzr branches from
# Non-bzr directories under this path will also be visible in loggerhead
http_root_dir = '${repos_path}'

# The url prefix for the bzr branches.
http_user_prefix = '${http_user_prefix}'

# Directory to put cache files in
http_sql_dir = '/var/cache/gforge/loggerhead'
EOF
	    mkdir -p $cachedir
	    chown gforge $cachedir
	fi
        ;;

    purge)
	rm -rf $configfile $cachedir
	rmdir $(dirname $configfile) || true
        ;;

    *)
        echo "Usage: $0 {configure|purge}"
        exit 1
esac
