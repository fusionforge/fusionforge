<?php
/**
 * FusionForge Tracker Listing
 *
 * Copyright 1999-2000 (c) The SourceForge Crew
 * Copyright 2010, FusionForge Team
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfwww.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';
require_once $gfcommon.'tracker/ArtifactExtraFieldElement.class.php';

$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));

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

if ($group_id && $atid) {
//
//		UPDATING A PARTICULAR ARTIFACT TYPE
//

	session_require_perm ('tracker_admin', $group_id) ;

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
		include $gfwww.'tracker/admin/updates.php';

	} elseif (getStringFromRequest('deletetemplate')) {
		db_query_params ('UPDATE artifact_group_list SET custom_renderer=$1 WHERE group_artifact_id=$2',
				 array ('',
					$ath->getID()));
		echo db_error();
		$feedback .= _('Renderer Deleted');
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
			'clone_tracker', 'uploadtemplate', 'downloadtemplate', 'downloadcurrenttemplate', 
			'update_canned', 'update_box', 'update_opt', 'delete', 'delete_opt', 'deleteextrafield','update_type');
		$action = '';
		foreach ($actions as $a) {
			if (getStringFromRequest($a)) {
				$action = $a;
				break;
			}
		}
	}

	if ($action == 'add_extrafield') {  

		include $gfwww.'tracker/admin/form-addextrafield.php';

	} elseif ($action == 'customize_list') {

		include $gfwww.'tracker/admin/form-customizelist.php';

	} elseif ($action == 'workflow') {

		include $gfwww.'tracker/admin/form-workflow.php';

	} elseif ($action == 'workflow_roles') {

		include $gfwww.'tracker/admin/form-workflow_roles.php';

	} elseif ($action == 'add_opt' ||
			  $action == 'updownorder_opt' ||
			  $action == 'post_changes_order' ||
			  $action == 'post_changes_alphaorder') {

		include $gfwww.'tracker/admin/form-addextrafieldoption.php';

	} elseif ($action == 'copy_opt') {

		include $gfwww.'tracker/admin/form-extrafieldcopy.php';

	} elseif ($action == 'add_canned') {

		include $gfwww.'tracker/admin/form-addcanned.php';

	} elseif ($action == 'clone_tracker') {

		include $gfwww.'tracker/admin/form-clonetracker.php';

	} elseif ($action == 'uploadtemplate') {

		include $gfwww.'tracker/admin/form-uploadtemplate.php';

	} elseif ($action == 'downloadtemplate') {

		echo $ath->getRenderHTML();

	} elseif ($action == 'downloadcurrenttemplate') {

		echo $ath->getRenderHTML(array(),'DETAIL');

	} elseif ($action == 'update_canned') {

		include $gfwww.'tracker/admin/form-updatecanned.php';

	} elseif ($action == 'update_box') {

		include $gfwww.'tracker/admin/form-updateextrafield.php';

	} elseif ($action == 'update_opt') {

		include $gfwww.'tracker/admin/form-updateextrafieldelement.php';

	} elseif ($action == 'delete_opt') {

		include $gfwww.'tracker/admin/form-deleteextrafieldelement.php';

	} elseif ($action == 'delete') {

		include $gfwww.'tracker/admin/form-deletetracker.php';

	} elseif ($action == 'deleteextrafield') {

		include $gfwww.'tracker/admin/form-deleteextrafield.php';

	} elseif ($action == 'update_type') {

		include $gfwww.'tracker/admin/form-updatetracker.php';

	} else {

		include $gfwww.'tracker/admin/tracker.php';

	}

} elseif ($group_id) {
	if (getStringFromRequest('tracker_deleted')) {
		$feedback .= _('Successfully Deleted.');
	}

	include $gfwww.'tracker/admin/ind.php';

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
