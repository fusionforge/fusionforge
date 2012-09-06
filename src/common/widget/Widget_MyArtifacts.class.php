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

require_once 'Widget.class.php';
require_once $gfcommon.'include/utils.php';
require_once $gfwww.'include/html.php';
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
		$optionsArray = array('A','S','M','AS','AM','SM', 'ASM');
		$textsArray = array();
		$textsArray[] = _('assigned to me'.' [A]');
		$textsArray[] = _('submitted by me'.' [S]');
		$textsArray[] = _('monitored by me'.' [M]');
		$textsArray[] = _('assigned to or submitted by me'.' [AS]');
		$textsArray[] = _('assigned to or monitored by me'.' [AM]');
		$textsArray[] = _('submitted by or monitored by me'.' [SM]');
		$textsArray[] = _('assigned to or submitted by or monitored by me'.' [ASM]');
		$prefs = _("Display artifacts:").html_build_select_box_from_arrays($optionsArray, $textsArray, "show", $this->_artifact_show);
		return $prefs;
	}

	function getContent() {

		$atf = new ArtifactsForUser(@UserManager::instance()->getCurrentUser());
		$my_artifacts = array();
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
			$html_my_artifacts = '<table style="width:100%">';
			$html_my_artifacts .= $this->_display_artifacts($my_artifacts, 1);
			$html_my_artifacts .= '</table>';
		} else {
			$html_my_artifacts = '<div class="warning">'. _("You have no artifacts") . '</div>';
		}

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
		$allIds = array();

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
						my_hide_url('artifact', $atid_old, $hide_item_id, $count_aids, $hide_artifact);
					$html_hdr =  '<tr class="boxitem"><td colspan="3">' .
						$hide_url.
						util_make_link('/tracker/?group_id='.$group_id_old, $group_name, array('class'=>'tabtitle-nw', 'title'=>_('Browse Trackers List for this project'))).
						' - '.
						util_make_link('/tracker/?group_id='.$group_id_old.'&amp;atid='.$atid_old, $tracker_name, array('class'=>'tabtitle', 'title'=>_('Browse this tracker for this project'))).
						'    ';
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
					$AS_title = '';
					if($trackers_array->getAssignedTo()== user_getid()) {
						$AS_flag .= 'A';
						$AS_title .= _('Assigned');
					}
					if ($trackers_array->getSubmittedBy()== user_getid()) {
						$AS_flag .= 'S';
						if (strlen($AS_title))
							$AS_title .= ' / ';
						$AS_title .= _('Submitted');
					}
					if ($trackers_array->isMonitoring()) {
						$AS_flag .= 'M';
						if (strlen($AS_title))
							$AS_title .= ' / ';
						$AS_title .= _('Monitored');
					}
					if (!strlen($AS_flag)) {
						$AS_flag .= 'N';
					}

					if($AS_flag !='N') {
						$html .= '
							<tr '. $HTML->boxGetAltRowStyle($count_aids) .'>'.
							'<td class="priority'.$trackers_array->getPriority().'">'.$trackers_array->getPriority().'</td>'.
							'<td>'.util_make_link('/tracker/?func=detail&amp;group_id='.$group_id.'&amp;aid='.$aid.'&amp;atid='.$atid, stripslashes($summary), array("class"=>"tabtitle", "title"=>_('Browse this artefact'))).
							'</td>'.
							'<td class="small tabtitle-ne" title="'.$AS_title.'">';
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
				$hide_url.
				util_make_link('/tracker/?group_id='.$group_id_old, $group_name, array('class'=>'tabtitle-nw', 'title'=>_('Browse Trackers List for this project'))).
				' - '.
				util_make_link('/tracker/?group_id='.$group_id_old.'&amp;atid='.$atid_old, $tracker_name, array('class'=>'tabtitle', 'title'=>_('Browse this tracker for this project'))).
				'    ';
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
		return _("List artifacts you have submitted or assigned to you or you are monitoring, by project.");
	}
}

?>
