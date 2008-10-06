#! /usr/bin/php -f
<?php
/**
 * Copyright 2005 (c) Daniel A. Pérez
 *
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
 * You should have received a copy of the GNU General Public License
 * along with GForge-plugin-svntracker; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
/**
 *
 *       This script maintains post-commit hook files for groups
 *
 */
require ('squal_pre.php');
require ('common/include/cron_utils.php');
require ('plugins/svntracker/config.php');

$Res = db_query("SELECT * FROM groups WHERE status='A';");
if (!$Res) {
        echo "Error. Couldn't get Group List!\n";
}

function addSvnTrackerToFile(& $Group, $path) {
	global $sys_plugins_path, $sys_users_host;
	
	$FOut = fopen($path, "a+");
	if($FOut) {
		$Line = '#!/bin/sh' . "\n";
		fwrite($FOut,$Line);
		$Line = 'REPOS="$1"'  . "\n";
		fwrite($FOut,$Line);
		$Line = 'REV="$2"' . "\n";
		fwrite($FOut,$Line);
		$Line = "/usr/bin/php -d include_path=".ini_get('include_path').
				" ".$sys_plugins_path. "/svntracker/bin/post.php".  ' "$REPOS" "$REV"' . "\n";
		fwrite($FOut,$Line);
		`chmod +x $path `;
		fclose($FOut);
	}
}

while ($Row = db_fetch_array($Res)) {
	$Group = group_get_object($Row["group_id"]);
	if ($Group->usesPlugin("svntracker")) {
		$LineFound=FALSE;
		$FIn  = fopen($sys_svnroot_path."/".$Row["unix_group_name"]."/hooks/post-commit","r");
		
		if ($FIn) {
			while (!feof($FIn))  {
				$Line = fgets ($FIn);
				if(!preg_match("/^#/", $Line) &&
					preg_match("/svntracker/",$Line)) {
					$LineFound = TRUE;
				}
			}
			fclose($FIn);
			if($LineFound==FALSE) {
				echo $Group->getUnixName().": post-commit modified\n";
				addSvnTrackerToFile($Group, $sys_svnroot_path."/".$Row["unix_group_name"]."/hooks/post-commit");
			}
		} else {
			//create the file
			echo $Group->getUnixName().": post-commit modified and created\n";
			addSvnTrackerToFile($Group, $sys_svnroot_path."/".$Row["unix_group_name"]."/hooks/post-commit");
		}
	}
}


?>
