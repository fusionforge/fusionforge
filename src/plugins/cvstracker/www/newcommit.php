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
  */
/**
 * This script takes some POST variables.It makes a check and it
 * store in DB the commit info attached to the tracker or task.
 *
 */
require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfconfig.'plugins/cvstracker/config.php';

/**
 * Getting POST variables
 * UserName, Repository, Path, FileName, PrevVersion, ActualVersion, Log,
 * ArtifactNumbers and TaskNumbers
 */
$Config = array();
$SubmittedVars = array();
$SubmittedVars = unserialize(str_replace('\\\\', '\\', str_replace('\"','"',$_POST['data'])));

$i = 0;
foreach ($SubmittedVars as $SubmittedVar) {
	$Configs[$i]['UserName']        = $SubmittedVar['UserName'];
	//$Configs[$i]['UserName']        = "def_admin";   USE THIS FOR TESTING PURPOSES
	$Configs[$i]['Repository']      = $SubmittedVar['Repository'];
	$Configs[$i]['FileName']        = $SubmittedVar['FileName'];
	$Configs[$i]['PrevVersion']     = $SubmittedVar['PrevVersion'];
	$Configs[$i]['ActualVersion']   = $SubmittedVar['ActualVersion'];
	$Configs[$i]['ArtifactNumbers'] = $SubmittedVar['ArtifactNumbers'];
	$Configs[$i]['TaskNumbers']     = $SubmittedVar['TaskNumbers'];
	$Configs[$i]['Log']             = $SubmittedVar['Log'];
	$Configs[$i]['CvsDate']         = $SubmittedVar['CvsDate'];
	$i++;
}


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
	$repos_path = forge_get_config ('repos_path', 'scmcvs') ;

	$Result = array();
	$Result['check'] = true;
	$Repository = $Config['Repository'];
	$UserName = $Config['UserName'];

	// add a trailing / if needed
	if($repos_path[strlen($repos_path)-1]!='/') {
		$repos_path.='/';
	}
	if(strncmp($Repository,$repos_path, strlen($repos_path)) == 0) {
		$GroupName = substr($Repository, strlen($repos_path));
	} else {
		$GroupName = $Repository;
	}

	$Result['user']     = user_get_object_by_name($UserName);
	if (!$Result['user'] || !is_object($Result['user']) ||
		$Result['user']->isError() || !$Result['user']->isActive()) {
		$Result['check'] = false;
		$Result['error'] = 'Invalid User';
		return $Result;
	}
	session_set_new($Result['user']->getID());

	$Result['group']    = group_get_object_by_name($GroupName);
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
	$return = array();
	$Result = db_query_params ('SELECT * from artifact,artifact_group_list WHERE artifact.group_artifact_id=artifact_group_list.group_artifact_id AND artifact_group_list.group_id=$1 AND artifact.artifact_id=$2',
				   array ($GroupId,
					  $Num));
	$Rows = db_numrows($Result);
	if ($Rows == 0) {
		$return['Error'] .= "Artifact ".$Num." Not Found.";
	}

	if ($Rows == 1) {
		db_begin();
		$DBRes = db_query_params ('INSERT INTO plugin_cvstracker_data_artifact (kind, group_artifact_id) VALUES (0, $1)',
					  array ($Num));
		$HolderID= db_insertid($DBRes,'plugin_cvstracker_data_artifact','id');
		if (!$DBRes || !$HolderID) {
			$return['Error']='Problems with Artifact $Num: '.db_error();
			db_rollback();
		} else {
			$DBRes = db_query_params ('INSERT INTO plugin_cvstracker_data_master (holder_id, cvs_date, log_text, file, prev_version, actual_version, author) VALUES ($1,$2,$3,$4,$5,$6,$7)',
						  array ($HolderID,
							 $Config['CvsDate'],
							 $Config['Log'],
							 $Config['FileName'],
							 $Config['PrevVersion'],
							 $Config['ActualVersion'],
							 $Config['UserName']));
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
	$Result = db_query_params ('SELECT * from project_task,project_group_list WHERE project_task.group_project_id=project_group_list.group_project_id AND project_task.project_task_id=$1 AND project_group_list.group_id=$2',
				   array ($Num,
					  $GroupId));
	$Rows = db_numrows($Result);
	if ($Rows == 0) {
		$return['Error'] .= "Task:$Num Not Found.";
	}
	if ($Rows == 1) {
		db_begin();
		$DBRes = db_query_params ('INSERT INTO plugin_cvstracker_data_artifact (kind, project_task_id) VALUES (1, $1)',
					  array ($Num));
		$HolderID= db_insertid($DBRes,'plugin_cvstracker_data_artifact','id');
		if (!$DBRes || !$HolderID) {
			$return['Error']='Problems with Task $Num: '.db_error();
			db_rollback();
		} else {
			$DBRes = db_query_params ('INSERT INTO plugin_cvstracker_data_master (holder_id, cvs_date, log_text, file, prev_version, actual_version, author) VALUES ($1,$2,$3,$4,$5,$6,$7)',
						  array ($HolderID,
							 $Config['CvsDate'],
							 $Config['Log'],
							 $Config['FileName'],
							 $Config['PrevVersion'],
							 $Config['ActualVersion'],
							 $Config['UserName']));
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
}

exit (0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
