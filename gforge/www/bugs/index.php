<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: index.php,v 1.44 2000/12/06 19:07:35 pfalcon Exp $

require('pre.php');
require('../bugs/bug_utils.php');
require('../bugs/bug_data.php');

if ($group_id) {

	$project=&project_get_object($group_id);

	switch ($func) {

		case 'addbug' : {
			include '../bugs/add_bug.php';
			break;
		}

		case 'postaddbug' : {
			//data control layer
			$bug_id=bug_data_create_bug($project,$summary,$details,$category_id,$bug_group_id,$priority,$assigned_to);
			if ($bug_id) {
				$feedback='Bug Submitted Successfully';
				include '../bugs/browse_bug.php';
			} else {
				//some error occurred
				exit_error('ERROR',$feedback);
			}
			break;
		}

		case 'postmodbug' : {
			//data control layer
			if (bug_data_handle_update ($project,$bug_id,$status_id,$priority,$category_id,
				$assigned_to,$summary,$bug_group_id,$resolution_id,$details,
				$dependent_on_task,$dependent_on_bug,$canned_response,$project_id)) {
				$feedback ='Bug Updated Successfully';
				include '../bugs/browse_bug.php';
			} else {
				//some error occurred
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'massupdate' : {
			//data control layer
			if (bug_data_mass_update ($project,$bug_id,$status_id,$priority,$bug_category_id,
				$assigned_to,$bug_group_id,$resolution_id)) {
				$feedback='Bugs Updated Successfully';
				include '../bugs/browse_bug.php';
			} else {
				//some error occurred
				exit_error('ERROR',$feedback);
			}
			break;
		}
		case 'postaddcomment' : {
			/*
				Attach a comment to the bug report
			*/
			if (bug_data_add_followup($project,$bug_id,$details)) {
				$feedback='Comment Added To Bug<br>';
				if ($project->sendAllBugUpdates()) {
					$address=$project->getNewBugAddress();
				}
				mail_followup($bug_id,$address);
				include '../bugs/browse_bug.php';
			} else {
				//some error occurred
                                exit_error('ERROR',$feedback);
			}
			break;
		}

		case 'browse' : {
			include '../bugs/browse_bug.php';
			break;
		}

		case 'detailbug' : {
			if ($project->userIsBugAdmin()) {
				include '../bugs/mod_bug.php';
			} else {
				include '../bugs/detail_bug.php';
			}
			break;
		}

		case 'modfilters' : {
			if (user_isloggedin()) {
				include '../bugs/mod_filters.php';
				break;
			} else {
				exit_not_logged_in();
			}
		}

		case 'postmodfilters' : {
			if (user_isloggedin()) {
				include '../bugs/postmod_filters.php';
				include '../bugs/mod_filters.php';
				break;
			} else {
				exit_not_logged_in();
			}
		}

		default : {
			include '../bugs/browse_bug.php';
			break;
		}

	}

} else {

	exit_no_group();

}

?>
