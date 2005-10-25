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

require_once('common/include/SCM.class') ;

setup_plugin_manager () ;

$use_cvs_acl = false;
$maincvsroot = "/cvsroot";

/**
* Retrieve a file into a temporary directory from a CVS server
*
* @param String $repos Repository Name
* @param String $file File Name
*
* return String the FileName in the working repository
*/
function get_CVS_file($repos,$file) {
        $actual_dir = getcwd();
        $tempdirname = tempnam("/tmp","cvstracker");
        if (!$tempdirname) 
                return false;
        if (!unlink($tempdirname))
                return false;

        // Create the temporary directory and returns its name.
        if (!mkdir($tempdirname))
                return false;

        if (!chdir($tempdirname))
                return false;
        system("cvs -d ".$repos." co ".$file);

        chdir($actual_dir);
        return $tempdirname."/".$file;
}

/**
* is_logininfo_line_found
*
* @param String $repos Repository
*
* Returns true if loginfo line has already been added
*/
function is_logininfo_line_found($repos,&$tempfile){
	$LineFound=FALSE;
	$tempfile=get_CVS_File($repos,"CVSROOT/loginfo");
	$FIn = fopen($tempfile,"r");
	if ($FIn) {
		while (!feof($FIn))  {
			$Line = fgets ($FIn);
			if(!preg_match("/^#/", $Line) && preg_match("/cvstracker/",$Line)) {
				$LineFound = TRUE;
			}
		}
	}
	fclose($FIn);
	return $LineFound;
}

/**
	* Function to add cvstracker lines to a loginfo file
	* The lines are taken from the global var loginfo_lines, 
	* loaded by the CVSTracker Plugin.
	* @param   string  $path The filename of loginfo
	*
	*/
function add_CVSTracker_to_file($path) {
	global $sys_plugins_path, $sys_users_host, $cvs_binary_version;
	$lines=array();
	$lines=$GLOBALS["loginfo_lines"];
	$FOut = fopen($path, "a");
	if($FOut) {
		fwrite($FOut, $lines[0]);
		fwrite($FOut, $lines[1]);
		fwrite($FOut, $lines[2]);			
		fclose($FOut);
	}
}


/**
* put_CVS_File commit a file to the repository
*
* @param String $repos Repository
* @param String $file to commit
* @param String $message to commit
*/
function put_CVS_File($repos,$file,$message="Automatic updated by cvstracker") {
	$actual_dir = getcwd();
	chdir(dirname($file));	
	system("cvs -d ".$repos." ci -m \"".$message."\" ".basename($file));
	// unlink (basename($file));
	chdir($actual_dir);
}

/**
 * release_CVS_File - Remove the file that was checked out from cvs
 * @see get_CVS_file
 */
function release_CVS_File($file) {
	// $file is something like /tmp/(tmp_dir)/path/to/file
	// we must delete /tmp/tmp_dir
	if (!preg_match("/^(\\/tmp\\/[^\\/]*)\\/.*/", $file, $result)) {		// Make sure the dir is under /tmp
		echo "Trying to release a directory not in /tmp. Skipping...";
		return;
	}
	$dir = $result[1];
	
	// this shouldn't happen... but add it as a security checke
	if (util_is_root_dir($dir)) {
		echo "Trying to delete root dir. Skipping...";
		return;
	}
	$dir = escapeshellarg($dir);
	system("rm -rf ".$dir);
}

//the directory exists
if(is_dir($maincvsroot)) {
	add_Project_Repositories();
} else {
	if(is_file($maincvsroot)) {
		$err .= "$maincvsroot exists but is a file\n";
		exit;
	} else {
		if (mkdir($maincvsroot)) {
			//need to update group permissions using chmod
			add_Project_Repositories();
		} else {
			$err .= "unable to make $maincvsroot directory\n";
			exit;
		}	
	}
}

