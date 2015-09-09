default: checkdepends installtemplate

help: 
	@echo "read the Makefile to see available targets"

checkdepends: /usr/bin/lxc-start /lib/bridge-utils /usr/bin/netmask /usr/sbin/debootstrap /usr/sbin/rinse /usr/bin/yum

/usr/bin/lxc-start:
	@[ -f $@ ] || apt-get install lxc

/lib/bridge-utils:
	@[ -d $@ ] || apt-get install bridge-utils

/usr/bin/netmask:
	@[ -f $@ ] || apt-get install netmask

/usr/sbin/debootstrap:
	@[ -f $@ ] || apt-get install debootstrap

/usr/sbin/rinse:
	@[ -f $@ ] || apt-get install rinse

/usr/bin/yum:
	@[ -f $@ ] || apt-get install yum

installtemplate: /usr/local/sbin/lxc-wrapper

/usr/local/sbin/lxc-wrapper: lxc-wrapper
	install -m 00755 lxc-wrapper /usr/local/sbin/

removetemplate:
	rm -f /usr/local/sbin/lxc-wrapper
