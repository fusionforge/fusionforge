<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require_once('pre.php');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/ArtifactFile.class');
require_once('www/tracker/include/ArtifactFileHtml.class');
require_once('common/tracker/ArtifactType.class');
require_once('common/tracker/ArtifactTypeFactory.class');
require_once('www/tracker/include/ArtifactTypeHtml.class');
require_once('www/tracker/include/ArtifactHtml.class');
require_once('common/tracker/ArtifactCanned.class');
require_once('common/tracker/ArtifactExtraField.class');
require_once('common/tracker/ArtifactExtraFieldElement.class');

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
		exit_error($Language->getText('general','error').'',$ath->getErrorMessage());
	}
	if (!$ath->userIsAdmin()) {
		exit_permission_denied();
	}

	if ($post_changes) {
		include('updates.php');
	} 
//
//		FORMS TO ADD/UPDATE DATABASE
//
	if ($add_extrafield) {  

		include ('form-addextrafield.php');

	} elseif ($add_opt) {

		include ('form-addextrafieldoption.php');

	} elseif ($copy_opt) {

		include ('form-extrafieldcopy.php');

	} elseif ($add_canned) {

		include ('form-addcanned.php');

	} elseif ($uploadtemplate) {

		include ('form-uploadtemplate.php');

	} elseif ($downloadtemplate) {

		echo $ath->getRenderHTML();

	} elseif ($deletetemplate) {

		db_query("UPDATE artifact_group_list SET custom_renderer='' WHERE group_artifact_id='".$ath->getID()."'");
		echo db_error();
		$feedback .= 'Renderer Deleted';
		include ('form-addextrafield.php');

	} elseif ($update_canned) {

		include ('form-updatecanned.php');

	} elseif ($update_box) {

		include ('form-updateextrafield.php');

	} elseif ($update_opt) {

		include ('form-updateextrafieldelement.php');

	} elseif ($delete) {

		include ('form-deletetracker.php');

	} elseif ($deleteextrafield) {

		include ('form-deleteextrafield.php');

	} elseif ($update_type) {

		include ('form-updatetracker.php');

	} else {

		include ('tracker.php');

	}

} elseif ($group_id) {

	include ('ind.php');

} else {

	//browse for group first message
	exit_no_group();

}

?>
