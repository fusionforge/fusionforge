-include ../config/default
-include ../config/$(shell hostname)

# Guess where lxc data is and default to /usr/lib/lxc
LXC_DATA:=$(shell [ -d /usr/lib/lxc ] && echo /usr/lib/lxc)$(shell [ -d /usr/share/lxc ] && echo /usr/share/lxc)
LXC_DATA:=$(shell [ -z "$(LXC_DATA)" ] && echo /usr/lib/lxc)$(shell [ -z "$(LXC_DATA)" ] || echo "$(LXC_DATA)")
TEMPLATELIST:=$(LXC_DATA)/templates/lxc-debian7 $(LXC_DATA)/templates/lxc-debian6 $(LXC_DATA)/templates/lxc-centos5 $(LXC_DATA)/templates/lxc-centos6

default: checkdepends installtemplate

help: 
	@echo "read the Makefile to see available targets"

/cgroup:
	[ -d /cgroup ] || ( if [ $(shell id -u) != "0" ] then; echo "you should be root to run this" ; else; mkdir /cgroup; fi

checkmount: checkfstab /cgroup
	grep -q 0 /cgroup/cpuset.cpus || mount /cgroup

checkfstab:
	@grep ^cgroup /etc/fstab || (echo "You need to add cgroup entry to fstab 'cgroup /cgroup cgroup defaults 0 0'" && false)

checkdepends: $(LXC_DATA) /lib/bridge-utils /usr/bin/netmask /usr/sbin/rinse /usr/sbin/tunctl

$(LXC_DATA):
	@[ -d $@ ] || sudo apt-get install lxc

/lib/bridge-utils:
	@[ -d $@ ] || sudo apt-get install bridge-utils

/usr/bin/netmask:
	@[ -f $@ ] || sudo apt-get install netmask

/usr/sbin/rinse:
	@[ -f $@ ] || sudo apt-get install rinse

/usr/sbin/tunctl:
	@[ -f $@ ] || sudo apt-get install uml-utilities

bridging:
	@grep -q '^iface br0 inet' /etc/network/interfaces || (echo "Please setup network bridging" && false)

ssh:
	[ -e ssh ] || (echo "You need a ssh dir containing host keys like /etc/ssh_host_*" && false)

setname: beroot
	echo "debian7.local" > $(VMROOT)/rootfs/etc/hostname
	grep -q debian7.local $(VMROOT)/rootfs/etc/hosts || echo "$(IPDEBBASE).$(VEIDDEB)	debian7.local debian7" >> $(VMROOT)/rootfs/etc/hosts

beroot:
	@[ $(shell id -u) = "0" ] || (echo "you should be root to run this" ; exit 1)

addrights: beroot
	lxc-setcap

delrights: beroot
	lxc-setcap -d

installtemplate: $(TEMPLATELIST)

removetemplate:
	rm -f $(TEMPLATELIST)

$(LXC_DATA)/templates/lxc-debian6: lxc-debian6 $(LXC_DATA) Makefile
	cat lxc-debian6 | sed 's!http://cdn.debian.net/debian/!$(DEBMIRROR)!' > $(LXC_DATA)/templates/lxc-debian6
	chmod 755 $(LXC_DATA)/templates/lxc-debian6

$(LXC_DATA)/templates/lxc-debian7: lxc-debian7 $(LXC_DATA) Makefile
	cat lxc-debian7 | sed 's!http://cdn.debian.net/debian/!$(DEBMIRROR)!' > $(LXC_DATA)/templates/lxc-debian7
	chmod 755 $(LXC_DATA)/templates/lxc-debian7

$(LXC_DATA)/templates/lxc-centos5: lxc-centos5 $(LXC_DATA) Makefile
	cp lxc-centos5 $(LXC_DATA)/templates/

$(LXC_DATA)/templates/lxc-centos6: lxc-centos6 $(LXC_DATA) Makefile
	cp lxc-centos6 $(LXC_DATA)/templates/

createdeb:
	sudo /usr/bin/lxc-create -n dhdebian6.local -f config.debian6 -t debian6
	sudo /usr/bin/lxc-create -n dhdebian7.local -f config.debian7 -t debian7

createcos: /usr/bin/netmask /usr/bin/rinse
	sudo /usr/bin/lxc-create -n dhcentos5.local -f config.centos5 -t centos5

startdeb: checkmount
	sudo LANG=C /usr/bin/lxc-start -n dhdebian7.local -d

stopdeb:
	sudo LANG=C /usr/bin/lxc-stop -n dhdebian7.local

startcos: checkmount
	sudo LANG=C /usr/bin/lxc-start -n dhcentos5.local -d

stopcos:
	sudo LANG=C /usr/bin/lxc-stop -n dhcentos5.local

destroydeb:
	sudo LANG=C /usr/bin/lxc-stop -n dhdebian7.local
	sudo LANG=C /usr/bin/lxc-destroy -n dhdebian7.local

destroycos:
	sudo LANG=C /usr/bin/lxc-stop -n dhcentos5.local
	sudo LANG=C /usr/bin/lxc-destroy -n dhcentos5.local

