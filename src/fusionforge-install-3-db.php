#!/usr/bin/php
<?php
/**
 * FusionForge Installation Dependency Setup
 *
 * Copyright 2006 GForge, LLC
 * http://fusionforge.org/
 *
 * @version
 *
 * This file is part of GInstaller, it is called by install.sh.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 * Francisco Gimeno
 */

define ('GREEN', "\033[01;32m" );
define ('NORMAL', "\033[00m" );
define ('RED', "\033[01;31m" );

$STDOUT = fopen('php://stdout','w');
$STDIN = fopen('php://stdin','r');

show("\n-=# Welcome to FusionForge DB-Installer #=-");

//TO DO: add dependency check
//if (!run("php check-deps.php", true)) {
//	echo RED.'Not all the necessary dependencies were found. aborting.'.NORMAL."\n";
//	exit(1);
//}

// Make sure the DB is initialized by starting postgresql service
if (is_file('/etc/init.d/postgresql')) 
{
	$pgservice='/etc/init.d/postgresql';
} 
elseif (is_file('/etc/init.d/postgresql-8.2'))
{
	$pgservice='/etc/init.d/postgresql-8.2';
} 
elseif (is_file('/etc/init.d/postgresql-8.3'))
{
	$pgservice='/etc/init.d/postgresql-8.3';
}
elseif (is_file('/etc/init.d/postgresql-8.4'))
{
	$pgservice='/etc/init.d/postgresql-8.4';
}
elseif (is_file('/etc/init.d/cswpostgres'))
{
	$pgservice='/etc/init.d/cswpostgres';
}
else
{
	die("ERROR: Could not find Postgresql init script\n");
}

# Fedora9 (an maybe newer) requires running initdb
if ($pgservice == '/etc/init.d/postgresql') {
	if (!is_dir("/var/lib/pgsql/data/base")) {
		run("service postgresql initdb &>/dev/null", true);
	}
}

// Might fail if it's already running, so we'll ingnore the result
run("$pgservice start", true);

require_once 'install-common.inc' ;

if (!is_dir($fusionforge_src_dir))
{
	die("Error: GForge folder doesn't exist. Run fusionforge-install-2.php first.");
}

// Where the PGHBA config file is
if (is_file("/var/lib/pgsql/data/pg_hba.conf"))
{
	// RedHat & SuSE
	$PGHBA='/var/lib/pgsql/data/pg_hba.conf';
}
elseif (is_file('/etc/postgresql/8.2/main/pg_hba.conf'))
{
	$PGHBA='/etc/postgresql/8.2/main/pg_hba.conf';
}
elseif (is_file('/etc/postgresql/8.4/main/pg_hba.conf'))
{
	$PGHBA='/etc/postgresql/8.4/main/pg_hba.conf';
}
elseif (is_file('/opt/csw/var/pgdata/pg_hba.conf'))
{
	$PGHBA='/opt/csw/var/pgdata/pg_hba.conf';
}
else
{
	die("ERROR: Could not find pg_hba.conf file\n");
}


if (is_file('/usr/share/pgsql/contrib/tsearch2.sql'))
{
	// RedHat
	$tsearch2_sql='/usr/share/pgsql/contrib/tsearch2.sql';
}
elseif (is_file('/usr/share/postgresql/contrib/tsearch2.sql'))
{
	// SuSE
	$tsearch2_sql='/usr/share/postgresql/contrib/tsearch2.sql';
}
elseif (is_file('/usr/share/postgresql/8.2/contrib/tsearch2.sql'))
{
	$tsearch2_sql='/usr/share/postgresql/8.2/contrib/tsearch2.sql';
}
elseif (is_file('/usr/share/postgresql/8.3/contrib/tsearch2.sql'))
{
	$tsearch2_sql='/usr/share/postgresql/8.3/contrib/tsearch2.sql';
}
elseif (is_file('/usr/share/postgresql/8.4/contrib/tsearch2.sql'))
{
	$tsearch2_sql='/usr/share/postgresql/8.4/contrib/tsearch2.sql';
}
elseif (is_file('/opt/csw/postgresql/share/contrib/tsearch2.sql'))
{
	// Solaris 10
	$tsearch2_sql='/opt/csw/postgresql/share/contrib/tsearch2.sql';
}
else
{
	die("ERROR: Could not find tsearch2.sql file\n");
}


