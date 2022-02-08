<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012-2013,2018-2019,2021,  Franck Villaume - TrivialDev
 * Copyright 2013, French Ministry of Education
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
require_once $gfcommon.'include/utils.php';
require_once $gfcommon.'include/html.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';
require_once $gfcommon.'tracker/ArtifactsForUser.class.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';

/**
 * Widget_MyArtifacts
 *
 * Artifact assigned to or submitted by or monitored by this person
 */

class Widget_MyArtifacts extends Widget {
	var $_artifact_show;
	function __construct() {
		parent::__construct('myartifacts');
		$this->_artifact_show = session_get_user()->getPreference('my_artifacts_show');
		if($this->_artifact_show === false) {
			$this->_artifact_show = 'ASM';
			session_get_user()->setPreference('my_artifacts_show', $this->_artifact_show);
		}
	}

	function getTitle() {
		return _('My Artifacts');
	}

	function updatePreferences() {
		$show = getStringFromRequest('show');
		switch($show) {
			case 'A':
				$this->_artifact_show = 'A';
				break;
			case 'S':
				$this->_artifact_show = 'S';
				break;
			case 'M':
				$this->_artifact_show = 'M';
				break;
			case 'AS':
				$this->_artifact_show = 'AS';
				break;
			case 'AM':
				$this->_artifact_show = 'AM';
				break;
			case 'SM':
				$this->_artifact_show = 'SM';
				break;
			case 'ASM':
			default:
				$this->_artifact_show = 'ASM';
		}
		session_get_user()->setPreference('my_artifacts_show', $this->_artifact_show);
		return true;
	}

	function hasPreferences() {
		return true;
	}

	function getPreferences() {
		$optionsArray = array('A','S','M','AS','AM','SM', 'ASM');
		$textsArray = array();
		$textsArray[] = _('assigned to me'.' [A]');
		$textsArray[] = _('submitted by me'.' [S]');
		$textsArray[] = _('monitored by me'.' [M]');
		$textsArray[] = _('assigned to or submitted by me'.' [AS]');
		$textsArray[] = _('assigned to or monitored by me'.' [AM]');
		$textsArray[] = _('submitted by or monitored by me'.' [SM]');
		$textsArray[] = _('assigned to or submitted by or monitored by me'.' [ASM]');
		return _('Display artifacts:').html_build_select_box_from_arrays($optionsArray, $textsArray, 'show', $this->_artifact_show);
	}

