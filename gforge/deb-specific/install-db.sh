#! /bin/sh
# 
# $Id$
#
# Configure exim for Sourceforge
# Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $# != 1 ] 
    then 
    exec $0 default
else
    target=$1
fi

case "$target" in
    default)
	echo "Usage: $0 {configure|purge}"
	exit 1
	;;
    configure)
	# Create the appropriate database user
	pattern=$(basename $0)
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
	if su postgres -c "createuser --no-createdb --no-adduser sourceforge" 1> $tmp1 2> $tmp2 \
	    && [ "$(head -1 $tmp1)" = 'CREATE USER' ] \
	    || [ "$(head -1 $tmp2)" = 'ERROR:  CREATE USER: user name "sourceforge" already exists' ] ; then
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
	if su postgres -c "createdb sourceforge" 1> $tmp1 2> $tmp2 \
	    && [ "$(head -1 $tmp1)" = 'CREATE DATABASE' ] \
	    || [ "$(head -1 $tmp2)" = 'ERROR:  CREATE DATABASE: database "sourceforge" already exists' ] ; then
	    # Creation OK of database already existing -- no problem here
	    echo -n ""
	else
	    echo "Cannot create PostgreSQL database...  This shouldn't have happened."
	    echo "Maybe a problem in your PostgreSQL configuration?"
	    echo "Please report a bug to the Debian bug tracking system"
	    cat $tmp1 $tmp2
	    exit 1
	fi
	rm -f $tmp1 $tmp2
	
	# Tell PostgreSQL to let us use the database
	db_passwd=$(perl -e'require "/etc/sourceforge/local.pl"; print "$sys_dbpasswd\n";')
	ip_address=$(perl -e'require "/etc/sourceforge/local.pl"; print "$sys_dbhost\n";')
	su postgres -c "touch /var/lib/postgres/data/sourceforge_passwd"
	su postgres -c "/usr/lib/postgresql/bin/pg_passwd /var/lib/postgres/data/sourceforge_passwd > /dev/null" <<-EOF
sourceforge
$db_passwd
$db_passwd
EOF
	if grep -q "^host.*sourceforge_passwd$" /etc/postgresql/pg_hba.conf ; then
	    perl -pi -e "s/^host.*sourceforge_passwd$/host  sourceforge        $ip_address     255.255.255.255           password sourceforge_passwd/" /etc/postgresql/pg_hba.conf
	else
	    cur=$(mktemp /tmp/$pattern)
	    echo "### Next line inserted by Sourceforge install" > $cur
	    echo "host  sourceforge        $ip_address     255.255.255.255           password sourceforge_passwd" >> $cur
	    cat /etc/postgresql/pg_hba.conf >> $cur
	    cat $cur > /etc/postgresql/pg_hba.conf
	    rm -f $cur
	fi

	# Install/upgrade the database contents (tables and data)
	/usr/lib/sourceforge/bin/db-upgrade.pl
	;;
    purge)
        if grep -q "### Next line inserted by Sourceforge install" /etc/postgresql/pg_hba.conf
        then
                perl -pi -e "s/### Next line inserted by Sourceforge install\n//" /etc/postgresql/pg_hba.conf
                perl -pi -e "s/^host.*sourceforge_passwd\n//" /etc/postgresql/pg_hba.conf
        fi
	su postgres -c "dropdb sourceforge" &> /dev/null || true
	su postgres -c "dropuser sourceforge" &> /dev/null || true
	rm -f /var/lib/postgres/data/sourceforge_passwd
	;;
esac
