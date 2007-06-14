<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$


unset ($BROWSER_AGENT);
unset ($BROWSER_VER);
unset ($BROWSER_PLATFORM);

function browser_get_agent () {
	global $BROWSER_AGENT;
	return $BROWSER_AGENT;
}

function browser_get_version() {
	global $BROWSER_VER;
	return $BROWSER_VER;
}

function browser_get_platform() {
	global $BROWSER_PLATFORM;
	return $BROWSER_PLATFORM;
}

function browser_is_mac() {
	if (browser_get_platform()=='Mac') {
		return true;
	} else {
		return false;
	}
}

function browser_is_windows() {
	if (browser_get_platform()=='Win') {
		return true;
	} else {
		return false;
	}
}

function browser_is_ie() {
	if (browser_get_agent()=='IE') {
		return true;
	} else {
		return false;
	}
}

function browser_is_netscape() {
	if (browser_get_agent()=='MOZILLA') {
		return true;
	} else {
		return false;
	}
}


/*
	Determine browser and version
*/


$user_agent = getStringFromServer('HTTP_USER_AGENT');

if (ereg( 'MSIE ([0-9].[0-9]{1,2})',$user_agent,$log_version)) {
	$BROWSER_VER=$log_version[1];
	$BROWSER_AGENT='IE';
} elseif (ereg( 'Opera ([0-9].[0-9]{1,2})',$user_agent,$log_version)) {
	$BROWSER_VER=$log_version[1];
	$BROWSER_AGENT='OPERA';
} elseif (ereg( 'Mozilla/([0-9].[0-9]{1,2})',$user_agent,$log_version)) {
	$BROWSER_VER=$log_version[1];
	$BROWSER_AGENT='MOZILLA';
} else {
	$BROWSER_VER=0;
	$BROWSER_AGENT='OTHER';
}

/*
	Determine platform
*/

if (strstr($user_agent,'Win')) {
	$BROWSER_PLATFORM='Win';
} else if (strstr($user_agent,'Mac')) {
	$BROWSER_PLATFORM='Mac';
} else if (strstr($user_agent,'Linux')) {
	$BROWSER_PLATFORM='Linux';
} else if (strstr($user_agent,'Unix')) {
	$BROWSER_PLATFORM='Unix';
} else {
	$BROWSER_PLATFORM='Other';
}

/*
echo "<br>Agent: $user_agent";
echo "<br>\nIE: ".browser_is_ie();
echo "<br>\nMac: ".browser_is_mac();
echo "<br>\nWindows: ".browser_is_windows();
echo "<br>\nPlatform: ".browser_get_platform();
echo "<br>\nVersion: ".browser_get_version();
echo "<br>\nAgent: ".browser_get_agent();
*/

?>
