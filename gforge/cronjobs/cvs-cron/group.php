#!/usr/bin/php
<?php

//this reads the database and creates groups in /etc/group
//this script must be ran before you run the add users to groups first,
//because you need a group to be a memeber of it

require_once('squal_pre.php');

//1) read in groups from db
$groups = array();
$res=db_query("SELECT unix_group_name FROM groups");
for($i = 0; $i < db_numrows($res); $i++) {
	$groups[] = db_result($res,$i,'unix_group_name');
}

//2) read in groups from /etc/group
$h = fopen("/etc/group.backup","r");

if(!$h) {
	die("Groups.php -- unable to open /etc/group for reading");
}

$filecontent = fread($h, filesize("/etc/group.backup"));
fclose($h);
$lines = explode("\n",$filecontent);

//3) if group is listed in the db and not /etc/group add
$h2 = fopen("/etc/group.backup","w");

if(!h2) {
	die("Groups.php -- unable to open /etc/group for writing");
}

//write the group file out again, followed by new gforge stuff
$i = 0;
for($i; $i < count($lines)-1; $i++) {
	fwrite($h2,$lines[$i]."\n");
}
fwrite($h2,$lines[$i]);

//see if there is no group with same name, if not add group, if so don't add group	
foreach($groups as $group) {
	foreach($lines as $line) {
		$etcline = explode(":",$line);

		if($group == $etcline[0]) {
			continue 2;
		}
	}

	$gid = random_gid(100,60000);
	$writegrouptofile = "$group:x:$gid:\n";
	fwrite($h2,$writegrouptofile);
}

function random_gid($start,$finish) {
	global $lines;

	$searching = true;

	while($searching) {
		$temp = rand($start,$finish);

		foreach($lines as $line) {
			$temp1 = explode(":",$line);

			if($temp1[2] == $temp)
				continue 2;
		}

		return $temp;
	}
}

fclose($h2);	
?>
