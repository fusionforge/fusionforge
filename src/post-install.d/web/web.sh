#!/bin/bash -e
# Configure Apache
#
# Copyright (C) 2014  Inria (Sylvain Beucler)
#
# This file is part of FusionForge. FusionForge is free software;
# you can redistribute it and/or modify it under the terms of the
# GNU General Public License as published by the Free Software
# Foundation; either version 2 of the Licence, or (at your option)
# any later version.
#
# FusionForge is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with FusionForge; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)

case "$1" in
    configure)
	$0 configure-conffiles
	$0 configure-exec
	;;

    # Prepare config files for installation
    configure-conffiles)
	# Distros may want to install new conffiles using tools such as ucf(1)
	DESTDIR=$2
	mkdir -m 755 -p $DESTDIR$config_path/httpd.conf.d/

	cd $source_path/templates/
	for i in httpd.conf $(ls httpd.conf.d/*); do
	    if [ ! -e $DESTDIR$config_path/$i -o $i = "post-install-secrets.inc" ]; then
		$source_path/post-install.d/web/expand-conf.php $i $DESTDIR$config_path/$i
	    fi
	    case $i in
		*secrets*) chmod 600 $DESTDIR$config_path/$i;;
	    esac
	done
	;;

    # Configure once config files are installed
    configure-exec)
	apache_user=$(forge_get_config apache_user)
	apache_group=$(forge_get_config apache_group)
	apache_service=$(forge_get_config apache_service)

	if [ -x /usr/sbin/a2ensite ]; then
	    ln -nfs $config_path/httpd.conf /etc/apache2/sites-available/fusionforge.conf
	    a2ensite fusionforge.conf
	elif [ -e /etc/redhat-release ]; then
	    ln -nfs $config_path/httpd.conf /etc/httpd/conf.d/fusionforge.conf
	else
	    echo "*** Note: please install $config_path/httpd.conf in your Apache configuration"
	fi
	
	# Generate SSL certs if needed
	web_host=$(forge_get_config web_host)
	cert=$config_path/ssl-cert.pem
	key=$config_path/ssl-cert.key
	if [ ! -e $key ] ; then
	    openssl genrsa -out $key
	    chmod 600 $key
	fi
	if [ ! -e $cert ] ; then
	    openssl req -x509 -days 3650 -new -nodes -batch -text -key $key -subj "/CN=$web_host" -out $cert
	fi

	scm_host=$(forge_get_config scm_host)
	scmcert=$config_path/ssl-cert-scm.pem
	if [ ! -e $scmcert ] ; then
	    openssl req -x509 -days 3650 -new -nodes -batch -text -key $key -subj "/CN=$scm_host" -out $scmcert
	fi
	
	# Setup Docman/FRS/Tracker attachments
	# (not done in 'make install' because e.g. dpkg ignores existing dirs, cf. DP10.9[1])
	chown $apache_user: $data_path/docman/
	chown $apache_user: $data_path/download/
	chown $apache_user: $data_path/forum/
	chown $apache_user: $data_path/tracker/
	
	# Plugins activation from the web UI
	chown $apache_user: $source_path/www/plugins/
	
	# Enable required modules
	if [ -x /usr/sbin/a2enmod ]; then
	    a2enmod version 2>/dev/null || true  # opensuse..
	    a2enmod php5
	    a2enmod ssl
	    a2enmod env
	    a2enmod headers
	    a2enmod rewrite
	    a2enmod alias
	    a2enmod dir
	    a2enmod vhost_alias
	    a2enmod cgi  # ViewVC bootstrap, gitweb, mailman
	    #a2enmod proxy
	    #a2enmod proxy_http
	    a2enmod macro
	    a2enmod authz_groupfile
	    a2enmod dav
	fi
	# else: Apache modules already enabled in CentOS

	# Enable mpm-itk on RH/CentOS
	if [ -e /etc/httpd/conf.modules.d/00-mpm-itk.conf ] \
	       && ! grep -q ^LoadModule.mpm_itk_module /etc/httpd/conf.modules.d/00-mpm-itk.conf ; then
	    sed -i -e s/^#LoadModule/LoadModule/ /etc/httpd/conf.modules.d/00-mpm-itk.conf
	fi

	if [ -x /usr/sbin/a2dissite ]; then
	    a2dissite 000-default
	fi
	# Prevent double NameVirtualHost warning
	if [ -e /etc/apache2/ports.conf ]; then
	    sed -i 's/^NameVirtualHost \*:80/#&/' /etc/apache2/ports.conf
	fi

	# Start web server on boot
	if [ -x /sbin/chkconfig ]; then
	    chkconfig $apache_service on
	fi
	# Refresh configuration
	if service $apache_service status >/dev/null; then
	    service $apache_service reload
	else
	    service $apache_service restart
	fi
	;;

    remove)
	if [ -x /usr/sbin/a2ensite ]; then
	    a2dissite fusionforge.conf
	elif [ -e /etc/redhat-release ]; then
	    rm /etc/httpd/conf.d/fusionforge.conf
	fi
	;;

    purge)
	# note: can't be called from Debian's postrm - rely on ucfq(1)
	cd $source_path/templates/
	for i in httpd.conf $(ls httpd.conf.d/*); do
	    rm -f $config_path/$i
	done
	;;

    *)
	echo "Usage: $0 {configure|configure-conffiles|configure-exec|remove|purge}"
	exit 1
	;;
esac
