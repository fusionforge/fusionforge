if test -f .vbox_version ; then
  # Use version from Debian
  apt-get -y install --no-install-recommends linux-headers-amd64 virtualbox-guest-dkms
fi
