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
#
# Author: aljeux <aljeux@free.fr>
#

if [ $# -ne 1  ]; then
	echo 1>&2 Usage: $0 hostname
	exit 127
fi

hostname=$1

type="";
if [ -f "/etc/redhat-release" ]
then
	type="redhat"
	distrib=`awk '{print $1}' /etc/redhat-release`
fi


if [ $distrib = "CentOS" ]
then
	deps="CENTOS"
fi
if [ $distrib = "Red" ]
then
	deps="RHEL5"
fi
if [ $distrib = "Fedora" ]
then
	deps="FEDORA"
fi


if [ $type = "redhat" ]
then
	yum -y install php
	php gforge-install-1-deps.php $deps
	php gforge-install-2.php "$hostname" apache apache
	php gforge-install-3-db.php

	php /opt/gforge/db/startpoint.php 4.7

	# Post installation fixes.
	perl -spi -e "s/^#ServerName (.*):80/ServerName $hostname:80/" /etc/httpd/conf/httpd.conf
	perl -spi -e 's/^LoadModule/#LoadModule/g' /etc/gforge/httpd.conf 

	chkconfig httpd on
	chkconfig postgresql on
	chkconfig iptables off

	service httpd restart
	service iptables stop

	cp cron.gforge /etc/cron.d
	service crond reload

	echo "IMPORTANT: Service iptables (firewall) disabled, please reconfigure after";

	exit 0;
else
	echo "Only Red Hat, Fedora or CentOS are supported by this script.";
	echo "See INSTALL for normal installation";
	exit 1;
fi
