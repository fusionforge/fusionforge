#! /bin/bash
# 
# $Id$
#
# Configure postgresql database for GForge
# Roland Mas, gforge

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
	db_passwd=$(grep ^db_password= /etc/gforge/gforge.conf | cut -d= -f2-)
	ip_address=$(grep ^ip_address= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_host=$(grep ^db_host= /etc/gforge/gforge.conf | cut -d= -f2-)
	pattern=$(basename $0).XXXXXX
	pg_version=$(dpkg -s postgresql | awk '/^Version: / { print $2 }')
	if [ "$db_host" == "127.0.0.1" -o "$db_host" == "localhost" ]
	then
		# Otherwise the line wouldn't be used
		# And postgres auth would fail
		ip_address=127.0.0.1
	fi
	if dpkg --compare-versions $pg_version lt 7.3 ; then
            # PostgreSQL configuration for versions prior to 7.3
	    echo "Configuring for PostgreSQL 7.2"
	    cp -a /etc/postgresql/pg_hba.conf /etc/postgresql/pg_hba.conf.gforge-new
	    # if previous string, else no previous string
	    if grep -q "^host.*gforge_passwd$" /etc/postgresql/pg_hba.conf.gforge-new ; then
		perl -pi -e "s/^host.*gforge_passwd$/host $db_name $ip_address 255.255.255.255 password gforge_passwd/" /etc/postgresql/pg_hba.conf.gforge-new
	    else
		cur=$(mktemp /tmp/$pattern)
		echo "### Next line inserted by GForge install" > $cur
		echo "host $db_name $ip_address 255.255.255.255 password gforge_passwd" >> $cur
		cat /etc/postgresql/pg_hba.conf.gforge-new >> $cur
		cat $cur > /etc/postgresql/pg_hba.conf.gforge-new
		rm -f $cur
	    fi
	    su -s /bin/sh postgres -c "touch /var/lib/postgres/data/gforge_passwd"
	    su -s /bin/sh postgres -c "/usr/lib/postgresql/bin/pg_passwd /var/lib/postgres/data/gforge_passwd > /dev/null" <<-EOF
$db_user
$db_passwd
$db_passwd
EOF
	else
            # PostgreSQL configuration for versions from 7.3 on
	    echo "Configuring for PostgreSQL 7.3"
	    cp -a /etc/postgresql/pg_hba.conf /etc/postgresql/pg_hba.conf.gforge-new
	    if ! grep -q 'BEGIN GFORGE BLOCK -- DO NOT EDIT' /etc/postgresql/pg_hba.conf.gforge-new ; then
		cur=$(mktemp /tmp/$pattern)
		# Make sure our configuration is inside a delimited BLOCK
		if grep -q "^host.*gforge_passwd$" /etc/postgresql/pg_hba.conf.gforge-new ; then
		    perl -e "open F, \"/etc/postgresql/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^host.*gforge_passwd\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
		    cat $cur > /etc/postgresql/pg_hba.conf.gforge-new
		elif grep -q "^### Next line inserted by GForge install" /etc/postgresql/pg_hba.conf.gforge-new ; then
		    perl -e "open F, '/etc/postgresql/pg_hba.conf.gforge-new' or die \$!; undef \$/; \$l=<F>; \$l=~ s/^### Next line inserted by GForge install\nhost $db_name $db_user $ip_address 255.255.255.255 password/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
		    cat $cur > /etc/postgresql/pg_hba.conf.gforge-new
		else
		    perl -e "open F, '/etc/postgresql/pg_hba.conf.gforge-new' or die \$!; undef \$/; \$l=<F>; \$l=~ s/^host $db_name $db_user.*password\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
		    cat $cur > /etc/postgresql/pg_hba.conf.gforge-new
		fi
	    fi
	    rm -f $cur
	    
	    cur=$(mktemp /tmp/$pattern)
	    perl -e "open F, '/etc/postgresql/pg_hba.conf.gforge-new' or die \$!; undef \$/; \$l=<F>; \$l=~ s/^### BEGIN GFORGE BLOCK -- DO NOT EDIT.*### END GFORGE BLOCK -- DO NOT EDIT\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\nhost $db_name $db_user $ip_address 255.255.255.255 password\nhost $db_name gforge_nss $ip_address 255.255.255.255 trust\n### END GFORGE BLOCK -- DO NOT EDIT/ms; print \$l;" > $cur
	    cat $cur > /etc/postgresql/pg_hba.conf.gforge-new
	    rm -f $cur

	    # Remove old password file, created by 7.2, not used by 7.3
	    if [ -e /var/lib/postgres/data/gforge_passwd ] ; then
		rm -f /var/lib/postgres/data/gforge_passwd
	    fi

	fi
	;;
    configure)
	# Create the appropriate database user
	pg_version=$(dpkg -s postgresql | awk '/^Version: / { print $2 }')
	db_passwd=$(grep ^db_password= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
	if dpkg --compare-versions $pg_version lt 7.3 ; then
	    if su -s /bin/sh postgres -c "createuser --no-createdb --no-adduser $db_user" 1> $tmp1 2> $tmp2 \
		&& [ "$(head -1 $tmp1)" = 'CREATE USER' ] \
		|| grep -q "^ERROR: .* user name \"$db_user\" already exists$" $tmp2 ; then
	        # Creation OK or user already existing -- no problem here
		rm -f $tmp1 $tmp2
	    else
		echo "Cannot create PostgreSQL user...  This shouldn't have happened."
		echo "Maybe a problem in your PostgreSQL configuration?"
		echo "Please report a bug to the Debian bug tracking system"
		echo "Please include the following output:"
		echo "createuser's STDOUT:"
		cat $tmp1
		echo "createuser's STDERR:"
		cat $tmp2
		rm -f $tmp1 $tmp2
		exit 1
	    fi
	else
	    if su -s /bin/sh postgres -c "/usr/bin/psql template1" &> /dev/null <<-EOF
CREATE USER $db_user WITH PASSWORD '$db_passwd' ;
EOF
		then
		rm -f $tmp1 $tmp2
	    else
		echo "Cannot create PostgreSQL user...  This shouldn't have happened."
		echo "Maybe a problem in your PostgreSQL configuration?"
		echo "Please report a bug to the Debian bug tracking system"
		echo "Please include the following output:"
		echo "CREATE USER's STDOUT:"
		cat $tmp1
		echo "CREATE USER's STDERR:"
		cat $tmp2
		rm -f $tmp1 $tmp2
		exit 1
	    fi
	    if su -s /bin/sh postgres -c "/usr/bin/psql template1" &> /dev/null <<-EOF
CREATE USER gforge_nss WITH PASSWORD '' ;
EOF
		then
		rm -f $tmp1 $tmp2
	    else
		echo "Cannot create PostgreSQL user...  This shouldn't have happened."
		echo "Maybe a problem in your PostgreSQL configuration?"
		echo "Please report a bug to the Debian bug tracking system"
		echo "Please include the following output:"
		echo "CREATE USER's STDOUT:"
		cat $tmp1
		echo "CREATE USER's STDERR:"
		cat $tmp2
		rm -f $tmp1 $tmp2
		exit 1
	    fi
	fi

        # Create the appropriate database
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
	if su -s /bin/sh postgres -c "createdb --encoding=UNICODE $db_name" 1> $tmp1 2> $tmp2 \
	    && [ "$(head -1 $tmp1)" = 'CREATE DATABASE' ] \
	    || grep -q "ERROR: .* database \"$db_name\" already exists" $tmp2 ; then
	    # Creation OK or database already existing -- no problem here
	    echo -n ""
	    rm -f $tmp1 $tmp2
	else
	    echo "Cannot create PostgreSQL database...  This shouldn't have happened."
	    echo "Maybe a problem in your PostgreSQL configuration?"
	    echo "Please report a bug to the Debian bug tracking system"
	    echo "Please include the following output:"
	    echo "createdb's STDOUT:"
	    cat $tmp1
	    echo "createdb's STDERR:"
	    cat $tmp2
	    rm -f $tmp1 $tmp2
	    exit 1
	fi

	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
	if su -s /bin/sh postgres -c "/usr/lib/postgresql/bin/enable_lang plpgsql $db_name" 1> $tmp1 2> $tmp2 \
	    || grep -q "plpgsql added to $db_name" $tmp1 \
	    || grep -q "plpgsql is already enabled in $db_name" $tmp1 ; then
	    # Creation OK or user already existing -- no problem here
	    echo -n ""
	    rm -f $tmp1 $tmp2
	else
	    echo "Cannot enable the PLPGSQL language in the database...  This shouldn't have happened."
	    echo "Maybe a problem in your PostgreSQL configuration?"
	    echo "Please report a bug to the Debian bug tracking system"
	    echo "Please include the following output:"
	    echo "enable_lang's STDOUT:"
	    cat $tmp1
	    echo "enable_lang's STDERR:"
	    cat $tmp2
	    rm -f $tmp1 $tmp2
	    exit 1
	fi
	
	# Install/upgrade the database contents (tables and data)
	kill -HUP $(head -1 /var/lib/postgres/data/postmaster.pid)
	su -s /bin/sh gforge -c /usr/lib/gforge/bin/db-upgrade.pl 2>&1  | grep -v ^NOTICE:
	p=${PIPESTATUS[0]}
	if [ $p != 0 ] ; then
	    exit $p
	fi
	
	;;
    purge-files)
	cp -a /etc/postgresql/pg_hba.conf /etc/postgresql/pg_hba.conf.gforge-new
        if grep -q "### Next line inserted by GForge install" /etc/postgresql/pg_hba.conf.gforge-new
        then
	    perl -pi -e "s/### Next line inserted by GForge install\n//" /etc/postgresql/pg_hba.conf.gforge-new
	    # same problem below with gforge required to be the first host that
	    # uses password, required for allowing change of db_name.
	    perl -pi -e "s/^host.*password\n//" /etc/postgresql/pg_hba.conf.gforge-new
	    perl -pi -e "s/^host.*gforge_passwd\n//" /etc/postgresql/pg_hba.conf.gforge-new
        fi
	;;
    purge)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
	su -s /bin/sh postgres -c "dropdb $db_name" > /dev/null 2>&1 || true
	su -s /bin/sh postgres -c "dropuser $db_user" > /dev/null 2>&1 || true
	rm -f /var/lib/postgres/data/gforge_passwd
	kill -HUP $(head -1 /var/lib/postgres/data/postmaster.pid)
	;;
    dump)
	if [ -e /etc/sourceforge/local.pl ] ; then
	    db_name=$(perl -e'require "/etc/sourceforge/local.pl"; print "$sys_dbname\n";')
	elif [ -e /etc/gforge/gforge.conf ] ; then
	    db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	else
	    db_name=sourceforge
	fi
	if [ "x$2" != "x" ] ;then
		DUMPFILE=$2
	else
		DUMPFILE=/var/lib/gforge/dumps/db_dump
	fi
	if [ "x$3" != "x" ] ;then
		DB=$3
	else
		DB=$db_name
	fi
	echo "Dumping $DB database in $DUMPFILE"
	su -s /bin/sh $DB -c /usr/lib/postgresql/bin/pg_dump $DB > $DUMPFILE
	;;
    restore)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	pattern=$(basename $0).XXXXXX
	newpg=$(mktemp /tmp/$pattern)
	pg_version=$(dpkg -s postgresql | awk '/^Version: / { print $2 }')
	if dpkg --compare-versions $pg_version lt 7.3 ; then
	    localtrust="local all trust"
	else
	    localtrust="local all all trust"
	fi
	echo "### Next line inserted by GForge restore" > $newpg
	echo "$localtrust" >> $newpg
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
	su -s /bin/sh postgres -c "dropdb $db_name" || true
	su -s /bin/sh postgres -c "createdb --encoding=UNICODE $db_name"  || true
	su -s /bin/sh postgres -c "/usr/lib/postgresql/bin/psql -f $RESTFILE $db_name"
        perl -pi -e "s/### Next line inserted by GForge restore\n//" /etc/postgresql/pg_hba.conf
        perl -pi -e "s/$localtrust\n//" /etc/postgresql/pg_hba.conf
        #perl -pi -e "s/host all 127.0.0.1 255.255.255.255 trust\n//" /etc/postgresql/pg_hba.conf
	/etc/init.d/postgresql reload
	;;
esac
