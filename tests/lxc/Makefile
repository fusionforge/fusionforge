
VMROOT=/var/lib/lxc/vmdebian6

-include ../config/default
-include ../config/$(shell hostname)

#default: vmdebian6-config netstaticdeb installkey
default: installtemplate
	@echo "read the Makefile to see available targets"

/cgroup: beroot
	[ ! -d /cgroup ] || mkdir /cgroup

checkmount: checkfstab /cgroup
	grep -q 0 /cgroup/cpuset.cpu || mount /cgroup

checkfstab:
	grep ^cgroup /etc/fstab || (echo "You need to add cgroup entry to fstab \"cgroup        /cgroup        cgroup        defaults    0    0\"" && false)

/var/lib/lxc/vmdebian6: beroot /usr/bin/lxc-info bridging
	mkdir $@
	LANG=C MIRROR=$(DEBMIRROR) SUITE=$(DIST) ./lxc-debian6 -p $@

/usr/bin/lxc-info: beroot
	apt-get install lxc bridge-utils

bridging:
	@grep -q '^iface br0 inet' /etc/network/interfaces || (echo "Please setup network bridging" && false)

vmdebian6-config: beroot /var/lib/lxc/vmdebian6
	@grep -q lxc.utsname /var/lib/lxc/vmdebian6/config || \
	(sh -c "echo \"lxc.utsname = vmdebian6\" >> $(VMROOT)/config" ; \
	sh -c "echo \"lxc.network.type = veth\" >> $(VMROOT)/config" ; \
	sh -c "echo \"lxc.network.flags = up\" >> $(VMROOT)/config" ; \
	sh -c "echo \"lxc.network.link = br0\" >> $(VMROOT)/config" ; \
	)

vmdebian6-ip: beroot
	#sh -c "echo \"lxc.network.ipv4 = $(IPDEBBASE).$(VEIDDEB)/24\">> $(VMROOT)/config" ; \
	#

cleandeb6: beroot
	lxc-stop -n vmdebian6 || true
	lxc-destroy -n vmdebian6 || true

netstaticdeb: beroot
	@grep -q "iface eth0 inet static" $(VMROOT)/rootfs/etc/network/interfaces || \
	(sh -c "echo \"auto lo\" > $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"iface lo inet loopback\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"auto eth0\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"iface eth0 inet static\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"	address $(IPDEBBASE).$(VEIDDEB)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"	netmask 255.255.255.0\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"	gateway $(IPDEBBASE).1\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	)

netstaticdns: beroot
	#sh -c "echo \"	dns-nameservers $(IPDEBDNS)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	#sh -c "echo \"	dns-search $(IPDEBDOMAIN)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	#

installkey: beroot ssh
	[ -d $(VMROOT)/root/.ssh ] || mkdir -p $(VMROOT)/rootfs/root/.ssh
	cp $(SSHPUBKEY) $(VMROOT)/rootfs/root/.ssh/authorized_keys
	[ -d $(VMROOT)/etc/ssh ] || mkdir -p $(VMROOT)/rootfs/etc/ssh
	cp ssh/ssh_host_* $(VMROOT)/rootfs/etc/ssh/

ssh:
	[ -e ssh ] || (echo "You need a ssh dir containing host keys like /etc/ssh_host_*" && false)

setname: beroot
	echo "debian6.local" > $(VMROOT)/rootfs/etc/hostname
	grep -q debian6.local $(VMROOT)/rootfs/etc/hosts || echo "$(IPDEBBASE).$(VEIDDEB)	debian6.local debian6" >> $(VMROOT)/rootfs/etc/hosts

beroot:
	@[ $(shell id -u) = "0" ] || (echo "you should be root to run this" ; exit 1)

lxcstart: /cgroup/tasks
	lxc-start -n vmdebian6 -d
	sleep 5
	lxc-info -n vmdebian6

addrights: beroot
	lxc-setcap

delrights: beroot
	lxc-setcap -d

installtemplate: beroot
	cat lxc-debian6 | sed 's!http://cdn.debian.net/debian/!$(DEBMIRROR)!' > /usr/lib/lxc/templates/lxc-debian6
	chmod 755 /usr/lib/lxc/templates/lxc-debian6
	cp lxc-centos5 /usr/lib/lxc/templates/

createdeb: beroot
	/usr/bin/lxc-create -n debian6.local -f config.debian6 -t debian6

createcos: beroot
	/usr/bin/lxc-create -n centos5.local -f config.centos5 -t centos5

startdeb:
	sudo LANG=C /usr/bin/lxc-start -n debian6.local -d

stopdeb:
	sudo LANG=C /usr/bin/lxc-stop -n debian6.local

startcos:
	sudo LANG=C /usr/bin/lxc-start -n centos5.local -d

stopcos:
	sudo LANG=C /usr/bin/lxc-stop -n centos5.local

destroydeb: beroot
	sudo LANG=C /usr/bin/lxc-stop -n debian6.local
	sudo LANG=C /usr/bin/lxc-destroy -n debian6.local

destroycos: beroot
	sudo LANG=C /usr/bin/lxc-stop -n centos5.local
	sudo LANG=C /usr/bin/lxc-destroy -n centos5.local
