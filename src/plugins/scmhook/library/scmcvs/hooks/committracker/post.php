#! /usr/bin/php
<?php
/**
 * Copyright (C) 2014 Philipp Keidel - EDAG Engineering AG
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

 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
/**
 *  This is the script called by cvs. It takes some params, and prepare some
 *  HTTP POSTs to scmhook/www/newcommitcvs.php.
 */

require_once dirname(__FILE__).'/../../../../../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';

/**
 * getInvolvedArtifacts - It returns a list of involved artifacts.
 * An artifact is identified if [#(NUMBER)] if found.
 *
 * @param	string	$Log	Log message to be parsed.
 *
 * @return	string	$Result	Returns artifact.
 */
function getInvolvedArtifacts($Log) {
	preg_match_all('/[\[]#[\d]+[\]]/', $Log,  $Matches );
	foreach($Matches as $Match) {
		$Result = preg_replace ('/[[]#([\d]+)[]]/', '\1', $Match);
	}
	return $Result;
}

/**
 * getInvolvedTasks - It returns a list of involved tasks.
 * A task is identified if [T(NUMBER)] if found.
 *
 * @param	string	$Log	Log message to be parsed.
 *
 * @return	string	$Result	Returns artifact.
 */
function getInvolvedTasks($Log) {
	preg_match_all('/[\[]T[\d]+[\]]/', $Log,  $Matches );
	foreach($Matches as $Match) {
		$Result = preg_replace ('/[[]T([\d]+)[]]/', '\1', $Match);
	}
	return $Result;
}

$files = array();

/**
 *   $argv: Array
 *   (
 *       [0] => Full local path to post.php
 *       [1] => /tmp/cvswrapper_IWRtXb
 *       [2] => repo name
 *       [3] => dir file1,1.58,1.59 file2,1.16,1.17
 *       [4] => username
 *   )
 *
 *   $stdin: Update of /var/lib/gforge/chroot/scmrepos/cvs/progress/ADMIN
 *           In directory gforge:/var/tmp/cvs-serv16298
 *
 *           Modified Files:
 *           	admin_env
 *           Log Message:
 *           test60
 */

if (count($argv) != 5 || !file_exists($argv[1])) {
	echo "Usage: post.php <tmpfile> <project> <params: sVv> <user> \n";
	echo "You must control parameters! \n";
	exit(1);
}

$tmpname     = $argv[1];

$stdin       = file_get_contents($tmpname);
$projectname = $argv[2];
$username    = $argv[4];
$misc        = explode(" ", $argv[3], 2); // db\/usersess\/appserver admin_env,1.58,1.59 codecheck,1.16,1.17
$dirpath     = $misc[0];

$files       = explode(" ", $misc[1]);
$allfiles    = array();

unlink($tmpname);

echo "dirpath: $dirpath\n";

foreach($files as $file) {
	$i = explode(",", $file);
	$allfiles[] = array(
			'filename' => $i[0],
			'oldrev'   => $i[1],
			'newrev'   => $i[2]
			);
}

// Log Message aus der stdin String auslesen
$logmessage = trim(substr(stristr($stdin, 'Log Message:'), 12));
$tasks_involved = getInvolvedTasks($logmessage);
$artifacts_involved = getInvolvedArtifacts($logmessage);

if ((!is_array($tasks_involved) || count($tasks_involved) < 1) &&
    (!is_array($artifacts_involved) || count($artifacts_involved) < 1)) {
	//nothing to post
	die("No artifacts nor tasks in the commit log\n");
}

$SubmitUrl = util_make_url('/plugins/scmhook/committracker/newcommitcvs.php');

$SubmitVars = array();
$i = 0;
foreach ( $allfiles as $onefile ) {
	$SubmitVars[$i]["UserName"]        = $username;
	$SubmitVars[$i]["Repository"]      = $projectname;
	$SubmitVars[$i]["Directory"]       = $dirpath;
	$SubmitVars[$i]["FileName"]        = $onefile['filename'];
	$SubmitVars[$i]["PrevVersion"]     = $onefile['oldrev'];
	$SubmitVars[$i]["ActualVersion"]   = $onefile['newrev'];
	$SubmitVars[$i]["Log"]             = $logmessage;
	$SubmitVars[$i]["TaskNumbers"]     = $tasks_involved;
	$SubmitVars[$i]["ArtifactNumbers"] = $artifacts_involved;
	$SubmitVars[$i]["CVSDate"]         = time();
	$i++;
}

$vars['data'] = urlencode(serialize($SubmitVars));

// Since Snoopys last modifications were made back in 2008, we don't want to use it here.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $SubmitUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
// $info = curl_getinfo($ch);
curl_close($ch);
