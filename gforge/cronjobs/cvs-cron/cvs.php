#! /usr/bin/php4 -f
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require ('squal_pre.php');
require ('common/include/cron_utils.php');

$use_cvs_acl = false;
$maincvsroot = "/cvsroot/";

//the directory exists
if(is_dir($maincvsroot)) {
	addProjectRepositories();
} else {
	if(is_file($maincvsroot)) {
		$err .= "$maincvsroot exists but is a file\n";
		exit;
	} else {
		if (mkdir($maincvsroot)) {
			//need to update group permissions using chmod
			addProjectRepositories();
		} else {
			$err .= "unable to make $maincvsroot directory\n";
			exit;
		}	
	}
}

function writeFile($filePath, $content) {
	$file = fopen($filePath, 'a');
	flock($file, LOCK_EX);
	ftruncate($file, 0);
	rewind($file);
	if(!empty($content)) {
		fwrite($file, $content);
	}
	flock($file, LOCK_UN);
	fclose($file);
}

/**
*addsyncmail
*Copyright GForge 2004
*addsyncmail write to /CVSROOT/loginfo unix_name-commits@lists.gforge.company.com
*
*@autor Luis A. Hurtado A. luis@gforgegroup.com
*@param $unix_group_name Name Group
*@return void
*@date 2004-10-25
*/
function addsyncmail($unix_group_name) {

	global $sys_lists_host;
	global $maincvsroot;
	$loginfo = $maincvsroot.$unix_group_name.'/CVSROOT/loginfo';

	if (checkLoginfo($loginfo)) {
		$pathsyncmail = "ALL ".dirname(__FILE__)."/syncmail -u %1{sVv} ".$unix_group_name."-commits@".$sys_lists_host."\n";
		if(is_file($loginfo)){
			writeFile($loginfo, $pathsyncmail);
		}
	}
}

function addCvsTrackerToFile($path, $unix_group_name) {
	global $sys_plugins_path, $sys_users_host;
	
	$FOut = fopen($path, "a");
	if($FOut) {
		fwrite($FOut, "# BEGIN added by gforge-plugin-cvstracker\n");
		$Line = "ALL ( php -q -d include_path=".ini_get('include_path').
			" ".$sys_plugins_path."/cvstracker/bin/post.php".
			" $unix_group_name %{sVv} )\n";
		fwrite($FOut,$Line);
		fwrite($FOut, "# END added by gforge-plugin-cvstracker\n");
		fclose($FOut);
	}
}
function isCvsTrackerSet($path) {
	$LineFound=FALSE;
	$FIn  = fopen($path,"r");
	
	if ($FIn) {
		while (!feof($FIn)) {
			$Line = fgets ($FIn);
			if(!preg_match("/^#/", $Line) &&
				preg_match("/cvstracker/",$Line)) {
				$LineFound = TRUE;
			}
		}
	}
	fclose($FIn);
	return $LineFound;
}
function addProjectRepositories() {
	global $maincvsroot;
	global $use_cvs_acl;

	$res = db_query("select groups.group_id,groups.unix_group_name,groups.enable_anonscm,groups.enable_pserver 
	FROM groups, plugins, group_plugin
    WHERE groups.status != 'P'
    AND groups.group_id=group_plugin.group_id
    AND group_plugin.plugin_id=plugins.plugin_id
    AND plugins.plugin_name='scmcvs'");
	
	for($i = 0; $i < db_numrows($res); $i++) {
		/*
			Simply call cvscreate.sh
		*/
		$project = &group_get_object(db_result($res,$i,'group_id')); // get the group object for the current group
		
		if ( (!$project) || (!is_object($project))  )  {
			echo "Error Getting Group." . " Id : " . db_result($res,$i,'group_id') . " , Name : " . db_result($res,$i,'unix_group_name');
			break; // continue to the next project
		}
		
		$repositoryPath = $maincvsroot."/".$project->getUnixName();
		if (is_dir($repositoryPath)) {
			$writersContent = '';
			$readersContent = '';
			$passwdContent = '';
			if($project->enableAnonSCM()) {
				$repositoryMode = 02775;
				if ($project->enablePserver()) {
					$readersContent = 'anonymous';
					$passwdContent = 'anonymous:8Z8wlZezt48mY';
				}
			} else {
				$repositoryMode = 02770;
			}
			chmod($repositoryPath, $repositoryMode);
			writeFile($repositoryPath.'/CVSROOT/writers', $writersContent);
			writeFile($repositoryPath.'/CVSROOT/readers', $readersContent);
			writeFile($repositoryPath.'/CVSROOT/passwd', $passwdContent);
			addsyncmail($project->getUnixName());
			if (!isCvsTrackerSet($repositoryPath.'/CVSROOT/loginfo')) {
				if ($project->usesPlugin("cvstracker")) {
					addCvsTrackerToFile($repositoryPath.'/CVSROOT/loginfo', $project->getUnixName());
				}
			}
		} elseif (is_file($repositoryPath)) {
			$err .= $repositoryPath.' already exists as a file';
		} else {
			system('./cvscreate.sh '.
				$project->getUnixName().
				' '.($project->getID()+50000).
				' '.$project->enableAnonSCM().
				' '.$project->enablePserver());
			addsyncmail($project->getUnixName());
			if (!isCvsTrackerSet($repositoryPath.'/CVSROOT/loginfo')) {
				if ($project->usesPlugin("cvstracker")) {
					addCvsTrackerToFile($repositoryPath.'/CVSROOT/loginfo', $project->getUnixName());
				}
			}
 			if ($use_cvs_acl == true) {
 				system ("cp ".dirname($_SERVER['_'])."/aclconfig.default ".$repositoryPath.'/CVSROOT/aclconfig');
 				$res_admins = db_query("SELECT users.user_name FROM users,user_group WHERE users.user_id=user_group.user_id AND user_group.group_id='".$project->getID()."'");
 				$useradmin_group = db_result($res_admins,0,'user_name');
 				system("cvs -d ".$repositoryPath." racl ".$useradmin_group.":p -r ALL -d ALL");
 			}
		}
	}
}

// return's true if it's ok to write the file
function checkLoginfo($file_name) {
	if (!file_exists($file_name)) {
		// files does't exist, it's ok to write it
		return true;
	} else { // check if file is empty or commented out
		$file = @fopen($file_name, 'r');
		if (!$file) { // couldn't open file
			return false;
		}

		while (!feof($file)) {
			$content = trim(fgets($file, 4096));
			if (strlen($content) > 1) {
				if ($content{0} != '#') { // it's not a comment
					fclose($file);
					return false;
				}
			}
		}
		fclose($file);
		return true;
	}
}

cron_entry(13,$err);

?>
