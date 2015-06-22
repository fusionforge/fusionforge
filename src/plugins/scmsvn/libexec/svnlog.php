<?php
/**
 * Returns commit log for inclusion in web frontend
 *
 * Copyright 2015  Inria (Sylvain Beucler)
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// Don't try to connect to the DB, just dumping SVN log
putenv('FUSIONFORGE_NO_DB=true');

require_once '../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

header('Content-type: text/plain');

# Authentify request
if (!preg_match(',^/anonscm/,', $_SERVER['REQUEST_URI'])) {
	$web_host = forge_get_config('web_host');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://' . $web_host . '/account/check_forwarded_session.php');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Forwarded-For: '.$_SERVER['HTTP_X_FORWARDED_FOR']));
	//$info = curl_getinfo($ch);
	$body = curl_exec($ch);
	curl_close($ch);
	if ($body != 'OK')  {
		die($body);
	}
}


$unix_group_name = $_GET['unix_group_name'];
$mode = $_GET['mode'];
if (!preg_match('/^(date_range|latest|latest_user)$/', $mode))
	die('Invalid mode');
if (!preg_match('/^[a-z0-9][-a-z0-9_\.]+\z/', $unix_group_name))
	die('Invalid group name');

if ($mode == 'date_range') {
	$start_time = $_GET['begin'];
	$end_time = $_GET['end'];
	if (!ctype_digit($start_time))
		die('Invalid start time');
	if (!ctype_digit($end_time))
		die('Invalid end time');
	$d1 = date('Y-m-d', $start_time - 80000);
	$d2 = date('Y-m-d', $end_time + 80000);
	$options = "-r '{".$d2."}:{".$d1."}'";
} else if ($mode == 'latest' or $mode == 'latest_user') {
	$limit = $_GET['limit'];
	if (!ctype_digit($limit))
		die('Invalid limit');
	$options = "--limit $limit";
	
	if ($mode == 'latest_user') {
		$user_name = $_GET['user_name'];
		if (!preg_match('/^[a-z0-9][-a-z0-9_\.]+\z/', $user_name))
			die('Invalid user name');
		$options .= " --search '$user_name'";
	}
}

$repo = forge_get_config('repos_path', 'scmsvn') . '/' . $unix_group_name;
if (is_dir($repo)) {
	passthru("svn log file://$repo --xml -v $options 2> /dev/null");
}
