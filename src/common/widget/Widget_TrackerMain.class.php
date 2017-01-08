<?php
/**
 * Main Tracker Content Widget Class
 *
 * Copyright 2016, Franck Villaume - TrivialDev
 * http://fusionforge.org
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
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'Widget.class.php';

class Widget_TrackerMain extends Widget {

	var $content;

	function __construct() {
		$request =& HTTPRequest::instance();
		$owner_id   = (int)substr($request->get('owner'), 1);
		parent::__construct('trackermain', $owner_id, WidgetLayoutManager::OWNER_TYPE_TRACKER);
		$this->content['title'] = _('Internal Fields');
	}

	function getTitle() {
		return $this->content['title'];
	}

	function getDescription() {
		return _('Default widget where default fields are stored & displayed. Priority, Data Types, ...');
	}

	function isAvailable() {
		return isset($this->content['title']);
	}

	function getContent() {
		global $ath;
		global $ah;
		global $group_id;
		global $group;
		global $aid;
		global $atid;
		global $HTML;
		global $func;

		//manage redirect in case of missing required fields
		global $assigned_to;
		global $priority;

		$return = $HTML->listTableTop();
		$i = 0;
		$atf = new ArtifactTypeFactory ($group);
		$cells = array();
		if (forge_check_perm('tracker', $atid, 'manager') && ($func == 'detail')) {
			$tids = array();
			foreach ($atf->getArtifactTypes() as $at) {
				if (forge_check_perm ('tracker', $at->getID(), 'manager')) {
					$tids[] = $at->getID();
				}
			}

			$res = db_query_params('SELECT group_artifact_id, name
						FROM artifact_group_list
						WHERE group_artifact_id = ANY ($1)',
						array (db_int_array_to_any_clause($tids)));

			$cells[][] = html_e('strong', array(), _('Data Type')._(': '));
			$cells[][] = html_build_select_box($res, 'new_artifact_type_id', $ath->getID(), false, '', false, '', false, array('form' => 'trackerform'));
		} else {
			$cells[][] = html_e('strong', array(), _('Data Type')._(': '));
			$cells[][] = $ath->getName();
		}
		$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
		$cells = array();
		$cells[][] = html_e('strong', array(), _('Assigned to')._(': '));
		if (forge_check_perm('tracker', $atid, 'manager')) {
			if ($func == 'detail') {
				$cells[][] = $ath->technicianBox('assigned_to', $ah->getAssignedTo(), true, 'none', -1, '', false, array('form' => 'trackerform'));
			} else {
				$cells[][] = $ath->technicianBox('assigned_to', $assigned_to, true, 'none', -1, '', false, array('form' => 'trackerform'));
			}
		} else {
			$cells[][] = $ah->getAssignedRealName().' ('.$ah->getAssignedUnixName().')';
		}
		$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
		if (!$ath->usesCustomStatuses()) {
			$cells = array();
			$cells[][] = html_e('strong', array(), _('State')._(': '));
			if (forge_check_perm('tracker', $atid, 'tech')) {
				if ($func == 'detail') {
					$cells[][] = $ath->statusBox('status_id', $ah->getStatusID(), false, '', array('form' => 'trackerform'));
				} else {
					$cells[][] = $ath->statusBox('status_id', 'xzxz', false, '', array('form' => 'trackerform'));
				}
			} else {
				$cells[][] = $ah->getStatusName();
			}
			$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
		}
		$cells = array();
		$cells[][] = html_e('strong', array(), _('Priority')._(': '));
		if (forge_check_perm('tracker', $atid, 'manager')) {
			if ($func == 'detail') {
				$cells[][] = build_priority_select_box('priority', $ah->getPriority(), false, array('form' => 'trackerform'));
			} else {
				$cells[][] = build_priority_select_box('priority', $priority, false, array('form' => 'trackerform'));
			}
		} else {
			$cells[][] = $ah->getPriority();
		}
		$return .= $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i++, true)), $cells);
		$return .= $HTML->listTableBottom();
		if (forge_check_perm('tracker', $atid, 'tech')) {
			$return .= html_e('p', array('class' => 'middleRight'), html_e('input', array('form' => 'trackerform', 'type' => 'submit', 'name' => 'submit', 'value' => _('Save Changes'), 'title' => _('Save is validating the complete form'), 'onClick' => 'iefixform()')));
		}
		return $return;
	}

	function canBeRemove() {
		return false;
	}

	function canBeMinize() {
		return false;
	}

	function getCategory() {
		return _('Trackers');
	}
}
