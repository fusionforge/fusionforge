-include ../config/default
-include ../config/$(shell hostname)

default: installtemplate

help: 
	@echo "read the Makefile to see available targets"

/cgroup:
	[ -d /cgroup ] || ( if [ $(shell id -u) != "0" ] then; echo "you should be root to run this" ; else; mkdir /cgroup; fi

checkmount: checkfstab /cgroup
	grep -q 0 /cgroup/cpuset.cpus || mount /cgroup

checkfstab:
	@grep ^cgroup /etc/fstab || (echo "You need to add cgroup entry to fstab 'cgroup /cgroup cgroup defaults 0 0'" && false)

/usr/lib/lxc:
	@[ -d $@ ] || sudo apt-get install lxc

/usr/share/bridge-utils:
	@[ -d $@ ] || apt-get install bridge-utils

/usr/bin/netmask:
	@[ -f $@ ] || sudo apt-get install netmask

/usr/bin/rinse:
	@[ -f $@ ] || sudo apt-get install rinse

/usr/sbin/tunctl:
	@[ -f $@ ] || sudo apt-get install uml-utilities

bridging:
	@grep -q '^iface br0 inet' /etc/network/interfaces || (echo "Please setup network bridging" && false)

ssh:
	[ -e ssh ] || (echo "You need a ssh dir containing host keys like /etc/ssh_host_*" && false)

setname: beroot
	echo "debian6.local" > $(VMROOT)/rootfs/etc/hostname
	grep -q debian6.local $(VMROOT)/rootfs/etc/hosts || echo "$(IPDEBBASE).$(VEIDDEB)	debian6.local debian6" >> $(VMROOT)/rootfs/etc/hosts

beroot:
	@[ $(shell id -u) = "0" ] || (echo "you should be root to run this" ; exit 1)

addrights: beroot
	lxc-setcap

delrights: beroot
	lxc-setcap -d

installtemplate: /usr/lib/lxc/templates/lxc-debian6 /usr/lib/lxc/templates/lxc-centos5

/usr/lib/lxc/templates/lxc-debian6: lxc-debian6 /usr/lib/lxc
	cat lxc-debian6 | sed 's!http://cdn.debian.net/debian/!$(DEBMIRROR)!' > /usr/lib/lxc/templates/lxc-debian6
	chmod 755 /usr/lib/lxc/templates/lxc-debian6

/usr/lib/lxc/templates/lxc-centos5: lxc-centos5 /usr/lib/lxc
	cp lxc-centos5 /usr/lib/lxc/templates/

createdeb:
	sudo /usr/bin/lxc-create -n debian6.local -f config.debian6 -t debian6

createcos: /usr/bin/netmask /usr/bin/rinse
	sudo /usr/bin/lxc-create -n centos5.local -f config.centos5 -t centos5

startdeb: checkmount
	sudo LANG=C /usr/bin/lxc-start -n debian6.local -d

stopdeb:
	sudo LANG=C /usr/bin/lxc-stop -n debian6.local

startcos: checkmount
	sudo LANG=C /usr/bin/lxc-start -n centos5.local -d

stopcos:
	sudo LANG=C /usr/bin/lxc-stop -n centos5.local

destroydeb: beroot
	sudo LANG=C /usr/bin/lxc-stop -n debian6.local
	sudo LANG=C /usr/bin/lxc-destroy -n debian6.local

destroycos: beroot
	sudo LANG=C /usr/bin/lxc-stop -n centos5.local
	sudo LANG=C /usr/bin/lxc-destroy -n centos5.local
