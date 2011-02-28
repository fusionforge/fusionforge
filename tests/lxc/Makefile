-include config.default
-include config.$(shell hostname)

VMROOT=/var/lib/lxc/vmdebian6

default: vmdebian6-config netstaticdeb
	@echo "read the Makefile to see available targets"

/cgroup:
	sudo mkdir /cgroup

checkfstab:
	grep ^cgroup /etc/fstab || echo "You need to add cgroup entry to fstab \"cgroup        /cgroup        cgroup        defaults    0    0\""

mount:
	sudo mount /cgroup

/var/lib/lxc/vmdebian6:
	sudo mkdir $@
	sudo LANG=C MIRROR=$(DEBMIRROR) SUITE=$(DIST) ./lxc-debian6 -p $@

vmdebian6-config: /var/lib/lxc/vmdebian6
	@grep -q lxc.utsname /var/lib/lxc/vmdebian6/config || \
	(sudo sh -c "echo \"lxc.utsname = vmdebian6\" >> $(VMROOT)/config" ; \
	sudo sh -c "echo \"lxc.network.type = veth\" >> $(VMROOT)/config" ; \
	sudo sh -c "echo \"lxc.network.flags = up\" >> $(VMROOT)/config" ; \
	sudo sh -c "echo \"lxc.network.link = br0\" >> $(VMROOT)/config" ; \
	)
	#sudo sh -c "echo \"lxc.network.ipv4 = $(IPDEBBASE).$(VEIDDEB)/24\">> $(VMROOT)/config" ; \
	#

vmdebian6-start:
	sudo lxc-start -n vmdebian6 -d
	sleep 5
	sudo lxc-info -n vmdebian6

vmdebian6-clean:
	sudo lxc-stop -n vmdebian6 || true
	sudo lxc-destroy -n vmdebian6 || true

/var/lib/lxc/vmcentos5:
	sudo mkdir $@

netstaticdeb:
	@grep -q "iface eth0 inet static" $(VMROOT)/rootfs/etc/network/interfaces || \
	(sudo sh -c "echo \"auto lo\" > $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"iface lo inet loopback\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"auto eth0\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"iface eth0 inet static\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"	address $(IPDEBBASE).$(VEIDDEB)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"	netmask 255.255.255.0\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"	gateway $(IPDEBBASE).1\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"	dns-nameservers $(IPDEBDNS)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	sudo sh -c "echo \"	dns-search $(IPDEBDOMAIN)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	)
