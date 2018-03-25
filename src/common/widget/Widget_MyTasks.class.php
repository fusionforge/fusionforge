<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2014-2016, Franck Villaume - TrivialDev
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';
require_once $gfcommon.'include/my_utils.php';

/**
* Widget_MyTasks
*
* Tasks assigned to me
*/
class Widget_MyTasks extends Widget {
	var $content;
	var $can_be_displayed;

	function __construct() {
		global $HTML;
		parent::__construct('mytasks');
		$this->content = '';
		$this->setOwner(user_getid(), WidgetLayoutManager::OWNER_TYPE_USER);

		$sql = 'SELECT groups.group_id, groups.group_name, project_group_list.group_project_id, project_group_list.project_name '.
			'FROM groups,project_group_list,project_task,project_assigned_to '.
			'WHERE project_task.project_task_id=project_assigned_to.project_task_id '.
			'AND project_assigned_to.assigned_to_id=$1'.
			' AND project_task.status_id=1 AND project_group_list.group_id=groups.group_id '.
			'AND project_group_list.group_project_id=project_task.group_project_id GROUP BY groups.group_id, groups.group_name, project_group_list.project_name, project_group_list.group_project_id';

		$result=db_query_params($sql,array(user_getid()));

		$plist = array();
		while ($r = db_fetch_array($result)) {
			if (forge_check_perm('project_read', $r['group_id'])
					&& forge_check_perm('pm', $r['group_project_id'], 'read')) {
				$plist[] = $r;
			}
		}

		$rows = count($plist);

		if ($result && $rows >= 1) {
			$request =& HTTPRequest::instance();
			$this->content .= $HTML->listTableTop();
			for ($j=0; $j<$rows; $j++) {

				$group_id = $plist[$j]['group_id'];
				$group_project_id = $plist[$j]['group_project_id'];

				$sql2 = 'SELECT project_task.project_task_id, project_task.priority, project_task.summary,project_task.percent_complete '.
					'FROM groups,project_group_list,project_task,project_assigned_to '.
					'WHERE project_task.project_task_id=project_assigned_to.project_task_id '.
					'AND project_assigned_to.assigned_to_id = $1 AND project_task.status_id = 1 '.
					'AND project_group_list.group_id=groups.group_id '.
					"AND groups.group_id=$2 ".
					'AND project_group_list.group_project_id=project_task.group_project_id '.
					'AND project_group_list.group_project_id= $3 ';

				$result2 = db_query_params($sql2,array(user_getid(),$group_id,$group_project_id), 100);
				$rows2 = db_numrows($result2);

				$vItemId = new Valid_UInt('hide_item_id');
				$vItemId->required();
				if($request->valid($vItemId)) {
					$hide_item_id = $request->get('hide_item_id');
				} else {
					$hide_item_id = null;
				}

				$vPm = new Valid_WhiteList('hide_pm', array(0, 1));
				$vPm->required();
				if($request->valid($vPm)) {
					$hide_pm = $request->get('hide_pm');
				} else {
					$hide_pm = null;
				}

				list($hide_now,$count_diff,$hide_url) = my_hide_url('pm',$group_project_id,$hide_item_id,$rows2,$hide_pm);

				$html_hdr = '<tr class="boxitem"><td colspan="3">'.
					$hide_url.util_make_link('/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id,
								db_result($result,$j,'group_name').' - '.db_result($result,$j,'project_name'));
				$html = '';
				$count_new = max(0, $count_diff);
				for ($i=0; $i<$rows2; $i++) {

					if (!$hide_now) {

						$html .= '
							<tr class="priority'.db_result($result2,$i,'priority').
							'"><td class="small">'.
							util_make_link('/pm/task.php/?func=detailtask&project_task_id='.db_result($result2, $i, 'project_task_id').
									'&group_id='.$group_id.'&group_project_id='.$group_project_id,
									stripslashes(db_result($result2,$i,'summary'))).'</td>'.
							'<td class="small">'.(db_result($result2,$i,'percent_complete')).'%</td></tr>';

					}
				}

				$html_hdr .= my_item_count($rows2,$count_new).'</td></tr>';
				$this->content .= $html_hdr.$html;
			}
			$this->content .= $HTML->listTableBottom();
		} else {
			$this->content .= $HTML->warning_msg(_('No task yet.'));
		}
	}

	function getTitle() {
		return _('My Tasks');
	}

	function getContent() {
		return $this->content;
	}

	function isAvailable() {
		if (!forge_get_config('use_pm')) {
			return false ;
		}

		foreach (UserManager::instance()->getCurrentUser()->getGroups(false) as $p) {
			if ($p->usesPM()) {
				return true ;
			}
		}
		return false ;
	}

	function getDescription() {
		return _('List the tasks assigned to you.');
	}

	function getCategory() {
		return _('Project Management');
	}
}
