<?php
/**
 * scmhook plugin: CVS committracker hook
 *
 * Copyright 2014, Philipp Keidel - EDAG Engineering AG
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * This script takes some POST variables. It makes a check and it
 * store in DB the commit info attached to the tracker or task.
 *
 */

require_once dirname(__FILE__)."/../../../env.inc.php";
require_once $gfcommon.'include/pre.php';

/**
 * Getting POST variables
 * UserName, Repository, Path, FileName, PrevVersion, ActualVersion, Log,
 * ArtifactNumbers and TaskNumbers
 */

$Config = array();
$Configs = array();
$Configs = unserialize(urldecode($_POST['data']));


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
function parseConfig(&$Config) {
	// $repos_path = forge_get_config ('repos_path', 'scmcvs') ;

	$Result = array();
	$Result['check'] = true;
	$Repository = $Config['Repository'];
	$UserName = $Config['UserName'];

	$Result['group'] = group_get_object_by_name($Repository);
	$Result['user']  = user_get_object_by_name($UserName);

	if (!$Result['group'] || !is_object($Result['group']) ||
		$Result['group']->isError() || !$Result['group']->isActive()) {
		$Result['check'] = false;
		$Result['error'] = "Repository/Group '$Repository' Not Found";
	} else {
		$Result['group_id'] = $Result['group']->getID();
		if (!$Result['group']->usesPlugin('scmhook')) {
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
function addArtifactLog($Config, $GroupId, $Num) {
	$return = array();
	// Abfrage des Tracker Items
	$Result = db_query_params ('SELECT * FROM artifact,artifact_group_list
					WHERE artifact.group_artifact_id=artifact_group_list.group_artifact_id
					AND artifact_group_list.group_id=$1 AND artifact.artifact_id=$2', array ($GroupId, $Num));
	$Rows = db_numrows($Result);
	if ($Rows == 0) {
		$return['Error'] = "Artifact ".$Num." Not Found.";
	}

	if ($Rows == 1) {
		db_begin();
		$DBRes = db_query_params ('INSERT INTO plugin_scmhook_scmcvs_committracker_data_artifact (kind, group_artifact_id) VALUES (0, $1)', array($Num));
		$HolderID= db_insertid($DBRes,'plugin_scmhook_scmcvs_committracker_data_artifact','id');

		if (!$DBRes || !$HolderID) {
			$return['Error']="Problems with Artifact $Num: ".db_error();
			db_rollback();
		} else {
			$DBRes = db_query_params ('INSERT INTO plugin_scmhook_scmcvs_committracker_data_master (holder_id, cvs_date, log_text, file, prev_version, actual_version, author) VALUES ($1, $2, $3, $4, $5, $6, $7)',
						  array($HolderID,
							$Config['CVSDate'],
							$Config['Log'],
							$Config['Directory'].'/'.$Config['FileName'],
							$Config['PrevVersion'],
							$Config['ActualVersion'],
							$Config['UserName'])) ;
			if(!$DBRes) {
				$return['Error'] = "Problems with Artifact $Num: ".db_error()."\n";
				db_rollback();
			} else {
				db_commit();
			}
		}
	}
	if ($Rows > 1) {
		$return['Error'] .= "Unknown problem adding Tracker: $Num.";
	}
	return $return;
}

/*
 * Add a entry in the DataBase for a Tracker associated to a commit
 *
 * @param   array    $Config Config
 * @param   string   $GroupId The GroupId to insert it into
 * @param   string   $Num The tracker_id
 *
 * @return  array    Returns 'check'=true if check passed, group, group_id
 */
function addTaskLog($Config, $GroupId, $Num) {
	$return = array();
	$Result = db_query_params ('SELECT * FROM project_task,project_group_list
					WHERE project_task.group_project_id=project_group_list.group_project_id
					AND project_task.project_task_id=$1
					AND project_group_list.group_id=$2', array ($Num, $GroupId));
	$Rows = db_numrows($Result);
	if ($Rows == 0) {
		$return['Error'] .= "Task: $Num Not Found.";
	}
	if ($Rows == 1) {
		db_begin();
		$DBRes = db_query_params ('INSERT INTO plugin_scmhook_scmcvs_committracker_data_artifact (kind, project_task_id) VALUES (1, $1)',
			array ($Num));
		$HolderID= db_insertid($DBRes,'plugin_scmhook_scmsvn_committracker_data_artifact','id');
		if (!$DBRes || !$HolderID) {
			$return['Error']='Problems with Task $Num: '.db_error();
			db_rollback();
		} else {
			$DBRes = db_query_params ('INSERT INTO plugin_scmhook_scmcvs_committracker_data_master (holder_id, svn_date, log_text, file, prev_version, actual_version, author) VALUES ($1, $2, $3, $4, $5, $6, $7)',
						  array($HolderID,
							$Config['CVSDate'],
							$Config['Log'],
							$Config['Directory'].'/'.$Config['FileName'],
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

if (isset($Configs) && is_array($Configs)) {
	foreach ($Configs as $Config) {
		$Result = parseConfig($Config);
		if (!$Result['check']) {
			exit_error('Check_error', $Result['error']);
		}
		// ArtifactNumbers
		if (!is_null($Config['ArtifactNumbers'])) {
			foreach ($Config['ArtifactNumbers'] as $Num) {
				$AddResult = addArtifactLog($Config, $Result['group_id'], $Num);
				if (isset($AddResult['Error'])) {
					exit_error('Adding ArtifactNumber',$AddResult['Error']);
				}
			}
		}
		// TaskNumbers
		if (!is_null($Config['TaskNumbers'])) {
			foreach ($Config['TaskNumbers'] as $Num) {
				$AddResult = addTaskLog($Config, $Result['group_id'], $Num);
				if (isset($AddResult['Error'])) {
					exit_error('Adding TaskNumber',$AddResult['Error']);
				}
			}
		}
	}
}
