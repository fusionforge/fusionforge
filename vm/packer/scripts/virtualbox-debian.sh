if test -f .vbox_version ; then
  # Use version from Debian
  apt-get -y install --no-install-recommends linux-headers-amd64 virtualbox-guest-dkms

  # Cleanup Virtualbox
  #apt-get -y --force-yes remove linux-headers-$(uname -r) build-essential
  #VBOX_VERSION=$(cat .vbox_version)
  #VBOX_ISO=VBoxGuestAdditions_$VBOX_VERSION.iso
  #rm $VBOX_ISO
fi
