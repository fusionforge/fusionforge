<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: patch_data.php,v 1.10 2000/11/24 12:51:44 pfalcon Exp $

/**
 *	Return the audit trail for this patch
 */
function patch_data_get_history ($patch_id) {
	$sql="select patch_history.field_name,patch_history.old_value,patch_history.date,users.user_name ".
		"FROM patch_history,users ".
		"WHERE patch_history.mod_by=users.user_id ".
		"AND patch_history.field_name <> 'details' ".
		"AND patch_id='$patch_id' ORDER BY patch_history.date DESC";
	return db_query($sql);
}

/**
 *	Return the comments attached to this patch
 */
function patch_data_get_details ($patch_id) {
	$sql="select patch_history.field_name,patch_history.old_value,patch_history.date,users.user_name ".
		"FROM patch_history,users ".
		"WHERE patch_history.mod_by=users.user_id ".
		"AND patch_history.field_name = 'details' ".
		"AND patch_id='$patch_id' ORDER BY patch_history.date DESC";
	return db_query($sql);
}

/**
 *	Return category_id's and names for this group
 */
function patch_data_get_categories($group_id) {
	global $patch_data_categories;
	if (!$patch_data_categories["$group_id"]) {
		$sql="select patch_category_id,category_name from patch_category WHERE group_id='$group_id'";
		$patch_data_categories["$group_id"]=db_query($sql);
	}
	return $patch_data_categories["$group_id"];
}

/**
 *	Return IDs and Names of patch technicians for this group
 */
function patch_data_get_technicians($group_id) {
	global $patch_data_technicians;
	if (!$patch_data_technicians["$group_id"]) {
		$sql="SELECT users.user_id,users.user_name ".
		"FROM users,user_group ".
		"WHERE users.user_id=user_group.user_id ".
		"AND user_group.patch_flags IN (1,2) ".
		"AND user_group.group_id='$group_id' ".
		"ORDER BY users.user_name ASC";
		$patch_data_technicians["$group_id"]=db_query($sql);
	}
	return $patch_data_technicians["$group_id"];
}

/**
 *	Return a list of universal patch statuses
 */
function patch_data_get_statuses() {
	global $patch_data_statuses;
	if (!$patch_data_statuses) {
		$sql="select * from patch_status";
		$patch_data_statuses=db_query($sql);
	}
	return $patch_data_statuses;
}

/**
 *	Return a particular patch status name
 */
function get_patch_status_name($string) {
	/*
		simply return status_name from patch_status
	*/
	$sql="select * from patch_status WHERE patch_status_id='$string'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'status_name');
	} else {
		return 'Error - Not Found';
	}       
}       

/**
 *	Return a patch category name
 */
function get_patch_category_name($string) {
	/*
		simply return the category_name from patch_category
	*/
	$sql="select * from patch_category WHERE patch_category_id='$string'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'category_name');
	} else {
		return 'Error - Not Found';
	}       
}       

/**
 *	Create a row of audit trail for this patch
 */
function patch_history_create($field_name,$old_value,$patch_id) {
	/*
		handle the insertion of history for these parameters
	*/
	if (!user_isloggedin()) {
		$user=100;
	} else {
		$user=user_getid();
	}

	$sql="insert into patch_history(patch_id,field_name,old_value,mod_by,date) ".
		"VALUES ('$patch_id','$field_name','$old_value','$user','".time()."')";
	return db_query($sql);
}

/*

	Add a new patch to the database

	Returns patch_id/false and $feedback

*/

function patch_data_add_patch($project,$patch_category_id,$upload_instead,$uploaded_data,$summary,$code) {
	global $feedback;

	$group_id=$project->getGroupID();

	if (!$patch_category_id) {
		$patch_category_id=100;
	}

	/*
		handle the HTTP upload - may only apply in the HTML interface
	*/
	if ($upload_instead) {
		$code = addslashes(fread( fopen($uploaded_data, 'r'), filesize($uploaded_data)));
		if ((strlen($code) > 20) && (strlen($code) < 512000)) {
			//size is fine
			$feedback .= ' Patch Uploaded ';
		} else {
			//too big or small
			$feedback .= ' ERROR - patch must be > 20 chars and < 512000 chars in length ';
			$code='';
			return false;
		}
	}

	if (!user_isloggedin()) {
		$user=100;
	} else {
		$user=user_getid();
	}

	if (!$group_id || !$summary || !$code) {
		$feedback .= ' ERROR - Missing Parameters ';
		return false;
	}

	$sql="INSERT INTO patch (close_date,group_id,patch_status_id,patch_category_id,submitted_by,assigned_to,open_date,summary,code) ".
		"VALUES ('0','$group_id','1','$patch_category_id','$user','100','". time() ."','". htmlspecialchars($summary) ."','". htmlspecialchars($code) ."')";

	db_begin();
	$result=db_query($sql);
	$patch_id=db_insertid($result,'patch','patch_id');

	if (!$result || !$patch_id) {
		$feedback .= ' ERROR - Submission failed '.db_error();
		db_rollback();
		return false;
	} else {
		$feedback .= ' Successfully Added Patch ';
		mail_followup($patch_id, $project->getNewPatchAddress());
	}
	db_commit();
	return $patch_id;

}


