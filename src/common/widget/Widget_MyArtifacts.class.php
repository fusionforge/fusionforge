<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2012, Franck Villaume - TrivialDev
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

require_once('Widget.class.php');
require_once('common/tracker/ArtifactTypeFactory.class.php');
require_once('common/tracker/ArtifactsForUser.class.php');
require_once('common/tracker/Artifact.class.php');
require_once('common/tracker/ArtifactFile.class.php');
require_once('common/tracker/ArtifactType.class.php');
require_once('common/tracker/ArtifactCanned.class.php');

/**
 * Widget_MyArtifacts
 *
 * Artifact assigned to or submitted by this person
 */
class Widget_MyArtifacts extends Widget {
	var $_artifact_show;
	function Widget_MyArtifacts() {
		$this->Widget('myartifacts');
		$this->_artifact_show = UserManager::instance()->getCurrentUser()->getPreference('my_artifacts_show');
		if($this->_artifact_show === false) {
			$this->_artifact_show = 'ASM';
			UserManager::instance()->getCurrentUser()->setPreference('my_artifacts_show', $this->_artifact_show);
		}
	}

	function getTitle() {
		return _("My Artifacts");
	}

	function updatePreferences(&$request) {
		$request->valid(new Valid_String('cancel'));
		$vShow = new Valid_WhiteList('show', array('A', 'S', 'M', 'N', 'AS', 'AM', 'SM', 'ASM'));
		$vShow->required();
		if (!$request->exist('cancel')) {
			if ($request->valid($vShow)) {
				switch($request->get('show')) {
					case 'A':
						$this->_artifact_show = 'A';
						break;
					case 'S':
						$this->_artifact_show = 'S';
						break;
					case 'M':
						$this->_artifact_show = 'M';
						break;
					case 'N':
						$this->_artifact_show = 'N';
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
				UserManager::instance()->getCurrentUser()->setPreference('my_artifacts_show', $this->_artifact_show);
			}
		}
		return true;
	}

	function hasPreferences() {
		return true;
	}

	function getPreferences() {
		$prefs  = '';
		$prefs .= _("Display artifacts:").' <select name="show">';
		$prefs .= '<option value="A"  '.($this->_artifact_show === 'A'?'selected="selected"':'').'>'._("assigned to me [A]");
		$prefs .= '<option value="S"  '.($this->_artifact_show === 'S'?'selected="selected"':'').'>'._("submitted by me [S]");
		$prefs .= '<option value="M"  '.($this->_artifact_show === 'M'?'selected="selected"':'').'>'._("monitored by me [M]");
		$prefs .= '<option value="AS" '.($this->_artifact_show === 'AS'?'selected="selected"':'').'>'._("assigned to or submitted by me [AS]");
		$prefs .= '<option value="AM" '.($this->_artifact_show === 'AM'?'selected="selected"':'').'>'._("assigned to or monitored by me [AM]");
		$prefs .= '<option value="SM" '.($this->_artifact_show === 'SM'?'selected="selected"':'').'>'._("submitted by or monitored by me [SM]");
		$prefs .= '<option value="ASM" '.($this->_artifact_show === 'ASM'?'selected="selected"':'').'>'._("assigned to or submitted by or monitored by me [ASM]");
		$prefs .= '</select>';
		return $prefs;
	}

	function getContent() {
		$html_my_artifacts = '<table style="width:100%">';
		$atf = new ArtifactsForUser(@UserManager::instance()->getCurrentUser());
		if ($this->_artifact_show == 'ASM'){
			$my_artifacts = $atf->getArtifactsFromSQLWithParams('SELECT * FROM artifact_vw av where (av.submitted_by=$1 OR av.assigned_to=$1 OR av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array( UserManager::instance()->getCurrentUser()->getID()));
		}
		if ($this->_artifact_show == 'AS'){
			$my_artifacts = $atf->getArtifactsFromSQLWithParams('SELECT * FROM artifact_vw av where (av.submitted_by=$1 OR av.assigned_to=$1) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array( UserManager::instance()->getCurrentUser()->getID()));
		}
		if ($this->_artifact_show == 'AM'){
			$my_artifacts = $atf->getArtifactsFromSQLWithParams('SELECT * FROM artifact_vw av where (av.assigned_to=$1 OR av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array( UserManager::instance()->getCurrentUser()->getID()));
		}
		if ($this->_artifact_show == 'SM'){
			$my_artifacts = $atf->getArtifactsFromSQLWithParams('SELECT * FROM artifact_vw av where (av.submitted_by=$1 OR av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array( UserManager::instance()->getCurrentUser()->getID()));
		}
		if ($this->_artifact_show== 'S') {
			$my_artifacts = $atf->getSubmittedArtifactsByGroup();
		}
		if ($this->_artifact_show== 'A') {
			$my_artifacts = $atf->getAssignedArtifactsByGroup();
		}
		if ($this->_artifact_show== 'M') {
			$my_artifacts = $atf->getArtifactsFromSQLWithParams('SELECT * FROM artifact_vw av where (av.artifact_id IN (select artifact_monitor.artifact_id FROM artifact_monitor WHERE artifact_monitor.user_id = $1)) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array( UserManager::instance()->getCurrentUser()->getID()));;
		}

		if (count($my_artifacts) > 0) {
			$html_my_artifacts .= $this->_display_artifacts($my_artifacts, 1);
		} else {
			$html_my_artifacts .= '<tr><td colspan="3">' .
			    _("You have no artifacts") . '</td></tr>';
		}
		$html_my_artifacts .= '<tr><td colspan="3">'.(($this->_artifact_show == 'N' || count($my_artifacts) > 0)?' ':_("None")).'</td></tr>';
		$html_my_artifacts .= '</table>';
		return $html_my_artifacts;
	}

	function _display_artifacts($list_trackers, $print_box_begin) {
		global $HTML;
		$request = HTTPRequest::instance();
		$vItemId = new Valid_UInt('hide_item_id');
		$vItemId->required();
		if($request->valid($vItemId)) {
			$hide_item_id = $request->get('hide_item_id');
		} else {
			$hide_item_id = null;
		}

		$vArtifact = new Valid_WhiteList('hide_artifact', array(0, 1));
		$vArtifact->required();
		if($request->valid($vArtifact)) {
			$hide_artifact = $request->get('hide_artifact');
		} else {
			$hide_artifact = null;
		}

		$j = $print_box_begin;
		$html_my_artifacts = "";
		$html = "";
		$html_hdr = "";

		$aid_old  = 0;
		$atid_old = 0;
		$group_id_old = 0;
		$count_aids = 0;
		$group_name = "";
		$tracker_name = "";

		$artifact_types = array();
		$allIds=array();

		$pm = ProjectManager::instance();
		foreach ($list_trackers as $trackers_array ) {
			$atid = $trackers_array->getArtifactType()->getID();
			$group_id = $trackers_array->getArtifactType()->getGroup()->getID();

			//create group
			$group = $pm->getProject($group_id);
			if (!$group || !is_object($group) || $group->isError()) {
				exit_no_group();
			}
			//Check if user can view artifact
			if (forge_check_perm ('tracker', $trackers_array->getArtifactType()->getID(), 'read')) {

				//work on the tracker of the last round if there was one
				if ($atid != $atid_old && $count_aids != 0) {
					list($hide_now,$count_diff,$hide_url) =
						my_hide_url('artifact',$atid_old,$hide_item_id,$count_aids,$hide_artifact);
					$html_hdr =  '<tr class="boxitem"><td colspan="3">' .
						$hide_url.'<a href="/tracker/?group_id='.$group_id_old.'&amp;atid='.$atid_old.'">'.
						$group_name." - ".$tracker_name.'</a>    ';
					$count_new = max(0, $count_diff);

					$html_hdr .= my_item_count($count_aids,$count_new).'</td></tr>';
					$html_my_artifacts .= $html_hdr.$html;

					$count_aids = 0;
					$html = '';
					$j++;

				}


				if ($count_aids == 0) {
					//have to call it to get at least the hide_now even if count_aids is false at this point
					$hide_now = my_hide('artifact',$atid,$hide_item_id,$hide_artifact);
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
					if($trackers_array->getAssignedTo()== user_getid()) {
						$AS_flag .= 'A';
					}
					if ($trackers_array->getSubmittedBy()== user_getid()) {
						$AS_flag .= 'S';
					}
					if ($trackers_array->isMonitoring()) {
						$AS_flag .= 'M';
					}
					if (!strlen($AS_flag)) {
						$AS_flag .= 'N';
					}

					if($AS_flag !='N') {
						$html .= '
							<tr '. $HTML->boxGetAltRowStyle($count_aids) .'>'.
							'<td class="priority'.$trackers_array->getPriority().'">'.$trackers_array->getPriority().'</td>'.
							'<td><a href="/tracker/?func=detail&amp;group_id='.
							$group_id.'&amp;aid='.$aid.'&amp;atid='.$atid.
							'">'. stripslashes($summary).'</a></td>'.
							'<td class="small">';
						$html .= '&nbsp;'.$AS_flag.'</td></tr>';

					}
				}
				$aid_old = $aid;
			}
		}
		//work on the tracker of the last round if there was one
		if ($atid_old != 0 && $count_aids != 0) {
			list($hide_now,$count_diff,$hide_url) = my_hide_url('artifact',$atid_old,$hide_item_id,$count_aids,$hide_artifact);
			$html_hdr = ($j ? '<tr class="boxitem"><td colspan="3">' : '').
				$hide_url.'<a href="/tracker/?group_id='.$group_id_old.'&amp;atid='.$atid_old.'">'.
				$group_name." - ".$tracker_name.'</a>    ';
			$count_new = max(0, $count_diff);

			$html_hdr .= my_item_count($count_aids,$count_new).'</td></tr>';
			$html_my_artifacts .= $html_hdr.$html;
		}
		return $html_my_artifacts;
	}

	function getCategory() {
		return 'Trackers';
	}

	function getDescription() {
		return _("List artifacts you have submitted or assigned to you, by project.");
	}
}

?>
