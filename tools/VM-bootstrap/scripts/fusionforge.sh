# Install sources
apt-get -y --force-yes install bzr
cd /root/
bzr checkout https://fusionforge.org/anonscm/bzr/deb-packaging/unstable fusionforge

# Install scripts
#wget -q -O - "https://fusionforge.org/plugins/scmgit/cgi-bin/gitweb.cgi?p=fusionforge/fusionforge.git;a=blob_plain;f=tools/VM-scripts/configure-scripts.sh;hb=HEAD" | sh -s trunk
ln -s fusionforge/tools/VM-scripts scripts

# Update system
# Not done here, it makes the .box too big (1.5GB)
# yes | scripts/update.sh

# Install dependencies
apt-get -y --force-yes install moreutils
wget http://ftp.fr.debian.org/debian/pool/main/l/loggerhead/loggerhead_1.19~bzr479-3_all.deb
aptitude install gdebi-core
gdebi --non-interactive loggerhead_1.19~bzr479-3_all.deb

# Help text for users
cat <<'EOF' > /etc/issue
Debian GNU/Linux + FusionForge Dev VM \l

Login with vagrant/vagrant and use 'sudo -i' to become root.
To install a graphical environment, run "/root/scripts/install-gui.sh".
Current keyboard layout is US QWERTY.
(Change it with 'dpkg-reconfigure console-data')

EOF

# Install convenience script/documentation for root to use:
mv /home/vagrant/Desktop /root/Desktop
chown -R root: /root/Desktop
