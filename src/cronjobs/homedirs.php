#! /usr/bin/php
<?php
/**
 * Fusionforge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * Copyright 2012, Franck Villaume - TrivialDev
 * Copyright © 2013 Thorsten Glaser, tarent solutions GmbH
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

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

setup_gettext_from_sys_lang();
define('USER_DEFAULT_GROUP', 'users');
// error variable
$err = '';

/*
 * check whether directory preficēs are set
 * and create the præfix directories unless they exist
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

if (!is_dir($hpfx)) {
	@mkdir($hpfx, 0755, true);
}

if (forge_get_config('use_ftp_uploads')) {
	if (!($fpfx = forge_get_config('ftp_upload_dir'))) {
		// this should be set in the configuration
		exit();
	}

	if (!is_dir($fpfx)) {
		@mkdir($fpfx, 0755, true);
	}
} else {
	/* signal that we do not use FTP */
	$fpfx = false;
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

$active_projects = group_get_active_projects();
$unames = array();
foreach ($active_projects as $project) {
	foreach ($project->getUsers() as $u) {
		$unames[] = $u->getUnixName();
	}
}
$unames = array_unique($unames);
foreach ($unames as $uname) {
	$uhome = $hpfx . "/" . $uname;
	if (!is_dir($uhome)) {
		@mkdir($uhome);
	}
	system("chown $uname:" . USER_DEFAULT_GROUP . " " . $uhome);
}

/* create project/group homes */

foreach ($active_projects as $project) {
	$groupname = $project->getUnixName() ;

	if ($fpfx && !is_dir($fpfx . '/' . $groupname)) {
		@mkdir($fpfx . '/' . $groupname);
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
			    sprintf(_('Welcome to %s'), $project->getPublicName()),
			    str_replace('##body##',
			    sprintf(_("We're Sorry but this Project hasn't yet uploaded their personal webpage yet. <br /> Please check back soon for updates or visit <a href=\"%s\">the project page</a>."),
			    util_make_url('/projects/' . $project->getUnixName())),
			    $contents)))));
			fclose($fw);
		}

		if (forge_get_config('use_manual_uploads')) {
			@mkdir($ghome . '/incoming');
		}

		//system('chmod -R ug=rwX,o=rX ' . $ghome);
		system('chown -R ' . forge_get_config('apache_user') . ':' .
		    forge_get_config('apache_group') . ' ' . $ghome);
		// find $ghome -type d -print0 | xargs -0 chmod g+s
		//XXX disabled because, why is this owned by apache_group?
	}
}

cron_entry(25,$err);
