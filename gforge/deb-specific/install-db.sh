#! /bin/sh
# 
# $Id$
#
# Configure exim for GForge
# Roland Mas, debian-sf (GForge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1 $2"
fi

if [ $# = 0 ] 
    then 
    exec $0 default
else
    target=$1
fi

case "$target" in
    default)
	echo "Usage: $0 {configure-files|configure|purge|purge-files|dump|restore}"
	exit 1
	;;
    configure-files)
	# Tell PostgreSQL to let us use the database
	db_passwd=$(perl -e'require "/etc/gforge/local.pl"; print "$sys_dbpasswd\n";')
	ip_address=$(perl -e'require "/etc/gforge/local.pl"; print "$sys_dbhost\n";')
	pattern=$(basename $0).XXXXXX
	cp -a /etc/postgresql/pg_hba.conf /etc/postgresql/pg_hba.conf.gforge-new
	if grep -q "^host.*gforge_passwd$" /etc/postgresql/pg_hba.conf.gforge-new ; then
	    perl -pi -e "s/^host.*gforge_passwd$/host gforge $ip_address 255.255.255.255 password gforge_passwd/" /etc/postgresql/pg_hba.conf.gforge-new
	else
	    cur=$(mktemp /tmp/$pattern)
	    echo "### Next line inserted by GForge install" > $cur
	    echo "host gforge $ip_address 255.255.255.255 password gforge_passwd" >> $cur
	    cat /etc/postgresql/pg_hba.conf.gforge-new >> $cur
	    cat $cur > /etc/postgresql/pg_hba.conf.gforge-new
	    rm -f $cur
	fi
	su -s /bin/sh postgres -c "touch /var/lib/postgres/data/gforge_passwd"
	su -s /bin/sh postgres -c "/usr/lib/postgresql/bin/pg_passwd /var/lib/postgres/data/gforge_passwd > /dev/null" <<-EOF
gforge
$db_passwd
$db_passwd
EOF
	;;
    configure)
	# Create the appropriate database user
	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
	if su -s /bin/sh postgres -c "createuser --no-createdb --no-adduser gforge" 1> $tmp1 2> $tmp2 \
	    && [ "$(head -1 $tmp1)" = 'CREATE USER' ] \
	    || [ "$(head -1 $tmp2)" = 'ERROR:  CREATE USER: user name "gforge" already exists' ] ; then
	    # Creation OK or user already existing -- no problem here
	    echo -n ""
	else
	    echo "Cannot create PostgreSQL user...  This shouldn't have happened."
	    echo "Maybe a problem in your PostgreSQL configuration?"
	    echo "Please report a bug to the Debian bug tracking system"
	    cat $tmp1 $tmp2
	    exit 1
	fi
	rm -f $tmp1 $tmp2

        # Create the appropriate database
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
	if su -s /bin/sh postgres -c "createdb gforge" 1> $tmp1 2> $tmp2 \
	    && [ "$(head -1 $tmp1)" = 'CREATE DATABASE' ] \
	    || [ "$(head -1 $tmp2)" = 'ERROR:  CREATE DATABASE: database "gforge" already exists' ] ; then
	    # Creation OK or database already existing -- no problem here
	    echo -n ""
	else
	    echo "Cannot create PostgreSQL database...  This shouldn't have happened."
	    echo "Maybe a problem in your PostgreSQL configuration?"
	    echo "Please report a bug to the Debian bug tracking system"
	    cat $tmp1 $tmp2
	    exit 1
	fi
	rm -f $tmp1 $tmp2
	
	# Install/upgrade the database contents (tables and data)
	kill -HUP $(head -1 /var/lib/postgres/data/postmaster.pid)
	/usr/lib/gforge/bin/db-upgrade.pl 2>&1 | grep -v ^NOTICE:
	;;
    purge-files)
	cp -a /etc/postgresql/pg_hba.conf /etc/postgresql/pg_hba.conf.gforge-new
        if grep -q "### Next line inserted by GForge install" /etc/postgresql/pg_hba.conf.gforge-new
        then
                perl -pi -e "s/### Next line inserted by GForge install\n//" /etc/postgresql/pg_hba.conf.gforge-new
                perl -pi -e "s/^host.*gforge_passwd\n//" /etc/postgresql/pg_hba.conf.gforge-new
        fi
	;;
    purge)
	su -s /bin/sh postgres -c "dropdb gforge" > /dev/null 2>&1 || true
	su -s /bin/sh postgres -c "dropuser gforge" > /dev/null 2>&1 || true
	rm -f /var/lib/postgres/data/gforge_passwd
	kill -HUP $(head -1 /var/lib/postgres/data/postmaster.pid)
	;;
    dump)
	if [ "x$2" != "x" ] ;then
		DUMPFILE=$2
	else
		DUMPFILE=/var/lib/gforge/dumps/db_dump
	fi
	echo "Dumping in $DUMPFILE"
	su -s /bin/sh gforge -c /usr/lib/postgresql/bin/pg_dump gforge > $DUMPFILE
	;;
    restore)
	pattern=$(basename $0).XXXXXX
	newpg=$(mktemp /tmp/$pattern)
	echo "### Next line inserted by GForge restore" > $newpg
	echo "local all  trust" >> $newpg
	#echo "host all 127.0.0.1 255.255.255.255 trust" >> $newpg
	cat /etc/postgresql/pg_hba.conf >> $newpg
	mv $newpg /etc/postgresql/pg_hba.conf
	chmod 644 /etc/postgresql/pg_hba.conf
	/etc/init.d/postgresql restart
	if [ "x$2" != "x" ] ;then
		RESTFILE=$2
	else
		RESTFILE=/var/lib/gforge/dumps/db_dump
	fi
	echo "Restoring $RESTFILE"
	su -s /bin/sh postgres -c "dropdb gforge" || true
	su -s /bin/sh postgres -c "createdb gforge"  || true
	su -s /bin/sh postgres -c "/usr/lib/postgresql/bin/psql -f $RESTFILE gforge"
        perl -pi -e "s/### Next line inserted by GForge restore\n//" /etc/postgresql/pg_hba.conf
        perl -pi -e "s/local all  trust\n//" /etc/postgresql/pg_hba.conf
        #perl -pi -e "s/host all 127.0.0.1 255.255.255.255 trust\n//" /etc/postgresql/pg_hba.conf
	/etc/init.d/postgresql restart
	;;
esac
