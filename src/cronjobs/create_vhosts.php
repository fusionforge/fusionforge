#! /usr/bin/php -f
<?php
/**
 * FusionForge vhost administration
 *
 * Copyright 2014, Franck Villaume - TrivialDev
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

require (dirname(__FILE__).'/../common/include/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

if (!forge_get_config('use_project_vhost')) {
	cron_entry(30, _('forge not using vhost'));
}

session_set_admin();

$res = db_query_params('select vh.vhost_name, vh.docdir, vh.cgidir from prweb_vhost vh, groups g where g.status = $1 and vh.group_id = g.group_id order by vh.vhost_name',
			array ('A'));
if (!$res) {
	cron_entry(30, _('Unable to get list of projects with vhost: ').db_error());
}

$enableRestart = false;
$httpdRestartedMsg = _('httpd server not restarted');

$inTemplateVhostFile = forge_get_config('source_path').'/etc/templates/httpd.vhosts';
$outVhostsFile = forge_get_config('config_path').'/httpd.conf.d/httpd.vhosts';
$logPath = forge_get_config('log_path');
$groupdirPrefix = forge_get_config('groupdir_prefix');

file_put_contents($outVhostsFile, '');
$count = 0;
while ($arr = db_fetch_array($res)) {
	$str = file_get_contents($inTemplateVhostFile);
	$str = str_replace('{vhost_name}', $arr['vhost_name'], $str);
	$str = str_replace('{docdir}', $arr['docdir'], $str);
	$str = str_replace('{cgidir}', $arr['cgidir'], $str);
	$str = str_replace('{core/log_path}', $logPath, $str);
	$str = str_replace('{core/groupdir_prefix}', $groupdirPrefix, $str);
	file_put_contents($outVhostsFile, $str, FILE_APPEND);
	$count++;
}

if ($enableRestart) {
	// debian specific
	//$httpd_restart_cmd = '/usr/sbin/invoke-rc.d --quiet apache2 reload';
	// redhat based
	//$httpd_restart_cmd = 'service httpd restart';
	if (isset($httpd_restart_cmd) && !empty($httpd_restart_cmd)) {
		system($httpd_restart_cmd);
		$httpdRestartedMsg = _('httpd server automatically restarted');
	}
}

$output .= $count._(' vhost created.').' '.$httpdRestartedMsg;
cron_entry(30, $output);
