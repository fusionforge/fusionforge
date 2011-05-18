#! /usr/bin/php
<?php
/**
 * GForge Plugin SVNTracker HTTPPoster
 *
 * Portions Copyright 2004 (c) Roland Mas <99.roland.mas @nospam@ aist.enst.fr>
 * The rest Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 *
 * This file is part of GForge-plugin-svntracker
 *
 * GForge-plugin-svntracker is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-svntracker is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
/**
 *
 *  This is the script called by svn. It takes some params, and prepare some
 *  HTTP POSTs to /plugins/svntracker/newcommit.php.
 *
 */

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require $gfplugins.'svntracker/common/Snoopy.class.php';

/**
 * It returns the usage and exit program
 *
 * @param   string   $argv
 *
 */
function usage( $argv ) {
	echo "Usage: $argv[0] <Repository> <Revision> \n";
	exit(0);
}

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
	foreach($Matches as $Match)
	{
		$Result = preg_replace ('/[[]#([\d]+)[]]/', '\1', $Match);
	}
	return $Result;
}

/**
 * It returns a list of involved artifacts.
 * An artifact is identified if [T(NUMBER)] is found.
 *
 * @param   string   $Log Log message to be parsed.
 *
 * @return  boot    Returns true if check passed.
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
 * Parse input and get the Log message.
 *
 * @param   string   $Input Input from stdin.
 *
 * @return  array    Array of lines of Log Message.
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

if (count($argv) != 3) {
    echo <<<USAGE
Usage: $0 <repository> <revision>
       This program should be automatically called by SVN
USAGE;

    exit;
}

$repository = $argv[1];
$revision   = $argv[2];

$UserName = trim(`svnlook author -r $revision $repository`); //username of author
$date    = trim(`svnlook date -r $revision $repository`); //date
$log     = trim(`svnlook log -r $revision $repository`); // the log
$changed = trim(`svnlook changed -r $revision $repository | sed 's/[A-Z]*   //'`); // the filenames

if ($svn_tracker_debug) {
	fwrite($file,"Vars filled:\n");
	fwrite($file,"username :  " . $UserName . " \n");
	fwrite($file,"date :  " . $date . " \n");
	fwrite($file,"log  :  " . $log . " \n");
	fwrite($file,"changed :  " . $changed . " \n");
}

$changed = explode("\n", $changed);

foreach ($changed as $onefile) {
	//we must see when it was last changed, and that's previous revision
	$exit=0;
	$actrev = $revision - 1;
	if ($revision==0) {
		$exit = 1;
		$prev = 1;
	}
	while ( (!$exit) && ($actrev != 0 ) ) {
		$changed2 = trim(`svnlook changed -r $actrev $repository | sed 's/[A-Z]*   //'`);
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
			'name' => $repository . "/" . $onefile,
			'previous' => $prev,
			'actual' => $revision
		);
}


// Our POSTer in Gforge
$snoopy = new Snoopy;

$SubmitUrl = util_make_url('/plugins/svntracker/newcommit.php');

$tasks_involved= getInvolvedTasks($log);
$artifacts_involved= getInvolvedArtifacts($log);
if ((!is_array($tasks_involved) || count($tasks_involved) < 1) &&
	(!is_array($artifacts_involved) || count($artifacts_involved) < 1)) {
	//nothing to post
	die("No artifacts nor tasks in the commit log\n");
}

$i = 0;
foreach ( $files as $onefile )
{
	$SubmitVars[$i]["UserName"]        = $UserName;
	$SubmitVars[$i]["Repository"]      = $repository;
	$SubmitVars[$i]["FileName"]        = $onefile['name'];
	$SubmitVars[$i]["PrevVersion"]     = $onefile['previous'];
	$SubmitVars[$i]["ActualVersion"]   = $onefile['actual'];
	$SubmitVars[$i]["Log"]             = $log;
	$SubmitVars[$i]["TaskNumbers"]     = getInvolvedTasks($log);
	$SubmitVars[$i]["ArtifactNumbers"] = getInvolvedArtifacts($log);
	$SubmitVars[$i]["SvnDate"]         = time();
	$i++;
}

$vars['data'] = serialize($SubmitVars);
$snoopy->submit($SubmitUrl,$vars);
?>
