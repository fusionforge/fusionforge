
DEBMIRROR=http://ftp.fr.debian.org/debian
RINSECONF=/etc/rinse/rinse.conf
RINSEBPI=
RINSEPOSTINST=--after-post-install $(CURDIR)/local.rinse.api
VEIDDEBTEMP=242
VEIDCOSTEMP=243
ARCH=$(shell dpkg-architecture -qDEB_BUILD_ARCH)
EXTRADEBPACKAGE=--include=ssh,less,htop,zsh,vim,debconf-utils
EXTRACOSPACKAGE=openssh-server,man,zsh,bind-utils,openssh-clients,php,wget
VZCTL=/usr/sbin/vzctl
DEBOOTSTRAP=/usr/sbin/debootstrap
RINSE=extrapackage="$(EXTRACOSPACKAGE)" /usr/sbin/rinse

# Default config
include ../config/default

# Optional local change
-include ../config/$(shell hostname)

# Local customization can be done using a local.<hostname> file
# This is convenient if you use local mirror
-include local.$(shell hostname)

default: 
	@echo "Run make <target>"
	@echo "Available targets are:"
	@echo " make net : this will setup a /etc/vz/vznet.conf to register new virtual host to vmbr0 bridge"
	@echo "  See interfaces.sample to know how to setup a bridge"
	@echo " make builddebiantemplate : build an openvz debian template (uses VEIDDEBTEMP=$(VEIDDEBTEMP))"
	@echo " make buildcentostemplate : build an openvz centos template (uses VEIDCOSTEMP=$(VEIDCOSTEMP))"
	@echo ""
	@echo " make createdeb   : build a debian openvz vm with ID=$(VEIDDEB) from the debian template"
	@echo " make netdhcpdeb  : set dhcp network for debian openvz vm with ID=$(VEIDDEB)"
	@echo " make netstaticdeb: set static network for debian openvz vm with ID=$(VEIDDEB)"
	@echo ""
	@echo " make createcentos   : build a centos openvz vm with ID=$(VEIDCOS) from the centos template"
	@echo " make netdhcpcentos  : set dhcp network for centos openvz vm with ID=$(VEIDCOS)"
	@echo " make netstaticcentos: set static network for centos openvz vm with ID=$(VEIDCOS)"
	@echo ""
	@echo "Read Makefile for other targets"

builddebiantemplate: beroot builddebianvm
	$(VZCTL) set $(VEIDDEBTEMP) --ostemplate debian-$(DEBVERS) --applyconfig basic --save
	# remove host keys
	#rm $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/ssh/ssh_host_*
	# Disable getty in /etc/inittab as openvz do not have that.
	[ ! -f $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/inittab ] || (grep -q getty $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/inittab || \
		sed -i -e '/getty/d' $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/inittab)
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/mtab
	ln -s /proc/mounts $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/mtab
	# LocalTime
	#ln -sf /usr/share/zoneinfo/Europe/Paris $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/localtime
	# Remove hostname
	[ ! -f $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/hostname ] || rm $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/hostname
	## NOW uses vzctl set $(VEIDDEBTEMP) --hostname command
	##grep -q debian6.local $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/hosts || \
	##	sed -i -e 's/localhost/debian6.local localhost/' $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/hosts
	##echo "debian6.local" > $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/hostname
	#
	[ -c $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/dev/ptmx ] || chroot $(VZPRIVATEDIR)/$(VEIDDEBTEMP) /bin/mknod /dev/ptmx c 5 2
	[ -d $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/root/.ssh ] || mkdir -p $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/root/.ssh
	cp $(SSHPUBKEY) $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/root/.ssh/authorized_keys
	cp ssh/ssh_host_* $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/etc/ssh/
	cd $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/ ; tar --numeric-owner -zcf $(VZTEMPLATEDIR)/cache/debian-$(DEBVERS)-$(ARCH)-minimal.tar.gz .

buildcentostemplate: beroot buildcentosvm
	$(VZCTL) set $(VEIDCOSTEMP) --ostemplate centos-$(COSVERS) --applyconfig basic --save
	#rm $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/ssh/ssh_host_*
	# Disable getty in /etc/inittab as openvz do not have that.
	[ ! -f $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/inittab ] || (grep -q getty $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/inittab || \
		sed -i -e '/getty/d' $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/inittab)
	# Don't start_udev
	sed -i -e '/start_udev/d' $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/rc.d/rc.sysinit
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/mtab
	ln -s /proc/mounts $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/mtab
	# LocalTime
	#ln -sf /usr/share/zoneinfo/Europe/Paris $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/localtime
	# Remove hostname
	[ ! -f $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/hostname ] || rm $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/hostname
	## NOW uses vzctl set $(VEIDCOSTEMP) --hostname command
	##echo "127.0.0.1 centos5.local localhost.localdomain localhost" > $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/hosts
	##echo "centos5.local" > $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/hostname
	##echo "HOSTNAME=centos5.local" >> $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/sysconfig/network
	#
	[ -c $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/ptmx ]     || chroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP) /bin/mknod /dev/ptmx c 5 2
	[ -d $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/pts ]      || chroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP) /bin/mkdir /dev/pts
	[ -c $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/vz/private/50/dev/ttyp ] || chroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP) /sbin/MAKEDEV -d /vz/private/50/dev ttyp ptyp
	-rm -f $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/null
	[ -c $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/null ]     || chroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP) /bin/mknod /dev/null c 1 3
	chmod ugo+w $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/null
	[ -c $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/random ]   || chroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP) /bin/mknod -m 644 /dev/random c 1 8
	-rm -f $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/urandom 
	[ -c $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/urandom ]  || chroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP) /bin/mknod /dev/urandom c 1 9 
	chmod ugo+w $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/dev/urandom
	[ -d $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/var/lock/rpm ] || chroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP) /bin/mkdir /var/lock/rpm	
	touch $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/fstab
	grep -q devpts $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/fstab || \
		echo "none /dev/pts devpts mode=0620 0 0" >> $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/fstab
	[ -d $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/root/.ssh ] || mkdir $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/root/.ssh
	cp $(SSHPUBKEY) $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/root/.ssh/authorized_keys
	cp ssh/ssh_host_* $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/etc/ssh/
	cd $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/ ; tar --numeric-owner -zcf $(VZTEMPLATEDIR)/cache/centos-$(COSVERS)-$(ARCH)-minimal.tar.gz .

