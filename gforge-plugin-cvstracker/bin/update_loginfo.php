#! /usr/bin/php4 -f
<?php
/**
 * Copyright 2004 (c) Francisco Gimeno
 *
 * @version   $Id$
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
 * You should have received a copy of the GNU General Public License
 * along with GForge-plugin-cvstracker; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
/**
 *
 *       This script maintain CVSROOT/loginfo files for groups
 *
 */
require ('squal_pre.php');
require ('common/include/cron_utils.php');
require ('plugins/cvstracker/config.php');

$Res = db_query("SELECT * FROM groups WHERE status='A';");
if (!$Res) {
        echo "Error. Couldn't get Group List!\n";
}

function addCvsTrackerToFile($path) {
	global $sys_plugins_path, $sys_users_host;
	
	$FOut = fopen($path, "a");
	if($FOut) {
		fwrite($FOut, "# BEGIN added by gforge-plugin-cvstracker\n");
		$Line = "ALL ( php -q -d include_path=".ini_get('include_path').
			" ".$sys_plugins_path."/cvstracker/bin/post.php".
			" %{sVv} )\n";
		fwrite($FOut,$Line);
		fwrite($FOut, "# END added by gforge-plugin-cvstracker\n");
		fclose($FOut);
	}
}

while ($Row = db_fetch_array($Res)) {
	$Group = group_get_object($Row["group_id"]);
	if ($Group->usesPlugin("cvstracker")) {
		$LineFound=FALSE;
		$FIn  = fopen($sys_cvsroot_path."/".$Row["unix_group_name"]."/CVSROOT/loginfo","r");
		
		if ($FIn) {
			while (!feof($FIn))  {
				$Line = fgets ($FIn);
				if(!preg_match("/^#/", $Line) &&
					preg_match("/cvstracker/",$Line)) {
					$LineFound = TRUE;
				}
			}
			fclose($FIn);
			if($LineFound==FALSE) {
				echo $Group->getUnixName().": loginfo modified\n";
				addCvsTrackerToFile($sys_cvsroot_path."/".$Row["unix_group_name"]."/CVSROOT/loginfo");
			}
		}
	}
}


?>