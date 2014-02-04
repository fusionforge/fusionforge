# Install sources
apt-get -y --force-yes install bzr
cd /root/
bzr checkout https://fusionforge.org/anonscm/bzr/deb-packaging/unstable fusionforge

# Update system
# Not done here, it makes the .box too big (1.5GB)
# yes | /root/fusionforge/tools/VM-scripts/update.sh

# Install dependencies
apt-get -y --force-yes install moreutils
wget http://ftp.fr.debian.org/debian/pool/main/l/loggerhead/loggerhead_1.19~bzr479-3_all.deb
aptitude install gdebi-core
gdebi --non-interactive loggerhead_1.19~bzr479-3_all.deb

# Help text for users
cat <<'EOF' > /etc/issue
Debian GNU/Linux + FusionForge Dev VM \l

Login with vagrant/vagrant and use 'sudo -i' to become root.
To install a graphical environment, run "/root/fusionforge/tools/VM-scripts/install-gui.sh".
Default keyboard layout is US QWERTY.
(Change it with 'dpkg-reconfigure keyboard-configuration --pri=high')
Default language is English.
(Change it with 'dpkg-reconfigure locales')

EOF

# Install convenience script/documentation for root to use:
mv /home/vagrant/Desktop /root/Desktop
chown -R root: /root/Desktop
