<?php
/**
  *
  * cvstracker plugin
  *
  * This script stand for receiving a HTTP Post from a hook in a
  *  CVS script. Arguments are passed and new entries in DB are
  *  generated.
  *
  * Copyright Francisco Gimeno <kikov@kikov.org>
  *
  * @version $Id$
  */
/**
 * This script takes some POST variables.It makes a check and it
 * store in DB the commit info attached to the tracker or task.
 *
 */
require_once('squal_pre.php');
require_once('/etc/gforge/plugins/cvstracker/cvstracker.conf');

/**
 * Getting POST variables
 * UserName, Repository, Path, FileName, PrevVersion, ActualVersion, Log,
 * ArtifactNumbers and TaskNumbers
 */
$Config['UserName']        = $_POST['UserName'];
$Config['Repository']      = $_POST['Repository'];
$Config['Path']            = $_POST['Path'];
$Config['FileName']        = $_POST['FileName'];
$Config['PrevVersion']     = $_POST['PrevVersion'];
$Config['ActualVersion']   = $_POST['ActualVersion'];
$Config['ArtifactNumbers'] = $_POST['ArtifactNumbers'];
$Config['TaskNumbers']     = $_POST['TaskNumbers'];
$Config['Log']             = $_POST['Log'];
$Config['CvsDate']         = $_POST['CvsDate'];

/**
 * Checks if the commit it's possible and parse arguments
 * Checks if repository, group and user_name are right.
 *  It extract group from cvsroot, and check if the plugin
 *  is availabe. It checks if the user exists.
 *
 * @param   array    $Config Config
 *
 * @return  array    Returns 'check'=true if check passed, group, group_id
 */
function parseConfig($Config)
{
	global $sys_cvsroot_path;
	
	$Result = array();
	$Result['check'] = true;
	$Repository = $Config['Repository'];
	$UserName = $Config['UserName'];

	if($sys_cvsroot_path[strlen($sys_cvsroot_path)]!='/') {
		$sys_cvsroot_path.='/';
	}
	if (strncmp($Repository,$sys_cvsroot_path, strlen($sys_cvsroot_path) )== 0)
		$GroupName=substr($Repository, strlen($sys_cvsroot_path));
	echo "GroupName=".$GroupName;
	echo "CVSRootPath=".$sys_cvsroot_path;

	$Result['group']    = group_get_object_by_name($GroupName);
	$Result['user']     = user_get_object_by_name($UserName);
	if (!$Result['group'] || !is_object($Result['group']) ||
		$Result['group']->isError() || !$Result['group']->isActive()) {
		$Result['check'] = false;
		$Result['error'] = 'Group Not Found';
	} else {
		$Result['group_id'] = $Result['group']->getID();
		if (!$Result['group']->usesPlugin('cvstracker')) {
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
	$Result = array();
	$Query = "SELECT * from artifact,artifact_group_list WHERE ".
		"artifact.group_artifact_id=artifact_group_list.group_artifact_id ".
		"AND artifact_group_list.group_id=".
		"'".$GroupId."' AND artifact.artifact_id='".$Num."'";
	$Result = db_query($Query);
	$Rows = db_numrows($Result);
	if ($Rows == 0) {
		$Result['Error'] .= "Artifact ".$Num." Not Found.";
	}

	if ($Rows == 1) {
		db_begin();
		$Query = "INSERT INTO plugin_cvstracker_data_artifact ".
		"(kind, group_artifact_id) VALUES ".
		"('0', '".$Num."')";
		$DBRes = db_query($Query);
		$HolderID= db_insertid($DBRes,'plugin_cvstracker_data_artifact','id');
		if (!$DBRes || !$HolderID) {
			$Result['Error']='Problems with Artifact $Num';
			db_rollback();
		} else {
			$Query = "INSERT INTO plugin_cvstracker_data_master ".
				"(holder_id, cvs_date, log_text, file, prev_version, ".
				"actual_version, author)".
				" VALUES ('".$HolderID."','".$Config['CvsDate']."','".$Config['Log'].
				"','".$Config['Path']."/".$Config['FileName']."','".
				$Config['PrevVersion']."','".
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
		$Result['Error'] .= "Unknown problem adding Tracker:$Num.";
	}
	return $Result;
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
	$Query = "SELECT * from project_task,project_group_list WHERE ".
		"project_task.group_project_id=".
		"project_group_list.group_project_id ".
		"AND project_task.project_task_id='".$Num."' AND ".
		" project_group_list.group_id='".$GroupId."'";
	var_dump($Query);
	$Result = db_query($Query);
	$Rows = db_numrows($Result);
	if ($Rows == 0) {
		$Result['Error'] .= "Task:$Num Not Found.";
	}
	if ($Rows == 1) {
		db_begin();
		$Query = "INSERT INTO plugin_cvstracker_data_artifact ".
		"(kind, project_task_id) VALUES ".
		"('1', '".$Num."')";
		$DBRes = db_query($Query);
		$HolderID= db_insertid($DBRes,'plugin_cvstracker_data_artifact','id');
		if (!$DBRes || !$HolderID) {
			$Result['Error']='Problems with Task $Num';
			db_rollback();
		} else {
			$Query = "INSERT INTO plugin_cvstracker_data_master ".
				"(holder_id, cvs_date, log_text, file, prev_version, ".
				"actual_version, author)".
				" VALUES ('".$HolderID."','".$Config['CvsDate']."','".$Config['Log'].
				"','".$Config['Path']."/".$Config['FileName'].
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
		$Result['Error'] .= "Unknown problem adding Task:$Num.";
	}
	return $Result;
}

$Result = parseConfig($Config);
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


exit (0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