function write_File($filePath, $content) {
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
*add_sync_mail
*Copyright GForge 2004
*add_sync_mail write to /CVSROOT/loginfo unix_name-commits@lists.gforge.company.com
*
*@autor Luis A. Hurtado A. luis@gforgegroup.com
*@param $unix_group_name Name Group
*@return void
*@date 2004-10-25
*/
function add_sync_mail($unix_group_name) {

	global $sys_lists_host;
	global $maincvsroot;
	$loginfo_file=get_CVS_file($maincvsroot."/".$unix_group_name,'CVSROOT/loginfo');
	if (!$loginfo_file) {
		echo "Couldn't get loginfo";
		return;
	}

	$pathsyncmail = "ALL ".
		dirname(__FILE__)."/syncmail -u %p %{sVv} ".
		$unix_group_name."-commits@".$sys_lists_host."\n";
	$content = file_get_contents ($loginfo_file);
	if ( strstr($content, "syncmail") == FALSE) {
		echo $unix_group_name.":Syncmail not found in loginfo.Adding\n";
		$content .= "\n#BEGIN Added by cvs.php script\n".
			$pathsyncmail. "\n#END Added by cvs.php script\n";
		if(is_file($loginfo_file)){
			echo $unix_group_name.":About to write the lines\n";
			write_File($loginfo_file, $content);
		}
		put_CVS_File($maincvsroot."/".$unix_group_name,$loginfo_file);
	} else {
		echo "Syncmail Found!\n";
	}
	release_CVS_File($loginfo_file);
}

function add_Project_Repositories() {
	global $maincvsroot;
	global $use_cvs_acl;

	$res = db_query("select groups.group_id,groups.unix_group_name,groups.enable_anonscm,groups.enable_pserver".
		" FROM groups, plugins, group_plugin".
		" WHERE groups.status != 'P' ".
		" AND groups.group_id=group_plugin.group_id ".
		" AND group_plugin.plugin_id=plugins.plugin_id ".
		" AND plugins.plugin_name='scmcvs'");
	
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
			write_File($repositoryPath.'/CVSROOT/writers', $writersContent);
			write_File($repositoryPath.'/CVSROOT/readers', $readersContent);
			write_File($repositoryPath.'/CVSROOT/passwd', $passwdContent);
			add_sync_mail($project->getUnixName());
			if ($project->usesPlugin("cvstracker")){
				$newfile="";
				if(!is_logininfo_line_found($repositoryPath,$newfile)){					
					plugin_hook("get_cvs_loginfo_lines",$hookParams);						
					add_CVSTracker_to_file($newfile);
					put_CVS_File($repositoryPath,$newfile);					
				}
				release_CVS_File($newfile);
			}
		} elseif (is_file($repositoryPath)) {
			$err .= $repositoryPath.' already exists as a file';
		} else {
			$enableAnonSCM = ($project->enableAnonSCM()) ? 1 : 0;
			$enablePserver = ($project->enablePserver()) ? 1 : 0;
			system(dirname(__FILE__).'/cvscreate.sh '.
				$project->getUnixName().
				' '.($project->getID()+50000).
				' '.$enableAnonSCM.
				' '.$enablePserver);
			add_sync_mail($project->getUnixName());
			if ($project->usesPlugin("cvstracker")){
				$newfile="";
				if(!is_logininfo_line_found($repositoryPath,$newfile)){
					plugin_hook("get_cvs_loginfo_lines",$hookParams);				
					add_CVSTracker_to_file($newfile);
					put_CVS_File($repositoryPath,$newfile);					
				}
				release_CVS_File($newfile);
			}			
			if ($use_cvs_acl == true) {
				system ("cp ".dirname($_SERVER['_']).
					"/aclconfig.default ".$repositoryPath.'/CVSROOT/aclconfig');
				$res_admins = db_query("SELECT users.user_name FROM users,user_group ".
					"WHERE users.user_id=user_group.user_id AND ".
					"user_group.group_id='".$project->getID()."'");
				$useradmin_group = db_result($res_admins,0,'user_name');
				system("cvs -d ".$repositoryPath." racl ".$useradmin_group.":p -r ALL -d ALL");
			}
		}
	}
}

// return's true if it's ok to write the file
function check_Log_info($file_name) {
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
