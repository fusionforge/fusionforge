#! /bin/sh
# 
# $Id$
#
# Configure exim for Sourceforge
# Christian Bayle, Roland Mas, debian-sf (Sourceforge for Debian)

case "$1" in
    configure)
	domain_name=$(perl -e'require "/etc/sourceforge/local.pl"; print "$domain_name\n";')
	ip_address=$(perl -e'require "/etc/sourceforge/local.pl"; print "$ip_address\n";')
	# export domain_name=$1
	# export ip_address=$2
  	if ! grep -q "// Next line inserted by Sourceforge install" /etc/bind/named.conf ; then
	    cat >> /etc/bind/named.conf <<-EOF
// Next line inserted by Sourceforge install
zone "$domain_name" { type master; file "/var/lib/sourceforge/bind/dns.zone"; };
EOF
  	fi
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
	;;

    purge)
	if grep -q "// Next line inserted by Sourceforge install" /etc/bind/named.conf ; then
	    perl -pi -e "s:zone.*sourceforge.*};\n::" /etc/bind/named.conf
	    perl -pi -e "s:// Next line inserted by Sourceforge install\n::" /etc/bind/named.conf
	fi
	;;

    *)
	echo "Usage: $0 {configure|purge}"
	exit 1
	;;

esac
