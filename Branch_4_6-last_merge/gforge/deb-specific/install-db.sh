#! /bin/bash
# 
# $Id$
#
# Configure postgresql database for GForge
# Roland Mas, gforge

# Simple function to know if a db exists
exist_db(){
	export db_name=$1
	su -s /bin/sh postgres -c "psql $1 >/dev/null 2>&1 </dev/null"
}

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

export LC_ALL=C
# Support for new place for pg_hba.conf
# I only try to upgrade on the default cluster
# I no database is found running, we exit with a big message
if [ -x /usr/bin/pg_lsclusters ]
then 
	# We are with new postgresql working with clusters
	# This is probably not te most elegant way to deal with database
	# I install or upgrade on the default cluster if it is online
	# or I quit gently with a big message
	pg_version=`/usr/bin/pg_lsclusters | grep 5432 | grep online | cut -d' ' -f1`
	if [ "x$pg_version" != "x" ] 
	then 
		export pg_hba_dir=/etc/postgresql/${pg_version}/main/
	else
		echo "No database found online on port 5432"
		echo "Couldn't initialize or upgrade gforge database."
		echo "Please see postgresql documentation"
		echo "and run dpkg-reconfigure -plow gforge-db-postgresql"
		echo "once the problem is solved"
		echo "exiting without error, but gforge db will not work"
		echo "right now"
		exit 0
	fi
else
    	export pg_hba_dir=/etc/postgresql
	if ! pidof postmaster > /dev/null 2> /dev/null ; then
		echo "No database postmaster found online running"
		echo "Couldn't initialize or upgrade gforge database."
		echo "Please see postgresql documentation"
		echo "and run dpkg-reconfigure -plow gforge-db-postgresql"
		echo "once the problem is solved"
		echo "exiting without error, but gforge db will not work"
		echo "right now"
		exit 0
	fi
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
	    cp -a ${pg_hba_dir}/pg_hba.conf ${pg_hba_dir}/pg_hba.conf.gforge-new
	    # if previous string, else no previous string
	    if grep -q "^host.*gforge_passwd$" ${pg_hba_dir}/pg_hba.conf.gforge-new ; then
		perl -pi -e "s/^host.*gforge_passwd$/host $db_name $ip_address 255.255.255.255 password gforge_passwd/" ${pg_hba_dir}/pg_hba.conf.gforge-new
	    else
		cur=$(mktemp /tmp/$pattern)
		echo "### Next line inserted by GForge install" > $cur
		echo "host $db_name $ip_address 255.255.255.255 password gforge_passwd" >> $cur
		cat ${pg_hba_dir}/pg_hba.conf.gforge-new >> $cur
		cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
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
	    echo "Configuring for PostgreSQL > 7.3"
	    cp -a ${pg_hba_dir}/pg_hba.conf ${pg_hba_dir}/pg_hba.conf.gforge-new
	    cur=$(mktemp /tmp/$pattern)
	    if ! grep -q 'BEGIN GFORGE BLOCK -- DO NOT EDIT' ${pg_hba_dir}/pg_hba.conf.gforge-new ; then
		# Make sure our configuration is inside a delimited BLOCK
		if grep -q "^host.*gforge_passwd$" ${pg_hba_dir}/pg_hba.conf.gforge-new ; then
		    perl -e "open F, \"${pg_hba_dir}/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^host.*gforge_passwd\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
		    cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
		elif grep -q "^### Next line inserted by GForge install" ${pg_hba_dir}/pg_hba.conf.gforge-new ; then
		    perl -e "open F, \"${pg_hba_dir}/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^### Next line inserted by GForge install\nhost $db_name $db_user $ip_address 255.255.255.255 password/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
		    cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
		else
		    perl -e "open F, \"${pg_hba_dir}/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^host $db_name $db_user.*password\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
		    cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
		fi
	    fi
	    echo "### BEGIN GFORGE BLOCK -- DO NOT EDIT" > $cur
	    echo "### END GFORGE BLOCK -- DO NOT EDIT" >> $cur
	    cat ${pg_hba_dir}/pg_hba.conf.gforge-new >> $cur
	    cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
	    rm -f $cur
	    
	    cur=$(mktemp /tmp/$pattern)
	    perl -e "open F, \"${pg_hba_dir}/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^### BEGIN GFORGE BLOCK -- DO NOT EDIT.*### END GFORGE BLOCK -- DO NOT EDIT\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\nhost $db_name $db_user $ip_address 255.255.255.255 password\nhost $db_name gforge_nss $ip_address 255.255.255.255 trust\nhost $db_name gforge_mta $ip_address 255.255.255.255 trust\n### END GFORGE BLOCK -- DO NOT EDIT/ms; print \$l;" > $cur
	    cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
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
	    if su -s /bin/sh postgres -c "/usr/bin/psql template1" 1> $tmp1 2> $tmp2 <<-EOF
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
	    if su -s /bin/sh postgres -c "/usr/bin/psql template1" 1> $tmp1 2> $tmp2 <<-EOF
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
	    if su -s /bin/sh postgres -c "/usr/bin/psql template1" 1> $tmp1 2> $tmp2 <<-EOF
