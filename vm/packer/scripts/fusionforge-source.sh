# Help text for users
# TODO: keyboard mapping stays qwerty in Jessie despite being
# documented at https://wiki.debian.org/Keyboard
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
else
    echo "TODO: instructions for CentOS" >> /etc/issue
    yum -y install git
fi

cd /usr/src/
git clone git://fusionforge.org/fusionforge/fusionforge.git -b 6.0 fusionforge/

git config --global color.diff auto
git config --global color.status auto
git config --global color.branch auto
