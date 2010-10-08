
DEBMIRROR=http://ftp.fr.debian.org/debian
RINSECONF=/etc/rinse/rinse.conf
RINSEBPI=
RINSEPOSTINST=--after-post-install $(CURDIR)/local.rinse.api
VEIDDEB=100
VEIDCEN=101
VEIDDEBTMPL=105
VEIDCENTOSTMPL=110
ARCH=$(shell dpkg-architecture -qDEB_BUILD_ARCH)
DIST=squeeze
EXTRADEBPACKAGE=--include=ssh,less,htop,zsh
EXTRACENTOSPACKAGE=openssh-server,man,zsh,bind-utils
DEBVERS=6.0
CENTVERS=5.5
VZPRIVATEDIR=/var/lib/vz/private
# You shouldn't change the next line, don't really work
VZTEMPLATEDIR=/var/lib/vz/template
VZCTL=sudo /usr/sbin/vzctl
DEBOOTSTRAP=/usr/sbin/debootstrap
RINSE=extrapackage="$(EXTRACENTOSPACKAGE)" /usr/sbin/rinse

IPDEBBASE=192.168.222
IPDEBDNS=192.168.222.1

IPCENTOSBASE=192.168.222
IPCENTOSDNS=192.168.222.1

# Local customization can be done using a local.<hostname> file
# This is convenient if you use local mirror
-include local.$(shell hostname)

default: 
	@echo "Run make <target>"
	@echo "Available targets are:"
	@echo " make net : this will setup a /etc/vz/vznet.conf to register new virtual host to vmbr0 bridge"
	@echo "  See interfaces.sample to know how to setup a bridge"
	@echo " make builddebiantemplate : build an openvz debian template"
	@echo " make buildcentostemplate : build an openvz centos template"
	@echo ""
	@echo " make createdeb   : build a debian openvz vm with ID=$(VEIDDEBTMPL) from the debian template"
	@echo " make netdhcpdeb  : set dhcp network for debian openvz vm with ID=$(VEIDDEBTMPL)"
	@echo " make netstaticdeb: set static network for debian openvz vm with ID=$(VEIDDEBTMPL)"
	@echo ""
	@echo " make createcentos   : build a centos openvz vm with ID=$(VEIDCENTOSTMPL) from the centos template"
	@echo " make netdhcpcentos  : set dhcp network for centos openvz vm with ID=$(VEIDCENTOSTMPL)"
	@echo " make netstaticcentos: set static network for centos openvz vm with ID=$(VEIDCENTOSTMPL)"
	@echo ""
	@echo "Read Makefile for other targets"

builddebiantemplate: beroot builddebianvm
	$(VZCTL) set $(VEIDDEB) --ostemplate debian-$(DEBVERS) --applyconfig basic --save
	# remove host keys
	#rm $(VZPRIVATEDIR)/$(VEIDDEB)/etc/ssh/ssh_host_*
	# Disable getty in /etc/inittab as openvz do not have that.
	[ ! -f $(VZPRIVATEDIR)/$(VEIDDEB)/etc/inittab ] || (grep -q getty $(VZPRIVATEDIR)/$(VEIDDEB)/etc/inittab || \
		sed -i -e '/getty/d' $(VZPRIVATEDIR)/$(VEIDDEB)/etc/inittab)
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f $(VZPRIVATEDIR)/$(VEIDDEB)/etc/mtab
	ln -s /proc/mounts $(VZPRIVATEDIR)/$(VEIDDEB)/etc/mtab
	# LocalTime
	#ln -sf /usr/share/zoneinfo/Europe/Paris $(VZPRIVATEDIR)/$(VEIDDEB)/etc/localtime
	# Remove hostname
	[ ! -f $(VZPRIVATEDIR)/$(VEIDDEB)/etc/hostname ] || rm $(VZPRIVATEDIR)/$(VEIDDEB)/etc/hostname
	#
	[ -c $(VZPRIVATEDIR)/$(VEIDDEB)/dev/ptmx ] || chroot $(VZPRIVATEDIR)/$(VEIDDEB) /bin/mknod /dev/ptmx c 5 2
	cd $(VZPRIVATEDIR)/$(VEIDDEB)/ ; tar --numeric-owner -zcf $(VZTEMPLATEDIR)/cache/debian-$(DEBVERS)-$(ARCH)-minimal.tar.gz .

