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
if [ -e /etc/debian_version ]; then
    apt-get -y --force-yes install git
    repo=git://fusionforge.org/deb-packaging/deb-packaging.git
else
    echo "TODO: instructions for CentOS" >> /etc/issue
    yum -y install git
    repo=git://fusionforge.org/fusionforge/fusionforge.git
fi
cd /usr/src/
git clone $repo fusionforge/
