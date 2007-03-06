<?php
/**
  *
  * svntracker plugin
  *
  * This script stand for receiving a HTTP Post from a hook in a
  *  svn script. Arguments are passed and new entries in DB are
  *  generated.
  *
  * Copyright Daniel A. Perez <danielperez.arg@gmail.com>
  *
  */
/**
 * This script takes some POST variables.It makes a check and it
 * store in DB the commit info attached to the tracker or task.
 *
 */

require_once('squal_pre.php');
require_once('plugins/svntracker/config.php');

if ($svn_tracker_debug) {
	$file = fopen($svn_tracker_debug_file,"a+");	
}

/**
 * Getting POST variables
 * UserName, Repository, Path, FileName, PrevVersion, ActualVersion, Log,
 * ArtifactNumbers and TaskNumbers
 */
$Config = array();
$SubmittedVars = array();
$SubmittedVars = unserialize(str_replace('\"','"',$_POST['data']));

$i = 0;
foreach ($SubmittedVars as $SubmittedVar) {
	$Configs[$i]['UserName']        = $SubmittedVar['UserName'];
	//$Configs[$i]['UserName']        = 'def_admin';   use this to make tests, just replace with a gforge user
	$Configs[$i]['Repository']      = $SubmittedVar['Repository'];
	$Configs[$i]['FileName']        = $SubmittedVar['FileName'];
	$Configs[$i]['PrevVersion']     = $SubmittedVar['PrevVersion'];
	$Configs[$i]['ActualVersion']   = $SubmittedVar['ActualVersion'];
	$Configs[$i]['ArtifactNumbers'] = $SubmittedVar['ArtifactNumbers'];
	$Configs[$i]['TaskNumbers']     = $SubmittedVar['TaskNumbers'];
	$Configs[$i]['Log']             = $SubmittedVar['Log'];
	$Configs[$i]['SvnDate']         = $SubmittedVar['SvnDate'];
	$i++;
	if($svn_tracker_debug) {
		echo "Variables received by newcommit.php:\n";
		print_r($Configs[$i]);
	}
}



/**
 * Checks if the commit it's possible and parse arguments
 * Checks if repository, group and user_name are right.
 *  It extract group from svnroot, and check if the plugin
 *  is availabe. It checks if the user exists.
 *
 * @param   array    $Config Config
 *
 * @return  array    Returns 'check'=true if check passed, group, group_id
 */
function parseConfig(&$Config)
{
	global $sys_svnroot_path, $svn_tracker_debug,$file;
	
	$Result = array();
	$Result['check'] = true;
	$Repository = $Config['Repository'];
	$UserName = $Config['UserName'];

	if($sys_svnroot_path[strlen($sys_svnroot_path)-1]!='/') {
		$sys_svnroot_path.='/';
	}
	$repo_root = substr($Repository,0,strrpos($Repository,"/") + 1); //we get the directory of the repository root (with trailing slash)
	
	if(fileinode($sys_svnroot_path) == fileinode($repo_root)) { // since the $sys_svnroot_path is usually $sys_svnroot, and that one is a symlink, we check that the inode is the same for both
		$GroupName = substr($Repository, strrpos($Repository,"/") + 1);
		$Config['FileName'] = substr($Config['FileName'],strlen($Repository)); //get only the filename relative to the repo
	} else {
		$GroupName = $Repository;
		$Config['FileName'] = $Config['FileName'];
	}
	
	if($svn_tracker_debug) {
		echo "GroupName = ".$GroupName."\n";
		echo "SVNRootPath = ".$sys_svnroot_path."\n";
	}
	
	if ($svn_tracker_debug) {
		fwrite($file,$GroupName."\n");
	}
	
	$Result['group']    = group_get_object_by_name($GroupName);
	$Result['user']     = user_get_object_by_name($UserName);
	if (!$Result['group'] || !is_object($Result['group']) ||
		$Result['group']->isError() || !$Result['group']->isActive()) {
		$Result['check'] = false;
		$Result['error'] = 'Group Not Found';
	} else {
		$Result['group_id'] = $Result['group']->getID();
		if (!$Result['group']->usesPlugin('svntracker')) {
			$Result['check'] = false;
			$Result['error'] = 'Plugin not enabled for this Group';
		}
	}

	if (!$Result['user'] || !is_object($Result['user']) ||
		$Result['user']->isError() || !$Result['user']->isActive()) {
		$Result['check'] = false;
		$Result['error'] = 'Invalid User';
	}
	return $Result;
}

/**
 * Add a entry in the DataBase for a Artifact associated to a commit
 *
 * @param   array    $Config Config
 * @param   string   $GroupId The GroupId to insert it into
 * @param   string   $Num The artifact_id
 *
 * @return  array    Returns 'check'=true if check passed, group, group_id
 */
