#!/usr/bin/php
<?php
/*


	PROBLEM - user's home dir is not owned by user or correct perms


*/
require_once('squal_pre.php');

//
//	Default shell for the user should be grap (cvs only)
//
define('DEFAULT_SHELL','/bin/grap');

//
//	Get the users' unix_name and password out of the database
//
$res = db_query("select distinct users.user_name,users.unix_pw,users.user_id 
	from users,user_group
	WHERE users.user_id=user_group.user_id ORDER BY user_id ASC");

$users = array();
$user_ids = array();
$user_pws = array();

$users    =& util_result_column_to_array($res,'user_name');
$user_ids =& util_result_column_to_array($res,'user_id');
$user_pws =& util_result_column_to_array($res,'unix_pw');

//
//	this is where we add users to /etc/passwd
//
$h = fopen("/etc/passwd.backup","r");
$passwdcontents = fread($h,filesize("/etc/passwd.backup"));
fclose($h);
$passwdlines = explode("\n",$passwdcontents);

$h2 = fopen("/etc/passwd.backup","a");

for($i = 0; $i < count($users); $i++) {
	for($k = 0; $k < count($passwdlines); $k++) {
		$passwdline = explode(":",$passwdlines[$k]);

		if($passwdline[0] == $users[$i]) {
			continue 2;
		}
	}

	$line = $users[$i] . ":x:" . ($user_ids[$i] + 1000) . ":" . ($user_ids[$i] + 1000) . "::/home/$users[$i]:".DEFAULT_SHELL."\n";
	fwrite($h2,$line);

}

fclose($h2);

//
//	this is where we add users to /etc/shadow
//
$h3 = fopen("/etc/shadow.backup","r");
$shadowcontents = fread($h3,filesize("/etc/shadow.backup"));
fclose($h3);
$shadowlines = explode("\n",$shadowcontents);

$h4 = fopen("/etc/shadow.backup","a");

for($i = 0; $i < count($users); $i++) {
	for($k = 0; $k < count($shadowlines); $k++) {
		$shadowline = explode(":",$shadowlines[$k]);
		if($shadowline[0] == $users[$i])
			continue 2;
	}

	$line = $users[$i] . ":" . $user_pws[$i] . ":12090:0:99999:7:::\n";
	fwrite($h4,$line);
}

fclose($h4);

//
//	this is where we give a user a home
//
foreach($users as $user) {
	@mkdir("/home/".$user);
}

//
//	this is where we add user primary groups (redhat specific right now)
//
$h5 = fopen("/etc/group.backup","r");
$groupcontents = fread($h5,filesize("/etc/group.backup"));
fclose($h5);
$grouplines = explode("\n",$groupcontents);

$h6 = fopen("/etc/group.backup","a");

for($i = 0; $i < count($users); $i++) {
	for($k = 0; $k < count($grouplines);$k++) {
		$groupline = explode(":", $grouplines[$k]);
		if($groupline[0] == $users[$i])
			continue 2;
	}

	$line = $users[$i] . ":x:" . ($user_ids[$i]+1000) . ":\n";

	fwrite($h6, $line);
}	

fclose($h6);

//
//	have to re-read the group file since we just modified it
//
$h7 = fopen("/etc/group.backup","r");
$groupcontent = fread($h7,filesize("/etc/group.backup"));
fclose($h7);

$grouplines = explode("\n",$groupcontent);

//
//	this is where we add users to groups in /etc/groups	
//
for($i = 0; $i < count($users); $i++) {
	$res6 = db_query("select groups.group_id,groups.unix_group_name 
		FROM user_group,groups 
		WHERE user_group.user_id='$user_ids[$i]'	
		AND groups.group_id=user_group.group_id");
	$rows = db_numrows($res6);

	for($k = 0; $k < $rows; $k++) {
		$group_id = db_result($res6,$k,'group_id');
		$group = db_result($res6,$k,'unix_group_name');

		for($j = 0; $j < count($grouplines); $j++) {
			list($group_name,$group_pw,$group_id,$members) = explode(":",$grouplines[$j]);

			if($group_name == $group) {
				$memberslist = explode(",",$members);

				foreach($memberslist as $member) {
					if($member == $users[$i]) {
						continue 3;
					}
				}
				if($memberslist[0] == "" && count($memberslist) == 1)
					$grouplines[$j] = $grouplines[$j] . "$users[$i]";
				else
					$grouplines[$j] = $grouplines[$j] . ",$users[$i]";
			}
		}
	}
}

$h8 = fopen("/etc/group.backup","w");
foreach($grouplines as $line)
fwrite($h8,$line."\n");
fclose($h8);

?>
