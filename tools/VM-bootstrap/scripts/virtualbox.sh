if test -f /home/vagrant/.vbox_version ; then
  apt-get -y install --no-install-recommends linux-headers-amd64 virtualbox-guest-dkms
fi
