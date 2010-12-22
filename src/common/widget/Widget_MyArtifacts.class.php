<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
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
			$this->_artifact_show = 'AS';
			UserManager::instance()->getCurrentUser()->setPreference('my_artifacts_show', $this->_artifact_show);
		}
	}

	function getTitle() {
		return _("My Artifacts");
	}

	function updatePreferences(&$request) {
		$request->valid(new Valid_String('cancel'));
		$vShow = new Valid_WhiteList('show', array('A', 'S', 'N', 'AS'));
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
					case 'N':
						$this->_artifact_show = 'N';
						break;
					case 'AS':
					default:
						$this->_artifact_show = 'AS';
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
		$prefs .= '<option value="AS" '.($this->_artifact_show === 'AS'?'selected="selected"':'').'>'._("assigned to or submitted by me [AS]");
		$prefs .= '</select>';
		return $prefs;

	}

	function isAjax() {
		return true;
	}

	function getContent() {
		$html_my_artifacts = '<table style="width:100%">';
		$atf = new ArtifactsForUser(@UserManager::instance()->getCurrentUser());
		$assigned = $atf->getAssignedArtifactsByGroup();
		$submitted = $atf->getSubmittedArtifactsByGroup();
		$all = $atf->getArtifactsFromSQLWithParams('SELECT * FROM artifact_vw av where (av.submitted_by=$1 OR  av.assigned_to=$1) AND av.status_id=1 ORDER BY av.group_artifact_id, av.artifact_id DESC',array( UserManager::instance()->getCurrentUser()->getID()));
		if($this->_artifact_show == 'AS'){
			$my_artifacts=$all;
		}
		if($this->_artifact_show== 'S') {
			$my_artifacts=$submitted;
		}
		if($this->_artifact_show== 'A') {
			$my_artifacts=$assigned;
		}

		if (count($my_artifacts) > 0) {
			$html_my_artifacts .= $this->_display_artifacts($my_artifacts, 0);
		} else {
			$html_my_artifacts .= _("You have no artifacts");
		}
		$html_my_artifacts .= '<TR><TD COLSPAN="3">'.(($this->_artifact_show == 'N' || count($my_artifacts) > 0)?'&nbsp;':_("None")).'</TD></TR>';
		$html_my_artifacts .= '</table>';
		return $html_my_artifacts;
	}

	function _display_artifacts($list_trackers, $print_box_begin) {
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

			// {{{ check permissions
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
						$hide_url.'<A HREF="/tracker/?group_id='.$group_id_old.'&atid='.$atid_old.'">'.
						$group_name." - ".$tracker_name.'</A>&nbsp;&nbsp;&nbsp;&nbsp;';
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

					// Form the 'Submitted by/Assigned to flag' for marking
					if($trackers_array->getAssignedTo()== user_getid())
					{
						if($trackers_array->getSubmittedBy()== user_getid()) {
							$AS_flag = 'AS';
						}
						else {
							$AS_flag='A';
						}
					}
					elseif($trackers_array->getSubmittedBy()== user_getid()){
						$AS_flag='S';
					}
					else {
						$AS_flag='N';
					}

					if($AS_flag !='N') {
						if ($count_aids % 2 == 0) {
							$class="bgcolor-white";
						}
						else {
							$class="bgcolor-grey";
						}

						$html .= '
							<TR class="'.$class.'">'.
							'<TD class="priority'.$trackers_array->getPriority().'">'.$trackers_array->getPriority().'</TD>'.
							'<TD><A HREF="/tracker/?func=detail&group_id='.
							$group_id.'&aid='.$aid.'&atid='.$atid.
							'">'. stripslashes($summary).'</A></TD>'.
							'<TD class="small">';
						$html .= '&nbsp;'.$AS_flag.'</TD></TR>';

					}
				}
				$aid_old = $aid;
			}
		}
		//work on the tracker of the last round if there was one
		if ($atid_old != 0 && $count_aids != 0) {
			list($hide_now,$count_diff,$hide_url) = my_hide_url('artifact',$atid_old,$hide_item_id,$count_aids,$hide_artifact);
			$html_hdr = ($j ? '<tr class="boxitem"><td colspan="3">' : '').
				$hide_url.'<A HREF="/tracker/?group_id='.$group_id_old.'&atid='.$atid_old.'">'.
				$group_name." - ".$tracker_name.'</A>&nbsp;&nbsp;&nbsp;&nbsp;';
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

	function getAjaxUrl($owner_id, $owner_type) {
		$request =& HTTPRequest::instance();
		$ajax_url = parent::getAjaxUrl($owner_id, $owner_type);
		if ($request->exist('hide_item_id') || $request->exist('hide_artifact')) {
			$ajax_url .= '&hide_item_id=' . $request->get('hide_item_id') . '&hide_artifact=' . $request->get('hide_artifact');
		}
		return $ajax_url;
	}
}

?>
