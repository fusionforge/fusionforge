#!/bin/sh
if [ $# -ne 4  ]; then
	echo 1>&2 Usage: $0  gforge.company.com  apacheuser  apachegroup  ip.add.re.ss
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
echo "$4" | egrep '^([0-9]){1,3}\.([0-9]){1,3}\.([0-9]){1,3}\.([0-9]){1,3}$' -q
found_ip=$?
if [ $found_ip -ne 0 ]; then
	echo 1>&2 "invalid IP address"
	exit 2
fi
if [ -f /etc/aliases.org ]; then
	echo 1>&2 "/etc/aliases.org already exists - clean up before starting install"
	exit 2
fi
if [ -f /etc/passwd.org ]; then
	echo 1>&2 "/etc/passwd.org already exists - clean up before starting install"
	exit 2
fi
if [ -f /etc/shadow.org ]; then
	echo 1>&2 "/etc/shadow.org already exists - clean up before starting install"
	exit 2
fi
if [ -f /etc/group.org ]; then
	echo 1>&2 "/etc/group.org already exists - clean up before starting install"
	exit 2
fi
if [ -d /etc/gforge ]; then
	echo 1>&2 "/etc/gforge already exists - clean up before starting install"
	exit 2
fi
if [ -d /usr/lib/gforge ]; then
	echo 1>&2 "/usr/lib/gforge already exists - clean up before starting install"
	exit 2
fi
if [ ! -d /opt/viewvc ]; then
	echo 1>&2 "/opt/viewvc didn't exist - error - make sure you've installed viewvc in /opt/viewvc. You can download from http://gforge.org/frs/?group_id=143"
	exit 2
fi
if [ ! -f /opt/viewvc/bin/cgi/viewcvs.cgi ]; then
	echo 1>&2 "/opt/viewvc/bin/cgi/viewcvs.cgi didn't exist - error - make sure you've installed viewvc in /opt/viewvc. You can download from http://gforge.org/frs/?group_id=143"
	exit 2
fi


mkdir /usr/lib/gforge
if [ ! -d /usr/lib/gforge ]; then
	echo 1>&2 "/usr/lib/gforge didn't exist - error - make sure you've got permission"
	exit 2
fi
mkdir /var/lib/gforge
if [ ! -d /var/lib/gforge ]; then
	echo 1>&2 "/var/lib/gforge didn't exist - error - make sure you've got permission"
	exit 2
fi

mv * /usr/lib/gforge
cd /var/lib/gforge
mkdir uploads
mkdir /var/lib/jpgraph
mkdir scmtarballs
mkdir scmsnapshots
mkdir localizationcache
ln -s /usr/bin/php /usr/bin/php4

#project vhost space
mkdir homedirs
mkdir /home/groups
ln -s /home/groups homedirs/groups

#Create default location for SVN repositories
mkdir svnroot
ln -s /var/lib/gforge/svnroot /svnroot

#Create default location for CVS repositories
mkdir cvsroot
ln -s /var/lib/gforge/cvsroot /cvsroot

cd /usr/lib/gforge

#sets up pretty xslt pages for svn when browsing with a web browser
cp cronjobs/dav-svn/www/svnindex* www/

#restricted shell for cvs accounts
cp cronjobs/cvs-cron/cvssh.pl /bin/
chmod 755 /bin/cvssh.pl

#Create default location for gforge config files
mkdir /etc/gforge
cp etc/local.inc.example /etc/gforge/local.inc
cp etc/gforge-httpd.conf.example /etc/gforge/httpd.conf

#copy cvsweb and make sure it's in the local.inc sys_scmweb path
cp plugins/scmcvs/cgi-bin/cvsweb /etc/gforge/

#copy viewvc and make sure it's in the local.inc sys_scmweb path
cp /opt/viewvc/bin/cgi/viewcvs.cgi /etc/gforge/

#copy the scmcvs plugin config to /etc/gforge/
cp -R plugins/scmcvs/etc/plugins/ /etc/gforge/

#copy the scmsvn config files to /etc/gforge/
cp -R plugins/scmsvn/etc/plugins/scmsvn/ /etc/gforge/plugins/

#copy the cvstracker config files to /etc/gforge/
cp -R plugins/cvstracker/etc/plugins/cvstracker/ /etc/gforge/plugins/

#symlink plugin www's
cd /usr/lib/gforge/www
/bin/mkdir plugins
cd plugins

ln -s ../../plugins/cvstracker/www/ cvstracker
ln -s ../../plugins/scmcvs/www scmcvs
ln -s ../../plugins/scmsvn/www/ scmsvn
cd scmsvn
ln -s /opt/viewvc/templates/docroot/ viewcvs

cd /usr/lib/gforge

chown -R root:$3 /usr/lib/gforge
chmod -R 644 /usr/lib/gforge/
cd /usr/lib/gforge && find -type d | xargs chmod 755
chown -R $2:$3 /var/lib/gforge/uploads
chmod -R 755 /usr/lib/gforge/cronjobs/

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
cd /etc/gforge && find -type f -exec perl -pi -e "s/192\.168\.100\.100/$4/" {} \;


echo "noreply:        /dev/null" >> /etc/aliases

cp /etc/aliases /etc/aliases.org
cp /etc/shadow /etc/shadow.org
cp /etc/passwd /etc/passwd.org
cp /etc/group /etc/group.org
