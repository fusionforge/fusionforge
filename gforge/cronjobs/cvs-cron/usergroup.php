#! /usr/bin/php4 -f
<?php
/*

This file creates user / group permissions by editing 
the /etc/passwd /etc/shadow and /etc/group files

It also creates blank user home directories and 
creates a group home directory with a template in it.

#
# * hosts
#
<VirtualHost 192.168.1.5>
ServerName gforge.company.com
ServerAlias *.gforge.company.com
VirtualDocumentRoot /home/groups/%1/htdocs
VirtualScriptAlias /home/groups/%1/cgi-bin
<Directory /home/groups>
Options Indexes FollowSymlinks
AllowOverride All
order allow,deny
allow from all
</Directory>
LogFormat "%h %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" gforge
CustomLog "|/usr/local/sbin/cronolog /home/groups/%1/logs/%Y/%m/%d/gforge.log" gforge
# Ensure that we don't try to use SSL on SSL Servers
 <IfModule apache_ssl.c>
 SSLDisable
 </IfModule>
</VirtualHost> 
*/
require_once('squal_pre.php');
require ('common/include/cron_utils.php');

//
//	Default values for the script
//
define('DEFAULT_SHELL','/bin/false'); //use /bin/grap for cvs-only
define('USER_ID_ADD',10000);
define('GROUP_ID_ADD',50000);
define('USER_DEFAULT_GROUP','users');
define('FILE_EXTENSION','.new'); // use .new when testing
define('CVS_ROOT','/cvsroot/');

if (!file_exists('/etc/passwd.org')) {
	echo "passwd.org missing";
	exit;
}

if (!file_exists('/etc/shadow.org')) {
	echo "shadow.org missing";
	exit;
}

if (!file_exists('/etc/group.org')) {
	echo "group.org missing";
	exit;
}

//
//	Get the users' unix_name and password out of the database
//	ONLY USERS WITH CVS COMMIT PRIVS ARE ADDED
//
$res = db_query("SELECT distinct users.user_name,users.unix_pw,users.user_id 
	FROM users,user_group,groups
	WHERE users.user_id=user_group.user_id 
	AND user_group.group_id=groups.group_id
	AND groups.status='A'
	AND user_group.cvs_flags='1'
	AND users.status='A'
	ORDER BY user_id ASC");

$users    =& util_result_column_to_array($res,'user_name');
$user_ids =& util_result_column_to_array($res,'user_id');
$user_pws =& util_result_column_to_array($res,'unix_pw');

//
//	Get anonymous pserver users
//
$res7=db_query("SELECT unix_group_name FROM groups WHERE status='A' AND is_public='1' AND enable_anonscm='1' AND type_id='1';");
$err .= db_error();
$rows = db_numrows($res7);
for($k = 0; $k < $rows; $k++) {
	$pserver_anon[db_result($res7,$k,'unix_group_name')]=',anonymous';
}

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

		$line = $users[$i] . ":x:" . ($user_ids[$i] + USER_ID_ADD) . ":" . 
			($user_ids[$i] + USER_ID_ADD) . "::/home/$users[$i]:".DEFAULT_SHELL."\n";
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
$res=db_query("SELECT group_id,unix_group_name FROM groups WHERE status='A' AND type_id='1'");
for($i = 0; $i < db_numrows($res); $i++) {
    $groups[] = db_result($res,$i,'unix_group_name');
    $gids[db_result($res,$i,'unix_group_name')]=db_result($res,$i,'group_id')+GROUP_ID_ADD;
}

for($i = 0; $i < count($groups); $i++) {

	if ($def_group[$groups[$i]]) {

		//this groupname was already existing in the "default" file

	} else {

		$line = $groups[$i] . ":x:" . ($gids[$groups[$i]]) . ":";

		$resusers=db_query("SELECT user_name 
			FROM users,user_group,groups 
			WHERE groups.group_id=user_group.group_id 
			AND users.user_id=user_group.user_id
			AND user_group.cvs_flags='1'
			AND users.status='A'
			AND groups.unix_group_name='$groups[$i]'");
		$gmembers =& util_result_column_to_array($resusers,'user_name');
		$line .= implode(',',$gmembers).$pserver_anon[$groups[$i]]."\n";

		fwrite($h6, $line);

	}

}

fclose($h6);

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


//
//	Create home dir for groups
//
foreach($groups as $group) {
	if (is_dir($groupdir_prefix."/".$group)) {

	} else {
		@mkdir($groupdir_prefix."/".$group);
		@mkdir($groupdir_prefix."/".$group."/htdocs");
		@mkdir($groupdir_prefix."/".$group."/cgi-bin");
		$g =& group_get_object_by_name($group);

		//
		//	Read in the template file
		//
		$fo=fopen('default_page.php','r');
		$contents = '';
		while (!feof($fo)) {
    		$contents .= fread($fo, 8192);
		}
		fclose($fo);
		//
		//	Change some defaults in the template file
		//
		//$contents=str_replace('<domain>',$sys_default_domain,$contents);
		//$contents=str_replace('<project_description>',$g->getDescription(),$contents);
		//$contents=str_replace('<project_name>',$g->getPublicName(),$contents);
		//$contents=str_replace('<group_id>',$g->getID(),$contents);
		//$contents=str_replace('<group_name>',$g->getUnixName(),$contents);

		//
		//	Write the file back out to the project home dir
		//
		$fw=fopen($groupdir_prefix."/".$group."/htdocs/index.php",'w');
		fwrite($fw,$contents);
		fclose($fw);
		
	}
	$resgroupadmin=db_query("SELECT u.user_name FROM users u,user_group ug,groups g
		WHERE u.user_id=ug.user_id 
		AND ug.group_id=g.group_id 
		AND g.unix_group_name='$group'
		AND ug.admin_flags='A'
		AND u.status='A'");
	if (!$resgroupadmin || db_numrows($resgroupadmin) < 1) {
		//group has no members, so cannot create dir
	} else {
		$user=db_result($resgroupadmin,0,'user_name');
		system("chown -R $user:$group $groupdir_prefix/$group");
	}
}

cron_entry(16,$err);

?>
