
VMROOT=/var/lib/lxc/vmdebian6
SSHPUBKEY=~$(USERCLIENT)/.ssh/id_rsa.pub

-include config.default
-include config.$(shell hostname)

default: vmdebian6-config netstaticdeb installkey
	@echo "read the Makefile to see available targets"

/cgroup:
	sudo mkdir /cgroup

checkfstab:
	grep ^cgroup /etc/fstab || echo "You need to add cgroup entry to fstab \"cgroup        /cgroup        cgroup        defaults    0    0\""

mount:
	sudo mount /cgroup

/var/lib/lxc/vmdebian6: /usr/bin/lxc-info bridging
	sudo mkdir $@
	sudo LANG=C MIRROR=$(DEBMIRROR) SUITE=$(DIST) ./lxc-debian6 -p $@

/usr/bin/lxc-info:
	sudo apt-get install lxc bridge-utils

bridging:
	@grep -q '^iface br0 inet' /etc/network/interfaces || (echo "Please setup network bridging" && false)

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
	)
	#sudo sh -c "echo \"	dns-search $(IPDEBDOMAIN)\" >> $(VMROOT)/rootfs/etc/network/interfaces" ; \
	#

installkey:
	[ -d $(VMROOT)/root/.ssh ] || sudo mkdir -p $(VMROOT)/root/.ssh
	sudo cp $(SSHPUBKEY) $(VMROOT)/root/.ssh/authorized_keys
	sudo cp ssh/ssh_host_* $(VMROOT)/etc/ssh/
