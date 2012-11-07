#! /bin/sh
# 
# Configure Loggerhead for FusionForge
# Roland Mas

PATH=/usr/share/gforge/bin:/usr/share/fusionforge/bin:$PATH
source_path=`forge_get_config source_path`
log_path=`forge_get_config log_path`
data_path=`forge_get_config data_path`
repos_path=$(forge_get_config repos_path scmbzr)
web_host=$(forge_get_config web_host)
url_prefix=$(forge_get_config url_prefix)


set -e

if [ `id -u` != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

configfile=~gforge/.bazaar/bazaar.conf
cachedir=/var/cache/gforge/loggerhead

case "$1" in
    configure)
	a2enmod wsgi
	if [ ! -e $configfile ] ; then
	    mkdir -p $(dirname $configfile)
	    cat > $configfile <<EOF
# Directory to serve bzr branches from
# Non-bzr directories under this path will also be visible in loggerhead
http_root_dir = '${repos_path}'

# The url prefix for the bzr branches.
http_user_prefix = 'http://${web_host}${url_prefix}scm/loggerhead'

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
