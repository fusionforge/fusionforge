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

require dirname(__FILE__).'/../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';

if (!forge_get_config('use_project_vhost')) {
	cron_entry('WEB_VHOSTS', _('forge not using vhost'));
}
$output = '';

session_set_admin();

$res = db_query_params('SELECT vh.vhost_name, vh.docdir, vh.cgidir, g.unix_group_name
			FROM prweb_vhost vh, groups g
			WHERE g.status = $1 AND vh.group_id = g.group_id
			ORDER BY vh.vhost_name',
			array ('A'));
if (!$res) {
	cron_entry('WEB_VHOSTS', _('Unable to get list of projects with vhost')._(': ').db_error());
}

$inTemplateVhostFile = forge_get_config('custom_path').'/httpd.vhosts.tmpl';
if (!is_readable($inTemplateVhostFile)) {
	$inTemplateVhostFile = forge_get_config('source_path').'/templates/httpd.vhosts.tmpl';
}
$outVhostsFile = forge_get_config('config_path').'/httpd.conf.d/httpd.vhosts';
$logPath = forge_get_config('log_path');
$groupdirPrefix = forge_get_config('groupdir_prefix');

file_put_contents($outVhostsFile, '');
$count = 0;
while ($arr = db_fetch_array($res)) {
	$str = file_get_contents($inTemplateVhostFile);
	$str = str_replace('{vhost_name}', $arr['vhost_name'], $str);
	$str = str_replace('{unix_group_name}', $arr['unix_group_name'], $str);
	$str = str_replace('{docdir}', $arr['docdir'], $str);
	$str = str_replace('{cgidir}', $arr['cgidir'], $str);
	file_put_contents($outVhostsFile, $str, FILE_APPEND);
	$count++;
}

cron_reload_apache();

$output .= $count._(' vhost created.');
cron_entry('WEB_VHOSTS', $output);
