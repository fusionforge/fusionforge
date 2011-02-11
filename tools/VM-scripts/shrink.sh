#! /bin/sh

apt-get clean
aptitude clean
find /var/cache -type f | xargs rm
rm -rf /tmp/*
rm -rf /root/debian-repository
# Maven2 stuff
rm -rf /root/.m2/
rm -f /root/.bzr/repository/obsolete_packs/*
rm -f /root/fusionforge-trunk/*999*
rm -f /var/log/*.gz
rm -f /var/lib/aptitude/pkgstates.old

df -h /

mount -oremount,ro /
fsck -fpC /dev/sda1
zerofree -v /dev/sda1
mount -oremount,rw /
swapoff -a
dd if=/dev/zero of=/dev/sda5
mkswap /dev/sda5
