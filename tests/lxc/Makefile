
VMROOT=/var/lib/lxc/vmdebian6
SSHPUBKEY=~$(USERCLIENT)/.ssh/id_rsa.pub

-include config.default
-include config.$(shell hostname)

default: vmdebian6-config netstaticdeb installkey
	@echo "read the Makefile to see available targets"

/cgroup: beroot
	mkdir /cgroup

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

vmdebian6-clean: beroot
	lxc-stop -n vmdebian6 || true
	lxc-destroy -n vmdebian6 || true

/var/lib/lxc/vmcentos5: beroot
	mkdir $@

netstaticdeb: beroot
	@grep -q "iface eth0 inet static" $(VMROOT)/rootfs/etc/network/interfaces || \
	(sh -c "echo \"auto lo\" > $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"iface lo inet loopback\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"auto eth0\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"iface eth0 inet static\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"	address $(IPDEBBASE).$(VEIDDEB)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"	netmask 255.255.255.0\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"	gateway $(IPDEBBASE).1\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sh -c "echo \"	dns-nameservers $(IPDEBDNS)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	)

netstaticdns: beroot
	#sh -c "echo \"	dns-search $(IPDEBDOMAIN)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	#

installkey: beroot
	[ -d $(VMROOT)/root/.ssh ] || mkdir -p $(VMROOT)/root/.ssh
	cp $(SSHPUBKEY) $(VMROOT)/root/.ssh/authorized_keys
	[ -d $(VMROOT)/etc/ssh ] || mkdir -p $(VMROOT)/etc/ssh
	cp ssh/ssh_host_* $(VMROOT)/etc/ssh/

beroot:
	@[ $(shell id -u) = "0" ] || (echo "you should be root to run this" ; exit 1)

start: /cgroup/tasks
	lxc-start -n vmdebian6 -d
	sleep 5
	lxc-info -n vmdebian6

