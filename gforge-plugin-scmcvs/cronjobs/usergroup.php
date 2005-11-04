#! /usr/bin/php4 -f
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*

NOTE - THIS SQL CAN BE USED ON A SEPARATED CVS SERVER TO GRANT ONLY THE NECESSARY PERMS


CREATE USER cvsuser WITH ENCRYPTED PASSWORD 'password';

GRANT SELECT ON groups, plugins, group_plugin, users, user_group, deleted_groups TO cvsuser;
GRANT INSERT, UPDATE ON deleted_groups TO cvsuser;
GRANT ALL ON stats_cvs_group, stats_cvs_user TO cvsuser;

*/

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
define('DEFAULT_SHELL','/bin/cvssh.pl'); //use /bin/grap for cvs-only
define('USER_ID_ADD',10000);
define('GROUP_ID_ADD',50000);
define('USER_DEFAULT_GROUP','users');
define('FILE_EXTENSION','.new'); // use .new when testing

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

if (util_is_root_dir($groupdir_prefix)) {
	$err .=  "Error! groupdir_prefix Points To Root Directory!";
	echo $err;
	cron_entry(16,$err);
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
$group_res=db_query("SELECT group_id,unix_group_name FROM groups WHERE status='A' AND type_id='1'");
for($i = 0; $i < db_numrows($group_res); $i++) {
    $groups[] = db_result($group_res,$i,'unix_group_name');
    $gids[db_result($group_res,$i,'unix_group_name')]=db_result($group_res,$i,'group_id')+GROUP_ID_ADD;
}

for($i = 0; $i < count($groups); $i++) {

	if ($def_group[$groups[$i]]) {

		//this groupname was already existing in the "default" file

	} else {

		$line = $groups[$i] . ":x:" . ($gids[$groups[$i]]) . ":";


		/* we need to get the project object to check if a project
		 * has a private CVS repository - in which case we need to add
		 * the apache user to the group so that ViewCVS can be used
		 */
		 
		$gid = db_result($group_res, $i, 'group_id');
		$project = &group_get_object($gid);
		
		$resusers=db_query("SELECT user_name 
			FROM users,user_group,groups 
			WHERE groups.group_id=user_group.group_id 
			AND users.user_id=user_group.user_id
			AND user_group.cvs_flags='1'
			AND users.status='A'
			AND groups.unix_group_name='$groups[$i]'");
			
		$gmembers =& util_result_column_to_array($resusers,'user_name');
		
		$group_name = $groups[$i];
		if (!($project->enableAnonSCM())) {
			if (!$gmembers) {
				//if there´s not a user in $gmembers, remove the initial "," from pserver_anon
				if ($pserver_anon[$groups[$i]]) {
					$this_anon = ltrim($pserver_anon[$groups[$i]],",");
					$line .= $this_anon . "," . $sys_apache_user . "\n";
				} else {
					$line .= $sys_apache_user . "\n"; // only the apache user then?
				}
			} else {
				$line .= implode(',',$gmembers) . $pserver_anon[$groups[$i]] . "," . $sys_apache_user . "\n";
			}
		} else {
			if (!$gmembers) {
				//if there´s not a user in $gmembers, remove the initial "," from pserver_anon
				if ($pserver_anon[$groups[$i]]) {
					$this_anon = ltrim($pserver_anon[$groups[$i]],",");
					$line .= $this_anon . "\n";
				} else {
					$line .= "\n"; //no users
				}
			} else {
				$line .= implode(',',$gmembers) . $pserver_anon[$groups[$i]] . "\n";
			}
		}
		
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

	//create an FTP upload dir for this project
	if ($sys_use_ftpuploads) { 
		if (!is_dir($sys_ftp_upload_dir.'/'.$group)) {
			@mkdir($sys_ftp_upload_dir.'/'.$group); 
		}
	}

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
		if (!$fo) {
			$err .= 'Default Page Not Found';
		} else {
			while (!feof($fo)) {
    			$contents .= fread($fo, 8192);
			}
			fclose($fo);
		}
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
	/*$resgroupadmin=db_query("SELECT u.user_name FROM users u,user_group ug,groups g
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
	}*/
	system("chown -R $sys_apache_user:$sys_apache_group $groupdir_prefix/$group");
}

//
//	Move CVS trees for deleted groups
//
$res8=db_query("SELECT unix_group_name FROM deleted_groups WHERE isdeleted = 0;");
$err .= db_error();
$rows	 = db_numrows($res8);
for($k = 0; $k < $rows; $k++) {
	$deleted_group_name = db_result($res8,$k,'unix_group_name');

	if(!is_dir($sys_cvsroot."/deleted"))
		system("mkdir ".$sys_cvsroot."/deleted");
		
	if(!is_dir($sys_cvsroot."/deleted/".$deleted_group_name))
		system("mkdir ".$sys_cvsroot."/deleted/".$deleted_group_name);

	system("mv -f $sys_cvsroot/$deleted_group_name/*.* $sys_cvsroot/.deleted/$deleted_group_name");
	
	
	$res9 = db_query("UPDATE deleted_groups set isdeleted = 1 WHERE unix_group_name = '$deleted_group_name';" );
	$err .= db_error();
}

cron_entry(16,$err);

?>
