<?php
/*
 * Create a initial database suitable for running the tests.
 * The initial database is made of:
 *  The initial database given by the package.
 *  + An admin account (login: admin, password: myadmin)
 *  + A simple project (projectA)
 */

ini_set('memory_limit', '16M');

require_once 'config.php';

if ( !CONFIGURED ) {
	print "File 'config.php' is not correctly configured, abording.\n";
	exit(1);
}

$opt_restart = true;
if (isset($argv[1]) && $argv[1] == '-no-restart') {
	$opt_restart = false;
}

if ( DB_TYPE == 'mysql') {
	// Reload a fresh database before running this test suite.
	system("mysqladmin -f -u".DB_USER." -p".DB_PASSWORD." drop ".DB_NAME." &>/dev/null");
	system("mysqladmin -u".DB_USER." -p".DB_PASSWORD." create ".DB_NAME);
	system("mysql -u".DB_USER." -p".DB_PASSWORD." ".DB_NAME." < ".dirname(dirname(__FILE__))."/db/gforge-struct-mysql.sql");
	system("mysql -u".DB_USER." -p".DB_PASSWORD." ".DB_NAME." < ".dirname(dirname(__FILE__))."/db/gforge-data-mysql.sql");
} elseif ( DB_TYPE == 'pgsql') {
	if (!function_exists('pg_connect')) {
		print "ERROR: Missing pgsql on PHP to run tests on PostgreSQL.\n";
		exit;
	}
	// Drop & create a fresh database before running this test suite.
	if ($opt_restart) {
		system("service httpd restart 2>&1 >/dev/null");
	}
	system("service postgresql restart 2>&1 >/dev/null");
	system("su - postgres -c 'dropdb -q ".DB_NAME."'");
	system("su - postgres -c 'createdb -q --encoding UNICODE ".DB_NAME."'");
	system("psql -q -U".DB_USER." ".DB_NAME." -f ".dirname(dirname(dirname(__FILE__)))."/gforge/db/gforge.sql &>/tmp/gforge-import.log");
} else {
	print "Unsupported database type: ".DB_TYPE. "\n";
	exit;
}

$sitename = 'ACOS Forge';
$adminPassword = 'myadmin';
$adminEmail = 'nobody@nowhere.com';

$session_hash = '000TESTSUITE000';

// Temporary.
$sys_default_theme_id = 5;

set_include_path(".:/opt/gforge/:/opt/gforge/www/include/:/etc/gforge/");

require_once '../../gforge/www/env.inc.php';    
require_once $gfwww.'include/squal_pre.php';    

// Add alcatel theme to the database.
db_query("INSERT INTO themes (theme_id, dirname, fullname, enabled) 
             VALUES (5, 'alcatel-lucent', 'Alcatel-Lucent Theme', true)");

// Install tsearch2 for phpwiki & patch it for safe backups.
system("psql -q -Upostgres ".DB_NAME." < /usr/share/pgsql/contrib/tsearch2.sql >/dev/null 2>&1");
system("psql -q -Upostgres ".DB_NAME." < /opt/gforge/acde/sql/20080408-regprocedure_update.sql");
system("echo \"GRANT SELECT ON pg_ts_dict, pg_ts_parser, pg_ts_cfg, pg_ts_cfgmap TO gforge;\" | psql -q -Upostgres ".DB_NAME);
system("echo \"UPDATE pg_ts_cfg set locale = 'en_US.UTF-8' WHERE ts_name = 'default';\" | psql -q -Upostgres ".DB_NAME);

$files = glob("sql/*.sql");
foreach ($files as $filename) {
	system("psql -q -U".DB_USER." ".DB_NAME." -f $filename 2>&1 | grep -v ': NOTICE: ' | egrep -v '^(NOTICE|DETAIL:)' | egrep -v '^(Creating|Applying|Initializing) '");
}

system("echo \"VACUUM FULL ANALYZE;\" | psql -q -Upostgres ".DB_NAME);

// Remove email log files before running a test.
system("rm -f $GLOBALS[sys_var_path]/logs/email-*.log");

//
// Create the initial admin account and activate it directly.
//
$user = new GFUser();
$user_id = $user->create('admin', $sitename, 'Admin', $adminPassword, $adminPassword,
	$adminEmail, 1, 1, 1,'GMT','',0,$GLOBALS['sys_default_theme_id'],'', '','','','','','US',false, 'admin');

if (!$user_id) {
	print "ERROR:creating user: ".$user->getErrorMessage()."\n";
	exit(1);
}

$user->setStatus('A');

if (!$user_id) {
	print "ERROR: Error creating admin account, no id returned";
} else {
	// Register the user in master group to get full admin rights.
	$sql = "INSERT INTO user_group (user_id,group_id,admin_flags, role_id) VALUES ($user_id,1,'A', 17);";
	$res = db_query($sql);
}

?>