buildcentostemplate: beroot buildcentosvm
	$(VZCTL) set $(VEIDCEN) --ostemplate centos-$(CENTVERS) --applyconfig basic --save
	#rm $(VZPRIVATEDIR)/$(VEIDCEN)/etc/ssh/ssh_host_*
	# Disable getty in /etc/inittab as openvz do not have that.
	[ ! -f $(VZPRIVATEDIR)/$(VEIDCEN)/etc/inittab ] || (grep -q getty $(VZPRIVATEDIR)/$(VEIDCEN)/etc/inittab || \
		sed -i -e '/getty/d' $(VZPRIVATEDIR)/$(VEIDCEN)/etc/inittab)
	# Don't start_udev
	sed -i -e '/start_udev/d' $(VZPRIVATEDIR)/$(VEIDCEN)/etc/rc.d/rc.sysinit
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f $(VZPRIVATEDIR)/$(VEIDCEN)/etc/mtab
	ln -s /proc/mounts $(VZPRIVATEDIR)/$(VEIDCEN)/etc/mtab
	# LocalTime
	#ln -sf /usr/share/zoneinfo/Europe/Paris $(VZPRIVATEDIR)/$(VEIDCEN)/etc/localtime
	# Remove hostname
	[ ! -f $(VZPRIVATEDIR)/$(VEIDCEN)/etc/hostname ] || rm $(VZPRIVATEDIR)/$(VEIDCEN)/etc/hostname
	#
	[ -c $(VZPRIVATEDIR)/$(VEIDCEN)/dev/ptmx ]     || chroot $(VZPRIVATEDIR)/$(VEIDCEN) /bin/mknod /dev/ptmx c 5 2
	[ -d $(VZPRIVATEDIR)/$(VEIDCEN)/dev/pts ]      || chroot $(VZPRIVATEDIR)/$(VEIDCEN) /bin/mkdir /dev/pts
	[ -c $(VZPRIVATEDIR)/$(VEIDCEN)/vz/private/50/dev/ttyp ] || chroot $(VZPRIVATEDIR)/$(VEIDCEN) /sbin/MAKEDEV -d /vz/private/50/dev ttyp ptyp
	-rm -f $(VZPRIVATEDIR)/$(VEIDCEN)/dev/null
	[ -c $(VZPRIVATEDIR)/$(VEIDCEN)/dev/null ]     || chroot $(VZPRIVATEDIR)/$(VEIDCEN) /bin/mknod /dev/null c 1 3
	[ -c $(VZPRIVATEDIR)/$(VEIDCEN)/dev/random ]   || chroot $(VZPRIVATEDIR)/$(VEIDCEN) /bin/mknod -m 644 /dev/random c 1 8
	-rm -f $(VZPRIVATEDIR)/$(VEIDCEN)/dev/urandom 
	[ -c $(VZPRIVATEDIR)/$(VEIDCEN)/dev/urandom ]  || chroot $(VZPRIVATEDIR)/$(VEIDCEN) /bin/mknod /dev/urandom c 1 9 
	[ -d $(VZPRIVATEDIR)/$(VEIDCEN)/var/lock/rpm ] || chroot $(VZPRIVATEDIR)/$(VEIDCEN) /bin/mkdir /var/lock/rpm	
	touch $(VZPRIVATEDIR)/$(VEIDCEN)/etc/fstab
	grep -q devpts $(VZPRIVATEDIR)/$(VEIDCEN)/etc/fstab || \
		echo "none /dev/pts devpts mode=0620 0 0" >> $(VZPRIVATEDIR)/$(VEIDCEN)/etc/fstab
	cd $(VZPRIVATEDIR)/$(VEIDCEN)/ ; tar --numeric-owner -zcf $(VZTEMPLATEDIR)/cache/centos-$(CENTVERS)-$(ARCH)-minimal.tar.gz .

builddebianvm: beroot $(VZPRIVATEDIR)/$(VEIDDEB)/tmp/vzdebvm

$(VZPRIVATEDIR)/$(VEIDDEB)/tmp/vzdebvm:
	[ -d $(VZPRIVATEDIR)/$(VEIDDEB)/tmp/vzdebvm ] || ($(DEBOOTSTRAP) --arch $(ARCH) $(EXTRADEBPACKAGE) $(DIST) $(VZPRIVATEDIR)/$(VEIDDEB) $(DEBMIRROR) && touch $(VZPRIVATEDIR)/$(VEIDDEB)/tmp/vzdebvm)

buildcentosvm: beroot $(VZPRIVATEDIR)/$(VEIDCEN)/tmp/vzcentvm

$(VZPRIVATEDIR)/$(VEIDCEN)/tmp/vzcentvm:
	[ -d $(VZPRIVATEDIR)/$(VEIDCEN)/tmp/vzcentvm ] || ($(RINSE) $(RINSEBPI) $(RINSEPOSTINST) --config $(RINSECONF) --arch $(ARCH) --distribution centos-5 --directory $(VZPRIVATEDIR)/$(VEIDCEN) && touch $(VZPRIVATEDIR)/$(VEIDCEN)/tmp/vzcentvm)

cleanvz:
	[ ! -d $(VZPRIVATEDIR)/$(VEID) ] || $(VZCTL) stop $(VEID)
	[ ! -d $(VZPRIVATEDIR)/$(VEID) ] || $(VZCTL) destroy $(VEID)
	-rm -f /etc/vz/conf/$(VEID).conf.destroyed

cleancentos: beroot
	make cleanvz VEID=$(VEIDCENTOSTMPL)
	
cleandeb: beroot
	make cleanvz VEID=$(VEIDDEBTMPL)

