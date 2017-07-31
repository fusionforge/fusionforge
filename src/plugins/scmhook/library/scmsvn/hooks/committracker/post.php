#! /usr/bin/php
<?php
/**
 * Fusionforge Plugin Scmhook scmsvn committracker HTTPPoster
 *
 * Portions Copyright 2004 (c) Roland Mas <99.roland.mas @nospam@ aist.enst.fr>
 * The rest Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2013,2015-2017, Franck Villaume - TrivialDev
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
/**
 *
 *  This is the script called by svn. It takes some params, and prepare some
 *  HTTP POSTs to committracker/newcommit.php
 *
 */

require_once dirname(__FILE__).'/../../../../../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';

/**
 * It returns a list of involved artifacts.
 * An artifact is identified if [#(NUMBER)] if found.
 *
 * @param   string   $Log Log message to be parsed.
 *
 * @return  boot    Returns true if check passed.
 */
function getInvolvedArtifacts($Log)
{
	preg_match_all('/[[]#[\d]+[]]/', $Log,  $Matches );
	foreach($Matches as $Match) {
		$Result = preg_replace ('/[[]#([\d]+)[]]/', '\1', $Match);
	}
	return $Result;
}

/**
 * It returns a list of involved Tasks.
 * A task is identified if [T(NUMBER)] is found.
 *
 * @param   string   $Log Log message to be parsed.
 *
 * @return  boot    Returns true if check passed.
 */
function getInvolvedTasks($Log)
{
	preg_match_all ('/[[]T[\d]+[]]/', $Log,  $Matches );
	foreach($Matches as $Match) {
		$Result = preg_replace ('/[[]T([\d]+)[]]/', '\1', $Match);
	}
	return $Result;
}

$files = array();

if (count($argv) != 3) {
	echo "Usage: $argv[0] <Repository> <Revision> \n";
	echo 'This program should be automatically called by SVN';
	exit(0);
}

$repository = $argv[1];
$revision   = $argv[2];
$svn_tracker_debug = 0;

$UserName = trim(`svnlook author -r $revision $repository`); //username of author
$date    = trim(`svnlook date -r $revision $repository`); //date
$log     = trim(`svnlook log -r $revision $repository`); // the log
$changed = trim(`svnlook changed -r $revision $repository | sed 's/[A-Z]*   //'`); // the filenames

if (isset($svn_tracker_debug) && $svn_tracker_debug == 1) {
	$svn_tracker_debug_file = sys_get_temp_dir().'/scmhook_svn_committracker.debug';
	$file = fopen($svn_tracker_debug_file, 'a+');
	fwrite($file,"Vars filled:\n");
	fwrite($file,"username: " . $UserName . "\n");
	fwrite($file,"date: " . $date . "\n");
	fwrite($file,"log: " . $log . "\n");
	fwrite($file,"changed: " . $changed . "\n");
	fclose($file);
}

$changed = explode("\n", $changed);

// First check if anything must be done before diving deeper into the svn history
$tasks_involved = getInvolvedTasks($log);
$artifacts_involved = getInvolvedArtifacts($log);
if ((!is_array($tasks_involved) || count($tasks_involved) < 1) &&
	(!is_array($artifacts_involved) || count($artifacts_involved) < 1)) {
	// No artifacts nor tasks in the commit log
	exit(0);
}

foreach ($changed as $onefile) {

	// Get revision history for each file into an array and search for
	// current and previous revision in memory to eliminate looping
	// all revisions for each file
	$prev = 1;
	if ($revision!=0) {
		// use tail to strip off header and sed to get only the revision numbers
		$allrevs = trim(`svnlook history $repository $onefile | tail -n +3 | sed 's/ *\\([0-9]*\\).*/\\1/'`);
		$allrevs = explode("\n", $allrevs);
		if ( in_array($revision,$allrevs) ) {
			// get index in array of current rev and increment by one for prev rev
			$found = array_search($revision, $allrevs, true) + 1;
			if ($found < count($allrevs)) {
				$prev = $allrevs[$found];
			}
		}
	}

	$files[] = array(
			'name' => $repository . '/' . $onefile,
			'previous' => $prev,
			'actual' => $revision
		);
}


// Our POSTer in Fusionforge
$SubmitUrl = util_make_url('/plugins/scmhook/committracker/newcommit.php');

$i = 0;
foreach ($files as $onefile) {
	$SubmitVars[$i]['UserName']        = $UserName;
	$SubmitVars[$i]['Repository']      = $repository;
	$SubmitVars[$i]['FileName']        = $onefile['name'];
	$SubmitVars[$i]['PrevVersion']     = $onefile['previous'];
	$SubmitVars[$i]['ActualVersion']   = $onefile['actual'];
	$SubmitVars[$i]['Log']             = $log;
	$SubmitVars[$i]['TaskNumbers']     = $tasks_involved;
	$SubmitVars[$i]['ArtifactNumbers'] = $artifacts_involved;
	$SubmitVars[$i]['SvnDate']         = time();
	$i++;
}

$vars['data'] = urlencode(serialize($SubmitVars));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $SubmitUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
//$info = curl_getinfo($ch);
curl_close($ch);
