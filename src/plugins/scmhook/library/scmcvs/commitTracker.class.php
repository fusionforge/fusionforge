<?php
/**
 * scmhook CVSCommitTracker Plugin Class
 * Copyright 2014, Philipp Keidel - EDAG Engineering AG
 * Copyright 2017, Franck Villaume - TrivialDev
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

class CVSCommitTracker extends scmhook {
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Commit Tracker";
		$this->description = _('Every commit is pushed into related tracker or task.');
		$this->classname = "commitTracker";
		$this->hooktype = "post-commit";
		$this->label = "scmcvs";
		$this->unixname = "committracker";
		$this->needcopy = 0;
		$filepath = forge_get_config('plugins_path').'/scmhook/library/'.$this->label.'/hooks/cvs_wrapper.php';
		$this->command = 'ALL /usr/bin/php '.$filepath.' '.$this->hooktype.' '.$this->group->getUnixName().' %{sVv} $USER';
	}

	function isAvailable() {
		if ($this->group->usesTracker()) {
			return true;
		}
		$this->disabledMessage = _('Hook not available due to missing dependency')._(': ')._('Project not using tracker.');
		$this->description = '['.$this->disabledMessage.'] '.$this->description;
		return false;
	}

	function getDisabledMessage() {
		return $this->disabledMessage;
	}

	function artifact_extra_detail(&$params) {
		global $HTML;
		$DBResult = db_query_params('SELECT * FROM plugin_scmhook_scmcvs_committracker_data_master dm, plugin_scmhook_scmcvs_committracker_data_artifact da
					WHERE da.group_artifact_id = $1 AND dm.holder_id = da.id ORDER BY cvs_date desc', array($params['artifact_id']));
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
		global $HTML;
		$DBResult = db_query_params ('SELECT * FROM plugin_scmhook_scmcvs_committracker_data_master dm, plugin_scmhook_scmcvs_committracker_data_artifact da
					WHERE da.project_task_id = $1 AND dm.holder_id = da.id ORDER BY cvs_date desc', array($params['task_id']));
		if (!$DBResult) {
			echo $HTML->error_msg(_('Unable to retrieve data'));
		} else {
			$this->getCommitEntries($DBResult, $params['group_id']);
		}
	}

	/**
	 * It display a table with commit related to this tracker or task_extra_detail
	 *
	 * @param	string	$Query		Query to be executed to get the commit entries.
	 * @param	int	$group_id	Group_id of the actual Group_id
	 *
	 */
	function getCommitEntries($DBResult, $group_id) {
		global $HTML;
		$groupObj = group_get_object($group_id);
		$Rows= db_numrows($DBResult);
		$return = '';

		if ($Rows > 0) {
			$return .= '<tr><td>';
			$return .= html_e('h2', array(), _('Related CVS commits'), false);

			$title_arr = $this->getTitleArr();
			$return .= $HTML->listTableTop($title_arr);

			while ($Row = db_fetch_array($DBResult)) {
				$cells = array();
				$cells[][] = $this->getFileLink($groupObj->getUnixName(), $Row['file'],$Row['actual_version']);
				$cells[][] = date(_('Y-m-d'), $Row['cvs_date']);
				$cells[][] = $this->getDiffLink($groupObj->getUnixName(), $Row['file'], $Row['prev_version'], $Row['actual_version']);
				$cells[][] = $this->getActualVersionLink($groupObj->getUnixName(), $Row['file'], $Row['actual_version']);
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
	 * Return an array with titles of Box to display the entries
	 *
	 * @return	array  The array containing the titles
	 *
	 */
	function getTitleArr() {
		$title_arr   = array();
		$title_arr[] = _('File Name');
		$title_arr[] = _('Date');
		$title_arr[] = _('Previous Version');
		$title_arr[] = _('Current Version');
		$title_arr[] = _('Log Message');
		$title_arr[] = _('Author');
		return $title_arr;
	}

	/**
	 * Return a link to the File in cvsweb
	 *
	 * @param	string  $GroupName is the Name of the project
	 * @param	string  $FileName  is the FileName ( with path )
	 * @param	int		$LatestRevision	is the last revision for the file
	 *
	 * @return	string  The string containing a link to the File in the cvsweb
	 *
	 */
	function getFileLink($GroupName, $FileName, $LatestRevision) {
		return util_make_link('/scm/viewvc.php/'.$FileName . '?root='.$GroupName.'&view=log&rev=' . $LatestRevision, $FileName) ;
	}

	/**
	 * Return a link to the File in viewcvs in the specified Version
	 *
	 * @param	string  $GroupName is the Name of the project
	 * @param	string  $FileName  is the FileName ( with path )
	 * @param	string  $Version   the version to retrieve
	 *
	 * @return	string  The string containing a link to the File in the viewcvs
	 *
	 */
	 function getActualVersionLink($GroupName, $FileName, $Version) {
		return util_make_link('/scm/viewvc.php/'.$FileName . '?root='.$GroupName.'&rev='.$Version, $Version);
	}

	/**
	 * Return a link to the diff between two versions of a File in viewcvs
	 *
	 * @param	string  $GroupName is the Name of the project
	 * @param	string  $FileName  is the FileName ( with path )
	 * @param	string  $PrevVersion   First version to retrieve
	 * @param	string  $ActualVersion Second version to retrieve
	 *
	 * @return	string  The string containing a link to the File in the cvsweb
	 *
	 */
	function getDiffLink($GroupName, $FileName, $PrevVersion, $ActualVersion) {
		if($PrevVersion != 'NONE' && $ActualVersion != 'NONE') {
			return util_make_link('/scm/viewvc.php/'.$FileName . '?root='.$GroupName.'&r1='.$PrevVersion . '&r2='.$ActualVersion, _('Diff To').' '.$PrevVersion);
		}
		return _('Wrong situation');
	}
}