finalizedeb: beroot
	# Give it a hostname.
	echo `hostname`-$(VEIDDEBTMPL) > $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/hostname
	# Disable getty in /etc/inittab as openvz do not have that.
	sed -i -e '/getty/d' $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/inittab
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/mtab
	ln -s /proc/mounts $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/mtab
	# If an IP address is needed for the virtual server
	#$(VZCTL) set $(VEIDDEBTMPL) --ipadd x.x.x.x --save
	# In that case you also need to enable IP forwarding
	#sysctl -w net.ipv4.ip_forward=1

netdhcpdeb: beroot
	# Add eth0 interface
	$(VZCTL) set $(VEIDDEBTMPL) --netif_add eth0 --save
	# Setup dhcp
	grep -q "auto lo" $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces || \
		echo "auto lo" >> $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces
	grep -q "iface lo inet loopback" $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces || \
		echo "iface lo inet loopback" >> $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces
	grep -q "auto eth0" $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces || \
		echo "auto eth0" >> $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces
	grep -q "iface eth0 inet dhcp" $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces || \
		echo "iface eth0 inet dhcp" >> $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/network/interfaces
	# Setup dhclient extra
	grep -q "^send host-name" $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/dhcp/dhclient.conf || \
		echo "send host-name \"`hostname`-$(VEIDDEBTMPL)\";" >> $(VZPRIVATEDIR)/$(VEIDDEBTMPL)/etc/dhcp/dhclient.conf

netstaticdeb: beroot
	$(VZCTL) set $(VEIDDEBTMPL) --ipadd $(IPDEBBASE).$(VEIDDEBTMPL) --save
	$(VZCTL) set $(VEIDDEBTMPL) --nameserver $(IPDEBDNS) --save
	sysctl -w net.ipv4.ip_forward=1
	sysctl -w net.ipv4.conf.default.forwarding=1
	sysctl -w net.ipv4.conf.default.proxy_arp=0
	sysctl -w net.ipv4.conf.all.rp_filter=1
	sysctl -w kernel.sysrq=1

finalizecentos: beroot
	# Give it a hostname.
	echo `hostname`-$(VEIDCENTOSTMPL) > $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/hostname
	touch $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/sysconfig/network
	grep -q "^HOSTNAME=" $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/sysconfig/network || \
		echo "HOSTNAME=`hostname`-$(VEIDCENTOSTMPL)" >> $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/sysconfig/network

netdhcpcentos: beroot
	# Add eth0 interface
	$(VZCTL) set $(VEIDCENTOSTMPL) --netif_add eth0 --save
	# Setup dhcp
	echo "DEVICE=eth0" > $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/sysconfig/network-scripts/ifcfg-eth0
	echo "BOOTPROTO=dhcp" >> $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/sysconfig/network-scripts/ifcfg-eth0
	echo "ONBOOT=yes" >> $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/sysconfig/network-scripts/ifcfg-eth0
	# Setup dhclient extra
	[ -f $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/dhclient.conf ] || \
		echo "send host-name \"`hostname`-$(VEIDCENTOSTMPL)\";" > $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL)/etc/dhclient.conf
	
netstaticcentos: beroot
	$(VZCTL) set $(VEIDCENTOSTMPL) --ipadd $(IPCENTOSBASE).$(VEIDCENTOSTMPL) --save
	$(VZCTL) set $(VEIDCENTOSTMPL) --nameserver $(IPCENTOSDNS) --save
	sysctl -w net.ipv4.ip_forward=1
	sysctl -w net.ipv4.conf.default.forwarding=1
	sysctl -w net.ipv4.conf.default.proxy_arp=0
	sysctl -w net.ipv4.conf.all.rp_filter=1
	sysctl -w kernel.sysrq=1

net: /etc/vz/vznet.conf

/etc/vz/vznet.conf:
	echo '#!/bin/bash' > /etc/vz/vznet.conf
	echo 'EXTERNAL_SCRIPT="/usr/sbin/vznetaddbr"' >> /etc/vz/vznet.conf

addbridge:
	# Prefered method is to change /etc/network/interfaces
	#sudo brctl addbr vmbr0
	#sudo ifconfig vmbr0 0
	#sudo brctl addif vmbr0 eth0

createdeb: beroot cleandeb
	$(VZCTL) create $(VEIDDEBTMPL) --private $(VZPRIVATEDIR)/$(VEIDDEBTMPL) --ostemplate debian-$(DEBVERS)-$(ARCH)-minimal
	make finalizedeb
	
createcentos: beroot cleancentos
	$(VZCTL) create $(VEIDCENTOSTMPL) --private $(VZPRIVATEDIR)/$(VEIDCENTOSTMPL) --ostemplate centos-$(CENTVERS)-$(ARCH)-minimal
	make finalizecentos
	
beroot:
	@[ $(shell id -u) = "0" ] || (echo "you should be root to run this" ; exit 1)

start:
	sudo $(VZCTL) start $(VEID)