	function getContent() {
		global $HTML;
		$user = session_get_user();
		$atf = new ArtifactsForUser($user);
		$my_artifacts = array();
		if ($this->_artifact_show == 'ASM') {
			$my_artifacts = $atf->getArtifactsFromSQLwithParams('SELECT * FROM artifact_vw av where (av.submitted_by=$1 OR av.assigned_to=$1 OR av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array(user_getid()));
		}
		if ($this->_artifact_show == 'AS') {
			$my_artifacts = $atf->getArtifactsFromSQLwithParams('SELECT * FROM artifact_vw av where (av.submitted_by=$1 OR av.assigned_to=$1) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array(user_getid()));
		}
		if ($this->_artifact_show == 'AM') {
			$my_artifacts = $atf->getArtifactsFromSQLwithParams('SELECT * FROM artifact_vw av where (av.assigned_to=$1 OR av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array(user_getid()));
		}
		if ($this->_artifact_show == 'SM') {
			$my_artifacts = $atf->getArtifactsFromSQLwithParams('SELECT * FROM artifact_vw av where (av.submitted_by=$1 OR av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array(user_getid()));
		}
		if ($this->_artifact_show== 'S') {
			$my_artifacts = $atf->getSubmittedArtifactsByGroup();
		}
		if ($this->_artifact_show== 'A') {
			$my_artifacts = $atf->getAssignedArtifactsByGroup();
		}
		if ($this->_artifact_show== 'M') {
			$my_artifacts = $atf->getArtifactsFromSQLwithParams('SELECT * FROM artifact_vw av where (av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array(user_getid()));
		}

		if (count($my_artifacts) > 0) {
			$html_my_artifacts = $HTML->listTableTop();
			$html_my_artifacts .= $this->_display_artifacts($my_artifacts);
			$html_my_artifacts .= $HTML->listTableBottom();
		} else {
			$html_my_artifacts = $HTML->warning_msg(_('You have no artifacts.'));
		}

		return $html_my_artifacts;
	}

	function _display_artifacts($list_trackers) {
		global $HTML;
		$hide_item_id = getIntFromRequest('hide_item_id', 0);
		$hide_artifact = getIntFromRequest('hide_artifact', 0);

		$html_my_artifacts = '';
		$html = '';

		$aid_old  = 0;
		$atid_old = 0;
		$group_id_old = 0;
		$count_aids = 0;
		$group_name = '';
		$tracker_name = '';

		foreach ($list_trackers as $trackers_array) {
			$atid = $trackers_array->getArtifactType()->getID();
			$group_id = $trackers_array->getArtifactType()->getGroup()->getID();

			//create group
			$group = group_get_object($group_id);
			if (!$group || !is_object($group) || $group->isError()) {
				exit_no_group();
			}
			//Check if user can view artifact
			if (forge_check_perm ('tracker', $trackers_array->getArtifactType()->getID(), 'read')) {

				//work on the tracker of the last round if there was one
				if ($atid != $atid_old && $count_aids != 0) {
					list($hide_now,$count_diff,$hide_url) = my_hide_url('artifact', $atid_old, $hide_item_id, $count_aids, $hide_artifact);
					$count_new = max(0, $count_diff);
					$cells = array();
					$cells[] = array($hide_url.
							util_make_link('/tracker/?group_id='.$group_id_old, $group_name, array('title'=>_('Browse Trackers List for this project'))).
							' - '.
							util_make_link('/tracker/?group_id='.$group_id_old.'&atid='.$atid_old, $tracker_name, array('title'=>_('Browse this tracker for this project'))).
							'    '.
							my_item_count($count_aids,$count_new), 'colspan' => 3);
					$html_my_artifacts .= $HTML->multiTableRow(array('class' => 'boxitem'), $cells).$html;
					$count_aids = 0;
					$html = '';
				}

				if ($count_aids == 0) {
					//have to call it to get at least the hide_now even if count_aids is false at this point
					$hide_now = my_hide('artifact', $atid, $hide_item_id, $hide_artifact);
				}

				$group_name   = $trackers_array->ArtifactType->Group->getPublicName();
				$tracker_name = $trackers_array->ArtifactType->getName();
				$aid          = $trackers_array->getID();
				$summary      = $trackers_array->getSummary();
				$atid_old     = $atid;
				$group_id_old = $group_id;

				// If user is assignee and submitter of an artifact, it will
				// appears 2 times in the result set.
				if($aid != $aid_old) {
					$count_aids++;
				}

				if (!$hide_now && $aid != $aid_old) {

					// Form the 'Submitted by/Assigned/Monitored_by to flag' for marking
					$AS_flag = '';
					$AS_title = '';
					if($trackers_array->getAssignedTo() == user_getid()) {
						$AS_flag .= 'A';
						$AS_title .= _('Assigned');
					}
					if ($trackers_array->getSubmittedBy() == user_getid()) {
						$AS_flag .= 'S';
						if (strlen($AS_title)) {
							$AS_title .= ' / ';
						}
						$AS_title .= _('Submitted');
					}
					if ($trackers_array->isMonitoring()) {
						$AS_flag .= 'M';
						if (strlen($AS_title)) {
							$AS_title .= ' / ';
						}
						$AS_title .= _('Monitored');
					}
					if (!strlen($AS_flag)) {
						$AS_flag .= 'N';
					}

					if($AS_flag !='N') {
						$cells = array();
						$cells[] = array($trackers_array->getPriority(), 'class' => 'priority'.$trackers_array->getPriority());
						$cells[][] = util_make_link('/tracker/?func=detail&group_id='.$group_id.'&aid='.$aid.'&atid='.$atid, stripslashes($summary), array('title' => _('Browse this artifact')));
						$cells[] = array($AS_flag, 'title' => $AS_title, 'class' => 'small');
						$html .= $HTML->multiTableRow(array(), $cells);
					}
				}
				$aid_old = $aid;
			}
		}
		//work on the tracker of the last round if there was one
		if ($atid_old != 0 && $count_aids != 0) {
			list($hide_now,$count_diff,$hide_url) = my_hide_url('artifact', $atid_old, $hide_item_id, $count_aids, $hide_artifact);
			$count_new = max(0, $count_diff);
			$cells = array();
			$cells[] = array($hide_url.
					util_make_link('/tracker/?group_id='.$group_id_old, $group_name, array('title'=>_('Browse Trackers List for this project'))).
					' - '.
					util_make_link('/tracker/?group_id='.$group_id_old.'&atid='.$atid_old, $tracker_name, array('title'=>_('Browse this tracker for this project'))).
					'    '.
					my_item_count($count_aids,$count_new), 'colspan' => 3);
			$html_my_artifacts .= $HTML->multiTableRow(array('class' => 'boxitem'), $cells).$html;
		}
		return $html_my_artifacts;
	}

	function getCategory() {
		return _('Trackers');
	}

	function getDescription() {
		return _('List artifacts you have submitted or assigned to you or you are monitoring, by project.');
	}

	function isAvailable() {
		if (!forge_get_config('use_tracker')) {
			return false;
		}
		foreach (session_get_user()->getGroups(false) as $p) {
			if ($p->usesTracker()) {
				return true;
			}
		}
		return false;
	}
}
