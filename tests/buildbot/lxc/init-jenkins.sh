#! /bin/sh
HOST=`hostname -f`
EMAIL="buildbot@$HOST"

# Setup sudo command needed by jenkins
echo "Setup sudoers"
if [ ! -f /etc/sudoers.d/ci ]
then
cat > /etc/sudoers.d/ci <<-EOF
jenkins ALL= NOPASSWD: /usr/local/sbin/lxc-wrapper
EOF
fi

# Setup some git defaults
echo "Setup Git config"
if [ ! -f ~jenkins/.gitconfig ]
then
cat > ~jenkins/.gitconfig <<-EOF
[user]
        email = $EMAIL
        name = Jenkins's Buildbot
EOF
chown jenkins: ~jenkins/.gitconfig
fi

# Setup ssh key to be able to connect to vm
echo "Setup VM Key"
if [ ! -f ~jenkins/.ssh/id_rsa.pub ]
then
	su - jenkins -c "ssh-keygen -q -t rsa -f ~/.ssh/id_rsa -N ''"
fi

# Setup botkey - only needed for 5.3?
echo "Setup Bot Key"
if ! su - jenkins -c "gpg --list-secret-keys $EMAIL 2>/dev/null"
then 
su - jenkins -c "gpg --batch --gen-key" <<EOF
     Key-Type: RSA
     Key-Length: 2048
     Subkey-Type: RSA
     Subkey-Length: 2048
     Name-Real: buildbot@$(hostname -f)
     Expire-Date: 0
     %commit
EOF
fi
