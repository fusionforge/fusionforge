#! /usr/bin/php4 -f
<?php
/**
 * Implement CVS ACLs based on GForge roles
 *
 * Copyright 2004 GForge, LLC
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

if (((int) $_SERVER['argc']) < 1) {
	print "Usage: ".basename(__FILE__)." /cvsroot/projectname\n";
	exit(1);
}

require_once('plugins/scmcvs/config.php');
require ($sys_plugins_path.'/scmcvs/common/Snoopy.class');

// Input cleansing
$env_cvsroot = (string) $_ENV['CVSROOT'];

# Rules
# 1. Must begin with /cvs/ or /cvsroot/
# 2. Then must contain 3 - 15 alphanumeric chars or -
preg_match("/^\/(cvs)(root)*\/([[:alnum:]-]{3,15})$/", $env_cvsroot, $matches);

if (count($matches) == 0) {
	print "Invalid CVS directory\n";
	exit(1);
}

$projectName = $matches[count($matches)-1];

$userArray=posix_getpwuid ( posix_geteuid ( ) );
$userName= $userArray['name'];

// Our POSTer in Gforge
$snoopy = new Snoopy;

$SubmitUrl='http'.(($use_ssl): 's' ? '' ).'://'.$sys_default_domain.'/plugins/scmcvs/acl.php';
$SubmitVars['group'] = $projectName;
$SubmitVars['user'] = $userName;

if ($userName == 'root') {
	exit(0);
} else {

	$snoopy->submit($SubmitUrl,$SubmitVars);
	if (!empty($snoopy->results)) {
		print $snoopy->results."\n";
		exit(1);
	}

}

?>
