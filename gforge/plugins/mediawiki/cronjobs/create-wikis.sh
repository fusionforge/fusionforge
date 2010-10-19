#! /bin/sh

tmp3=$(mktemp)
perl -e'require "/etc/gforge/local.pl"; print "*:*:$sys_dbname:$sys_dbuser:$sys_dbpasswd\n"' > $tmp3
dbuser=$(perl -e'require "/etc/gforge/local.pl"; print "$sys_dbuser\n"')
dbname=$(perl -e'require "/etc/gforge/local.pl"; print "$sys_dbname\n"')

projects=$(echo "SELECT g.unix_group_name from groups g, group_plugin gp, plugins p where g.group_id = gp.group_id and gp.plugin_id = p.plugin_id and p.plugin_name = 'mediawiki' ;" \
    | PGPASSFILE=$tmp3 /usr/bin/psql -U $dbuser $dbname \
    | tail -n +3 \
    | grep '^ ')

wdprefix=/var/lib/gforge/plugins/mediawiki/wikidata

for project in $projects ; do
    if [ ! -d $wdprefix/$project/images ] ; then
	mkdir -p $wdprefix/$project/images
    fi
    if [ ! -e $wdprefix/$project/LocalSettings.php ] ; then
	cat > $wdprefix/$project/LocalSettings.php <<EOF
<?php
// To enable uploads for the wiki, you'll need to edit this value:
\$wgEnableUploads = false;
// Don't forget to "chown www-data $wdprefix/$project/images"

// Edit permissions for group members
//\$wgGroupPermissions['Members']['edit']          = true;
//\$wgGroupPermissions['Members']['createpage']    = true;
//\$wgGroupPermissions['Members']['createtalk']    = true;

// Edit permissions for non-members
//\$wgGroupPermissions['user']['edit']          = false;
//\$wgGroupPermissions['user']['createpage']    = false;
//\$wgGroupPermissions['user']['createtalk']    = false;

// Edit permissions for anonymous users
//\$wgGroupPermissions['*']['edit']          = false;
//\$wgGroupPermissions['*']['createpage']    = false;
//\$wgGroupPermissions['*']['createtalk']    = false;

// Override default wiki logo
//\$wgLogo = "/themes/\$sys_theme/images/wgLogo.png";

EOF

	filteredprojects="$filteredprojects $project"
    fi
done

projects=$filteredprojects

for project in $projects ; do
    schema=$(echo plugin_mediawiki_$project | sed s/-/_/g)

    tmp1=$(mktemp)
    tmp2=$(mktemp)

    if su -s /bin/sh postgres -c "/usr/bin/psql $dbname" 1> $tmp1 2> $tmp2 <<-EOF \
        && [ "$(tail -n +2 $tmp1 | head -1)" = 'CREATE SCHEMA' ] ;
SET LC_MESSAGES = 'C' ;
CREATE SCHEMA $schema ;
ALTER SCHEMA $schema OWNER TO $dbuser;
EOF
    then
        rm -f $tmp1 $tmp2
    else
        echo "CREATE SCHEMA's STDOUT:"
        cat $tmp1
        echo "CREATE SCHEMA's STDERR:"
        cat $tmp2
        rm -f $tmp1 $tmp2 $tmp3
	exit 1
    fi

    tmp1=$(mktemp)
    tmp2=$(mktemp)

    if PGPASSFILE=$tmp3 /usr/bin/psql -U $dbuser $dbname 1> $tmp1 2> $tmp2 <<-EOF \
        && true || [ "$(tail -1 $tmp1)" = 'COMMIT' ] ;
SET search_path = "$schema" ;
\i /usr/share/mediawiki/maintenance/postgres/tables.sql
CREATE TEXT SEARCH CONFIGURATION $schema.default ( COPY = pg_catalog.english );
COMMIT ;
EOF
    then
        rm -f $tmp1 $tmp2
    else
        echo "Database creation STDOUT:"
        cat $tmp1
        echo "Database creation STDERR:"
        cat $tmp2
        rm -f $tmp1 $tmp2 $tmp3
        exit 1
    fi

done

projects=$(echo "SELECT g.unix_group_name from groups g, group_plugin gp, plugins p where g.group_id = gp.group_id and gp.plugin_id = p.plugin_id and p.plugin_name = 'mediawiki' ;" \
    | PGPASSFILE=$tmp3 /usr/bin/psql -U $dbuser $dbname \
    | tail -n +3 \
    | grep '^ ')

tmp4=$(mktemp)
# Disable read anonymous if project is private
for project in $projects ; do
	ispublic=$(echo "SELECT is_public from groups where unix_group_name = '${project}' ;" \
	    | PGPASSFILE=$tmp3 /usr/bin/psql -U $dbuser $dbname \
			| tail -n +3 \
			| grep '^ ')

	# Purge anonymous read
	cat $wdprefix/$project/LocalSettings.php | grep -vi "\$wgGroupPermissions\['user'\]\['read'\]" > $tmp4
	cat $tmp4 > $wdprefix/$project/LocalSettings.php
	cat $wdprefix/$project/LocalSettings.php | grep -vi "\$wgGroupPermissions\['\*'\]\['read'\]" > $tmp4
	cat $tmp4 > $wdprefix/$project/LocalSettings.php


	if [ $ispublic = '0' ] ; then
		# private
		echo "\$wgGroupPermissions['user']['read']       = false;" >> $wdprefix/$project/LocalSettings.php
		echo "\$wgGroupPermissions['*']['read']          = false;" >> $wdprefix/$project/LocalSettings.php
	else
		#public
		echo "\$wgGroupPermissions['user']['read']       = true;" >> $wdprefix/$project/LocalSettings.php
		echo "\$wgGroupPermissions['*']['read']          = true;" >> $wdprefix/$project/LocalSettings.php
	fi

done
rm -f $tmp4
rm -f $tmp3
