<?php
/**
 * scmhook GitCommitTracker Plugin Class
 * Copyright 2004, Francisco Gimeno <kikov @nospam@ kikov.org>
 * Copyright 2005, Guillaume Smet <guillaume-gforge@smet.org>
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014, Benoit Debaenst - TrivialDev
 * Copyright 2014,2016-2017, Franck Villaume - TrivialDev
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

global $gfplugins;
require_once $gfplugins.'scmhook/common/scmhook.class.php';

class GitCommitTracker extends scmhook {
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Commit Tracker";
		$this->description = _('Every commit is pushed into related tracker or task.');
		$this->classname = "commitTracker";
		$this->hooktype = "post-receive";
		$this->label = "scmgit";
		$this->unixname = "committracker";
		$this->needcopy = 0;
		$this->command = '/usr/bin/php -d include_path='.ini_get('include_path').' '.forge_get_config('plugins_path').'/scmhook/library/'.
				$this->label.'/hooks/'.$this->unixname.'/post.php $PARAMS $SCRIPTPATH';
	}

	function isAvailable() {
		if (!$this->group->usesTracker()) {
			$this->disabledMessage = _('Hook not available due to missing dependency')._(': ')._('Project not using tracker.');
			return false;
		}
		return true;
	}

	function getDisabledMessage() {
		return $this->disabledMessage;
	}

	function artifact_extra_detail(&$params) {
		global $HTML;
		$DBResult = db_query_params('SELECT * FROM plugin_scmhook_scmgit_committracker_data_master, plugin_scmhook_scmgit_committracker_data_artifact
						WHERE plugin_scmhook_scmgit_committracker_data_artifact.group_artifact_id = $1
						AND plugin_scmhook_scmgit_committracker_data_master.holder_id = plugin_scmhook_scmgit_committracker_data_artifact.id
						ORDER BY git_date',
						array($params['artifact_id']));
		if (!$DBResult) {
			$return = $HTML->error_msg(_('Unable to retrieve data'));
		} else {
			$return = $this->getCommitEntries($DBResult, $params['group_id']);
		}
		if (isset($params['content'])) {
			$params['content'] .= $return;
		} else {
			echo $return;
		}
	}

	function task_extra_detail($params) {
		$return = '';
		$DBResult = db_query_params ('SELECT * FROM plugin_scmhook_scmgit_committracker_data_master, plugin_scmhook_scmgit_committracker_data_artifact
						WHERE plugin_scmhook_scmgit_committracker_data_artifact.project_task_id = $1
						AND plugin_scmhook_scmgit_committracker_data_master.holder_id = plugin_scmhook_scmgit_committracker_data_artifact.id
						ORDER BY git_date',
						array($params['task_id']));
		if (!$DBResult) {
			$return = $HTML->error_msg(_('Unable to retrieve data'));
		} else {
			$return = $this->getCommitEntries($DBResult, $params['group_id']);
		}
		if (isset($params['content'])) {
			$params['content'] = $return;
		} else {
			echo $return;
		}
	}

	/**
	* getCommitEntries - It display a table with commit related to this tracker or task_extra_detail
	*
	* @param	string	$DBResult	Result of the commit entries.
	* @param	integer	$group_id	Group_id of the actual Group_id
	*
	*/
	function getCommitEntries($DBResult, $group_id) {
		global $HTML;
		$group = group_get_object($group_id);
		$Rows = db_numrows($DBResult);
		$return = '';

		if ($Rows > 0) {
			$return .= '<tr><td>';
			$return .= html_e('h2', array(), _('Related Git commits'), false);

			$title_arr = $this->getTitleArr($group_id);
			$return .= $HTML->listTableTop($title_arr);

			while ($Row = db_fetch_array($DBResult)) {
				$cells = array();
				$cells[][] = $this->getFileLink($group->getUnixName(), $Row['file'], $Row['actual_version']);
				$cells[][] = date(_('Y-m-d'), $Row['git_date']);
				$cells[][] = $this->getDiffLink($group->getUnixName(), $Row['file'], $Row['prev_version'], $Row['actual_version']);
				$cells[][] = $this->getActualVersionLink($group->getUnixName(), $Row['file'], $Row['actual_version']);
				$cells[][] = htmlspecialchars($Row['log_text']);
				$commituser = user_get_object_by_name($Row['author']);
				$cells[][] = util_display_user($commituser->getUnixName(), $commituser->getId(), $commituser->getRealname());
				$return .= $HTML->multiTableRow(array(), $cells);
			}
			$return .= $HTML->listTableBottom();
			$return .= '</td></tr>';
		}
		return $return;
	}

	/**
	* getTitleArr - Return an array with titles of Box to display the entries
	*
	* @param	integer	$group_id	Group_id of the actual Group_id
	*
	* @return	Array	$title_arr	The array containing the titles
	*
	*/
	function getTitleArr($group_id) {
		$title_arr=array();
		$title_arr[]=_('File Name').' ('.util_make_link('/scm/browser.php?group_id='.$group_id, _('Browse')).')';
		$title_arr[]=_('Date');
		$title_arr[]=_('Previous Version');
		$title_arr[]=_('Current Version');
		$title_arr[]=_('Log Message');
		$title_arr[]=_('Author');
		return $title_arr;
	}

	/**
	* getFileLink - Return a link to the Filename
	*
	* @param	String	$GroupName	is the Name of the project
	* @param	String	$FileName	is the FileName ( with path )
	* @param 	Int	$LatestRevision	is the last revision for the file
	*
	* @return	String	$FileName	The string containing a link to the File in the gitwe
	*
	*/
	function getFileLink($GroupName, $FileName, $LatestRevision) {
		return $FileName;
	}

	/**
	* getActualVersionLink - Return a link to the actual version File
	*
	* @param	String	$GroupName	is the Name of the project
	* @param	String	$FileName	is the FileName (with path)
	* @param	String	$Version	the version to retrieve
	*
	* @return	String	$Version	The string containing a link to the actual version File
	*
	*/
	function getActualVersionLink($GroupName, $FileName, $Version) {
		return $Version;
	}

	/**
	* getDiffLink - Return a link to the old versions of a File
	*
	* @param	String	$GroupName	is the Name of the project
	* @param	String	$FileName	is the FileName ( with path )
	* @param	String	$PrevVersion	First version to retrieve
	* @param	String	$ActualVersion	Second version to retrieve
	*
	* @return	String	$PrevVersion	The string containing the old version File
	*
	*/
	function getDiffLink($GroupName, $FileName, $PrevVersion, $ActualVersion) {
		return $PrevVersion;
	}
}
