#! /usr/bin/php
<?php
/**
 * Fusionforge Plugin SVNTracker HTTPPoster
 *
 * Portions Copyright 2004 (c) Roland Mas <99.roland.mas @nospam@ aist.enst.fr>
 * The rest Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2013, Franck Villaume - TrivialDev
 * Copyright 2014, Benoit Debaenst - TrivialDev
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
 *
 *  This is the script called by svn. It takes some params, and prepare some
 *  HTTP POSTs to scmhook/www/newcommitgit.php.
 *
 */

require_once dirname(__FILE__).'/../../../../../../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfplugins.'scmhook/library/scmgit/hooks/committracker/Snoopy.class.php';

/**
 * usage - It returns the usage and exit program
 *
 * @param   string   $prog
 *
 */
function usage( $prog ) {
	echo "Usage: $prog <oldrev> <newrev> <refname> <repo_path> \n";
	echo "You must control parameters! \n";
	exit(1);
}

/**
 * getInvolvedArtifacts - It returns a list of involved artifacts.
 * An artifact is identified if [#(NUMBER)] if found.
 *
 * @param	string	$Log	Log message to be parsed.
 *
 * @return	string	$Result	Returns artifact.
 */
function getInvolvedArtifacts($Log)
{
	preg_match_all('/[[]#[\d]+[]]/', $Log,  $Matches );
	foreach($Matches as $Match)
	{
		$Result = preg_replace ('/[[]#([\d]+)[]]/', '\1', $Match);
	}
	return $Result;
}

/**
 * getInvolvedTasks - It returns a list of involved tasks.
 * A task is identified if [T(NUMBER)] is found.
 *
 * @param	string	$Log	Log message to be parsed.
 *
 * @return	string	$Result	Returns task.
 */
function getInvolvedTasks($Log)
{
	preg_match_all ('/[[]T[\d]+[]]/', $Log,  $Matches );
	foreach($Matches as $Match)
	{
		$Result = preg_replace ('/[[]T([\d]+)[]]/', '\1', $Match);
	}
	return $Result;
}

/**
 * getLog - Parse input and get the Log message.
 *
 * @param	string	$Input	Input from stdin.
 *
 * @return	array	Array of lines of Log Message.
 */
function getLog($Input)
{
	$Lines = explode("\n", $Input);
	$ii = count($Lines);
	$Logging=false;
	for ( $i=0; $i < $ii ; $i++ )
	{
		if ($Logging==true)
			$Log.=$Lines[$i]."\n";
		if ($Lines[$i]=='Log Message:')
			$Logging=true;
	}
	return trim($Log);
}

$files = array();

if (count($argv) != 5) {
	usage("post.php");
}

$oldrev    = $argv[1];
$newrev    = $argv[2];
$refname   = $argv[3];
$repo_path = substr_replace($argv[4],'',-6);

$git_tracker_debug = 0;

chdir($repo_path);

$UserName = trim(`git log -n 1 --format=%an $newrev`);
$email    = trim(`git log -n 1 --format=%ae $newrev`);
$date     = trim(`git log -n 1 --format=%ai $newrev`);
$log      = trim(`git log -n 1 --format=%s $newrev`);
$changed  = trim(`git log -n 1 --format=%b --name-only -p $newrev`);

if (isset($git_tracker_debug) && $git_tracker_debug == 1) {
	$file=fopen("/tmp/debug.post","a+");
	fwrite($file,"Vars filled:\n");
	fwrite($file,"arg :  " . print_r($argv,true) . " \n");
	fwrite($file,"rev :  " . $newrev . " \n");
	fwrite($file,"username :  " . $UserName . " \n");
	fwrite($file,"email :  " . $email . " \n");
	fwrite($file,"date :  " . $date . " \n");
	fwrite($file,"log  :  " . $log . " \n");
	fwrite($file,"changed :  " . $changed . " \n");
	fclose($file);
}

$changed = explode("\n", $changed);
foreach ($changed as $onefile) {
	//we must see when it was last changed, and that's previous revision
	$exit=0;
	$actrev = $newrev - 1;
	if ($newrev==0) {
		$exit = 1;
		$prev = 1;
	}
	while ( (!$exit) && ($actrev != 0 ) ) {
		$changed2 = trim(`git log -n 1 --format=%b --name-only -p $newrev`);
		$changed2 = explode("\n", $changed2);
		if ( in_array($onefile,$changed2) ) {
			$prev = $actrev;
			$exit = 1;
		}
		$actrev = $actrev - 1 ;
	}
	if ($actrev == 0) {
		$prev = 1;
	}

	$files[] = array(
			'name' => $onefile,
			'previous' => $oldrev,
			'actual' => $newrev
		);
}


// Our POSTer in Fusionforge
$snoopy = new Snoopy;

$SubmitUrl = util_make_url('/plugins/scmhook/committracker/newcommitgit.php');

$tasks_involved = getInvolvedTasks($log);
$artifacts_involved = getInvolvedArtifacts($log);

if ((!is_array($tasks_involved) || count($tasks_involved) < 1) &&
	(!is_array($artifacts_involved) || count($artifacts_involved) < 1)) {
	//nothing to post
	die("No artifacts nor tasks in the commit log\n");
}

$i = 0;
foreach ( $files as $onefile )
{
	$SubmitVars[$i]["UserName"]        = $UserName;
	$SubmitVars[$i]["Email"]           = $email;
	$SubmitVars[$i]["Repository"]      = $repo_path;
	$SubmitVars[$i]["FileName"]        = $onefile['name'];
	$SubmitVars[$i]["PrevVersion"]     = $onefile['previous'];
	$SubmitVars[$i]["ActualVersion"]   = $onefile['actual'];
	$SubmitVars[$i]["Log"]             = $log;
	$SubmitVars[$i]["TaskNumbers"]     = getInvolvedTasks($log);
	$SubmitVars[$i]["ArtifactNumbers"] = getInvolvedArtifacts($log);
	$SubmitVars[$i]["GitDate"]         = time();
	$i++;
}

$vars['data'] = urlencode(serialize($SubmitVars));
$snoopy->submit($SubmitUrl, $vars);
// echo $snoopy->results;
