#!/usr/bin/php
<?php
	/**
	 * Copyright (c) 2005-2006 GForge, LLC
	 *
	 * @version $Id: $
	 *
	 * May not be modified or redistributed without permission of GForge Group
	 *
	 */

	define ('GREEN', "\033[01;32m" );
	define ('NORMAL', "\033[00m" );
	define ('RED', "\033[01;31m" );

	$STDOUT = fopen('php://stdout','w');
	$STDIN = fopen('php://stdin','r');

	show("\n-=# Welcome to GForge DB-Installer v4.6 #=-");

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
	elseif (is_file('/etc/init.d/postgresql-7.4'))
	{
		$pgservice='/etc/init.d/postgresql-7.4';
	} 
	elseif (is_file('/etc/init.d/cswpostgres'))
	{
		$pgservice='/etc/init.d/cswpostgres';
	}
	else
	{
		die("ERROR: Could not find Postgresql init script\n");
	}

	// Might fail if it's already running, so we'll ingnore the result
	run("$pgservice start", true);


	// Where the PHP code will live
	//$gforge_lib_dir = '/opt/gforge5';   //CAMBIE ESTO
	$gforge_lib_dir = '/opt/gforge';

	if (!is_dir($gforge_lib_dir))
	{
		die("Error: GForge folder doesn't exist. Run install-gforge-1-deps.php first.");
	}


	// Where the configuration files will live
	$gforge_etc_dir = getenv('GFORGE_ETC_DIR');
	if (empty($gforge_etc_dir))
	{
		$gforge_etc_dir = '/etc/gforge';
	}


	// Where the PGHBA config file is
	if (is_file("/var/lib/pgsql/data/pg_hba.conf"))
	{
		// RedHat & SuSE
		$PGHBA='/var/lib/pgsql/data/pg_hba.conf';
	}
	elseif (is_file('/etc/postgresql/7.4/main/pg_hba.conf'))
	{
		$PGHBA='/etc/postgresql/7.4/main/pg_hba.conf';
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
	elseif (is_file('/usr/share/postgresql/7.4/contrib/tsearch2.sql'))
	{
		$tsearch2_sql='/usr/share/postgresql/7.4/contrib/tsearch2.sql';
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
	global $PGHBA, $gforge_lib_dir, $gforge_etc_dir, $tsearch2_sql, $pgservice, $STDIN, $STDOUT;
	show("\n * Enter the Database Name (gforge): ");

	$gforge_db = trim(fgets($STDIN));
	if (strlen($gforge_db) == 0) {
		$gforge_db = 'gforge';
		show(" ...using '$gforge_db'");
	}
	show(' * Enter the Database Username (gforge): ');
	$gforge_user = trim(fgets($STDIN));
	if (strlen($gforge_user) == 0) {
		$gforge_user = 'gforge';
		show(" ...using '$gforge_user'");
	}
	show(" * Modifying DB Access Permissions...");
	if (!file_exists("$PGHBA.gforge.backup")) {
		run("cp $PGHBA $PGHBA.gforge.backup", true);
	}
	run("echo \"# GFORGE\nlocal all all trust\" > $PGHBA");
	show(' * Restarting PostgreSQL...');
	run("$pgservice stop", true);
	run("$pgservice start");


	show(" * Creating '$gforge_user' Group...");
	run("/usr/sbin/groupadd $gforge_user", true);

	show(" * Creating '$gforge_user' User...");
	run("/usr/sbin/useradd -g $gforge_user $gforge_user", true);

	show(" * Creating Database User '$gforge_user'...");
	//run("su - postgres -c \"createuser -A -d -E $gforge_user\"", true);
	run("su - postgres -c \"createuser -A -d -E $gforge_user\"", true);

	show(' * Creating Language...');
	run("su - postgres -c \"createlang plpgsql template1\"", true);

	if (!is_dir("/home/$gforge_user")) {
	    $susufix = '';
	} else {
	    $susufix = '-';
	}

	show(" * Creating '$gforge_db' Database...");
	run("su $susufix $gforge_user -c \"createdb $gforge_db\"", true);

	show(" * Dumping tsearch2 Database Into '$gforge_db' DB");
	run("su - postgres -c \"psql $gforge_db < $tsearch2_sql\"");

	$tables = array('pg_ts_cfg', 'pg_ts_cfgmap', 'pg_ts_dict', 'pg_ts_parser');
	foreach ($tables as $table) {
		run('su - postgres -c "psql '.$gforge_db.' -c \\"GRANT ALL on '.$table.' TO '.$gforge_user.';\\""');
	}

	show(' * Dumping GForge DB');
	//run("su $susufix $gforge_user -c \"psql $gforge_db < 
	//	$gforge_lib_dir/db/pgsql/gforge5-complete.sql\" > /tmp/gforge-import.log");

	//run("su $susufix $gforge_user -c \"psql $gforge_db < 
	//	$gforge_lib_dir/db/gforge.sql\" > /tmp/gforge-import.log");

	//LINEA SUGERIDA POR MARCELO
	//system("createlang -U postgres plpgsql gforge");
	run("su $susufix $gforge_user -c \"psql $gforge_db < $gforge_lib_dir/db/gforge.sql\" > /tmp/gforge-import.log");

	show(' * Dumping GForge FTI DB');
	//	run("su $susufix $gforge_user -c \"psql $gforge_db < 
	//		$gforge_lib_dir/db/pgsql/FTI-gforge5.sql\" >> /tmp/gforge-import.log");
	run("su $susufix $gforge_user -c \"psql $gforge_db < $gforge_lib_dir/db/FTI.sql\" >> /tmp/gforge-import.log");
	run("su $susufix $gforge_user -c \"psql $gforge_db < $gforge_lib_dir/db/FTI-20050315.sql\" >> /tmp/gforge-import.log");
	run("su $susufix $gforge_user -c \"psql $gforge_db < $gforge_lib_dir/db/FTI-20050401.sql\" >> /tmp/gforge-import.log");
	run("su $susufix $gforge_user -c \"psql $gforge_db < $gforge_lib_dir/db/FTI-20050530.sql\" >> /tmp/gforge-import.log");
	run("su $susufix $gforge_user -c \"psql $gforge_db < $gforge_lib_dir/db/FTI-20060130.sql\" >> /tmp/gforge-import.log");
	run("su $susufix $gforge_user -c \"psql $gforge_db < $gforge_lib_dir/db/FTI-20061025.sql\" >> /tmp/gforge-import.log");

	show(" * Enter the Admin Username (gforgeadmin): ");
	$admin_user = trim(fgets($STDIN));

	if (strlen($admin_user) == 0) {
		$admin_user = 'gforgeadmin';
		show(" ...using '$admin_user'");
	}

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
	run("su - postgres -c \"psql $gforge_db -c \\\"INSERT INTO users (user_name, email, user_pw, unix_pw, status, theme_id) VALUES ('$admin_user', 'root@localhost', '$pw_md5', '$pw_crypt', 'A', 1); INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (currval('users_pk_seq'), 1, 'A')\\\"\"");

//echo "BREAKPOINT 2\n";
//$t = trim(fgets($STDIN));

//	run("su - postgres -c \"psql $gforge_db -c \\\"INSERT INTO user_group (user_id, group_id, admin_flags) VALUES (currval('users_pk_seq'), 1, 'A')\\\"\"" );

//echo "BREAKPOINT 3\n";
//$t = trim(fgets($STDIN));

	}
	if (!is_dir($gforge_etc_dir)) {
		mkdir($gforge_etc_dir);
	}

	//show(' * Setting up Config File for GForge');
	//$gforge5_db_conf = array("'hostspec'"=>		"'hostspec' => '',", 
	//					 	 "'database'"=>			"'database' => '$gforge_db',",
	//					 	 "'username'"=>			"'username' => '$gforge_user',",
	//					 	 "'password'"=>			"'password' => '',");
	//
	//exec('/bin/cp conf/gforge5-db-conf.php.example '.$gforge_etc_dir.'/gforge5-db-conf.php');
	//echo "BP 1\n";
	//foreach($gforge5_db_conf as $key => $val) {
	//	$key = str_replace("'", "\\'", $key);
	//	$val = str_replace("'", "\\'", $val);
	//	run("perl -pi -e \"s/$key.*/$val/gi\" $gforge_etc_dir/gforge5-db-conf.php");
	//}
	//finish();
}
/*
function uninstall() {
	global $PGHBA, $gforge_lib_dir, $gforge_var_dir, $gforge_etc_dir, $gforge_db, $gforge_user, $tsearch2_sql;

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
	if ($newLine) {
		$text = GREEN.$text .NORMAL."\n";
	}
	fwrite($STDOUT, $text);
}

function run($command, $ignore = false) {
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

