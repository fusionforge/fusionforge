#! /usr/bin/php
<?php
/**
 * GForge Plugin CVSTracker HTTPPoster
 *
 * Portions Copyright 2004 (c) Roland Mas <99.roland.mas @nospam@ aist.enst.fr>
 * The rest Copyright 2004 (c) Francisco Gimeno <kikov @nospam@ kikov.org>
 *
 * This file is part of GForge-plugin-cvstracker
 *
 * GForge-plugin-cvstracker is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * GForge-plugin-cvstracker is distributed in the hope that it will
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
 *  This is the script called by cvs. It takes some params, and prepare some
 *  HTTP POSTs to /plugins/cvstracker/newcommit.php.
 *
 */

require dirname(__FILE__).'/../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once ($gfcommon.'common/include/utils.php');
require_once ($gfplugins.'cvstracker/common/Snoopy.class.php');

$cvs_binary_version = get_cvs_binary_version () ;

if ($cvs_binary_version != "1.12" &&
	$cvs_binary_version != "1.11" )
	$cvs_binary_version = "1.12";
/**
 * It returns the usage and exit program
 *
 * @param   string   $argv
 *
 */
function usage( $argv ) {
	global $cvs_binary_version;
	if ($cvs_binary_version == "1.12" ) {
		echo "Usage: $argv[0] <Repository> <Path> [<File> <VersionFrom> <VersionTo>]xN\n";
	}
	if ($cvs_binary_version == "1.11" ) {
		echo "Usage: $argv[0] <Repository> [<File>,<VersionFrom>,<VersionTo>xN]\n";
	}
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
	$Log = "";
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

if( $cvs_binary_version == "1.11" ) {
	if ($argc <= 3 ) {
		usage ( $argv );
	}

	$repository      = $argv[1];
	$parameters = explode(' ', $argv[2]);
	$path = $parameters[0];

	for($i = 1; $i < count($parameters); $i++) {
		$filesInformation = explode(',', trim($parameters[$i], ','));

		$files[] = array(
			'name' => $path."/".$filesInformation[0],
			'previous' => $filesInformation[1],
			'actual' => $filesInformation[2]
		);
	}

}
if ( $cvs_binary_version == "1.12" ) {
	if ($argc < 6 ) {
		usage ( $argv );
	}

	if ( (($argc - 3) % 3 ) != 0 ) {
		echo "There should be 3 params + 3*N, instead of $argc\n";
		usage ( $argv );
	}
	$NumFiles= (($argc-3) / 3 ); // 3 Fixed params + 3 * File
	$repository      = $argv[1];
	$path            = $argv[2];

	for ( $i=0; $i < $NumFiles; $i++ ) {
		$files[] = array(
			'name' => $path."/".$argv[3 + 3*$i],
			'previous' => $argv[4 + 3*$i],
			'actual' => $argv[5 + 3*$i]
		);
	}
}

// Our POSTer in Gforge
$snoopy = new Snoopy;

if ($use_ssl) {
	$http = "https://";
} else {
	$http = "http://";
}

$SubmitUrl = util_make_url('/plugins/cvstracker/newcommit.php');

$UserArray=posix_getpwuid ( posix_geteuid ( ) );
$UserName= $UserArray['name'];

$Input = file_get_contents ("/dev/stdin" );
$Log   = getLog($Input);

$tasks_involved= getInvolvedTasks($Log);
$artifacts_involved= getInvolvedArtifacts($Log);
if ((!is_array($tasks_involved) || count($tasks_involved) < 1) &&
	(!is_array($artifacts_involved) || count($artifacts_involved) < 1)) {
	//nothing to post
	die("No artifacts nor tasks in the commit log\n");
}

$i = 0;
foreach ( $files as $file )
{
	$SubmitVars[$i]["UserName"]        = $UserName;
	$SubmitVars[$i]["Repository"]      = $repository;
	$SubmitVars[$i]["FileName"]        = $file['name'];
	$SubmitVars[$i]["PrevVersion"]     = $file['previous'];
	$SubmitVars[$i]["ActualVersion"]   = $file['actual'];
	$SubmitVars[$i]["Log"]             = $Log;
	$SubmitVars[$i]["TaskNumbers"]     = getInvolvedTasks($Log);
	$SubmitVars[$i]["ArtifactNumbers"] = getInvolvedArtifacts($Log);
	$SubmitVars[$i]["CvsDate"]         = time();
	$i++;
}

$vars['data'] = serialize($SubmitVars);
$snoopy->submit($SubmitUrl,$vars);

?>
