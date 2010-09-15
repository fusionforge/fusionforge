
DEBMIRROR=http://ftp.fr.debian.org/debian
RINSECONF=/etc/rinse/rinse.conf
VEID=100
VEIDDEBTMPL=105
VEIDCENTOSTMPL=110
ARCH=amd64
DIST=squeeze
EXTRAPACKAGE="--include=ssh,less,htop"
DEBVERS=5.0
CENTVERS=5.4

-include local.$(shell hostname)

default: createcentos
	@echo "README !"

builddebiantemplate: builddebianvm
	vzctl set $(VEID) --applyconfig basic --save
	grep -q "^OSTEMPLATE=" /etc/vz/conf/$(VEID).conf || \
		echo "OSTEMPLATE=debian-$(DEBVERS)" >> /etc/vz/conf/$(VEID).conf
	rm /var/lib/vz/private/$(VEID)/etc/ssh/ssh_host_*
	# Disable getty in /etc/inittab as openvz do not have that.
	sed -i -e '/getty/d' /var/lib/vz/private/$(VEID)/etc/inittab
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f /var/lib/vz/private/$(VEID)/etc/mtab
	ln -s /proc/mounts /var/lib/vz/private/$(VEID)/etc/mtab
	# LocalTime
	#ln -sf /usr/share/zoneinfo/Europe/Paris /var/lib/vz/private/$(VEID)/etc/localtime
	# Remove hostname
	rm -f /var/lib/vz/private/$(VEID)/etc/hostname
	cd /var/lib/vz/private/$(VEID)/ ; tar --numeric-owner -zcf /var/lib/vz/template/cache/debian-$(DEBVERS)-$(ARCH)-minimal.tar.gz .

buildcentostemplate: buildcentosvm
	vzctl set $(VEID) --applyconfig basic --save
	grep -q "^OSTEMPLATE=" /etc/vz/conf/$(VEID).conf || \
		echo "OSTEMPLATE=centos-$(CENTVERS)" >> /etc/vz/conf/$(VEID).conf
	#rm /var/lib/vz/private/$(VEID)/etc/ssh/ssh_host_*
	# Disable getty in /etc/inittab as openvz do not have that.
	sed -i -e '/getty/d' /var/lib/vz/private/$(VEID)/etc/inittab
	# Don't start_udev
	sed -i -e '/start_udev/d' /var/lib/vz/private/$(VEID)/etc/rc.d/rc.sysinit
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f /var/lib/vz/private/$(VEID)/etc/mtab
	ln -s /proc/mounts /var/lib/vz/private/$(VEID)/etc/mtab
	# LocalTime
	#ln -sf /usr/share/zoneinfo/Europe/Paris /var/lib/vz/private/$(VEID)/etc/localtime
	# Remove hostname
	rm -f /var/lib/vz/private/$(VEID)/etc/hostname
	#
	[ -c /var/lib/vz/private/$(VEID)/dev/ptmx ]     || chroot /var/lib/vz/private/$(VEID) /bin/mknod /dev/ptmx c 5 2
	[ -d /var/lib/vz/private/$(VEID)/dev/pts ]      || chroot /var/lib/vz/private/$(VEID) /bin/mkdir /dev/pts
	[ -c /var/lib/vz/private/$(VEID)/vz/private/50/dev/ttyp ] || chroot /var/lib/vz/private/$(VEID) /sbin/MAKEDEV -d /vz/private/50/dev ttyp ptyp
	-rm -f /var/lib/vz/private/$(VEID)/dev/null
	[ -c /var/lib/vz/private/$(VEID)/dev/null ]     || chroot /var/lib/vz/private/$(VEID) /bin/mknod /dev/null c 1 3
	[ -c /var/lib/vz/private/$(VEID)/dev/random ]   || chroot /var/lib/vz/private/$(VEID) /bin/mknod -m 644 /dev/random c 1 8
	-rm -f /var/lib/vz/private/$(VEID)/dev/urandom 
	[ -c /var/lib/vz/private/$(VEID)/dev/urandom ]  || chroot /var/lib/vz/private/$(VEID) /bin/mknod /dev/urandom c 1 9 
	[ -d /var/lib/vz/private/$(VEID)/var/lock/rpm ] || chroot /var/lib/vz/private/$(VEID) /bin/mkdir /var/lock/rpm	
	grep -q devpts /var/lib/vz/private/$(VEID)/etc/fstab || \
		echo "none /dev/pts devpts mode=0620 0 0" >> /var/lib/vz/private/$(VEID)/etc/fstab
	cd /var/lib/vz/private/$(VEID)/ ; tar --numeric-owner -zcf /var/lib/vz/template/cache/centos-$(CENTVERS)-$(ARCH)-minimal.tar.gz .
		

builddebianvm: /var/lib/vz/private/$(VEID)/etc /etc/vz/conf/$(VEID).conf 