function addArtifactLog($Config, $GroupId, $Num)
{
	global $file;
	$return = array();
	$Query = "SELECT * from artifact,artifact_group_list WHERE ".
		"artifact.group_artifact_id=artifact_group_list.group_artifact_id ".
		"AND artifact_group_list.group_id=".
		"'".$GroupId."' AND artifact.artifact_id='".$Num."'";
	$Result = db_query($Query);
	$Rows = db_numrows($Result);
	if ($svn_tracker_debug) {
		fwrite($file,"query : " . $Query ."\n");
		fwrite($file,"rows : " . $Rows ."\n");
	}
	if ($Rows == 0) {
		$return['Error'] .= "Artifact ".$Num." Not Found.";
	}

	if ($Rows == 1) {
		db_begin();
		$Query = "INSERT INTO plugin_svntracker_data_artifact ".
		"(kind, group_artifact_id) VALUES ".
		"('0', '".$Num."')";
		if ($svn_tracker_debug) {
			fwrite($file,"query 2: " . $Query ."\n");
		}
		$DBRes = db_query($Query);
		$HolderID= db_insertid($DBRes,'plugin_svntracker_data_artifact','id');
		if (!$DBRes || !$HolderID) {
			$return['Error']='Problems with Artifact $Num: '.db_error($DBRes);
			db_rollback();
		} else {
			$Query = "INSERT INTO plugin_svntracker_data_master ".
				"(holder_id, svn_date, log_text, file, prev_version, ".
				"actual_version, author)".
				" VALUES ('".$HolderID."','".$Config['SvnDate']."','".$Config['Log'].
				"','".$Config['FileName']."','".
				$Config['PrevVersion']."','".
				$Config['ActualVersion']."','".$Config['UserName']."')";
			if ($svn_tracker_debug) {
				fwrite($file,"query 3: " . $Query ."\n");
			}
			$DBRes = db_query($Query);
			if(!$DBRes) {
				db_rollback();
			} else {
				db_commit();
			}
		}

	}
	if ($Rows > 1) {
		$return['Error'] .= "Unknown problem adding Tracker:$Num.";
	}
	return $return;
}

/**
 * Add a entry in the DataBase for a Tracker associated to a commit
 *
 * @param   array    $Config Config
 * @param   string   $GroupId The GroupId to insert it into
 * @param   string   $Num The tracker_id
 *
 * @return  array    Returns 'check'=true if check passed, group, group_id
 */
function addTaskLog($Config, $GroupId, $Num)
{
	$return = array();
	$Query = "SELECT * from project_task,project_group_list WHERE ".
		"project_task.group_project_id=".
		"project_group_list.group_project_id ".
		"AND project_task.project_task_id='".$Num."' AND ".
		" project_group_list.group_id='".$GroupId."'";
	var_dump($Query);
	$Result = db_query($Query);
	$Rows = db_numrows($Result);
	if ($Rows == 0) {
		$return['Error'] .= "Task:$Num Not Found.";
	}
	if ($Rows == 1) {
		db_begin();
		$Query = "INSERT INTO plugin_svntracker_data_artifact ".
		"(kind, project_task_id) VALUES ".
		"('1', '".$Num."')";
		$DBRes = db_query($Query);
		$HolderID= db_insertid($DBRes,'plugin_svntracker_data_artifact','id');
		if (!$DBRes || !$HolderID) {
			$return['Error']='Problems with Task $Num: '.db_error($DBRes);
			db_rollback();
		} else {
			$Query = "INSERT INTO plugin_svntracker_data_master ".
				"(holder_id, svn_date, log_text, file, prev_version, ".
				"actual_version, author)".
				" VALUES ('".$HolderID."','".$Config['SvnDate']."','".$Config['Log'].
				"','".$Config['FileName'].
				"','".$Config['PrevVersion']."','".
				$Config['ActualVersion']."','".$Config['UserName']."')";
				$DBRes = db_query($Query);
			if(!$DBRes) {
				db_rollback();
			} else {
				db_commit();
			}
		}
	}
	if ($Rows > 1) {
		$return['Error'] .= "Unknown problem adding Task:$Num.";
	}
	return $return;
}

foreach ($Configs as $Config) {
	$Result = parseConfig(&$Config);
	if ($Result['check'] == false) {
		exit_error('Check_error', $Result['error']);
	}
	
	if (!is_null($Config['ArtifactNumbers'])) {
		foreach ($Config['ArtifactNumbers'] as $Num)
		{
			$AddResult = addArtifactLog($Config, $Result['group_id'], $Num);
			if (isset($AddResult['Error'])) {
				exit_error('Adding ArtifactNumber',$AddResult['Error']);
			}
		}
	}
	
	if (!is_null($Config['TaskNumbers'])) {
		foreach ($Config['TaskNumbers'] as $Num)
		{
			$AddResult = addTaskLog($Config, $Result['group_id'], $Num);
			if (isset($AddResult['Error'])) {
				exit_error('Adding TaskNumber',$AddResult['Error']);
			}
		}
	}	
}



exit (0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
