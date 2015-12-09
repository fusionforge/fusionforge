#! /usr/bin/php
<?php
/**
 * Send reminder about not yet approved news items
 *
 * Copyright (C) 2004  Vicente J. Ruiz Jurado (vjrj AT ourproject.org)
 * Copyright (C) 2015  Inria (Sylvain Beucler)
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require dirname(__FILE__).'/../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';

$shortopts = 'd';		// enable verbose mode
$longopts = array('debug');
$options = getopt($shortopts, $longopts);
if (count($options) != (count($argv)-1)) {	// PHP just strips invalid options
	print "Usage: {$argv[0]} [-d|--debug]\n";
	exit(1);
}
$debug = false;
if (isset($options['d']) or isset($options['debug'])) {
	print "verbose mode ON\n";
	$debug = true;
}

if ($debug) {
	print "Getting the news not approved.\n";
}

$old_date = time()-60*60*24*30;
$res = db_query_params("SELECT group_name,summary,details,g.group_id
	FROM news_bytes n, groups g
	WHERE is_approved = 0
	AND n.group_id=g.group_id
	AND n.post_date > $1
	AND g.status=$2
	ORDER BY post_date", array($old_date, 'A'));

$results_array = array();
while ($arr = db_fetch_array($res)) {
	array_push($results_array, $arr);
}

$thereisnews = false;
$emailformatted = '';
$ra = RoleAnonymous::getInstance();
foreach ($results_array as $newsnotapprob) {
	list($group_name, $summary, $details,$group_id) = $newsnotapprob;

	if ($ra->hasPermission('project_read', $group_id)) {
		$thereisnews = true;
		$title = "$group_name: $summary\n";
		$emailformatted .= wordwrap($title, 78);
		$emailformatted .= "----------------------------------------------------------------------\n";
		$t = explode("\n", wordwrap($details, 70));
		foreach ($t as $line)
			$emailformatted .= str_repeat(' ', 8) . $line . "\n";
		$emailformatted .= "\n\n";
	}
}

if ($thereisnews) {
	if ($debug) { print "Sending the news not approved.\n"; }
	$web_host = forge_get_config('web_host');
	$admin_email = forge_get_config('admin_email');
	$forge_name = forge_get_config('forge_name');
    $subject = "$forge_name pending news";
	$emailformatted .= "Please visit: http://$web_host/news/admin/";
	$emailformatted .= "\n\n";
	util_send_message($admin_email, $subject, $emailformatted, "noreply@$web_host");
    if ($debug) {
        print "Subject: $subject\n";
        print $emailformatted;
    }
} else {
	if ($debug) { print "No news to approved.\n"; }
}

if ($debug) { print "get_news_notapproved process finished ok\n"; }
exit(0);
