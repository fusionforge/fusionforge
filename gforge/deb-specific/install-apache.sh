#! /bin/sh
# 
# $Id$
#
# Configure exim for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure-files)
	# Make sure Apache sees us
	cp -a /etc/apache/httpd.conf /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule php4_module/LoadModule php4_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule ssl_module/LoadModule ssl_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule apache_ssl_module/LoadModule apache_ssl_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule env_module/LoadModule env_module/gi" /etc/apache/httpd.conf.sourceforge-new
	perl -pi -e "s/# *LoadModule vhost_alias_module/LoadModule vhost_alias_module/gi" /etc/apache/httpd.conf.sourceforge-new
	
	if ! grep -q "^Include /etc/sourceforge/sf-httpd.conf" /etc/apache/httpd.conf.sourceforge-new ; then
	    echo "### Next line inserted by Sourceforge install" >> /etc/apache/httpd.conf.sourceforge-new
	    echo "Include /etc/sourceforge/sf-httpd.conf" >> /etc/apache/httpd.conf.sourceforge-new
	fi

	# Make sure pgsql,ldap and gd are enabled in the PHP config files
	cp -a /etc/php4/apache/php.ini /etc/php4/apache/php.ini.sourceforge-new
	cp -a /etc/php4/cgi/php.ini /etc/php4/cgi/php.ini.sourceforge-new
	if [ -f /etc/php4/apache/php.ini.sourceforge-new ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" /etc/php4/apache/php.ini.sourceforge-new; then
		echo "Enabling pgsql in /etc/php4/apache/php.ini"
		echo "extension=pgsql.so" >> /etc/php4/apache/php.ini.sourceforge-new
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*gd.so" /etc/php4/apache/php.ini.sourceforge-new; then
		echo "Enabling gd in /etc/php4/apache/php.ini"
		echo "extension=gd.so" >> /etc/php4/apache/php.ini.sourceforge-new
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*ldap.so" /etc/php4/apache/php.ini.sourceforge-new; then
		echo "Enabling ldap in /etc/php4/apache/php.ini"
		echo "extension=ldap.so" >> /etc/php4/apache/php.ini.sourceforge-new
	    fi
	fi
	if [ -f /etc/php4/cgi/php.ini.sourceforge-new ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" /etc/php4/cgi/php.ini.sourceforge-new; then
		echo "Enabling pgsql in /etc/php4/cgi/php.ini"
		echo "extension=pgsql.so" >> /etc/php4/cgi/php.ini.sourceforge-new
	    fi
	fi

	;;
    configure)
	invoke-rc.d apache restart
	;;

    purge-files)
	cp -a /etc/apache/httpd.conf /etc/apache/httpd.conf.sourceforge-new
  	if grep -q "Include /etc/sourceforge/sf-httpd.conf" /etc/apache/httpd.conf.sourceforge-new ; then
	    pattern=$(basename $0)
	    tmp=$(mktemp /tmp/$pattern.XXXXXX)
	    grep -v "Include /etc/sourceforge/sf-httpd.conf\|### Next line inserted by Sourceforge install" /etc/apache/httpd.conf.sourceforge-new > $tmp
	    cat $tmp > /etc/apache/httpd.conf.sourceforge-new
	    rm -f $tmp
  	fi
	;;
    purge)
	invoke-rc.d apache restart
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
	;;
	
esac