/*

	Add a comment to a patch and/or allow the original submittor
	to upload a new patch over the old one

	Returns true/false and $feedback

*/

function patch_data_add_comment ($project,$patch_id,$details,$upload_new,$uploaded_data ) {
	global $feedback;

	$group_id=$project->getGroupID();

	if (!$details && !$upload_new) {
		$feedback .= ' ERROR - No action taken ';
		return false;
	}

	db_begin();

	if ($details != '') {
		patch_history_create('details',htmlspecialchars($details),$patch_id);
		$feedback .= ' Comment added to patch ';
	}

	//user is uploading a new version of the patch
	if ($upload_new && user_isloggedin()) {

		//see if this user submitted this patch
		$result=db_query("SELECT * FROM patch WHERE submitted_by='".user_getid()."' AND patch_id='$patch_id'");
		if (!$result || db_numrows($result) < 1) {
			$feedback .= ' ERROR - Only the original submittor of a patch can upload a new version.
				If you submitted your patch anonymously, contact the admin of this project for instructions. '.db_error();
			db_rollback();
			return false;
		} else {
			//patch for this user was found, so update it now
	
			$code = addslashes(fread( fopen($uploaded_data, 'r'), filesize($uploaded_data)));
			if ((strlen($code) > 20) && (strlen($code) < 512000)) {
				//new patch must be > 20 bytes

				$result=db_query("UPDATE patch SET code='".htmlspecialchars($code)."' WHERE submitted_by='". user_getid() ."' AND patch_id='$patch_id'");

				//see if the update actually worked
				if (!$result || db_affected_rows($result) < 1) {
					$feedback .= ' ERROR - Patch not changed - error '.db_error();
					db_rollback();
					return false;
				} else {
					patch_history_create('Patch Code','Modified - New Version',$patch_id);
					$feedback .= ' Patch Code Updated ';
				}
			} else {
				$feedback .= ' ERROR - Patch not changed - patch must be > 20 chars and < 512000 chars in length';
				return false;
			}
		}
	} else if ($upload_new) {
		$feedback .= ' ERROR - not logged in - Couldn\'t Upload New Patch ';
		db_rollback();
		return false;
	}

	if ($project->sendAllPatchUpdates()) {
		$address=$project->getNewPatchAddress();
	}
				
	/*      
		now send the email
		it's no longer optional due to the group-level notification address
	*/
	mail_followup($patch_id,$address);
	db_commit();
	return true;
}

/*

	Mass update of patches

	Handles security

	Returns true/false and $feedback


*/

