#!/bin/bash

# Create the user vagrant with password vagrant,
# and avoid /bin/sh aka dash under Debian
useradd vagrant -s /bin/bash
echo 'vagrant'|passwd --stdin vagrant
echo 'vagrant ALL=NOPASSWD:ALL' > /etc/sudoers.d/vagrant
chmod 0440 /etc/sudoers.d/vagrant

# Installing vagrant keys
# Cf. http://docs.vagrantup.com/v2/boxes/base.html
mkdir -pm 700 /home/vagrant/.ssh
wget --no-check-certificate 'https://raw.github.com/mitchellh/vagrant/master/keys/vagrant.pub' -O /home/vagrant/.ssh/authorized_keys
chmod 0600 /home/vagrant/.ssh/authorized_keys
chown -R vagrant /home/vagrant/.ssh

# Install a copy for root, so we can 'vagrant ssh -- -l root'
mkdir -pm 700 /root/.ssh
wget --no-check-certificate 'https://raw.github.com/mitchellh/vagrant/master/keys/vagrant.pub' -O /root/.ssh/authorized_keys
chmod 0600 /root/.ssh/authorized_keys