builddebianvm: beroot $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/tmp/vzdebvm

$(VZPRIVATEDIR)/$(VEIDDEBTEMP)/tmp/vzdebvm:
	[ -d $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/tmp/vzdebvm ] || ($(DEBOOTSTRAP) --arch $(ARCH) $(EXTRADEBPACKAGE) $(DIST) $(VZPRIVATEDIR)/$(VEIDDEBTEMP) $(DEBMIRROR) && touch $(VZPRIVATEDIR)/$(VEIDDEBTEMP)/tmp/vzdebvm)

buildcentosvm: beroot $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/tmp/vzcentvm

$(VZPRIVATEDIR)/$(VEIDCOSTEMP)/tmp/vzcentvm:
	[ -d $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/tmp/vzcentvm ] || ($(RINSE) $(RINSEBPI) $(RINSEPOSTINST) --config $(RINSECONF) --arch $(ARCH) --distribution centos-5 --directory $(VZPRIVATEDIR)/$(VEIDCOSTEMP) && touch $(VZPRIVATEDIR)/$(VEIDCOSTEMP)/tmp/vzcentvm)

cleanvz:
	[ ! -d $(VZPRIVATEDIR)/$(VEID) ] || $(VZCTL) stop $(VEID)
	[ ! -d $(VZPRIVATEDIR)/$(VEID) ] || $(VZCTL) destroy $(VEID)
	-rm -f /etc/vz/conf/$(VEID).conf.destroyed

cleancentos: beroot
	make cleanvz VEID=$(VEIDCOS)
	
cleandeb: beroot
	make cleanvz VEID=$(VEIDDEB)

netdhcpdeb: beroot
	# Add eth0 interface
	$(VZCTL) set $(VEIDDEB) --netif_add eth0 --save
	# Setup dhcp
	grep -q "auto lo" $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces || \
		echo "auto lo" >> $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces
	grep -q "iface lo inet loopback" $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces || \
		echo "iface lo inet loopback" >> $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces
	grep -q "auto eth0" $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces || \
		echo "auto eth0" >> $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces
	grep -q "iface eth0 inet dhcp" $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces || \
		echo "iface eth0 inet dhcp" >> $(VZPRIVATEDIR)/$(VEIDDEB)/etc/network/interfaces
	# Setup dhclient extra
	grep -q "^send host-name" $(VZPRIVATEDIR)/$(VEIDDEB)/etc/dhcp/dhclient.conf || \
		echo "send host-name \"`hostname`-$(VEIDDEB)\";" >> $(VZPRIVATEDIR)/$(VEIDDEB)/etc/dhcp/dhclient.conf

netstaticdeb: beroot
	$(VZCTL) set $(VEIDDEB) --hostname `hostname`-$(VEIDDEB) --save
	$(VZCTL) set $(VEIDDEB) --ipadd $(IPDEBBASE).$(VEIDDEB) --save
	$(VZCTL) set $(VEIDDEB) --nameserver $(IPDEBDNS) --save

netdhcpcentos: beroot
	# Add eth0 interface
	$(VZCTL) set $(VEIDCOS) --netif_add eth0 --save
	# Setup dhcp
	echo "DEVICE=eth0" > $(VZPRIVATEDIR)/$(VEIDCOS)/etc/sysconfig/network-scripts/ifcfg-eth0
	echo "BOOTPROTO=dhcp" >> $(VZPRIVATEDIR)/$(VEIDCOS)/etc/sysconfig/network-scripts/ifcfg-eth0
	echo "ONBOOT=yes" >> $(VZPRIVATEDIR)/$(VEIDCOS)/etc/sysconfig/network-scripts/ifcfg-eth0
	# Setup dhclient extra
	[ -f $(VZPRIVATEDIR)/$(VEIDCOS)/etc/dhclient.conf ] || \
		echo "send host-name \"`hostname`-$(VEIDCOS)\";" > $(VZPRIVATEDIR)/$(VEIDCOS)/etc/dhclient.conf
	
netstaticcentos: beroot
	$(VZCTL) set $(VEIDCOS) --hostname `hostname`-$(VEIDCOS)  --save
	$(VZCTL) set $(VEIDCOS) --ipadd $(IPCOSBASE).$(VEIDCOS) --save
	$(VZCTL) set $(VEIDCOS) --nameserver $(IPCOSDNS) --save

netctl:
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
	$(VZCTL) create $(VEIDDEB) --private $(VZPRIVATEDIR)/$(VEIDDEB) --ostemplate debian-$(DEBVERS)-$(ARCH)-minimal
	make netstaticdeb
	
createcentos: beroot cleancentos
	$(VZCTL) create $(VEIDCOS) --private $(VZPRIVATEDIR)/$(VEIDCOS) --ostemplate centos-$(COSVERS)-$(ARCH)-minimal
	make netstaticcentos
	
beroot:
	@[ $(shell id -u) = "0" ] || (echo "you should be root to run this" ; exit 1)

start:
	sudo $(VZCTL) start $(VEID)
