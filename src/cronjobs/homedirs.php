#! /usr/bin/php
<?php
/**
 * Fusionforge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * Copyright 2012, Franck Villaume - TrivialDev
 * Copyright © 2013 Thorsten Glaser, tarent solutions GmbH
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
 * This file creates blank user home directories and
 * creates a group home directory with a template in it.

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

require dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

setup_gettext_from_sys_lang();
define('USER_DEFAULT_GROUP', 'users');
// error variable
$err = '';

/*
 * check whether directory prefices are set
 * and create the prefix directories unless they exist
 */

if (!($gpfx = forge_get_config('groupdir_prefix'))) {
	// this should be set in configuration
	exit();
}

if (!is_dir($gpfx)) {
	@mkdir($gpfx, 0755, true);
}

if (!($hpfx = forge_get_config('homedir_prefix'))) {
	// this should be set in configuration
	exit();
}
rtrim($hpfx, '/');

if (!is_dir($hpfx)) {
	@mkdir($hpfx, 0755, true);
}

if (forge_get_config('use_ftp_uploads')) {
	if (!($ftp_pfx = forge_get_config('ftp_upload_dir'))) {
		// this should be set in the configuration
		exit();
	}

	if (!is_dir($ftp_pfx)) {
		@mkdir($ftp_pfx, 0755, true);
	}
} else {
	/* signal that we do not use FTP */
	$ftp_pfx = false;
}

/* read in the group home template file */
$contents = '';
if (($fo = fopen(dirname(__FILE__) . '/../utils/default_page.php', 'r'))) {
	while (!feof($fo)) {
		$contents .= fread($fo, 8192);
	}
	fclose($fo);
} else {
	$err .= 'Default Page not found';
}

/* create user homes */
$dirs = array_flip(glob("$hpfx/*/"));
$res = db_query_params('SELECT DISTINCT(user_name) FROM nss_usergroups', array());
foreach(util_result_column_to_array($res,0) as $uname) {
	$uhome = "$hpfx/$uname/";
	if (!isset($dirs[$uhome])) {
		mkdir($uhome);
		chown($uhome, $uname);
		chgrp($uhome, USER_DEFAULT_GROUP);
	}
}

/* create project/group homes */
$res = db_query_params('SELECT unix_group_name, group_name FROM groups WHERE status=$1', array('A'));
while ($row = pg_fetch_array($res)) {
	$groupname = $row['unix_group_name'] ;

	if ($ftp_pfx && !is_dir($ftp_pfx . '/' . $groupname)) {
		@mkdir($ftp_pfx . '/' . $groupname);
		//XXX chown/chgrp/chmod?
	}

	$ghome = $gpfx . '/' . $groupname;
	if (!is_dir($ghome)) {
		@mkdir($ghome);
		/* this is safe as this directory still belongs to root */
		@mkdir($ghome . '/htdocs');
		@mkdir($ghome . '/cgi-bin');

		/* write substituted template to group home */
		if (($fw = fopen($ghome . '/htdocs/index.html', 'w'))) {
			fwrite($fw, str_replace('##comment##',
			    _('Default Web Page for groups that haven\'t setup their page yet'),
			    str_replace('##purpose##',
			    _('Please replace this file with your own website'),
			    str_replace('##welcome_to##',
			    sprintf(_('Welcome to %s'), $row['group_name']),
			    str_replace('##body##',
			    _("We're Sorry but this Project hasn't uploaded their personal webpage yet.").'<br />'.
			    sprintf(_('Please check back soon for updates or visit <a href="%s">the project page</a>.'),
			    util_make_url('/projects/' . $row['unix_group_name'])),
			    $contents)))));
			fclose($fw);
		}

		if (forge_get_config('use_manual_uploads')) {
			@mkdir($ghome . '/incoming');
		}

		//system('chmod -R ug=rwX,o=rX ' . $ghome);
		system('chown -R ' . forge_get_config('apache_user') . ':' .$groupname. ' ' . $ghome);
	}
}

cron_entry(25,$err);