function patch_data_mass_update ($project,$patch_id,$assigned_to,$patch_status_id,$patch_category_id) {
	global $feedback;

	$group_id=$project->getGroupID();

	//patch_id is an array of patches that were checked. The other params are not arrays.
	if (!$group_id || !$patch_id || !$assigned_to || !$patch_status_id || !$patch_category_id) {
		$feedback .= ' ERROR - You need to select at least one patch and change at least one option. ';
		return false;
	}
	$count=count($patch_id);
	if ($count > 0) {
		for ($i=0; $i<$count;$i++) {

			//get this patch from the db
			$sql="SELECT * FROM patch WHERE patch_id='$patch_id[$i]' AND group_id='$group_id'";
			$result=db_query($sql);
			$group_id=db_result($result,0,'group_id');

			if ((db_numrows($result) < 1) || !($project->userIsPatchAdmin())) {
				//verify permissions
				$feedback .= ' ERROR - PERMISSION DENIED ';
				return false;
			}

			// We should assume no update is needed until otherwise
			// verified (imagine all the possible unnecessary queries
			// for a 50 item bug list!) -- G
			$update = false;
			$sql="UPDATE patch SET ";

			db_begin();

			/*
				See which fields changed during the modification
			*/
			$sql .= "patch_status_id='";
			if ( (db_result($result,0,'patch_status_id') != $patch_status_id) && ($patch_status_id != 100) ) {
				patch_history_create('patch_status_id',db_result($result,0,'patch_status_id'),$patch_id[$i]);
				$update = true;
				$sql .= $patch_status_id;
			} else {
				$sql .= db_result($result,0,'patch_status_id');
			}

			$sql .= "', patch_category_id='";
			if ( (db_result($result,0,'patch_category_id') != $patch_category_id) && ($patch_category_id != 100) ) {
				patch_history_create('patch_category_id',db_result($result,0,'patch_category_id'),$patch_id[$i]);
				$update = true;
				$sql .= $patch_category_id;
			} else {
				$sql .= db_result($result,0,'patch_category_id');
			}

			$sql .= "', assigned_to='";
			if ( (db_result($result,0,'assigned_to') != $assigned_to) && ($assigned_to != 100)) {
				patch_history_create('assigned_to',db_result($result,0,'assigned_to'),$patch_id[$i]);
				$update = true;
				$sql .= $assigned_to;
			} else {
				$sql .= db_result($result,0,'assigned_to');
			}

			$sql .= "'";

			/*
				Enter the timestamp if we are changing to non-open
			*/
			if ($patch_status_id != "1" && $patch_status_id != "100") {
				$now=time();
				$sql .= ", close_date='$now'";
				patch_history_create('close_date',db_result($result,0,'close_date'),$patch_id[$i]);
			}

			/*
				Finally, update the patch itself
			*/
			if ($update){
				$sql .= " WHERE patch_id='$patch_id[$i]'";

				$result=db_query($sql);

				if (!$result) {
					$feedback .= ' ERROR - Update Failed For Patch ID: '. $patch_id[$i] .' '.db_error();
					db_rollback();
					return false;
				} else {
					$feedback .= " Successfully Modified Patch $patch_id[$i]<BR>\n";
				}

				/*
					see if we're supposed to send all modifications to an address
				*/
				$project=project_get_object($group_id);
				if ($project->sendAllPatchUpdates()) {
					$address=$project->getNewPatchAddress();
				}

				/*
					now send the email
					it's no longer optional due to the group-level notification address
				*/
				mail_followup($patch_id[$i],$address);
				db_commit();
			} else {
				$feedback .= "Patch $patch_id[$i] was not modified<BR>\n";
				db_rollback();
			}

		}
	}
	return true;

}

/*

	Update a patch

	Return true/false and $feedback

*/

function patch_data_handle_update ($project,$patch_id,$upload_new,$uploaded_data,$code,$patch_status_id,$patch_category_id,$assigned_to,$summary,$details) {
	global $feedback;

	$group_id=$project->getGroupID();

	$sql="SELECT * FROM patch WHERE patch_id='$patch_id' AND group_id='$group_id'";

	$result=db_query($sql);

	if ((db_numrows($result) < 1) || !($project->userIsPatchAdmin())) {
		$feedback .= ' ERROR - permission denied ';
		return false;
	}
	//user is uploading a new version of the patch

	if ($upload_new) {
		$code = addslashes(fread( fopen($uploaded_data, 'r'), filesize($uploaded_data)));
		if ((strlen($code) > 20) && (strlen($code) < 512000)) {
			$codesql=", code='".htmlspecialchars($code)."' ";
			 patch_history_create('Patch Code','Modified - New Version',$patch_id);
		} else {
			$feedback .= ' Patch not changed - patch must be > 20 chars and < 512000 chars in length ';
			return false;
		}
	} else {
		$codesql='';
	}

	db_begin();

	/*
		See which fields changed during the modification
	*/
	if (db_result($result,0,'patch_status_id') != $patch_status_id) 
		{ patch_history_create('patch_status_id',db_result($result,0,'patch_status_id'),$patch_id);  }
	if (db_result($result,0,'patch_category_id') != $patch_category_id) 
		{ patch_history_create('patch_category_id',db_result($result,0,'patch_category_id'),$patch_id);  }
	if (db_result($result,0,'assigned_to') != $assigned_to) 
		{ patch_history_create('assigned_to',db_result($result,0,'assigned_to'),$patch_id);
	}
	if (db_result($result,0,'summary') != stripslashes(htmlspecialchars($summary)))
		{ patch_history_create('summary',htmlspecialchars(addslashes(db_result($result,0,'summary'))),$patch_id);  }

	/*
		Details field is handled a little differently

		Details are comments attached to patches
		They are still stored in the patch_history (audit trail)
		system, but they are not shown in the regular audit trail

		Someday, these should technically be split into their own table.

	*/
	if ($details != '') 
		{ patch_history_create('details',htmlspecialchars($details),$patch_id);  }

	/*
		Enter the timestamp if we are changing to closed
	*/
	if ($patch_status_id != "1" && $patch_status_id != "100") {
		$now=time();
		$close_date=", close_date='$now' ";
		patch_history_create('close_date',db_result($result,0,'close_date'),$patch_id);
	} else {
		$close_date='';
	}

	/*
		Finally, update the patch itself
	*/
	$sql="UPDATE patch SET patch_status_id='$patch_status_id'$close_date $codesql, patch_category_id='$patch_category_id', ".
		"assigned_to='$assigned_to', summary='".htmlspecialchars($summary)."' ".
		"WHERE patch_id='$patch_id'";

	$result=db_query($sql);

	if (!$result) {
		$feedback .= ' ERROR - update failed! '.db_error();
		db_rollback();
		return false;
	} else {
		$feedback .= " Successfully Modified Patch ";
	}

	if ($project->sendAllPatchUpdates()) {
		$address=$project->getNewPatchAddress();
	}       
				
	/*
		now send the email
		it's no longer optional due to the group-level notification address
	*/
	mail_followup($patch_id,$address);
	db_commit();
	return true;
}

