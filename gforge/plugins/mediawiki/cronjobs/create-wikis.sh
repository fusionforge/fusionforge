#! /bin/sh

sys_etc_path="/etc/gforge"
sys_var_apth="/var/lib/gforge"

# set the data dir for the plugin
wdprefix=$sys_var_path/plugins/mediawiki/wikidata

# get DB credentials
tmp3=$(mktemp)
perl -e'require "'$sys_etc_path'/local.pl"; print "*:*:$sys_dbname:$sys_dbuser:$sys_dbpasswd\n"' > $tmp3

# get all projects that use the mediawiki plugin
all_projects=$(echo "SELECT g.unix_group_name from groups g, group_plugin gp, plugins p where g.group_id = gp.group_id and gp.plugin_id = p.plugin_id and p.plugin_name = 'mediawiki' ;" \
    | PGPASSFILE=$tmp3 /usr/bin/psql -U gforge gforge \
    | tail -n +3 \
    | grep '^ ')

# create image directory and LocalSettings.php for all projects that don't have it yet
for project in $all_projects ; do
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
\$wgGroupPermissions['Members']['edit']          = true;
\$wgGroupPermissions['Members']['createpage']    = true;
\$wgGroupPermissions['Members']['createtalk']    = true;

// Edit permissions for non-members
\$wgGroupPermissions['ForgeUsers']['edit']          = false;
\$wgGroupPermissions['ForgeUsers']['createpage']    = false;
\$wgGroupPermissions['ForgeUsers']['createtalk']    = false;

// Edit permissions for anonymous users
\$wgGroupPermissions['*']['edit']          = false;
\$wgGroupPermissions['*']['createpage']    = false;
\$wgGroupPermissions['*']['createtalk']    = false;

// Uncomment these if you must import XML dumps
//\$wgGroupPermissions['Administrators']['import']        = true;
//\$wgGroupPermissions['Administrators']['importupload']  = true;
// Uncomment these if you must edit the MediaWiki:Sidebar
//\$wgGroupPermissions['Administrators']['editinterface'] = true;

// Override default wiki logo
//\$wgLogo = "/themes/\$sys_theme/images/wgLogo.png";

EOF

	new_projects="$new_projects $project"
    fi
done

# create mediawiki database for all projects that started to use mediawiki
for project in $new_projects ; do
    schema=$(echo plugin_mediawiki_$project | sed s/-/_/g)

    tmp1=$(mktemp)
    tmp2=$(mktemp)

    if su -s /bin/sh postgres -c "/usr/bin/psql gforge" 1> $tmp1 2> $tmp2 <<-EOF \
        && [ "$(tail -n +2 $tmp1 | head -1)" = 'CREATE SCHEMA' ] ;
SET LC_MESSAGES = 'C' ;
CREATE SCHEMA $schema ;
ALTER SCHEMA $schema OWNER TO gforge;
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

    if PGPASSFILE=$tmp3 /usr/bin/psql -U gforge gforge 1> $tmp1 2> $tmp2 <<-EOF \
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

tmp4=$(mktemp)
# Disable read anonymous if project is private
for project in $all_projects ; do
	ispublic=$(echo "SELECT is_public from groups where unix_group_name = '${project}' ;" \
	    | PGPASSFILE=$tmp3 /usr/bin/psql -U gforge gforge \
			| tail -n +3 \
			| grep '^ ')

	# Purge anonymous read
	(fgrep -vie '$wgGroupPermissions['\''Members'\'']['\''read'\'']' \
	    -e '$wgGroupPermissions['\''*'\'']['\''read'\'']' \
	    $wdprefix/$project/LocalSettings.php
	if [ $ispublic = '0' ] ; then
		echo "\$wgGroupPermissions['Members']['read']    = true;"
		echo "\$wgGroupPermissions['*']['read']          = false;"
	fi) >$wdprefix/$project/LocalSettings.php

done
rm -f $tmp4
rm -f $tmp3
