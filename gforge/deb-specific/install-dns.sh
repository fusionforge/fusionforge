#! /bin/sh
# 
# $Id$
#
# Configure Bind 9 for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure-files)
	cp -a /etc/bind/named.conf /etc/bind/named.conf.sourceforge-new
	domain_name=$(perl -e'require "/etc/sourceforge/local.pl"; print "$domain_name\n";')
	ip_address=$(perl -e'require "/etc/sourceforge/local.pl"; print "$sys_dbhost\n";')
	# export domain_name=$1
	# export ip_address=$2
  	if ! grep -q "// Next line inserted by Sourceforge install" /etc/bind/named.conf.sourceforge-new ; then
	    cat >> /etc/bind/named.conf.sourceforge-new <<-EOF
// Next line inserted by Sourceforge install
zone "$domain_name" { type master; file "/var/lib/sourceforge/bind/dns.zone"; };
EOF
  	fi
	;;
    configure)
  	echo "Creating /var/lib/sourceforge/bind/dns.head"
  	serial=`date '+%Y%m%d'`01
  	# cvs_host lists_host are useless for now
  	for i in domain_name ip_address serial ; do
  	    eval "sedexpr=\"$sedexpr|sed 's/{$i}/\${$i}/g'\""
 	done
  	eval "cat /var/lib/sourceforge/bind/dns.head.template $sedexpr > /var/lib/sourceforge/bind/dns.head"
  	if [ ! -f /var/lib/sourceforge/bind/dns.zone ] ; then
	    cp /var/lib/sourceforge/bind/dns.head /var/lib/sourceforge/bind/dns.zone
  	fi
  	chown sourceforge:sourceforge /var/lib/sourceforge/bind
  	chown sourceforge:sourceforge /var/lib/sourceforge/bind/dns.head
  	chown sourceforge:sourceforge /var/lib/sourceforge/bind/dns.zone
  	echo "DNS Config is not complete:"
  	echo "	-Does not do reverse, maybe not in the state of the art"
  	echo "	-Suppose that all servers are in the same box"
  	echo "	-Wizards advices are welcome"
	/usr/lib/sourceforge/bin/dns_conf.pl

	invoke-rc.d bind9 restart
	# This is equivalent but require some signature, not always there
	# /usr/sbin/rndc reload

	;;

    purge-files)
	cp -a /etc/bind/named.conf /etc/bind/named.conf.sourceforge-new
	if grep -q "// Next line inserted by Sourceforge install" /etc/bind/named.conf.sourceforge-new ; then
	    perl -pi -e "s:zone.*sourceforge.*};\n::" /etc/bind/named.conf.sourceforge-new
	    perl -pi -e "s:// Next line inserted by Sourceforge install\n::" /etc/bind/named.conf.sourceforge-new
	fi
	;;
    purge)
	invoke-rc.d bind9 restart
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
	;;

esac
