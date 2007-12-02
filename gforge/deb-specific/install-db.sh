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

case "$target" in
    default)
	echo "Usage: $0 {configure-files|configure|purge|purge-files|dump|restore}"
	exit 1
	;;
    configure-files)
	# Tell PostgreSQL to let us use the database
	db_passwd=$(grep ^db_password= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
	pattern=$(basename $0).XXXXXX

        # PostgreSQL configuration for versions from 7.3 on
	cp -a ${pg_hba_dir}/pg_hba.conf ${pg_hba_dir}/pg_hba.conf.gforge-new
	cur=$(mktemp /tmp/$pattern)
	if ! grep -q 'BEGIN GFORGE BLOCK -- DO NOT EDIT' ${pg_hba_dir}/pg_hba.conf.gforge-new ; then
	    # Make sure our configuration is inside a delimited BLOCK
	    if grep -q "^host.*gforge_passwd$" ${pg_hba_dir}/pg_hba.conf.gforge-new ; then
		perl -e "open F, \"${pg_hba_dir}/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^host.*gforge_passwd\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
		cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
	    elif grep -q "^### Next line inserted by GForge install" ${pg_hba_dir}/pg_hba.conf.gforge-new ; then
		perl -e "open F, \"${pg_hba_dir}/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^### Next line inserted by GForge install\nhost $db_name $db_user [0-9. ]+ password/### BEGIN GFORGE BLOCK -- DO NOT EDIT\n### END GFORGE BLOCK -- DO NOT EDIT/s; print \$l;" > $cur
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
	perl -e "open F, \"${pg_hba_dir}/pg_hba.conf.gforge-new\" or die \$!; undef \$/; \$l=<F>; \$l=~ s/^### BEGIN GFORGE BLOCK -- DO NOT EDIT.*### END GFORGE BLOCK -- DO NOT EDIT\$/### BEGIN GFORGE BLOCK -- DO NOT EDIT\nlocal $db_name $db_user md5\nlocal $db_name gforge_nss trust\nlocal $db_name gforge_mta md5\n### END GFORGE BLOCK -- DO NOT EDIT/ms; print \$l;" > $cur
	cat $cur > ${pg_hba_dir}/pg_hba.conf.gforge-new
	rm -f $cur
	
	;;
    configure)
	# Create the appropriate database user
	db_passwd=$(grep ^db_password= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	tmp2=$(mktemp /tmp/$pattern)
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
CREATE USER gforge_nss WITH PASSWORD 'gforge_nss' ;
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
CREATE USER gforge_mta WITH PASSWORD 'gforge_mta' ;
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

	# Make sure the database accepts connections from these new users
	pg_name=postgresql-$pg_version
	invoke-rc.d ${pg_name} reload
	# Install/upgrade the database contents (tables and data)
	su -s /bin/sh gforge -c /usr/lib/gforge/bin/db-upgrade.pl 2>&1  | grep -v ^NOTICE:
	p=${PIPESTATUS[0]}
	if [ $p != 0 ] ; then
	    exit $p
	fi
	;;
    purge-files)
	cp -a ${pg_hba_dir}/pg_hba.conf ${pg_hba_dir}/pg_hba.conf.gforge-new
	perl -pi -e "BEGIN { undef \$/; } s/^### BEGIN GFORGE BLOCK -- DO NOT EDIT.*### END GFORGE BLOCK -- DO NOT EDIT\n//ms;" ${pg_hba_dir}/pg_hba.conf.gforge-new
	;;
    purge)
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	db_user=$(grep ^db_user= /etc/gforge/gforge.conf | cut -d= -f2-)
	su -s /bin/sh postgres -c "dropdb $db_name" > /dev/null 2>&1 || true
	su -s /bin/sh postgres -c "dropuser $db_user" > /dev/null 2>&1 || true
	pg_name=postgresql-$pg_version
	invoke-rc.d ${pg_name} reload
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
	pg_name=postgresql-$pg_version
	db_name=$(grep ^db_name= /etc/gforge/gforge.conf | cut -d= -f2-)
	pattern=$(basename $0).XXXXXX
	newpg=$(mktemp /tmp/$pattern)
	localtrust="local all all trust"
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
