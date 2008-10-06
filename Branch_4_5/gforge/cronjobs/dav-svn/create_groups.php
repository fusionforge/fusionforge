#! /usr/bin/php4 -f
<?php
/**
 * create_groups.php 
 *
 * Francisco Gimeno <kikov@fco-gimeno.com>
 *
 * @version   $Id
 */

require ('squal_pre.php');

//
//	Owner of files - apache
//
$file_owner='nobody:nogroup';

$first_letter = false;

/*

	This script create the gforge/upload directory for users

*/

if ($argc < 2 ) {
	echo "Usage ".$argv[0]." <path> <-f>\n";
	echo "-f  First Letter activated... do groups/m/myprojec\n";
	exit (0);
}
if ( $argv[2]=='-f' ) {
   $first_letter = true;
}

$upload_path = $argv[1];
echo "Creating Groups at ". $upload_path."\n";

$res = db_query("SELECT unix_group_name FROM groups WHERE status != 'P';");
if (!$res) {
	echo "Error!\n";
}

system("[ ! -d ".$upload_path."/groups ] && mkdir $upload_path/groups"); 

while ( $row = db_fetch_array($res) ) {
	echo "Name:".$row["unix_group_name"]." \n";
	if ($first_letter) {
		system ("[ ! -d $upload_path/groups/".$row["unix_group_name"][0]."/".$row["unix_group_name"]." ] && mkdir -p $upload_path/groups/".$row["unix_group_name"][0]."/".$row["unix_group_name"]);
	} else {
		system ("[ ! -d $upload_path/groups/".$row["unix_group_name"]." ] && mkdir $upload_path/groups/".$row["unix_group_name"]);
	}
}

system("chown $file_owner -R $upload_path/groups");
system("cd $upload_path/groups ; find -type d -exec chmod 700 {} \;");
system("cd $upload_path/groups ; find -type f -exec chmod 600 {} \;");

?>
