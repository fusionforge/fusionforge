#!/bin/bash -e
# Configure Apache
#
# Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
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

. $(forge_get_config source_path)/post-install.d/common/service.inc

source_path=$(forge_get_config source_path)
config_path=$(forge_get_config config_path)
data_path=$(forge_get_config data_path)

case "$1" in
    # Update configuration values in Apache define directives
    # Also called from common/plugin.sh
    update-defines)
	cd $config_path/httpd.conf.d/
	t=$(mktemp 00-defines.conf.XXXXXX)  # mod=0600
	(
	    echo "# This file is autogenerated, do not edit"
	    echo "# Run '$0 $1' to refresh this file"
	    echo "# Configuration variables are obtained from $config_path/config.ini and $config_path/config.ini.d/*.ini"
	    echo
	    for i in $(grep --only-matching --no-filename '\${FF__[^}]*}' *|sort -u) ; do
		section=$(echo $i|sed -e 's/.*__\(.*\)__.*/\1/')
		variable=$(echo $i|sed -e 's/.*__.*__\(.*\)\}/\1/')
		echo "Define FF__${section}__${variable} \"$(forge_get_config $variable $section)\""
	    done
	) > $t
	mv $t 00-defines.conf
	;;

    configure)
	$0 update-defines

	# '${FF__core__config_path}' not yet available in the top-level config file, so generate it:
	# (unless it was manually emptied, meaning sites will be individually enabled e.g. via Puppet)
	if [ ! -e $config_path/httpd.conf -o -s $config_path/httpd.conf ]; then
	    cat > $config_path/httpd.conf <<-EOF
		# Include all FusionForge-related configuration files
		Include $config_path/httpd.conf.d/*.conf
		EOF
	fi

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
	cert_scm=$config_path/ssl-cert-scm.pem
	key_scm=$config_path/ssl-cert-scm.key
	if [ ! -e $key_scm ] ; then
	    openssl genrsa -out $key_scm
	    chmod 600 $key_scm
	fi
	if [ ! -e $cert_scm ] ; then
	    openssl req -x509 -days 3650 -new -nodes -batch -text -key $key_scm -subj "/CN=$scm_host" -out $cert_scm
	fi

	# Setup Docman/FRS/Forum/Tracker/RSS attachments
	# (not done in 'make install' because e.g. dpkg ignores existing dirs, cf. DP10.9[1])
	chown $apache_user: $data_path/docman/
	chown $apache_user: $data_path/download/
	chown $apache_user: $data_path/forum/
	chown $apache_user: $data_path/forum/pending/
	chown $apache_user: $data_path/tracker/
	chown $apache_user: $data_path/rss/

	# Plugins activation from the web UI
	chown $apache_user: $source_path/www/plugins/

	# Enable required modules
	if [ -x /usr/sbin/a2enmod ]; then
	    a2enmod version 2>/dev/null || true  # opensuse..
	    a2enmod macro
	    a2enmod php7.0 || a2enmod php5
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
	    a2enmod authz_groupfile
	    a2enmod dav
	else
	    if ! [ -e /etc/httpd/conf.modules.d/00-macro.conf ] ; then
		echo "LoadModule macro_module modules/mod_macro.so" > /etc/httpd/conf.modules.d/00-macro.conf
	    fi
	    if [ -e /etc/httpd/conf.modules.d/00-mpm-itk.conf ] \
		   && ! grep -q ^LoadModule.mpm_itk_module /etc/httpd/conf.modules.d/00-mpm-itk.conf ; then
		sed -i -e s/^#LoadModule/LoadModule/ /etc/httpd/conf.modules.d/00-mpm-itk.conf
	    fi
	fi

	# Enable mpm-itk on RH/CentOS

	if [ -x /usr/sbin/a2dissite ]; then
	    a2dissite 000-default
	fi
	# Prevent double NameVirtualHost warning
	if [ -e /etc/apache2/ports.conf ]; then
	    sed -i 's/^NameVirtualHost \*:80/#&/' /etc/apache2/ports.conf
	fi

        $0 servicerestart
        ;;

    servicerestart)
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
	    rm -f /etc/apache2/sites-available/fusionforge.conf
	elif [ -e /etc/redhat-release ]; then
	    rm /etc/httpd/conf.d/fusionforge.conf
	fi
	rm $config_path/httpd.conf $config_path/httpd.conf.d/00-defines.conf
	;;

    purge)
	log_path=$(forge_get_config log_path)
	rm -f $log_path/access.log
	rm -f $log_path/awstats.log
	;;

    *)
	echo "Usage: $0 {configure|remove|purge|update-defines|servicerestart}"
	exit 1
	;;
esac
