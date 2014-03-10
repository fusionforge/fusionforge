if test -f .vbox_version ; then
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
    
    mount -o loop VBoxGuestAdditions_$VBOX_VERSION.iso /mnt
    sh /mnt/VBoxLinuxAdditions.run
    umount /mnt
    rm -rf VBoxGuestAdditions_*.iso
    
    # TODO: FIXME: this leads to yum trying to remove 'yum' through a chain of dependencies
    #yum -y erase   gcc make gcc-c++ kernel-devel-`uname -r` zlib-devel readline-devel sqlite-devel perl dkms nfs-utils
    rm -rf /etc/yum.repos.d/epel.repo
    rm -rf VBoxGuestAdditions_*.iso
fi
