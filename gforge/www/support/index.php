<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: index.php,v 1.21 2000/11/06 21:14:04 tperdue Exp $

require('pre.php');
require('../support/support_utils.php');
require('../support/support_data.php');

if ($group_id) {
	$project=&project_get_object($group_id);

	switch ($func) {

		case 'addsupport' : {
			include '../support/add_support.php';
			break;
		}
		case 'postaddsupport' : {
			/*

				Create a new support request

			*/
			if (support_data_create_support($project,$support_category_id,$user_email,$summary,$details)) {
				$feedback = 'Successfully Created Support Request';
				include '../support/browse_support.php';
			} else {
				//some kind of error in creation
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'postmodsupport' : {
			/*
				Modify a support request

				Used by support admins
			*/
			if (support_data_update ($project,$support_id,$priority,$support_status_id,
				$support_category_id,$assigned_to,$summary,$canned_response,$details)) {
				$feedback = 'Support Ticket(s) Updated';
				include '../support/browse_support.php';
			} else {
				//some kind of error in creation
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'postaddcomment' : {
			/*
				Attach a comment to a support request

				Used by non-admins
			*/
			if (support_data_add_comment ($project,$support_id,$details,$user_email)) {
				$feedback='Comment Added To Support Ticket';
				include '../support/browse_support.php';
			} else {
				//some kind of error in creation
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'browse' : {
			include '../support/browse_support.php';
			break;
		}
		case 'detailsupport' : {
			if ($project->userIsSupportAdmin()) {
				include '../support/mod_support.php';
			} else {
				include '../support/detail_support.php';
			}
			break;
		}
		default : {
			include '../support/browse_support.php';
			break;
		}
	}

} else {

	exit_no_group();

}

?>
