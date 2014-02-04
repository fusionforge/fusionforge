#!/bin/bash

# Create the user vagrant with password vagrant
# cf. http/preseed.cfg
#useradd -G sudo -p $(perl -e'print crypt("vagrant", "vagrant")') -m -s /bin/bash -N vagrant

# Set up sudo - cf. http/preseed.cfg:late_command
#echo %vagrant ALL=NOPASSWD:ALL > /etc/sudoers.d/vagrant
#chmod 0440 /etc/sudoers.d/vagrant

# Installing vagrant keys
# Cf. http://docs.vagrantup.com/v2/boxes/base.html
mkdir /home/vagrant/.ssh
cd /home/vagrant/.ssh
chmod 700 .
wget --no-check-certificate 'https://raw.github.com/mitchellh/vagrant/master/keys/vagrant.pub' -O authorized_keys
chmod 600 authorized_keys
chown -R vagrant .

# Install a copy for root, so we can 'vagrant ssh -- -l root'
mkdir /root/.ssh
cd /root/.ssh
chmod 700 .
wget --no-check-certificate 'https://raw.github.com/mitchellh/vagrant/master/keys/vagrant.pub' -O authorized_keys
chmod 600 authorized_keys

# Install NFS for Vagrant
#apt-get -y --force-yes update
#apt-get -y --force-yes install nfs-common
