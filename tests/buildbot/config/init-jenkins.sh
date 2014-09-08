#! /bin/sh
HOST=`hostname -f`
EMAIL="buildbot@$HOST"

# Setup sudo command needed by jenkins
echo "Setup sudoers"
if [ ! -f /etc/sudoers.d/jenkins ]
then
cat > /etc/sudoers.d/jenkins <<-EOF
jenkins ALL = NOPASSWD: /usr/bin/apt-get,/usr/bin/lxc-create,/usr/bin/lxc-start,/usr/bin/lxc-stop,/usr/bin/lxc-destroy, /usr/bin/lxc-console
jenkins ALL = NOPASSWD:SETENV: /usr/sbin/cowbuilder
EOF
fi

# Setup some git defaults
echo "Setup Git config"
if [ ! -f /var/lib/jenkins/.gitconfig ]
then
cat > /var/lib/jenkins/.gitconfig <<-EOF
[user]
        email = $EMAIL
        name = Jenkins's Buildbot
EOF
chown jenkins.jenkins /var/lib/jenkins/.gitconfig 
fi

# Setup ssh key to be able to connect to vm
echo "Setup VM Key"
if [ ! -f /var/lib/jenkins/.ssh/id_rsa.pub ]
then
	su - jenkins -c "ssh-keygen -q -t rsa -f /var/lib/jenkins/.ssh/id_rsa -N ''"
fi

# Setup botkey
echo "Setup Bot Key"
if ! su - jenkins -c "gpg --list-secret-keys $EMAIL 2>/dev/null"
then 
cat > /var/lib/jenkins/botkey <<-EOF
%echo Generating a standard key'
Key-Type: DSA
Key-Length: 1024
Subkey-Type: ELG-E
Subkey-Length: 1024
Name-Real: FusionForge Bot
Name-Comment: with stupid passphrase
Name-Email: $EMAIL
Expire-Date: 0
#Passphrase: abc
#%pubring botkey.pub
#%secring botkey.sec
# Do a commit here, so that we can later print "done" :-)
%commit
%echo done
EOF
su - jenkins -c "gpg --batch --gen-key /var/lib/jenkins/botkey"
fi




