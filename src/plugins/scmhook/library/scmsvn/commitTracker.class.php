<?php
/**
 * scmhook commitTracker Plugin Class
 * Copyright 2004, Francisco Gimeno <kikov @nospam@ kikov.org>
 * Copyright 2005, Guillaume Smet <guillaume-gforge@smet.org>
 * Copyright 2011, Franck Villaume - Capgemini
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

class commitTracker extends scmhook {
	function commitTracker() {
		$this->name = "Commit Tracker";
		$this->description = _('Every commit is pushed into related tracker or task.');
		$this->classname = "commitTracker";
		$this->hooktype = "post-commit";
		$this->label = "scmsvn";
		$this->unixname = "committracker";
		$this->needcopy = 0;
		$this->command = '/usr/bin/php -d include_path='.ini_get('include_path').' '.forge_get_config('plugins_path').'/scmhook/library/'.
				$this->label.'/hooks/'.$this->unixname.'/post.php "$1" "$2"';
	}

	function artifact_extra_detail($params) {
		$DBResult = db_query_params('SELECT * FROM plugin_scmhook_scmsvn_committracker_data_master, plugin_scmhook_scmsvn_committracker_data_artifact
						WHERE plugin_scmhook_scmsvn_committracker_data_artifact.group_artifact_id = $1
						AND plugin_scmhook_scmsvn_committracker_data_master.holder_id = plugin_scmhook_scmsvn_committracker_data_artifact.id
						ORDER BY svn_date',
						array($params['artifact_id']));
		if (!$DBResult) {
			echo '<p class="error_msg">'._('Unable to retrieve data').'</p>';
		} else {
			$this->getCommitEntries($DBResult, $params['group_id']);
		}
	}

	function task_extra_detail($params) {
		$DBResult = db_query_params ('SELECT * FROM plugin_scmhook_scmsvn_committracker_data_master, plugin_scmhook_scmsvn_committracker_data_artifact
						WHERE plugin_scmhook_scmsvn_committracker_data_artifact.project_task_id=$1
						AND plugin_scmhook_scmsvn_committracker_data_master.holder_id=plugin_scmhook_scmsvn_committracker_data_artifact.id
						ORDER BY svn_date',
						array($params['task_id']));
		if (!$DBResult) {
			echo '<p class="error_msg">'._('Unable to retrieve data').'</p>';
		} else {
			$this->getCommitEntries($DBResult, $params['group_id']);
		}
	}

	/**
	* It display a table with commit related to this tracker or task_extra_detail
	*
	* @param   string   $Query Query to be executed to get the commit entries.
	* @param   integer  $group_id Group_id of the actual Group_id
	*
	*/
	function getCommitEntries($DBResult, $group_id) {
		$group = group_get_object($group_id);
		$Rows= db_numrows($DBResult);

		if ($Rows > 0) {
			echo '<tr><td colspan="2">';
			echo '<h4>'._('Links to related SVN commits').':</h4>';

			$title_arr = $this->getTitleArr();
			echo $GLOBALS['HTML']->listTableTop($title_arr);

			for ($i=0; $i<$Rows; $i++) {
				$Row = db_fetch_array($DBResult);
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
				'<td>'. $this->getFileLink($group->getUnixName(),
						$Row['file'],$Row['actual_version']). '</td>'.
				'<td>'. date(_('Y-m-d'), $Row['svn_date']).'</td>'.
				'<td>'. $this->getDiffLink($group->getUnixName(),
						$Row['file'],
						$Row['prev_version'],
						$Row['actual_version']).'</td>'.
				'<td>'. $this->getActualVersionLink($group->getUnixName(),
					$Row['file'], $Row['actual_version']).
				'</td>
				<td>'. htmlspecialchars($Row['log_text']).'</td>
				<td>'. util_make_link_u($Row['author'],
							 user_get_object_by_name($Row['author'])->getId(),
							 $Row['author']).'</td>
				</tr>';
			}
			echo $GLOBALS['HTML']->listTableBottom();
			echo '</td></tr>';
		}
	}

	/**
	* Return an array with titles of Box to display the entries
	*
	* @return   Array  The array containing the titles
	*
	*/
	function getTitleArr() {
		$title_arr=array();
		$title_arr[]=_('Filename');
		$title_arr[]=_('Date');
		$title_arr[]=_('Previous Version');
		$title_arr[]=_('Current Version');
		$title_arr[]=_('Log Message');
		$title_arr[]=_('Author');
		return $title_arr;
	}

	/**
	* Return a link to the File in svnweb
	*
	* @param    String  $GroupName is the Name of the project
	* @param    String  $FileName  is the FileName ( with path )
	* @param 	Int		$LatestRevision	is the last revision for the file
	*
	* @return   String  The string containing a link to the File in the svnweb
	*
	*/
	function getFileLink($GroupName, $FileName, $LatestRevision) {
		return util_make_link ('/scm/viewvc.php'.$FileName .
				       '?root='.$GroupName.'&view=log&rev=' .
				       $LatestRevision,
				       $FileName) ;
	}

	/**
	* Return a link to the File in viewcvs in the specified Version
	*
	* @param    String  $GroupName is the Name of the project
	* @param    String  $FileName  is the FileName ( with path )
	* @param    String  $Version   the version to retrieve
	*
	* @return   String  The string containing a link to the File in the viewcvs
	*
	*/
	function getActualVersionLink($GroupName, $FileName, $Version) {
		return util_make_link ('/scm/viewvc.php'.$FileName .
				       '?root='.$GroupName.'&rev='.$Version,
				       $Version);
	}

	/**
	* Return a link to the diff between two versions of a File in viewcvs
	*
	* @param    String  $GroupName is the Name of the project
	* @param    String  $FileName  is the FileName ( with path )
	* @param    String  $PrevVersion   First version to retrieve
	* @param    String  $ActualVersion Second version to retrieve
	*
	* @return   String  The string containing a link to the File in the cvsweb
	*
	*/
	function getDiffLink($GroupName, $FileName, $PrevVersion, $ActualVersion) {
		return util_make_link ('/scm/viewvc.php'.$FileName .
				       '?root='.$GroupName.'&r1='.$PrevVersion .
				       '&r2='.$ActualVersion,
				       _('Diff To').' '.$PrevVersion);
	}
}
?>