function install()
{
	global $PGHBA, $fusionforge_src_dir, $fusionforge_etc_dir, $tsearch2_sql, $pgservice, $STDIN, $STDOUT;

	show("\n * Enter the Database Name (gforge): ");

	if (getenv('FFORGE_DB')) {
		$gforge_db = getenv('FFORGE_DB');
	} else {
		$gforge_db = trim(fgets($STDIN));
		if (strlen($gforge_db) == 0) {
			$gforge_db = 'gforge';
		}
	}
	show(" ...using '$gforge_db'");

	show(' * Enter the Database Username (gforge): ');

	if (getenv('FFORGE_USER')) {
		$gforge_user = getenv('FFORGE_USER');
	} else {
		$gforge_user = trim(fgets($STDIN));
		if (strlen($gforge_user) == 0) {
			$gforge_user = 'gforge';
		}
	}
	show(" ...using '$gforge_user'");

	show(" * Modifying DB Access Permissions...");
	if (!file_exists("$PGHBA.fforge.backup")) {
		run("cp $PGHBA $PGHBA.fforge.backup", true);
	}
	run("echo \"# GFORGE\nlocal all all trust\" > $PGHBA");
	show(' * Restarting PostgreSQL...');
	run("$pgservice stop", true);
	run("$pgservice start");


	show(" * Creating '$gforge_user' Group...");
	run("/usr/sbin/groupadd $gforge_user", true);

	show(" * Creating '$gforge_user' User...");
	run("/usr/sbin/useradd -g $gforge_user $gforge_user", true);
	
	// Let's give some time for PostgreSQL to start
	sleep(5);

	show(" * Creating Database User '$gforge_user'...");
	run("su - postgres -c \"createuser -A -R -d -E $gforge_user\"", true);

	show(' * Creating Language...');
	run("su - postgres -c \"createlang plpgsql template1\"", true);

	if (!is_dir("/home/$gforge_user")) {
	    $susufix = '';
	} else {
	    $susufix = '-';
	}

	show(" * Creating '$gforge_db' Database...");
	run("su $susufix $gforge_user -c \"createdb --encoding UNICODE $gforge_db\"", true);

	# Detect postgresql version, load tsearch2 for pg < 8.3
	$pg_version = explode(' ', shell_exec("postgres --version"));
	$pgv = $pg_version[2];

	if (preg_match('/^(7\.|8\.1|8\.2)/', $pgv)) {
		show(" * Dumping tsearch2 Database Into '$gforge_db' DB");
		run("su - postgres -c \"psql $gforge_db < $tsearch2_sql\" >> /tmp/gforge-import.log");

		$tables = array('pg_ts_cfg', 'pg_ts_cfgmap', 'pg_ts_dict', 'pg_ts_parser');
		foreach ($tables as $table) {
			run('su - postgres -c "psql '.$gforge_db.' -c \\"GRANT ALL on '.$table.' TO '.$gforge_user.';\\""');
		}
		run("su - postgres -c \"psql $gforge_db -c \\\"UPDATE pg_ts_cfg SET locale='en_US.UTF-8' WHERE ts_name='default'\\\"\"");
//	} else {
//		show(" * Creating FTS default configuation (Full Text Search)");
//		run("su - postgres -c \"psql $gforge_db < $fusionforge_src_dir/db/FTS-20081108.sql\" >> /tmp/gforge-import.log");
	}


	show(' * Dumping FusionForge DB');
	run("su $susufix $gforge_user -c \"psql $gforge_db < $fusionforge_src_dir/db/gforge.sql\" >> /tmp/gforge-import.log");

//	show(' * Dumping FusionForge FTI DB');
//	run("su $susufix $gforge_user -c \"psql $gforge_db < $fusionforge_src_dir/db/FTI.sql\" >> /tmp/gforge-import.log");
//	run("su $susufix $gforge_user -c \"psql $gforge_db < $fusionforge_src_dir/db/FTI-20050315.sql\" >> /tmp/gforge-import.log");
//	run("su $susufix $gforge_user -c \"psql $gforge_db < $fusionforge_src_dir/db/FTI-20050401.sql\" >> /tmp/gforge-import.log");
//	run("su $susufix $gforge_user -c \"psql $gforge_db < $fusionforge_src_dir/db/FTI-20050530.sql\" >> /tmp/gforge-import.log");
//	run("su $susufix $gforge_user -c \"psql $gforge_db < $fusionforge_src_dir/db/FTI-20060130.sql\" >> /tmp/gforge-import.log");
//	run("su $susufix $gforge_user -c \"psql $gforge_db < $fusionforge_src_dir/db/FTI-20061025.sql\" >> /tmp/gforge-import.log");

	show(" * Enter the Admin Username (fforgeadmin): ");
	if (getenv('FFORGE_ADMIN_USER')) {
		$admin_user = getenv('FFORGE_ADMIN_USER');
	} else {
		$admin_user = trim(fgets($STDIN));

		if (strlen($admin_user) == 0) {
			$admin_user = 'fforgeadmin';
		}
	}
	show(" ...using '$admin_user'");

	if (getenv('FFORGE_ADMIN_PASSWORD')) {
		$bad_pwd = false;
		$pwd1 = getenv('FFORGE_ADMIN_PASSWORD');
	} else {
		$retries = 0;
		$bad_pwd = true;
		$pwd1 = '';
		$pwd2 = '';
		$error = '';
		while ($bad_pwd && $retries < 5) {
			if ($bad_pwd && $retries > 0) {
				show(' * ' . $error);
			}
			$pwd1 = readMasked(" * Enter the Site Admin Password:");
			$error = validatePassword($pwd1);
			if ($error != '') {
				$bad_pwd = true;
			} else {
				$pwd2 = readMasked(" * Please enter it again: \n");
				if ($pwd1 == $pwd2) {
					$bad_pwd = false;
				} else {
					$error = 'Passwords don\'t match. Please try again.';
				}
			}
			$retries++;
		}
	}

	if ($bad_pwd) {
		show('Passwords didn\'t match! Aborting.');
		die();
	} else {
		$pw_md5 = md5($pwd1);
		$pw_crypt = crypt($pwd1);
		$pw_crypt = str_replace('$', '\\\\\\$', $pw_crypt);
		//run(	'su - postgres -c "psql ' . 
		//	$gforge_db . 
		//	' -c \\"UPDATE \\\\\"user\\\\\" SET unix_name=\'' . 
		//	$admin_user . '\', password_md5=\'' . 
		//	$pw_md5 . '\', password_crypt=\'' . 
		//	$pw_crypt . '\' WHERE user_id=101;\\""'); // MODIFIQUE ESTO

		//run(	'su - postgres -c "psql ' . 
		//	$gforge_db . 
		//	' -c \\"UPDATE \\\\\"users\\\\\" SET user_name=\'' . 
		//	$admin_user . '\', user_pw=\'' . 
		//	$pw_md5 . '\', unix_pw=\'' . 
		//	$pw_crypt . '\' WHERE user_id=101;\\""');
//echo "BREAKPOINT 1\n";
//$t = trim(fgets($STDIN));

//	run("su - postgres -c \"psql $gforge_db -c \\\"INSERT INTO users (user_name, user_pw, unix_pw) VALUES ('$admin_user', '$pw_md5', '$pw_crypt')\\\"\"");
		if (file_exists ('/tmp/fusionforge-use-pfo-rbac')) { // USE_PFO_RBAC
			run("su - postgres -c \"psql $gforge_db -c \\\"INSERT INTO users (user_name, realname, firstname, lastname, email, user_pw, unix_pw, status, theme_id) VALUES ('$admin_user', 'Forge Admin', 'Forge', 'Admin', 'root@localhost.localdomain', '$pw_md5', '$pw_crypt', 'A', 1); INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (currval('users_pk_seq'), 1, 'A'); INSERT INTO pfo_user_role (user_id, role_id) VALUES (currval('users_pk_seq'), 3)\\\"\"");
		} else {
			run("su - postgres -c \"psql $gforge_db -c \\\"INSERT INTO users (user_name, realname, firstname, lastname, email, user_pw, unix_pw, status, theme_id) VALUES ('$admin_user', 'Forge Admin', 'Forge', 'Admin', 'root@localhost.localdomain', '$pw_md5', '$pw_crypt', 'A', 1); INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (currval('users_pk_seq'), 1, 'A')\\\"\"");
		}

//echo "BREAKPOINT 2\n";
//$t = trim(fgets($STDIN));

//	run("su - postgres -c \"psql $gforge_db -c \\\"INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (currval('users_pk_seq'), 1, 'A')\\\"\"" );

//echo "BREAKPOINT 3\n";
//$t = trim(fgets($STDIN));

	}
	if (!is_dir($fusionforge_etc_dir)) {
		mkdir($fusionforge_etc_dir);
	}


	show(' * Saving database configuration in FForge config file');
	$data = file_get_contents("$fusionforge_etc_dir/local.inc");
	$lines = explode("\n",$data);
	$config = '';
	foreach ($lines as $l) {
		$l = preg_replace("/^.sys_dbname\s*=\s*'(.*)'/", "\$sys_dbname='$gforge_db'", $l);
		$l = preg_replace("/^.sys_dbuser\s*=\s*'(.*)'/", "\$sys_dbuser='$gforge_user'", $l);
		$config .= $l."\n";
	}

	if ($fp = fopen("$fusionforge_etc_dir/local.inc", "w")) {
		fwrite ($fp, $config);
		fclose($fp);	
	}

	show(' * Saving installation log in /tmp/gforge-import.log');
}
/*
function uninstall() {
	global $PGHBA, $fusionforge_src_dir, $gforge_var_dir, $fusionforge_etc_dir, $gforge_db, $gforge_user, $tsearch2_sql;

	show(" * Removing DATABASE \n";
	system("su - $gforge_user -c \"dropdb $gforge_db\"", $ret );
	show(" done . ($ret)\n";

	show(" * Removing Language \n";
	system("su - postgres -c \"droplang plpgsql template1\"", $ret );
	show(" done. ($ret)\n";

	show(" * Removing GForge DATABASE User: \n";
	system("su - postgres -c \"dropuser $gforge_user\"", $ret );
	show(" done.($ret)");

	show(" * Removing GForge User: \n";
	system("userdel $gforge_user");
	show(" done.");

	show(" * Restoring $PGHBA file: ... ";
	system("cp $PGHBA.gforge.backup $PGHBA");
	show(" done.");

	show(" * Restarting PostgreSQL: ...\n";
	system("/etc/init.d/postgresql restart");
	show(" done.");
}
*/

