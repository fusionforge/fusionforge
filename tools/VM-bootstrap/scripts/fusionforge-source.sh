# Help text for users
cat <<'EOF' > /etc/issue
Debian GNU/Linux + FusionForge Dev VM \l

Login with root/vagrant
Default keyboard layout is US QWERTY.
(Change it with 'dpkg-reconfigure keyboard-configuration --pri=high')
Default language is English.
(Change it with 'dpkg-reconfigure locales')

EOF

# Install sources
apt-get -y --force-yes install bzr
cd /root/
bzr checkout https://fusionforge.org/anonscm/bzr/deb-packaging/master fusionforge
