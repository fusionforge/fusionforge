#! /bin/sh
# 
# $Id$
#
# Configure apache for GForge
# Christian Bayle, Roland Mas, debian-sf (GForge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

if [ -z "$etcapache" ] 
then 
	if [ -d /etc/apache ]
	then 
		etcapache="/etc/apache" 
	else 
		if [ -d /etc/apache-ssl ]
		then
			etcapache="/etc/apache-ssl"
		else
			echo '[No etcapache]' ; exit 1
		fi
	fi
fi

if [ -z "$etcgforge" ] 
then 
	if [ -d /etc/gforge ] 
	then 
		etcgforge="/etc/gforge" 
	else 
		echo '[No etcgforge]' ; exit 1
	fi
fi
if [ -z "$etcphp4apache" ] 
then 
	etcphp4apache="/etc/php4/apache"
else
	echo '[No etcphp4apache]' ; exit 1
fi

[ -z "$etcphp4cgi" ] && etcphp4cgi="/etc/php4/cgi"
[ -z "$gforgebin" ] && gforgebin="/usr/lib/gforge/bin"

case "$1" in
    configure-files)
	# Make sure Apache sees us
	if [ -e $etcapache/httpd.conf ] ; then
	    cp -a $etcapache/httpd.conf $etcapache/httpd.conf.gforge-new
	    perl -pi -e "s/# *LoadModule php4_module/LoadModule php4_module/gi" $etcapache/httpd.conf.gforge-new
	    perl -pi -e "s/# *LoadModule ssl_module/LoadModule ssl_module/gi" $etcapache/httpd.conf.gforge-new
	    perl -pi -e "s/# *LoadModule env_module/LoadModule env_module/gi" $etcapache/httpd.conf.gforge-new
	    perl -pi -e "s/# *LoadModule vhost_alias_module/LoadModule vhost_alias_module/gi" $etcapache/httpd.conf.gforge-new
	    
	    if ! grep -q "^Include $etcgforge/httpd.conf" $etcapache/httpd.conf.gforge-new ; then
		echo "### Next line inserted by GForge install" >> $etcapache/httpd.conf.gforge-new
		echo "Include $etcgforge/httpd.conf" >> $etcapache/httpd.conf.gforge-new
	    fi
	fi

	# Make sure pgsql, ldap and gd are enabled in the PHP config files
	cp -a $etcphp4apache/php.ini $etcphp4apache/php.ini.gforge-new
	cp -a $etcphp4cgi/php.ini $etcphp4cgi/php.ini.gforge-new
	if [ -f $etcphp4apache/php.ini.gforge-new ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" $etcphp4apache/php.ini.gforge-new; then
		echo "Enabling pgsql in $etcphp4apache/php.ini"
		echo "extension=pgsql.so" >> $etcphp4apache/php.ini.gforge-new
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*gd.so" $etcphp4apache/php.ini.gforge-new; then
		echo "Enabling gd in $etcphp4apache/php.ini"
		echo "extension=gd.so" >> $etcphp4apache/php.ini.gforge-new
	    fi
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*ldap.so" $etcphp4apache/php.ini.gforge-new; then
		echo "Enabling ldap in $etcphp4apache/php.ini"
		echo "extension=ldap.so" >> $etcphp4apache/php.ini.gforge-new
	    fi
	fi
	if [ -f $etcphp4cgi/php.ini.gforge-new ]; then
	    if ! grep -q "^[[:space:]]*extension[[:space:]]*=[[:space:]]*pgsql.so" $etcphp4cgi/php.ini.gforge-new; then
		echo "Enabling pgsql in $etcphp4cgi/php.ini"
		echo "extension=pgsql.so" >> $etcphp4cgi/php.ini.gforge-new
	    fi
	fi

	;;
    configure)
	[ -f $gforgebin/prepare-vhosts-file.pl ] && $gforgebin/prepare-vhosts-file.pl
	if [ -f /usr/sbin/modules-config ] ; then
		if [ -e /etc/apache/httpd.conf ] ; then
	    		/usr/sbin/modules-config apache enable mod_php4
	    		/usr/sbin/modules-config apache enable mod_ssl
	    		/usr/sbin/modules-config apache enable mod_env
	    		/usr/sbin/modules-config apache enable mod_vhost_alias
		fi
		if [ -e /etc/apache-ssl/httpd.conf ] ; then
	    		/usr/sbin/modules-config apache-ssl enable mod_php4
	    		/usr/sbin/modules-config apache-ssl enable mod_env
	    		/usr/sbin/modules-config apache-ssl enable mod_vhost_alias
		fi
	fi
	if [ -x /usr/sbin/apache ]; then
		invoke-rc.d apache restart || true
	fi
	if [ -x /usr/sbin/apache-ssl ]; then
		invoke-rc.d apache-ssl restart || true
	fi
	if [ -x /usr/sbin/apache ]; then
		invoke-rc.d apache restart || true
	fi
	if [ -x /usr/sbin/apache-ssl ]; then
		invoke-rc.d apache-ssl restart || true
	fi
	;;

    purge-files)
	cp -a $etcapache/httpd.conf $etcapache/httpd.conf.gforge-new
  	if grep -q "Include $etcgforge/httpd.conf" $etcapache/httpd.conf.gforge-new ; then
	    pattern=$(basename $0)
	    tmp=$(mktemp /tmp/$pattern.XXXXXX)
	    grep -v "Include $etcgforge/httpd.conf\|### Next line inserted by GForge install" $etcapache/httpd.conf.gforge-new > $tmp
	    cat $tmp > $etcapache/httpd.conf.gforge-new
	    rm -f $tmp
  	fi
	;;
    purge)
	if [ -x /usr/sbin/apache ]; then
		invoke-rc.d apache restart || true
	fi
	if [ -x /usr/sbin/apache-ssl ]; then
		invoke-rc.d apache-ssl restart || true
	fi
	;;
    setup)
    	$0 configure-files
	$0 configure
	cp $etcapache/httpd.conf $etcapache/httpd.conf.gforge-old
	mv $etcapache/httpd.conf.gforge-new $etcapache/httpd.conf
	;;
    cleanup)
    	$0 purge-files
	$0 purge
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files|setup|cleanup}"
	exit 1
	;;
	
esac
