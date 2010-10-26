<?php
/*
 * Copyright (C) 2008 Alain Peyrat <aljeux@free.fr>
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The test suite ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

/*
 * Create a initial database suitable for running the tests.
 * The initial database is made of:
 *  The initial database given by the package.
 *  + An admin account (login: admin, password: myadmin)
 *  + A simple project (projectA)
 */

ini_set('memory_limit', '16M');

$config = getenv('CONFIG_PHP') ? getenv('CONFIG_PHP'): dirname(__FILE__).'/config.php';
require_once $config;

if ( !CONFIGURED ) {
	print "ERROR: File 'config.php' is not correctly configured, aborting.\n";
	exit(1);
}

$opt_restart = true;
if (isset($argv[1]) && $argv[1] == '-no-restart') {
	$opt_restart = false;
}

// Search location of fusionforge main directory (gforge).
$forge_root = dirname(dirname(dirname(__FILE__))).'/src';
if (!file_exists($forge_root)) {
	$forge_root = dirname(dirname(dirname(__FILE__))).'/gforge';
	if (!file_exists($forge_root)) {
		print "ERROR: Unable to guess location of fusionforge main directory (gforge), aborting.\n";
		exit(1);
	}
}

if ( DB_TYPE == 'mysql') {
	// Reload a fresh database before running this test suite.
	system("mysqladmin -f -u".DB_USER." -p".DB_PASSWORD." drop ".DB_NAME." &>/dev/null");
	system("mysqladmin -u".DB_USER." -p".DB_PASSWORD." create ".DB_NAME);
	system("mysql -u".DB_USER." -p".DB_PASSWORD." ".DB_NAME." < $forge_root/db/gforge-struct-mysql.sql");
	system("mysql -u".DB_USER." -p".DB_PASSWORD." ".DB_NAME." < $forge_root/db/gforge-data-mysql.sql");
} elseif ( DB_TYPE == 'pgsql') {
	if (!function_exists('pg_connect')) {
		print "ERROR: Missing pgsql on PHP to run tests on PostgreSQL, aborting.\n";
		exit;
	}
	// Drop & create a fresh database before running this test suite.
	if ($opt_restart) {
		system("service httpd restart 2>&1 >/dev/null");
	}
	system("service postgresql restart 2>&1 >/dev/null");
	system("su - postgres -c 'dropdb -q ".DB_NAME."'");
	system("su - postgres -c 'createdb -q --encoding UNICODE ".DB_NAME."'");
	system("psql -q -U".DB_USER." ".DB_NAME." -f $forge_root/db/gforge.sql >> /var/log/gforge-import.log 2>&1");
	system("php $forge_root/db/upgrade-db.php >> /var/log/gforge-upgrade-db.log 2>&1");
} else {
	print "ERROR: Unsupported database type: ".DB_TYPE.", aborting.\n";
	exit;
}

$sitename = 'ACOS Forge';
$adminPassword = 'myadmin';
$adminEmail = 'nobody@nowhere.com';

$session_hash = '000TESTSUITE000';

//set_include_path(".:/opt/gforge/:/opt/gforge/www/include/:/etc/gforge/");

require_once '../../gforge/www/env.inc.php';    
require_once $gfwww.'include/pre.php';

// Install tsearch2 for phpwiki & patch it for safe backups.
//system("psql -q -Upostgres ".DB_NAME." < /usr/share/pgsql/contrib/tsearch2.sql >/dev/null 2>&1");
//system("psql -q -Upostgres ".DB_NAME." < /opt/gforge/acde/sql/20080408-regprocedure_update.sql");
//system("echo \"GRANT SELECT ON pg_ts_dict, pg_ts_parser, pg_ts_cfg, pg_ts_cfgmap TO gforge;\" | psql -q -Upostgres ".DB_NAME);
//system("echo \"UPDATE pg_ts_cfg set locale = 'en_US.UTF-8' WHERE ts_name = 'default';\" | psql -q -Upostgres ".DB_NAME);

$files = glob(dirname(__FILE__)."/sql/*.sql");
foreach ($files as $filename) {
	system("psql -q -U".DB_USER." ".DB_NAME." -f $filename 2>&1 | grep -v ': NOTICE: ' | egrep -v '^(NOTICE|DETAIL:)' | egrep -v '^(Creating|Applying|Initializing) '");
}

system("echo \"VACUUM FULL ANALYZE;\" | psql -q -Upostgres ".DB_NAME);

//
// Create the initial admin account and activate it directly.
//
$user = new GFUser();
$user_id = $user->create('admin', $sitename, 'Admin', $adminPassword, $adminPassword,
	$adminEmail, 1, 1, 1,'GMT','',0,1,'', '','','','','','US',false, 'admin');

if (!$user_id) {
	print "ERROR: Creating user: ".$user->getErrorMessage()."\n";
	exit(1);
}

$user->setStatus('A');

if (!$user_id) {
	print "ERROR: Error creating admin account, no id returned, aborting.\n";
} else {
	// Register the user in master group to get full admin rights.
	$res = db_query_params ('INSERT INTO user_group (user_id,group_id,admin_flags, role_id) VALUES ($1,1,$2,17)',
				array ($user_id,
				       'A'));

	if (file_exists ('/tmp/fusionforge-use-pfo-rbac')) { // USE_PFO_RBAC
		$res = db_query_params ('INSERT INTO pfo_user_role ($1, 3)',
					array ($user_id)) ;
	}
}

?>
