#! /usr/bin/php4 -f
<?php

require_once('squal_pre.php');

//
//	Default values for the script
//
define('DEFAULT_SHELL','/bin/false'); //use /bin/grap for cvs-only
define('USER_ID_ADD',10000);
define('GROUP_ID_ADD',50000);
define('USER_DEFAULT_GROUP','users');
define('FILE_EXTENSION',''); // use .new when testing
define('CVS_ROOT','/cvsroot/');
//
//	Get the users' unix_name and password out of the database
//	ONLY USERS WITH CVS COMMIT PRIVS ARE ADDED
//
$res = db_query("SELECT distinct users.user_name,users.unix_pw,users.user_id 
	FROM users,user_group
	WHERE users.user_id=user_group.user_id 
	AND user_group.cvs_flags='1'
	AND users.status='A'
	ORDER BY user_id ASC");

$users    =& util_result_column_to_array($res,'user_name');
$user_ids =& util_result_column_to_array($res,'user_id');
$user_pws =& util_result_column_to_array($res,'unix_pw');

//
//	Read in the "default" users
//
$h = fopen("/etc/passwd.org","r");
$passwdcontents = fread($h,filesize("/etc/passwd.org"));
fclose($h);
$passwdlines = explode("\n",$passwdcontents);

//
//	Write the "default" users to a temp file
//
$h2 = fopen("/etc/passwd".FILE_EXTENSION,"w");
for($k = 0; $k < count($passwdlines); $k++) {
	$passwdline = explode(":",$passwdlines[$k]);
	$def_users[$passwdline[0]]=1;
	fwrite($h2,$passwdlines[$k]."\n");
}

//
//	Now append the users from the gforge database
//
for($i = 0; $i < count($users); $i++) {

	if ($def_users[$users[$i]]) {

		//this username was already existing in the "default" file

	} else {

		$line = $users[$i] . ":x:" . ($user_ids[$i] + USER_ID_ADD) . ":" . ($user_ids[$i] + USER_ID_ADD) . "::/home/$users[$i]:".DEFAULT_SHELL."\n";
		fwrite($h2,$line);

	}

}

fclose($h2);

//
//	this is where we add users to /etc/shadow
//
$h3 = fopen("/etc/shadow.org","r");
$shadowcontents = fread($h3,filesize("/etc/shadow.org"));
fclose($h3);
$shadowlines = explode("\n",$shadowcontents);

//
//	Write the "default" shadow to a temp file
//
$h4 = fopen("/etc/shadow".FILE_EXTENSION,"w");
for($k = 0; $k < count($shadowlines); $k++) {
    $shadowline = explode(":",$shadowlines[$k]);
    $def_shadow[$shadowline[0]]=1;
    fwrite($h4,$shadowlines[$k]."\n");
}

//
//  Now append the users from the gforge database
//
for($i = 0; $i < count($users); $i++) {

    if ($def_shadow[$users[$i]]) {

        //this username was already existing in the "default" file

    } else {

		$line = $users[$i] . ":" . $user_pws[$i] . ":12090:0:99999:7:::\n";
		fwrite($h4,$line);

	}

}

fclose($h4);

//
//	Read the groups from the "default" file
//
$h5 = fopen("/etc/group.org","r");
$groupcontents = fread($h5,filesize("/etc/group.org"));
fclose($h5);
$grouplines = explode("\n",$groupcontents);

//
//	Write the "default" groups to a temp file
//
$h6 = fopen("/etc/group".FILE_EXTENSION,"w");
for($k = 0; $k < count($grouplines); $k++) {
    $groupline = explode(":",$grouplines[$k]);
    $def_group[$groupline[0]]=1;
    fwrite($h6,$grouplines[$k]."\n");
}

//
//	Add the groups from the gforge database
//
$res=db_query("SELECT group_id,unix_group_name FROM groups WHERE status='A'");
for($i = 0; $i < db_numrows($res); $i++) {
    $groups[] = db_result($res,$i,'unix_group_name');
    $gids[db_result($res,$i,'unix_group_name')]=db_result($res,$i,'group_id')+GROUP_ID_ADD;
}

for($i = 0; $i < count($users); $i++) {

    if ($def_group[$groups[$i]]) {

        //this username was already existing in the "default" file

    } else {

		$line = $groups[$i] . ":x:" . ($gids[$groups[$i]]) . ":\n";

		fwrite($h6, $line);

	}

}

fclose($h6);

//
//	have to re-read the group file since we just modified it
//
$h7 = fopen("/etc/group".FILE_EXTENSION,"r");
$groupcontent = fread($h7,filesize("/etc/group".FILE_EXTENSION));
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

$h8 = fopen("/etc/group".FILE_EXTENSION,"w");
foreach($grouplines as $line)
fwrite($h8,$line."\n");

#anoncvs_sourceforge:x:10129:50001::/cvsroot/sourceforge:/bin/false
$res7=db_query("SELECT group_id,unix_group_name FROM groups WHERE status='A' AND is_public='1'");
echo db_error();
$rows = db_numrows($res7);
echo $rows;
for($k = 0; $k < $rows; $k++) {
	$group_id = db_result($res7,$k,'group_id');
	$group = db_result($res7,$k,'unix_group_name');
	fwrite($h8,"anoncvs_$group:x:10129:".($group_id+GROUP_ID_ADD)."::".CVS_ROOT."$group:/bin/false\n");
}
fclose($h8);

//
//	this is where we give a user a home
//
foreach($users as $user) {
	if (is_dir("/home/".$user)) {
		
	} else {
		@mkdir("/home/".$user);
//		system("chown $user:".USER_DEFAULT_GROUP." /home/".$user);
	}
	system("chown $user:".USER_DEFAULT_GROUP." /home/".$user);
}

?>
