<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require_once('../../env.inc.php');
require_once('pre.php');
require_once('common/tracker/Artifact.class.php');
require_once('common/tracker/ArtifactFile.class.php');
require_once('www/tracker/include/ArtifactFileHtml.class.php');
require_once('common/tracker/ArtifactType.class.php');
require_once('common/tracker/ArtifactTypeFactory.class.php');
require_once('www/tracker/include/ArtifactTypeHtml.class.php');
require_once('www/tracker/include/ArtifactHtml.class.php');
require_once('common/tracker/ArtifactCanned.class.php');
require_once('common/tracker/ArtifactExtraField.class.php');
require_once('common/tracker/ArtifactExtraFieldElement.class.php');

$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');

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
		include('updates.php');
	} 
//
//		FORMS TO ADD/UPDATE DATABASE
//
	if (getStringFromRequest('add_extrafield')) {  

		include ('form-addextrafield.php');

	} elseif (getStringFromRequest('add_opt')) {

		include ('form-addextrafieldoption.php');

	} elseif (getStringFromRequest('copy_opt')) {

		include ('form-extrafieldcopy.php');

	} elseif (getStringFromRequest('add_canned')) {

		include ('form-addcanned.php');

	} elseif (getStringFromRequest('clone_tracker')) {

		include ('form-clonetracker.php');

	} elseif (getStringFromRequest('uploadtemplate')) {

		include ('form-uploadtemplate.php');

	} elseif (getStringFromRequest('downloadtemplate')) {

		echo $ath->getRenderHTML();

	} elseif (getStringFromRequest('deletetemplate')) {

		db_query("UPDATE artifact_group_list SET custom_renderer='' WHERE group_artifact_id='".$ath->getID()."'");
		echo db_error();
		$feedback .= 'Renderer Deleted';
		include ('form-addextrafield.php');

	} elseif (getStringFromRequest('update_canned')) {

		include ('form-updatecanned.php');

	} elseif (getStringFromRequest('update_box')) {

		include ('form-updateextrafield.php');

	} elseif (getStringFromRequest('update_opt')) {

		include ('form-updateextrafieldelement.php');

	} elseif (getStringFromRequest('delete')) {

		include ('form-deletetracker.php');

	} elseif (getStringFromRequest('deleteextrafield')) {

		include ('form-deleteextrafield.php');

	} elseif (getStringFromRequest('update_type')) {

		include ('form-updatetracker.php');

	} else {

		include ('tracker.php');

	}

} elseif ($group_id) {
	if (getStringFromRequest('tracker_deleted')) {
		$feedback .= _('Successfully Deleted.');
	}

	include ('ind.php');

} else {

	//browse for group first message
	exit_no_group();

}

?>
