# Help text for users
cat <<'EOF' > /etc/issue
Debian GNU/Linux + FusionForge Dev VM \l

Login with root/vagrant
Default keyboard layout is US QWERTY.
(Change it with 'dpkg-reconfigure keyboard-configuration --pri=high')
Default language is English.
(Change it with 'dpkg-reconfigure locales')

EOF

git config --global color.diff auto
git config --global color.status auto
git config --global color.branch auto

# Install sources
if [ -e /etc/debian_version ]; then
    apt-get -y --force-yes install git
    repo='git://fusionforge.org/deb-packaging/deb-packaging.git -b debian/5.3'
else
    echo "TODO: instructions for CentOS" >> /etc/issue
    yum -y install git
    repo='git://fusionforge.org/fusionforge/fusionforge.git -b Branch_5_3'
fi

cd /usr/src/
git clone $repo fusionforge/

if [ -e /etc/debian_version ]; then
    cd fusionforge/
    git remote add upstream git://scm.fusionforge.org/fusionforge/fusionforge.git
    git fetch upstream
fi
