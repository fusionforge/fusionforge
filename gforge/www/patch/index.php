<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: index.php,v 1.9 2000/11/06 21:14:04 tperdue Exp $

require('pre.php');
require('../patch/patch_utils.php');
require('../patch/patch_data.php');

if ($group_id) {

	$project=&project_get_object($group_id);

	switch ($func) {
		case 'addpatch' : {
			include '../patch/add_patch.php';
			break;
		}
		case 'postaddpatch' : {
			if (patch_data_add_patch($project,$patch_category_id,$upload_instead,$uploaded_data,$summary,$code)) {
				$feedback='Successfully Added Patch';
				include '../patch/browse_patch.php';
			} else {
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'postmodpatch' : {
			if (patch_data_handle_update ($project,$patch_id,$upload_new,$uploaded_data,$code,
				$patch_status_id,$patch_category_id,$assigned_to,$summary,$details)) {
				$feedback='Patch Updated Successfully';
				include '../patch/browse_patch.php';
			} else {
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'postaddcomment' : {
			if (patch_data_add_comment ($project,$patch_id,$details,$upload_new,$uploaded_data)) {
				$feedback='Comment Added To Patch';
				include '../patch/browse_patch.php';
			} else {
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'browse' : {
			include '../patch/browse_patch.php';
			break;
		}
		case 'detailpatch' : {
			if ($project->userIsPatchAdmin()) {
				include '../patch/mod_patch.php';
			} else {
				include '../patch/detail_patch.php';
			}
			break;
		}
		case 'massupdate' : {
			if (patch_data_mass_update ($project,$patch_id,$assigned_to,$patch_status_id,$patch_category_id)) {
				$feedback='Patches Updated Successfully';
				include '../patch/browse_patch.php';
			} else {
				exit_error('ERROR',$feedback);
			}
			break;
		}
		default : {
			include '../patch/browse_patch.php';
			break;
		}
	}

} else {

	exit_no_group();

}

?>