function validatePassword($password) {
	if (strlen($password)<6) {
		return 'Password is too short. Please try again.';
	}
	if (!preg_match('/[[:alnum:]]*/', $password)) {
		return 'Password contains invalid characters. Please try again.';
	}
	return '';
}

function readMasked($prompt) {
	global $STDIN;
	if (strtolower(php_uname('s')) == 'sunos') {
	    show($prompt);
	    $text_entered = fgets($STDIN);
	} else {
	    $options="-er -s -p";
	    $returned=popen("read $options \"".GREEN.$prompt.NORMAL."\n\"; echo \$REPLY", 'r');
	    $text_entered=fgets($returned, 100);
	    pclose($returned);
	    $text_entered=substr($text_entered, 0, strlen($text_entered));
	    @ob_flush();
	    flush();
	}
	return trim($text_entered);
}

function finish() {
	show(NORMAL."Done.\nYou are ready to run install-gforge-3.php");
}

function show($text, $newLine = true) {
	global $STDOUT;

	$hd = fopen ("/tmp/gforge-import.log", "a+");
	fwrite($hd, "*** $text\n");
	fclose($hd);

	if ($newLine) {
		$text = GREEN.$text .NORMAL."\n";
	}
	fwrite($STDOUT, $text);
}

function run($command, $ignore = false) {

	$hd = fopen ("/tmp/gforge-import.log", "a+");
	fwrite($hd, "CMD ".$command."\n");
	fclose($hd);

	system($command, $ret);
	if ($ignore) {
		if ($ret != 0) {
			return false;
		} else {
			return true;
		}
	} else {
		if ($ret != 0) {
			echo RED.'An error ocurred running the last command... aborting.'.NORMAL."\n";
			die();
		}
	}
}

install();
?>
