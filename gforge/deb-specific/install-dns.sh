#! /bin/sh
# 
# $Id$
#
# Configure Bind 9 for GForge
# Christian Bayle, Roland Mas, debian-sf (GForge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi


case "$1" in
    configure-files)
	cp -a /etc/bind/named.conf /etc/bind/named.conf.gforge-new
	domain_name=$(perl -e'require "/etc/gforge/local.pl"; print "$domain_name\n";')
	ip_address=$(perl -e'require "/etc/gforge/local.pl"; print "$sys_ip_address\n";')
	# export domain_name=$1
	# export ip_address=$2
  	if ! grep -q "// Next line inserted by GForge install" /etc/bind/named.conf.gforge-new ; then
	    cat >> /etc/bind/named.conf.gforge-new <<-EOF
// Next line inserted by GForge install
zone "$domain_name" { type master; file "/var/lib/gforge/bind/dns.zone"; };
EOF
  	fi
	;;
    configure)
	domain_name=$(perl -e'require "/etc/gforge/local.pl"; print "$domain_name\n";')
	ip_address=$(perl -e'require "/etc/gforge/local.pl"; print "$sys_ip_address\n";')
	sys_simple_dns=$(perl -e'require "/etc/gforge/local.pl"; print "$sys_simple_dns\n";')
  	serial=`date '+%Y%m%d'`01
  	# cvs_host lists_host are useless for now
  	for i in domain_name ip_address serial ; do
  	    eval "sedexpr=\"$sedexpr|sed 's/{$i}/\${$i}/g'\""
 	done

	if [ "$sys_simple_dns" = "false" ]; then
  	    echo "Creating /var/lib/gforge/bind/dns.head"
  	    eval "cat /var/lib/gforge/bind/dns.head.template $sedexpr > /var/lib/gforge/bind/dns.head"
	    cp /var/lib/gforge/bind/dns.head /var/lib/gforge/bind/dns.zone
	    chown -R gforge:gforge /var/lib/gforge/bind

	    /usr/lib/gforge/bin/dns_conf.pl
	else
            [ -f /var/lib/gforge/bind/dns.head ] && echo "Removing /var/lib/gforge/bind/dns.head" && \
	    rm /var/lib/gforge/bind/dns.head
  	    echo "Creating /var/lib/gforge/bind/dns.zone"
  	    eval "cat /var/lib/gforge/bind/dns.simple.template $sedexpr > /var/lib/gforge/bind/dns.zone"
	fi

  	echo "DNS Config is not complete:"
  	echo "	-Does not do reverse, maybe not in the state of the art"
  	echo "	-Suppose that all servers are in the same box"
  	echo "	-Wizards advices are welcome"

	/usr/sbin/invoke-rc.d bind9 restart
	# This is equivalent but require some signature, not always there
	# /usr/sbin/rndc reload

	;;

    purge-files)
	cp -a /etc/bind/named.conf /etc/bind/named.conf.gforge-new
	if grep -q "// Next line inserted by GForge install" /etc/bind/named.conf.gforge-new ; then
	    perl -pi -e "s:zone.*gforge.*};\n::" /etc/bind/named.conf.gforge-new
	    perl -pi -e "s:// Next line inserted by GForge install\n::" /etc/bind/named.conf.gforge-new
	fi
	;;
    purge)
	/usr/sbin/invoke-rc.d bind9 restart
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
	;;

esac
