<?php
/**
 * FusionForge Tracker Listing
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfcommon.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeFactoryHtml.class.php';
require_once $gfcommon.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfcommon.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';
require_once $gfcommon.'tracker/ArtifactExtraFieldElement.class.php';
require_once $gfcommon.'tracker/Roadmap.class.php';
require_once $gfcommon.'tracker/RoadmapFactory.class.php';

$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');

$add_extrafield = '';

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}
if ($group->isError()) {
	if($group->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage(),'tracker');
	} else {
		exit_error($group->getErrorMessage(),'tracker');
	}
}

session_require_perm('tracker_admin', $group_id);

if ($group_id && $atid) {
//
//		UPDATING A PARTICULAR ARTIFACT TYPE
//

	//
	//  Create the ArtifactType object
	//
	$ath = new ArtifactTypeHtml($group,$atid);
	if (!$ath || !is_object($ath)) {
		exit_error(_('ArtifactType could not be created'),'tracker');
	}
	if ($ath->isError()) {
		exit_error($ath->getErrorMessage(),'tracker');
	}

	$next = '';
	if (getStringFromRequest('post_changes') ||
		getStringFromRequest('updownorder_opt') ||
		getStringFromRequest('post_changes_order') ||
		getStringFromRequest('post_changes_alphaorder')) {
		include $gfcommon.'tracker/actions/admin-updates.php';

	} elseif (getStringFromRequest('edittemplate')) {

		include $gfcommon.'tracker/views/form-edittemplate.php';

	} elseif (getStringFromRequest('deletetemplate')) {

		$confirm = getStringFromRequest('confirm');
		$cancel = getStringFromRequest('cancel');

		if ($cancel) {
			header ("Location: /tracker/admin/?group_id=$group_id&atid=$atid");
			exit;
		}
		if (!$confirm) {
			$ath->adminHeader(array ('title'=>_('Delete Layout Template')));
			echo $HTML->confirmBox(_('You are about to delete your current Layout Template')
				. '<br/><br/>' . _('Do you really want to do that?'),
				array('group_id' => $group_id, 'atid' => $atid, 'deletetemplate' => 1),
				array('confirm' => _('Delete'), 'cancel' => _('Cancel')));
			$ath->footer(array());
			exit;
		}

		db_query_params ('UPDATE artifact_group_list SET custom_renderer=$1 WHERE group_artifact_id=$2',
				 array ('',
					$ath->getID()));
		$feedback .= _('Layout Template Deleted');
		$next = 'add_extrafield';
	}

//
//		FORMS TO ADD/UPDATE DATABASE
//
	if ($next) {
		$action = $next;
	} else {
		$actions = array('add_extrafield', 'customize_list', 'workflow', 'workflow_roles', 'add_opt',
			'updownorder_opt', 'post_changes_order', 'post_changes_alphaorder', 'copy_opt', 'add_canned',
			'clone_tracker', 'edittemplate',
			'update_canned', 'delete_canned', 'update_box', 'update_opt', 'delete', 'delete_opt', 'deleteextrafield','update_type');
		$action = '';
		foreach ($actions as $a) {
			if (getStringFromRequest($a)) {
				$action = $a;
				break;
			}
		}
	}

	if ($action == 'add_extrafield') {

		include $gfcommon.'tracker/views/form-addextrafield.php';

	} elseif ($action == 'customize_list') {

		include $gfcommon.'tracker/views/form-customizelist.php';

	} elseif ($action == 'workflow') {

		include $gfcommon.'tracker/views/form-workflow.php';

	} elseif ($action == 'workflow_roles') {

		include $gfcommon.'tracker/views/form-workflow_roles.php';

	} elseif ($action == 'add_opt' ||
			  $action == 'updownorder_opt' ||
			  $action == 'post_changes_order' ||
			  $action == 'post_changes_alphaorder') {

		include $gfcommon.'tracker/views/form-addextrafieldoption.php';

	} elseif ($action == 'copy_opt') {

		include $gfcommon.'tracker/views/form-extrafieldcopy.php';

	} elseif ($action == 'add_canned') {

		include $gfcommon.'tracker/views/form-addcanned.php';

	} elseif ($action == 'delete_canned') {

		$confirm = getStringFromRequest('confirm');
		$cancel = getStringFromRequest('cancel');
		$id = getIntFromRequest('id');

		if ($cancel) {
			header ("Location: /tracker/admin/?group_id=$group_id&atid=$atid&add_canned=1");
			exit;
		}
		if (!$confirm) {
			$ath->adminHeader(array ('title'=>_('Delete Canned Response'), 'modal' => 1));
			echo $HTML->confirmBox(_('You are about to delete your canned response')
				. '<br/><br/>' . _('Do you really want to do that?'),
				array('group_id' => $group_id, 'atid' => $atid, 'delete_canned' => 1, 'id' => $id),
				array('confirm' => _('Delete'), 'cancel' => _('Cancel')));
			$ath->footer(array());
			exit;
		}

		$acr = $acr = new ArtifactCanned($ath,$id);
		if (!$acr || !is_object($acr)) {
			$error_msg .= _('Unable to create ArtifactCanned Object');
		} else {
			if (!$acr->delete()) {
				$error_msg .= _('Error') . _(': ') . $acr->getErrorMessage();
				$acr->clearError();
			} else {
				$feedback .= _('Canned Response Deleted');
			}
		}
		include $gfcommon.'tracker/views/form-addcanned.php';

	} elseif ($action == 'clone_tracker') {

		include $gfcommon.'tracker/views/form-clonetracker.php';

	} elseif ($action == 'update_canned') {

		include $gfcommon.'tracker/views/form-updatecanned.php';

	} elseif ($action == 'update_box') {

		include $gfcommon.'tracker/views/form-updateextrafield.php';

	} elseif ($action == 'update_opt') {

		include $gfcommon.'tracker/views/form-updateextrafieldelement.php';

	} elseif ($action == 'delete_opt') {

		include $gfcommon.'tracker/views/form-deleteextrafieldelement.php';

	} elseif ($action == 'delete') {

		include $gfcommon.'tracker/views/form-deletetracker.php';

	} elseif ($action == 'deleteextrafield') {

		include $gfcommon.'tracker/views/form-deleteextrafield.php';

	} elseif ($action == 'update_type') {

		include $gfcommon.'tracker/views/form-updatetracker.php';

	} else {

		include $gfcommon.'tracker/actions/admin-tracker.php';

	}

} elseif ($group_id) {
	if (getStringFromRequest('tracker_deleted')) {
		$feedback .= _('Successfully Deleted.');
	}

	if (getStringFromRequest('clone_tracker')) {
		$ath = new ArtifactTypeFactoryHtml($group);
		include $gfcommon.'tracker/views/form-clonetracker.php';

	} elseif (getStringFromRequest('admin_roadmap')) {
		include $gfcommon.'tracker/views/form-adminroadmap.php';

	} else {
		include $gfcommon.'tracker/actions/admin-ind.php';
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
