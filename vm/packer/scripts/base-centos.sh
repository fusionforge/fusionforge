# Update the box
yum -y update
yum -y install wget curl unzip sudo gpm bzip2 rsync emacs-nox vim

# Tweak sshd to prevent DNS resolution (speed up logins)
echo 'UseDNS no' >> /etc/ssh/sshd_config

sed -i "s/^.*requiretty/#Defaults requiretty/" /etc/sudoers
