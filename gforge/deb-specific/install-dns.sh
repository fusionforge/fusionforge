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
	domain_name=$(perl -e'require "/etc/sourceforge/local.pl"; print "$domain_name\n";')
	ip_address=$(perl -e'require "/etc/sourceforge/local.pl"; print "$sys_dbhost\n";')
	sys_simple_dns=$(perl -e'require "/etc/sourceforge/local.pl"; print "$sys_simple_dns\n";')
  	serial=`date '+%Y%m%d'`01
  	# cvs_host lists_host are useless for now
  	for i in domain_name ip_address serial ; do
  	    eval "sedexpr=\"$sedexpr|sed 's/{$i}/\${$i}/g'\""
 	done

	if [ "$sys_simple_dns" = "false" ]; then
  	    echo "Creating /var/lib/sourceforge/bind/dns.head"
  	    eval "cat /var/lib/sourceforge/bind/dns.head.template $sedexpr > /var/lib/sourceforge/bind/dns.head"
	    cp /var/lib/sourceforge/bind/dns.head /var/lib/sourceforge/bind/dns.zone
	    chown -R sourceforge:sourceforge /var/lib/sourceforge/bind

	    /usr/lib/sourceforge/bin/dns_conf.pl
	else
  	    echo "Creating /var/lib/sourceforge/bind/dns.zone"
  	    eval "cat /var/lib/sourceforge/bind/dns.simple.template $sedexpr > /var/lib/sourceforge/bind/dns.zone"
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
	cp -a /etc/bind/named.conf /etc/bind/named.conf.sourceforge-new
	if grep -q "// Next line inserted by Sourceforge install" /etc/bind/named.conf.sourceforge-new ; then
	    perl -pi -e "s:zone.*sourceforge.*};\n::" /etc/bind/named.conf.sourceforge-new
	    perl -pi -e "s:// Next line inserted by Sourceforge install\n::" /etc/bind/named.conf.sourceforge-new
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
