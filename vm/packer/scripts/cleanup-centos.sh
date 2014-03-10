yum -y clean all
yum -y install gpm bzip2 rsync emacs-nox vim

# Remove traces of mac address from network configuration
sed -i /HWADDR/d /etc/sysconfig/network-scripts/ifcfg-eth0
rm /etc/udev/rules.d/70-persistent-net.rules