function mail_followup($patch_id,$more_addresses=false) {
	global $sys_datefmt,$feedback;
	/*

		Send a message to the person who opened this patch and the person it is assigned to

		Accepts the unique id of a patch and optionally a list of additional addresses to send to

	*/
	
	$sql="SELECT patch.group_id,patch.patch_id,patch.summary,groups.unix_group_name,".
		"patch_status.status_name,patch_category.category_name, ".
		"users.email,user2.email AS assigned_to_email, ".
                "users.user_name AS submittor_name,user2.user_name AS assignee_name ".
		"FROM patch,users,users user2,groups,patch_category,patch_status ".
		"WHERE user2.user_id=patch.assigned_to ".
		"AND patch.patch_status_id=patch_status.patch_status_id ".
		"AND groups.group_id=patch.group_id ".
		"AND patch.patch_category_id=patch_category.patch_category_id ".
		"AND users.user_id=patch.submitted_by ".
		"AND patch.patch_id='$patch_id'";
	
	$result=db_query($sql);
	
	if ($result && db_numrows($result) > 0) {
		
		$body = "Patch #".db_result($result,0,"patch_id")." has been updated. ".
			"\n\nProject: ".db_result($result,0,'unix_group_name').
			"\nCategory: ".db_result($result,0,'category_name').
			"\nStatus: ".db_result($result,0,'status_name').
                        "\nSubmitted by: ".db_result($result,0,'submittor_name').
                        "\nAssigned to : ".db_result($result,0,'assignee_name').
			"\nSummary: ".util_unconvert_htmlspecialchars(db_result($result,0,'summary'));
		
		/*

			Now get the followups to this patch

		*/
		$sql="SELECT users.email,users.user_name,patch_history.date,patch_history.old_value ".
			"FROM patch_history,users ".
			"WHERE users.user_id=patch_history.mod_by ".
			"AND patch_history.field_name='details' ".
			"AND patch_history.patch_id='$patch_id'";
		$result2=db_query($sql);
		$rows=db_numrows($result2);
		if ($result2 && $rows > 0) {
			$body .= "\n\nFollow-Ups:";
			for ($i=0; $i<$rows;$i++) {
				$body .= "\n\nDate: ".date($sys_datefmt,db_result($result2,$i,'date'));
				$body .= "\nBy: ".db_result($result2,$i,'user_name');
				$body .= "\n\nComment:\n".util_unconvert_htmlspecialchars(db_result($result2,$i,'old_value'));
				$body .= "\n-------------------------------------------------------";
			}       
		}       
		
		$body .= "\n\n-------------------------------------------------------".
			"\nFor more info, visit:".
			"\n\nhttp://$GLOBALS[sys_default_domain]/patch/?func=detailpatch&patch_id=".
				db_result($result,0,'patch_id') .'&group_id='. db_result($result,0,'group_id');
				
		$subject="[Patch #".db_result($result,0,'patch_id').'] '.
			util_unconvert_htmlspecialchars(db_result($result,0,'summary'));
			
		$to=db_result($result,0,'email'). ', '. db_result($result,0,'assigned_to_email');
		
		if ($more_addresses) {
			$to .= ','.$more_addresses;
		}       
		
		$more='From: noreply@'.$GLOBALS['sys_default_domain'];
		
		mail($to,$subject,$body,$more);
		
		$feedback .= " Patch Update Sent "; //to $to ";
		
	} else {
	
		$feedback .= " Could Not Send Patch Update ";
		echo db_error();
		
	}       
}       

?>
