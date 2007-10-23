#!/bin/sh
if [ $# -ne 3  ]; then
	echo 1>&2 Usage: $0  gforge.company.com  apacheuser  apachegroup
	exit 127
fi
#validate hostname
echo "$1" | egrep '^([[:alnum:].\-_])*$' -q
found_host=$?
if [ $found_host -ne 0 ]; then
	echo 1>&2 "invalid hostname"
	exit 2
fi
#validate apache user
getent passwd $2 > /dev/null
found_apacheuser=$?
if [ $found_apacheuser -ne 0 ]; then
	echo 1>&2 "invalid apache user"
	exit 2
fi
#validate apache group
getent group $3 > /dev/null
found_apachegroup=$?
if [ $found_apachegroup -ne 0 ]; then
	echo 1>&2 "invalid apache group"
	exit 2
fi
#validate IP Address
#echo "$4" | egrep '^([0-9]){1,3}\.([0-9]){1,3}\.([0-9]){1,3}\.([0-9]){1,3}$' -q
#found_ip=$?
#if [ $found_ip -ne 0 ]; then
#	echo 1>&2 "invalid IP address"
#	exit 2
#fi

mkdir /opt/gforge
if [ ! -d /opt/gforge ]; then
	echo 1>&2 "/opt/gforge didn't exist - error - make sure you've got permission"
	exit 2
fi
mkdir /var/lib/gforge
if [ ! -d /var/lib/gforge ]; then
	echo 1>&2 "/var/lib/gforge didn't exist - error - make sure you've got permission"
	exit 2
fi

mv * /opt/gforge
cd /var/lib/gforge
mkdir uploads
mkdir /opt/jpgraph
mkdir scmtarballs
mkdir scmsnapshots
mkdir localizationcache
if [ ! -f /usr/bin/php5 ]; then
	ln -s /usr/bin/php /usr/bin/php5
fi

#project vhost space
mkdir homedirs
mkdir /home/groups
if [ ! -d homedirs/groups ]; then
	ln -s /home/groups homedirs/groups
fi

#Create default location for SVN repositories
mkdir svnroot
if [ ! -d /svnroot ]; then
	ln -s /var/lib/gforge/svnroot /svnroot
fi

#Create default location for CVS repositories
mkdir cvsroot
if [ ! -d /cvsroot ]; then
	ln -s /var/lib/gforge/cvsroot /cvsroot
fi

cd /opt/gforge

#restricted shell for cvs accounts
cp plugins/scmcvs/bin/cvssh.pl /bin/
chmod 755 /bin/cvssh.pl

#Create default location for gforge config files
mkdir /etc/gforge
cp etc/local.inc.example /etc/gforge/local.inc
cp etc/gforge-httpd.conf.example /etc/gforge/httpd.conf

#copy the scmcvs plugin config to /etc/gforge/
if [ ! -d /etc/gforge/plugins/scmcvs ]; then
	mkdir /etc/gforge/plugins/scmcvs
fi
cp plugins/scmcvs/etc/plugins/scmcvs/config.php /etc/gforge/plugins/scmcvs/config.php

#copy the scmsvn config files to /etc/gforge/
if [ ! -d /etc/gforge/plugins/scmsvn ]; then
	mkdir /etc/gforge/plugins/scmsvn
fi
cp plugins/scmsvn/etc/plugins/scmsvn/config.php /etc/gforge/plugins/scmsvn/config.php

#copy the cvstracker config files to /etc/gforge/
if [ ! -d /etc/gforge/plugins/cvstracker ]; then
	mkdir /etc/gforge/plugins/cvstracker
fi
cp plugins/cvstracker/etc/plugins/cvstracker/config.php /etc/gforge/plugins/cvstracker/config.php

#copy the svntracker config files to /etc/gforge/
if [ ! -d /etc/gforge/plugins/svntracker ]; then
	mkdir /etc/gforge/plugins/svntracker
fi
cp plugins/svntracker/etc/plugins/svntracker/config.php /etc/gforge/plugins/svntracker/config.php

#symlink plugin www's
cd /opt/gforge/www
if [ ! -d plugins/ ]; then
	/bin/mkdir plugins
fi
cd plugins
if [ ! -d cvstracker ]; then
	ln -s ../../plugins/cvstracker/www/ cvstracker
fi
if [ ! -d svntracker ]; then
	ln -s ../../plugins/svntracker/www/ svntracker
fi
if [ ! -d scmcvs ]; then
	ln -s ../../plugins/scmcvs/www scmcvs
fi
if [ ! -d scmsvn ]; then
	ln -s ../../plugins/scmsvn/www/ scmsvn
fi

cd /opt/gforge

chown -R root:$3 /opt/gforge
chmod -R 644 /opt/gforge/
cd /opt/gforge && find -type d | xargs chmod 755
chown -R $2:$3 /var/lib/gforge/uploads
chmod -R 755 /opt/gforge/cronjobs/

if [ ! -d /etc/gforge ]; then
	echo 1>&2 "/etc/gforge didn't exist - error - make sure you've got permission"
	exit 2
fi
chown -R root:$3 /etc/gforge/
chmod -R 644 /etc/gforge/
cd /etc/gforge && find -type d | xargs chmod 755
cd /etc/gforge && find -type f -exec perl -pi -e "s/apacheuser/$2/" {} \;
cd /etc/gforge && find -type f -exec perl -pi -e "s/apachegroup/$3/" {} \;
cd /etc/gforge && find -type f -exec perl -pi -e "s/gforge\.company\.com/$1/" {} \;


echo "noreply:        /dev/null" >> /etc/aliases

# create symlink for fckeditor
cd /opt/gforge/www && ln -s ../utils/fckeditor/www/ fckeditor
