#!/bin/sh
#
# Simple wrapper for FusionForge installation
#
# Usage: ./install.sh <hostname>
#
# This will install all the fusionforge code in /opt/gforge
# Configuration is stored in /etc/gforge
#
# Currently supported:
# * Red Hat 4 / CentOS 4
# * Red Hat 5 / CentOS 5
# * OpenSuSE 11 (contributed by Martin Bernreuther)
#
# Author: Alain Peyrat <aljeux@free.fr>
#


#hostname="`hostname`.`dnsdomainname`"

if [ $# -ne 1  ]; then
	echo 1>&2 Usage: $0 hostname
	exit 127
fi

hostname=$1
type="";
msg="";
version=4.8.1

if [ -f "/etc/redhat-release" ]
then
	type="redhat"
	distrib=`awk '{print $1}' /etc/redhat-release`
elif [ -f "/etc/SuSE-release" ]
then
	type="suse"
	distrib=`awk '{print $1}' /etc/SuSE-release | head -n 1`
fi


if [ $distrib = "CentOS" ]
then
	deps="CENTOS"
elif [ $distrib = "Red" ]
then
	deps="RHEL5"
elif [ $distrib = "Fedora" ]
then
	deps="FEDORA"
elif [ $distrib = "openSUSE" ]
then
	deps="OPENSUSE"
fi

if [ -d "/opt/gforge" ]
then
	mode="update"
	echo "Upgrading previous installation ...";
else
	mode="install"
	echo "Installing FusionForge ...";
fi

if [ $type = "redhat" ]
then
	yum -y install php
	php gforge-install-1-deps.php $deps
	php gforge-install-2.php "$hostname" apache apache

	if [ $mode = "install" ]
	then
		php gforge-install-3-db.php
		php /opt/gforge/db/startpoint.php $version

		# Post installation fixes.
		perl -spi -e "s/^#ServerName (.*):80/ServerName $hostname:80/" /etc/httpd/conf/httpd.conf
		perl -spi -e 's/^LoadModule/#LoadModule/g' /etc/gforge/httpd.conf

		chkconfig httpd on
		chkconfig postgresql on
		chkconfig iptables off

		service httpd restart
		service iptables stop
		msg="IMPORTANT: Service iptables (firewall) disabled, please reconfigure after"

		cp cron.gforge /etc/cron.d
		cp plugins/*/etc/cron.d/* /etc/cron.d/
		service crond reload
	else
		php /opt/gforge/db/upgrade-db.php
	fi
elif [ $type = "suse" ]
then
	yast -i php5
	php gforge-install-1-deps.php $deps
	php gforge-install-2.php "$hostname" wwwrun www

	if [ $mode = "install" ]
	then
		php gforge-install-3-db.php
		php /opt/gforge/db/startpoint.php $version

		# Post installation fixes.
		#perl -spi -e "s/^#ServerName (.*):80/ServerName $hostname:80/" /etc/apache2/httpd.conf
		perl -spi -e 's/^LoadModule/#LoadModule/g' /etc/gforge/httpd.conf

		chkconfig -s apache2 on
		chkconfig -s postgresql on

		# Apache settings: modules
		for m in dav dav_svn authz_svn ssl; do
			a2enmod $m
			a2enflag $m
		done
		echo "Virtual hosts for ${hostname}:"
		httpd2 -S -DSSL 2>&1 | grep ${hostname}

		rcapache2 restart

		rcSuSEfirewall2 stop
		msg="IMPORTANT: Service SuSEfirewall2 stopped, please reconfigure after"

		cp cron.gforge /etc/cron.d
		cp plugins/*/etc/cron.d/* /etc/cron.d/
		rccron reload
	else
		php /opt/gforge/db/upgrade-db.php
	fi
else
	echo "Only Red Hat, Fedora or CentOS and OpenSUSE are supported by this script.";
	echo "See INSTALL for normal installation";
	exit 1;
fi

echo "check /etc/gforge/local.inc for ${hostname} specific FusionForge settings"
echo -e $msg;
exit 0;