CREATE USER gforge_mta WITH PASSWORD '' ;
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
	if ! exist_db $db_name ; then
		if su -s /bin/sh postgres -c "createdb --encoding=UNICODE $db_name" 1> $tmp1 2> $tmp2 \
	    	&& [ "$(head -1 $tmp1)" = 'CREATE DATABASE' ] \
	    	; then
	    	# Creation OK 
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
	fi

	# Enable plpgsql language
	# Old fashion < 7.4
	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
	if [ -f /usr/lib/postgresql/bin/enable_lang ] 
	then
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
	fi
	# New fashion
	if [ -f /usr/bin/createlang ]
	then 
		if [ `su -s /bin/sh postgres -c "/usr/bin/createlang -l $db_name | grep plpgsql | wc -l"` != 1 ]
		then
	 		su -s /bin/sh postgres -c "/usr/bin/createlang plpgsql $db_name"
		else
			echo "Procedural language on $db_name already enabled"
		fi
	else
	 	echo "No way found to enable plpgsql on $db_name here" 
	fi

	# Install/upgrade the database contents (tables and data)
	su -s /bin/sh gforge -c /usr/lib/gforge/bin/db-upgrade.pl 2>&1  | grep -v ^NOTICE:
	p=${PIPESTATUS[0]}
	if [ $p != 0 ] ; then
	    exit $p
	fi
	# Must be root to reorg these files, but only have to do it once
	[ ! -f /var/lib/gforge/db/20050127-frs-reorg.done ] &&\
	(/usr/lib/gforge/db/20050127-frs-reorg.php \
	-d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include &&\
	touch /var/lib/gforge/db/20050127-frs-reorg.done) || true
	# Le last line had the bad idea to create a cache file owned by root
	rm -f /var/cache/gforge/English.cache
	
	;;
    purge-files)
	cp -a ${pg_hba_dir}/pg_hba.conf ${pg_hba_dir}/pg_hba.conf.gforge-new
        if grep -q "### Next line inserted by GForge install" ${pg_hba_dir}/pg_hba.conf.gforge-new
        then
	    perl -pi -e "s/### Next line inserted by GForge install\n//" ${pg_hba_dir}/pg_hba.conf.gforge-new
	    # same problem below with gforge required to be the first host that
	    # uses password, required for allowing change of db_name.
	    perl -pi -e "s/^host.*password\n//" ${pg_hba_dir}/pg_hba.conf.gforge-new
	    perl -pi -e "s/^host.*gforge_passwd\n//" ${pg_hba_dir}/pg_hba.conf.gforge-new
        fi
	;;
    purge)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
	su -s /bin/sh postgres -c "dropdb $db_name" > /dev/null 2>&1 || true
	su -s /bin/sh postgres -c "dropuser $db_user" > /dev/null 2>&1 || true
	rm -f /var/lib/postgres/data/gforge_passwd
	[ -f /var/lib/postgres/data/postmaster.pid ] && kill -HUP $(head -1 /var/lib/postgres/data/postmaster.pid) || true
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
	if [ "x$pg_version" != "x" ] 
	then 
		pg_name=postgresql-$pg_version
	else
		pg_name=postgresql
	fi
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
	cat ${pg_hba_dir}/pg_hba.conf >> $newpg
	mv $newpg ${pg_hba_dir}/pg_hba.conf
	chmod 644 ${pg_hba_dir}/pg_hba.conf
	invoke-rc.d ${pg_name} restart
	if [ "x$2" != "x" ] ;then
		RESTFILE=$2
	else
		RESTFILE=/var/lib/gforge/dumps/db_dump
	fi
	echo "Restoring $RESTFILE"
	su -s /bin/sh postgres -c "dropdb $db_name" || true
	su -s /bin/sh postgres -c "createdb --encoding=UNICODE $db_name"  || true
	su -s /bin/sh postgres -c "/usr/lib/postgresql/bin/psql -f $RESTFILE $db_name"
        perl -pi -e "s/### Next line inserted by GForge restore\n//" ${pg_hba_dir}/pg_hba.conf
        perl -pi -e "s/$localtrust\n//" ${pg_hba_dir}/pg_hba.conf
	invoke-rc.d ${pg_name} reload
	;;
esac
