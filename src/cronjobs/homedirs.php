#! /usr/bin/php
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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

$active_projects = group_get_active_projects() ;
$unames = array () ;
foreach ($active_projects as $project) {
	foreach ($project->getUsers() as $u) {
		$unames[] = $u->getUnixName() ;
	}
}
$unames = array_unique ($unames) ;
foreach($unames as $uname) {
	if (is_dir(forge_get_config('homedir_prefix')."/".$uname)) {

	} else {
		@mkdir(forge_get_config('homedir_prefix')."/".$uname);
	}
	system("chown $uname:".USER_DEFAULT_GROUP." ".forge_get_config('homedir_prefix')."/".$uname);
}

//test if the FTP upload dir exists and create it if not
if (!is_dir(forge_get_config('ftp_upload_dir'))) {
	@mkdir(forge_get_config('ftp_upload_dir'),0755,true);
}

foreach($active_projects as $project) {
	$groupname = $project->getUnixName() ;
	//create an FTP upload dir for this project
	if (forge_get_config('use_ftp_uploads')) {
		if (!is_dir(forge_get_config('ftp_upload_dir').'/'.$groupname)) {
			@mkdir(forge_get_config('ftp_upload_dir').'/'.$groupname);
		}
	}

	if (is_dir(forge_get_config('groupdir_prefix')."/".$groupname)) {

	} else {
		@mkdir(forge_get_config('groupdir_prefix')."/".$groupname);
		@mkdir(forge_get_config('groupdir_prefix')."/".$groupname."/htdocs");
		@mkdir(forge_get_config('groupdir_prefix')."/".$groupname."/cgi-bin");

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
		$contents=str_replace('##comment##', _('Default Web Page for groups that haven\'t setup their page yet'), $contents);
		$contents=str_replace('##purpose##', _('Please replace this file with your own website'), $contents);
		$contents=str_replace('##welcome_to##', sprintf(_('Welcome to %s'), $project->getPublicName()), $contents);
		$contents=str_replace('##body##',
			sprintf(
				_("We're Sorry but this Project hasn't yet uploaded their personal webpage yet. <br /> Please check back soon for updates or visit <a href=\"%s\">the project page</a>."),
				util_make_url ('/projects/'.$project->getUnixName())),
				      $contents);
		//
		//	Write the file back out to the project home dir
		//
		$fw=fopen(forge_get_config('groupdir_prefix')."/".$groupname."/htdocs/index.html",'w');
		fwrite($fw,$contents);
		fclose($fw);
	}

	if (forge_get_config('use_manual_uploads')) {
		$incoming = forge_get_config('groupdir_prefix')/$groupname."/incoming" ;
		if (!is_dir($incoming))
		{
			@mkdir($incoming);
		}
	}

	system("chown -R ".forge_get_config('apache_user').":".forge_get_config('apache_group')." forge_get_config('groupdir_prefix')/$groupname");

}


cron_entry(25,$err);

?>
