<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: index.php 6506 2008-05-27 20:56:57Z aljeux $

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
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

$feedback = '';

if ($group_id && $atid) {
//
//		UPDATING A PARTICULAR ARTIFACT TYPE
//
	//	
	//  get the Group object
	//	
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_no_group();
	}

/*	$perm =& $group->getPermission( session_get_user() );

	if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
		exit_permission_denied();
	}
*/
	//
	//  Create the ArtifactType object
	//
	$ath = new ArtifactTypeHtml($group,$atid);
	if (!$ath || !is_object($ath)) {
		exit_error('Error','ArtifactType could not be created');
	}
	if ($ath->isError()) {
		exit_error(_('Error').'',$ath->getErrorMessage());
	}
	if (!$ath->userIsAdmin()) {
		exit_permission_denied();
	}

	if (getStringFromRequest('post_changes')) {
		include $gfwww.'tracker/admin/updates.php';
	} 
//
//		FORMS TO ADD/UPDATE DATABASE
//
	if (getStringFromRequest('add_extrafield')) {  

		include $gfwww.'tracker/admin/form-addextrafield.php';

	} elseif (getStringFromRequest('add_opt')) {

		include $gfwww.'tracker/admin/form-addextrafieldoption.php';

	} elseif (getStringFromRequest('copy_opt')) {

		include $gfwww.'tracker/admin/form-extrafieldcopy.php';

	} elseif (getStringFromRequest('add_canned')) {

		include $gfwww.'tracker/admin/form-addcanned.php';

	} elseif (getStringFromRequest('clone_tracker')) {

		include $gfwww.'tracker/admin/form-clonetracker.php';

	} elseif (getStringFromRequest('uploadtemplate')) {

		include $gfwww.'tracker/admin/form-uploadtemplate.php';

	} elseif (getStringFromRequest('downloadtemplate')) {

		echo $ath->getRenderHTML();

	} elseif (getStringFromRequest('deletetemplate')) {

		db_query("UPDATE artifact_group_list SET custom_renderer='' WHERE group_artifact_id='".$ath->getID()."'");
		echo db_error();
		$feedback .= 'Renderer Deleted';
		include $gfwww.'tracker/admin/form-addextrafield.php';

	} elseif (getStringFromRequest('update_canned')) {

		include $gfwww.'tracker/admin/form-updatecanned.php';

	} elseif (getStringFromRequest('update_box')) {

		include $gfwww.'tracker/admin/form-updateextrafield.php';

	} elseif (getStringFromRequest('update_opt')) {

		include $gfwww.'tracker/admin/form-updateextrafieldelement.php';

	} elseif (getStringFromRequest('delete')) {

		include $gfwww.'tracker/admin/form-deletetracker.php';

	} elseif (getStringFromRequest('deleteextrafield')) {

		include $gfwww.'tracker/admin/form-deleteextrafield.php';

	} elseif (getStringFromRequest('update_type')) {

		include $gfwww.'tracker/admin/form-updatetracker.php';

	} else {

		include $gfwww.'tracker/admin/tracker.php';

	}

} elseif ($group_id) {
	if (getStringFromRequest('tracker_deleted')) {
		$feedback .= _('Successfully Deleted.');
	}

	include $gfwww.'tracker/admin/ind.php';

} else {

	//browse for group first message
	exit_no_group();

}

?>
