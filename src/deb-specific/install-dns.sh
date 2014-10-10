#! /bin/sh
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
	domain_name=`forge_get_config web_host`
	ip_address=`forge_get_config ip_address`
	# export domain_name=$1
	# export ip_address=$2
	data_path=$(forge_get_config data_path)
  	if ! grep -q "// Next line inserted by GForge install" /etc/bind/named.conf.gforge-new ; then
	    cat >> /etc/bind/named.conf.gforge-new <<-EOF
// Next line inserted by GForge install
zone "$domain_name" { type master; file "$data_path/bind/dns.zone"; };
EOF
  	fi
	;;
    configure)
	domain_name=`forge_get_config web_host`
	ip_address=`forge_get_config ip_address`
	sys_simple_dns=`forge_get_config simple_dns`
	data_path=$(forge_get_config data_path)
  	serial=`date '+%Y%m%d'`01
  	# scm_host lists_host are useless for now
  	for i in domain_name ip_address serial ; do
  	    eval "sedexpr=\"$sedexpr|sed 's/{$i}/\${$i}/g'\""
 	done

	if [ "$sys_simple_dns" = "false" ]; then
  	    echo "Creating $data_path/bind/dns.head"
  	    eval "cat $data_path/bind/dns.head.template $sedexpr > $data_path/bind/dns.head"
	    cp $data_path/bind/dns.head $data_path/bind/dns.zone
	    chown -R gforge:gforge $data_path/bind

	    $(forge_get_config binary_path)/dns_conf.pl
	else
            [ -f $data_path/bind/dns.head ] && echo "Removing $data_path/bind/dns.head" && \
	    rm $data_path/bind/dns.head
  	    echo "Creating $data_path/bind/dns.zone"
  	    eval "cat $data_path/bind/dns.simple.template $sedexpr > $data_path/bind/dns.zone"
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
