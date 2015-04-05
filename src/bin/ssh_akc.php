#! /usr/bin/php -f
<?php
/**
 * Integration with OpenSSH through AuthorizedKeysCommand
 *
 * Copyright (C) 2014  Inria (Sylvain Beucler)
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// Don't load plugins
putenv('FUSIONFORGE_NO_PLUGINS=true');
// Using custom, restricted database connection
putenv('FUSIONFORGE_NO_DB=true');

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/account.php';

if (count ($argv) <= 1) {
	echo "Usage: {$argv[0]} username\n";
	exit(1);
}

// Filter out hard-coded list ("root") and weird usernames
if (!account_namevalid($argv[1], true, false)) {
	file_put_contents('php://stderr', "Invalid username {$argv[1]}\n");
	exit();
}

// The DB request
$host = forge_get_config('database_host');
$port = forge_get_config('database_port');
$dbname = forge_get_config('database_name');
$user = forge_get_config('database_user_ssh_akc');
$password = forge_get_config('database_password_ssh_akc');
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
$res = pg_query_params($conn,
		       'SELECT sshkey FROM ssh_authorized_keys WHERE user_name = $1',
		       array($argv[1]));
while ($row = pg_fetch_array($res))
	print $row['sshkey'] . "\n";


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
