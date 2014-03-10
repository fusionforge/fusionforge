if test -f .vbox_version ; then
    # Fix slow DNS
    echo 'RES_OPTIONS="single-request-reopen"' >> /etc/sysconfig/network
    service network restart

    # Installing the virtualbox guest additions
    VBOX_VERSION=$(cat .vbox_version)
    
    # For dmks
    cat > /etc/yum.repos.d/epel.repo << EOM
[epel]
name=epel
baseurl=http://download.fedoraproject.org/pub/epel/6/\$basearch
enabled=1
gpgcheck=0
EOM
    
    # Build dependencies for VirtualBox guest additions
    yum -y install gcc make gcc-c++ kernel-devel-`uname -r` zlib-devel openssl-devel readline-devel sqlite-devel perl dkms nfs-utils
    
    # Build work-around:
    # http://wiki.centos.org/HowTos/Virtualization/VirtualBox/CentOSguest
    # https://github.com/blalor/vm-image-configs/blob/master/scripts/virtualbox.sh
    for i in /usr/src/kernels/* ; do
	ln -s /usr/include/drm/drm{,_sarea,_mode,_fourcc}.h $i/include/drm/
    done

    mount -o loop VBoxGuestAdditions_$VBOX_VERSION.iso /mnt
    sh /mnt/VBoxLinuxAdditions.run
    umount /mnt
    rm -rf VBoxGuestAdditions_*.iso
    
    # TODO: FIXME: this leads to yum trying to remove 'yum' through a chain of dependencies
    #yum -y erase   gcc make gcc-c++ kernel-devel-`uname -r` zlib-devel readline-devel sqlite-devel perl dkms nfs-utils
    yum -y erase  kernel-devel-`uname -r`
    rm -rf /etc/yum.repos.d/epel.repo
    rm -rf VBoxGuestAdditions_*.iso
fi
