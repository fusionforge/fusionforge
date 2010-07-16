#! /usr/bin/php
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
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
require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

setup_gettext_from_sys_lang();	
define('USER_DEFAULT_GROUP','users');
//error variable
$err = '';

if (forge_get_config('groupdir_prefix') == '') {		// this should be set in configuration
	exit () ;
}

if (!is_dir(forge_get_config('groupdir_prefix'))) {
	@mkdir(forge_get_config('groupdir_prefix'),0755,true);
}

if (forge_get_config('homedir_prefix') == '') {		// this should be set in configuration
	exit () ;
}

if (!is_dir(forge_get_config('homedir_prefix'))) {
	@mkdir(forge_get_config('homedir_prefix'),0755,true);
}

$res = db_query_params ('SELECT distinct users.user_name,users.unix_pw,users.user_id
	FROM users,user_group,groups
	WHERE users.user_id=user_group.user_id
	AND user_group.group_id=groups.group_id
	AND groups.status=$1
	AND user_group.cvs_flags IN (0,1)
	AND users.status=$2
	ORDER BY user_id ASC',
			array('A',
			      'A'));
$err .= db_error();
$users    =& util_result_column_to_array($res,'user_name');

$group_res = db_query_params ('SELECT unix_group_name, (is_public=1 AND enable_anonscm=1 AND type_id=1) AS enable_pserver FROM groups WHERE status=$1 AND type_id=1',
			      array('A'));
$err .= db_error();
$groups = util_result_column_to_array($group_res,'unix_group_name');


//
//	this is where we give a user a home
//
foreach($users as $user) {
	if (is_dir(forge_get_config('homedir_prefix')."/".$user)) {
		
	} else {
		@mkdir(forge_get_config('homedir_prefix')."/".$user);
	}
	system("chown $user:".USER_DEFAULT_GROUP." ".forge_get_config('homedir_prefix')."/".$user);
}


//
//	Create home dir for groups
//
foreach($groups as $group) {

	//test if the FTP upload dir exists and create it if not
	if (!is_dir(forge_get_config('ftp_upload_dir'))) {
		@mkdir(forge_get_config('ftp_upload_dir'),0755,true);
	}
	
	//create an FTP upload dir for this project
	if (forge_get_config('use_ftp_uploads')) { 
		if (!is_dir(forge_get_config('ftp_upload_dir').'/'.$group)) {
			@mkdir(forge_get_config('ftp_upload_dir').'/'.$group); 
		}
	}

	if (is_dir(forge_get_config('groupdir_prefix')."/".$group)) {

	} else {
		@mkdir(forge_get_config('groupdir_prefix')."/".$group);
		@mkdir(forge_get_config('groupdir_prefix')."/".$group."/htdocs");
		@mkdir(forge_get_config('groupdir_prefix')."/".$group."/cgi-bin");
		$g =& group_get_object_by_name($group);
		

		//
		//	Read in the template file
		//
		$fo=fopen(dirname(__FILE__).'/../utils/default_page.php','r');
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
		//$contents=str_replace('<domain>',forge_get_config('web_host'),$contents);
		//$contents=str_replace('<project_description>',$g->getDescription(),$contents);
		//$contents=str_replace('<project_name>',$g->getPublicName(),$contents);
		//$contents=str_replace('<group_id>',$g->getID(),$contents);
		//$contents=str_replace('<group_name>',$g->getUnixName(),$contents);

		$contents=str_replace('##comment##', _('Default Web Page for groups that haven\'t setup their page yet'), $contents);
		$contents=str_replace('##purpose##', _('Please replace this file with your own website'), $contents);
		$contents=str_replace('##welcome_to##', sprintf(_('Welcome to %s'), $g->getPublicName()), $contents);
		$contents=str_replace('##body##',
			sprintf(
				_("We're Sorry but this Project hasn't yet uploaded their personal webpage yet. <br /> Please check back soon for updates or visit <a href=\"%s\">the project page</a>."),
				"http://".forge_get_config('web_host').'/projects/'.$g->getUnixName()),
			$contents);
		//
		//	Write the file back out to the project home dir
		//
		$fw=fopen(forge_get_config('groupdir_prefix')."/".$group."/htdocs/index.html",'w');
		fwrite($fw,$contents);
		fclose($fw);
	}

	if (forge_get_config('use_manual_uploads')) { 
		$incoming = forge_get_config('groupdir_prefix')/$group."/incoming" ;
		if (!is_dir($incoming))
		{
			@mkdir($incoming); 
		}
	}

	system("chown -R ".forge_get_config('apache_user').":".forge_get_config('apache_group')." forge_get_config('groupdir_prefix')/$group");

}


cron_entry(25,$err);

?>
