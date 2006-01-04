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
if [ -d /opt/gforge ]; then
	echo 1>&2 "/opt/gforge already exists - clean up before starting install"
	exit 2
fi

mkdir /opt/gforge/
if [ ! -d /opt/gforge ]; then
	echo 1>&2 "/opt/gforge didn't exist - error - make sure you've got permission"
	exit 2
fi
mv ../gforge-4.5.6 /opt/gforge/
cd /opt/gforge/
ln -s gforge-4.5.6 gforge
mkdir mailman
mkdir uploads
mkdir jpgraph
mkdir scmtarballs
mkdir scmsnapshots
mkdir localizationcache

#project vhost space
mkdir homedirs
mkdir /home/groups
ln -s /home/groups homedirs/groups

#Create default location for SVN repositories
mkdir /svnroot

#Create default location for CVS repositories
mkdir /cvsroot

#Optional - Set up some basic files for SVN-over-DAV only
mkdir svn
cp gforge/cronjobs/dav-svn/www/* svn/

# Optional - Set up basic index.php file for CVS vhost if desired
mkdir cvs
cp gforge/cronjobs/cvs-cron/www/* cvs/

#restricted shell for cvs accounts
cp gforge/cronjobs/cvs-cron/cvssh.pl /bin/
chmod 755 /bin/cvssh.pl

#Create default location for gforge config files
mkdir /etc/gforge
cp gforge/etc/local.inc.example /etc/gforge/local.inc
cp gforge/etc/gforge-httpd.conf.example /etc/gforge/httpd.conf

#copy cvsweb and make sure it's in the local.inc sys_scmweb path
cp gforge/plugins/scmcvs/cgi-bin/cvsweb /etc/gforge/

#copy the scmcvs plugin config to /etc/gforge/
cp -R gforge/plugins/scmcvs/etc/plugins/ /etc/gforge/

#copy the scmsvn config files to /etc/gforge/
cp -R gforge/plugins/scmsvn/etc/plugins/scmsvn/ /etc/gforge/plugins/

#copy the cvstracker config files to /etc/gforge/
cp -R gforge/plugins/cvstracker/etc/plugins/cvstracker/ /etc/gforge/plugins/

#symlink plugin www's
cd /opt/gforge/gforge/www
/bin/mkdir plugins
cd plugins

ln -s ../../plugins/cvstracker/www/ cvstracker
ln -s ../../plugins/scmcvs/www scmcvs
ln -s ../../plugins/scmsvn/www/ scmsvn

cd /opt/gforge

chown -R root:$3 /opt/gforge
chmod -R 644 gforge/
chown -R $2:$3 /opt/gforge/uploads
cd gforge && find -type d | xargs chmod 755
chmod -R 755 cronjobs/

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