/var/lib/vz/private/$(VEID)/etc: cleanvz /var/lib/vz/private/$(VEID)
	[ -d /var/lib/vz/private/$(VEID)/etc ] || sudo debootstrap --arch $(ARCH) $(EXTRAPACKAGE) $(DIST) /var/lib/vz/private/$(VEID) $(DEBMIRROR)

buildcentosvm: /var/lib/vz/private/$(VEID)/etc/redhat-release 

/var/lib/vz/private/$(VEID)/etc/redhat-release: cleanvz
	[ -d /var/lib/vz/private/$(VEID)/etc/redhat-release ] || sudo rinse --config $(RINSECONF) --arch $(ARCH) --distribution centos-5 --directory /var/lib/vz/private/$(VEID)

cleanvz:
	-rmdir /var/lib/vz/private/$(VEID)
	[ ! -d /var/lib/vz/private/$(VEID) ] || vzctl stop $(VEID)
	[ ! -d /var/lib/vz/private/$(VEID) ] || vzctl destroy $(VEID)
	-rm -f /etc/vz/conf/$(VEID).conf.destroyed
	

/var/lib/vz/private/$(VEID):
	sudo mkdir /var/lib/vz/private/$(VEID)

/etc/vz/conf/$(VEID).conf:
	vzctl set $(VEID) --applyconfig basic --save
	echo "OSTEMPLATE=debian-$(DEBVERS)" >> /etc/vz/conf/$(VEID).conf

finalizedeb:
	# Give it a hostname.
	echo `hostname`-$(VEID) > /var/lib/vz/private/$(VEID)/etc/hostname
	# Disable getty in /etc/inittab as openvz do not have that.
	sed -i -e '/getty/d' /var/lib/vz/private/$(VEID)/etc/inittab
	# Link /etc/mtab to /proc/mtab to make mount work as expected.
	rm -f /var/lib/vz/private/$(VEID)/etc/mtab
	ln -s /proc/mounts /var/lib/vz/private/$(VEID)/etc/mtab
	# If an IP address is needed for the virtual server
	#vzctl set $(VEID) --ipadd x.x.x.x --save
	# In that case you also need to enable IP forwarding
	#sysctl -w net.ipv4.ip_forward=1
	# Add eth0 interface
	vzctl set $(VEID) --netif_add eth0 --save
	# Setup dhcp
	grep -q "auto lo" /var/lib/vz/private/$(VEID)/etc/network/interfaces || \
		echo "auto lo" >> /var/lib/vz/private/$(VEID)/etc/network/interfaces
	grep -q "iface lo inet loopback" /var/lib/vz/private/$(VEID)/etc/network/interfaces || \
		echo "iface lo inet loopback" >> /var/lib/vz/private/$(VEID)/etc/network/interfaces
	grep -q "auto eth0" /var/lib/vz/private/$(VEID)/etc/network/interfaces || \
		echo "auto eth0" >> /var/lib/vz/private/$(VEID)/etc/network/interfaces
	grep -q "iface eth0 inet dhcp" /var/lib/vz/private/$(VEID)/etc/network/interfaces || \
		echo "iface eth0 inet dhcp" >> /var/lib/vz/private/$(VEID)/etc/network/interfaces
	# Setup dhclient extra
	grep -q "^send host-name" /var/lib/vz/private/$(VEID)/etc/dhcp/dhclient.conf || \
		echo "send host-name \"`hostname`-$(VEID)\";" >> /var/lib/vz/private/$(VEID)/etc/dhcp/dhclient.conf

finalizecentos:
	# Give it a hostname.
	echo `hostname`-$(VEID) > /var/lib/vz/private/$(VEID)/etc/hostname
	# Add eth0 interface
	vzctl set $(VEID) --netif_add eth0 --save
	# Setup dhcp
	

net: /etc/vz/vznet.conf

/etc/vz/vznet.conf:
	echo '#!/bin/bash' > /etc/vz/vznet.conf
	echo 'EXTERNAL_SCRIPT="/usr/sbin/vznetaddbr"' >> /etc/vz/vznet.conf

addbridge:
	# Prefered method is to change /etc/network/interfaces
	#sudo brctl addbr vmbr0
	#sudo ifconfig vmbr0 0
	#sudo brctl addif vmbr0 eth0

createdeb:
	vzctl create $(VEIDDEBTMPL) --ostemplate debian-$(DEBVERS)-$(ARCH)-minimal
	make finalizedeb VEID=$(VEIDDEBTMPL)
	
createcentos:
	#vzctl create $(VEIDCENTOSTMPL) --ostemplate centos-$(CENTVERS)-$(ARCH)-minimal
	make finalizecentos VEID=$(VEIDCENTOSTMPL)
	
start:
	sudo vzctl start $(VEID)
