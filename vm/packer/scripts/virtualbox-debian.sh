if test -f .vbox_version ; then
  # Fix slow DNS
  cat <<'EOF' > /etc/dhcp/dhclient-exit-hooks.d/virtualbox-fix-slow-dns
#!/bin/bash
echo 'options single-request-reopen' >> /etc/resolv.conf
EOF
  chmod 755 /etc/dhcp/dhclient-exit-hooks.d/virtualbox-fix-slow-dns
  /etc/dhcp/dhclient-exit-hooks.d/virtualbox-fix-slow-dns

  # Use version from Debian
  apt-get -y install --no-install-recommends linux-headers-amd64 virtualbox-guest-dkms

  # Cleanup Virtualbox
  #apt-get -y --force-yes remove linux-headers-$(uname -r) build-essential
  #VBOX_VERSION=$(cat .vbox_version)
  #VBOX_ISO=VBoxGuestAdditions_$VBOX_VERSION.iso
  #rm $VBOX_ISO
fi
