#! /usr/bin/php4 -f
<?php
/**
 * create_users.php 
 *
 * Francisco Gimeno <kikov@fco-gimeno.com>
 *
 * @version   $Id
 */

require ('squal_pre.php');

//
//      Owner of files - apache
//
$file_owner='nobody:nogroup';

$first_letter = false;

if ($argc < 2 ) {
	echo "Usage ".$argv[0]." <path> <-f>\n";
	echo "-f   First Letter activated: users/a/abel\n";
	exit (0);
}
$upload_path = $argv[1];
if ($argv[2]=='-f') {
   $first_letter = true;
}

/*

	This script create the gforge/upload directory for users

*/


/*
	Get users
*/

$res = db_query("SELECT user_name FROM users WHERE status='A';");
if (!$res) {
	echo "Error!\n";
}

system("[ ! -d $upload_path/users ] && mkdir $upload_path/users");

while ( $row = db_fetch_array($res) ) {
	echo "Name:".$row["user_name"]." \n";

	if ($first_letter) {
	   system ("[ ! -d $upload_path/users/".$row["user_name"][0]."/".$row["user_name"]." ] && mkdir -p $upload_path/users/".$row["user_name"][0]."/".$row["user_name"]);
	   system ("[ ! -d $upload_path/users/".$row["user_name"][0]."/".$row["user_name"]."/private ] && mkdir -p $upload_path/users/".$row["user_name"][0]."/".$row["user_name"]."/private");
	   system ("[ ! -d $upload_path/users/".$row["user_name"][0]."/".$row["user_name"]."/www ] && mkdir -p $upload_path/users/".$row["user_name"][0]."/".$row["user_name"]."/www");
	} else {
	   system ("[ ! -d $upload_path/users/".$row["user_name"]." ] && mkdir -p $upload_path/users/".$row["user_name"]);
	   system ("[ ! -d $upload_path/users/".$row["user_name"]."/private ] && mkdir -p $upload_path/users/".$row["user_name"]."/private");
	   system ("[ ! -d $upload_path/users/".$row["user_name"]."/www ] && mkdir -p $upload_path/users/".$row["user_name"]."/www");
	}
}

system("chown $file_owner -R $upload_path/users");
system("cd $upload_path/users ; find -type d -exec chmod 700 {} \;");
system("cd $upload_path/users ; find -type f -exec chmod 600 {} \;");

?>
