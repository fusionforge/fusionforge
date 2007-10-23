#! /usr/bin/php5 -f
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
This file creates blank user home directories and 
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

define('USER_DEFAULT_GROUP','users');

if (!isset($groupdir_prefix)) {		// this should be set in local.inc
	$groupdir_prefix = '/home/groups';
}

if (!is_dir($groupdir_prefix)) {
	@mkdir($groupdir_prefix);
}

$res = db_query("SELECT distinct users.user_name,users.unix_pw,users.user_id
	FROM users,user_group,groups
	WHERE users.user_id=user_group.user_id 
	AND user_group.group_id=groups.group_id
	AND groups.status='A'
	AND user_group.cvs_flags IN ('0','1')
	AND users.status='A'
	ORDER BY user_id ASC");
$err .= db_error();
$users    =& util_result_column_to_array($res,'user_name');

$group_res = db_query("SELECT unix_group_name, (is_public=1 AND enable_anonscm=1 AND type_id=1) AS enable_pserver FROM groups WHERE status='A' AND type_id='1'");
$err .= db_error();
$groups = util_result_column_to_array($group_res,'unix_group_name');


//
//	this is where we give a user a home
//
foreach($users as $user) {
	if (is_dir("/home/".$user)) {
		
	} else {
		@mkdir("/home/".$user);
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
		$fo=fopen(dirname(__FILE__).'/default_page.php','r');
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
// Move CVS trees for deleted groups
//
$res8 = db_query("SELECT unix_group_name FROM deleted_groups WHERE isdeleted = 0;");
$err .= db_error();
$rows	 = db_numrows($res8);
for($k = 0; $k < $rows; $k++) {
	$deleted_group_name = db_result($res8,$k,'unix_group_name');

	if(!is_dir($cvsdir_prefix."/.deleted"))
		system("mkdir ".$cvsdir_prefix."/.deleted");
		
	system("mv -f $cvsdir_prefix/$deleted_group_name/ $cvsdir_prefix/.deleted/");
	system("chown -R root:root $cvsdir_prefix/.deleted/$deleted_group_name");
	system("chmod -R o-rwx $cvsdir_prefix/.deleted/$deleted_group_name");
	
	
	$res9 = db_query("UPDATE deleted_groups set isdeleted = 1 WHERE unix_group_name = '$deleted_group_name';" );
	$err .= db_error();
}


cron_entry(25,$err);

?>
