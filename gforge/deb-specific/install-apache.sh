#! /bin/sh
# 
# $Id$
#
# Configure exim for GForge
# Christian Bayle, Roland Mas, debian-sf (GForge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure-files)
	# Remove old hack to have Apache see us
	for flavour in apache apache-perl apache-ssl ; do
	    if [ -e /etc/$flavour/httpd.conf ] && grep -q "Include /etc/gforge/httpd.conf" /etc/$flavour/httpd.conf ; then
		cp -a /etc/$flavour/httpd.conf /etc/$flavour/httpd.conf.gforge-new
		pattern=$(basename $0)
		tmp=$(mktemp /tmp/$pattern.XXXXXX)
		grep -v "Include /etc/gforge/httpd.conf\|### Next line inserted by GForge install" /etc/$flavour/httpd.conf.gforge-new > $tmp
		cat $tmp > /etc/$flavour/httpd.conf.gforge-new
		rm -f $tmp
	    fi
	done

	# Make sure pgsql, ldap and gd are enabled in the PHP config files
	cp -a /etc/php4/apache/php.ini /etc/php4/apache/php.ini.gforge-new
	cp -a /etc/php4/cgi/php.ini /etc/php4/cgi/php.ini.gforge-new
	if [ -f /etc/php4/apache/php.ini.gforge-new ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" /etc/php4/apache/php.ini.gforge-new; then
		echo "Enabling pgsql in /etc/php4/apache/php.ini"
		echo "extension=pgsql.so" >> /etc/php4/apache/php.ini.gforge-new
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*gd.so" /etc/php4/apache/php.ini.gforge-new; then
		echo "Enabling gd in /etc/php4/apache/php.ini"
		echo "extension=gd.so" >> /etc/php4/apache/php.ini.gforge-new
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*ldap.so" /etc/php4/apache/php.ini.gforge-new; then
		echo "Enabling ldap in /etc/php4/apache/php.ini"
		echo "extension=ldap.so" >> /etc/php4/apache/php.ini.gforge-new
	    fi
	fi
	if [ -f /etc/php4/cgi/php.ini.gforge-new ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" /etc/php4/cgi/php.ini.gforge-new; then
		echo "Enabling pgsql in /etc/php4/cgi/php.ini"
		echo "extension=pgsql.so" >> /etc/php4/cgi/php.ini.gforge-new
	    fi
	fi

	;;
    configure)
	/usr/lib/gforge/bin/prepare-vhosts-file.pl
	for flavour in apache apache-perl apache-ssl ; do
	    if [ -e /etc/$flavour/httpd.conf ] ; then
		/usr/sbin/modules-config $flavour enable mod_php4
		if [ $flavour != apache-ssl ] ; then
		    /usr/sbin/modules-config $flavour enable mod_ssl
		fi
		/usr/sbin/modules-config $flavour enable mod_env
		/usr/sbin/modules-config $flavour enable mod_vhost_alias
		[ ! -e /etc/$flavour/conf.d/gforge.httpd.conf ] && ln -s /etc/gforge/httpd.conf /etc/$flavour/conf.d/gforge.httpd.conf
	    fi
	    if [ -x /usr/sbin/$flavour ]; then
		invoke-rc.d $flavour restart || true
	    fi
	done
	;;

    purge-files)
	for flavour in apache apache-perl apache-ssl ; do
	    if [ -e /etc/$flavour/httpd.conf ] && grep -q "Include /etc/gforge/httpd.conf" /etc/$flavour/httpd.conf ; then
		cp -a /etc/$flavour/httpd.conf /etc/$flavour/httpd.conf.gforge-new
		pattern=$(basename $0)
		tmp=$(mktemp /tmp/$pattern.XXXXXX)
		grep -v "Include /etc/gforge/httpd.conf\|### Next line inserted by GForge install" /etc/$flavour/httpd.conf.gforge-new > $tmp
		cat $tmp > /etc/$flavour/httpd.conf.gforge-new
		rm -f $tmp
	    fi
	done
	;;
    purge)
	for flavour in apache apache-perl apache-ssl ; do
	    [ ! -e /etc/$flavour/conf.d/gforge.httpd.conf ] && rm -f /etc/$flavour/conf.d/gforge.httpd.conf
	    if [ -x /usr/sbin/$flavour ]; then
		invoke-rc.d $flavour restart || true
	    fi
	done
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
	;;
	
esac
