<?php

/**
 *	List of possible support_categories set up for the project
 */
function support_data_get_categories ($group_id) {
	global $support_data_categories;
	if (!$support_data_categories["$group_id"]) {
		$sql="select support_category_id,category_name from support_category WHERE group_id='$group_id'";
		$support_data_categories["$group_id"]=db_query($sql);
	}
	return $support_data_categories["$group_id"];
}

/**
 *	List of people that can be assigned this support request
 */
function support_data_get_technicians ($group_id) {
	global $support_data_technicians;
	if (!$support_data_technicians["$group_id"]) {
		$sql="SELECT users.user_id,users.user_name ".
		"FROM users,user_group ".
		"WHERE users.user_id=user_group.user_id ".
		"AND user_group.support_flags IN (1,2) ".
		"AND user_group.group_id='$group_id' ".
		"ORDER BY users.user_name";
		$support_data_technicians["$group_id"]=db_query($sql);
	}
	return $support_data_technicians["$group_id"];
}

/**
 *	return a result set of canned_responses for this group and group_id=0
 */
function support_data_get_canned_responses ($group_id) {
	/*
		show defined canned responses for this project
		and the site-wide canned responses
	*/
	$sql="SELECT support_canned_id,title,body ".
		"FROM support_canned_responses ".
		"WHERE (group_id='$group_id' OR group_id='0')";
	return db_query($sql);
}

/**
 *	returns a result set of statuses
 */
function support_data_get_statuses() {
	global $support_data_statuses;
	if (!$support_data_statuses) {
		$sql="select * from support_status";
		$support_data_statuses=db_query($sql);
	}
	return $support_data_statuses;
}

/**
 *	returns a result set of audit trail for this support request
 */
function support_data_get_history ($support_id) {
	$sql="select support_history.field_name,support_history.old_value,support_history.date,users.user_name ".
		"FROM support_history,users ".
		"WHERE support_history.mod_by=users.user_id ".
		"AND support_id='$support_id' ORDER BY support_history.date DESC";
	return db_query($sql);
}

function support_data_get_status_name($string) {
	/*
		simply return status_name from support_status
	*/
	$sql="select * from support_status WHERE support_status_id='$string'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'status_name');
	} else {
		return 'Error - Not Found';
	}
}

function support_data_get_category_name($string) {
	/*
		simply return the category_name from support_category
	*/
	$sql="select * from support_category WHERE support_category_id='$string'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'category_name');
	} else {
		return 'Error - Not Found';
	}
}

function support_data_create_message ($body,$support_id,$by) {
	global $feedback;

	if (!$body || !$support_id || !$by) {
		$feedback .= 'ERROR - Missing Parameters';
		return false;
	}

	if (user_isloggedin()) {
		$body="Logged In: YES \nuser_id=". user_getid() ."\nBrowser: ". $GLOBALS['HTTP_USER_AGENT'] ."\n\n".$body;
	} else {
		$body="Logged In: NO \nBrowser: ". $GLOBALS['HTTP_USER_AGENT'] ."\n\n".$body;
	}

	$sql="insert into support_messages(support_id,body,from_email,date) ".
		"VALUES ('$support_id','". htmlspecialchars($body). "','$by','".time()."')";
	return db_query($sql);
}

function support_data_create_history ($field_name,$old_value,$support_id) {
	/*
		handle the insertion of history for these parameters
	*/
	if (!user_isloggedin()) {
		$user=100;
	} else {
		$user=user_getid();
	}

	$sql="insert into support_history(support_id,field_name,old_value,mod_by,date) ".
		"VALUES ('$support_id','$field_name','$old_value','$user','".time()."')";
	return db_query($sql);
}

function support_data_get_messages ($support_id) {
	$sql="select * ".
		"FROM support_messages ".
		"WHERE support_id='$support_id' ORDER BY date DESC";
	return db_query($sql);
}

/*

	Add a comment to a support request

	Different than creating a new message

	This function is used by non-admins 
		to add a followup to an existing request

*/
function support_data_add_comment ($project,$support_id,$details,$user_email) {
	global $feedback;

	if (!user_isloggedin()) {
		if (!$user_email) {
			//force them to fill in user_email if they aren't logged in
			$feedback .= 'Go Back and fill in the user_email address or login';
			return false;
		}
	} else {
		//use their user_name if they are logged in
	  //$user_email=user_getname().'@'.$GLOBALS['sys_users_host'];
		$user = user_get_object_by_name (user_getname ());
		$user_email = $user->getEmail ();
	}

	if ($project && $support_id && $details) {
		//create the first message for this ticket
		if (!support_data_create_message($details,$support_id,$user_email)) {
			return false;
		} else {
			$feedback .= 'Comment added to support request';
			if ($project->sendAllSupportUpdates()) {
				$address=$project->getNewSupportAddress();
			}       
			mail_followup($support_id,$address);
			return true;
		}
	}
}

/*

	Create a new support request

	Returns true/false and $feedback

*/

function support_data_create_support ($project,$support_category_id,$user_email,$summary,$details) {
	global $feedback;

	$group_id=$project->getGroupID();

	if (!$group_id) {
		$feedback .= 'ERROR - Missing Group Id';
		return false;
	}

	if (!$support_category_id) {
		$support_category_id=100;
	}

	if (!user_isloggedin()) {
		$user=100;
		if (!$user_email) {
			//force them to fill in user_email if they aren't logged in
			$feedback .= 'ERROR - Go Back and fill in the user_email address or login';
			return false;
		}
	} else {
	        $user=user_getid();
		//use their user_name if they are logged in
                //$user_email=user_getname().'@'.$GLOBALS['sys_users_host'];
		$myuser = user_get_object_by_name (user_getname ());
		$user_email = $myuser->getEmail ();
	}

	if (!$group_id || !$summary || !$details) {
		$feedback .= 'ERROR - Go Back and fill in all the information requested';
		return false;
	}

	//make sure we aren't double-submitting this code
	$res=db_query("SELECT * FROM support WHERE submitted_by='$user' AND summary='". htmlspecialchars ($summary) ."'");
	if ($res && db_numrows($res) > 0) {
		$feedback .= 'ERROR - DOUBLE SUBMISSION. You are trying to double-submit this request. Please do not double-submit requests.';
		return false;
	}

	//now insert the request
	$sql="INSERT INTO support (priority,close_date,group_id,support_status_id,support_category_id,submitted_by,assigned_to,open_date,summary) ".
		"VALUES ('5','0','$group_id','1','$support_category_id','$user','100','". time() ."','". htmlspecialchars($summary) ."')";

	db_begin();
	$result=db_query($sql);
	$support_id=db_insertid($result,'support','support_id');

	if (!$result || !$support_id) {
		$feedback .= ' ERROR - Data insertion failed '.db_error();
		db_rollback();
		return false;
	} else {

		if ($details != '') {
			//create the first message for this ticket
			if (!support_data_create_message($details,$support_id,$user_email)) {
				$feedback .= ' Comment Failed ';
				db_rollback();
				return false;
			} else {
				$feedback .= ' Comment added to support request ';
			}
		}

		$feedback .= ' Successfully Added Support Request ';
	}
	mail_followup($support_id, $project->getNewSupportAddress());
	db_commit();
	return $support_id;
}

/*

	Update support requests

	Return true/false and $feedback

	Handles security

*/

//support_id is an array of patches that were checked. The other params are not arrays.
function support_data_update ($project,$support_id,$priority,$support_status_id,
	$support_category_id,$assigned_to,$summary,$canned_response,$details) {
	global $feedback;

	$group_id=$project->getGroupID();

	if(!is_Array($support_id)){ 
		$support_id = array($support_id); 
	}

	if (!$group_id || !$support_id || !$assigned_to || !$support_status_id || !$support_category_id || !$canned_response) {
		$feedback .= " ERROR: Missing required parameters to support_data_update ";
		return false;
	}
	$count=count($support_id);

	if ($count > 0) {
		for ($i=0; $i<$count;$i++) {

			//get this patch from the db
			$sql="SELECT * FROM support WHERE support_id='$support_id[$i]' AND group_id='$group_id'";
			$result=db_query($sql);
			$group_id=db_result($result,0,'group_id');

			if ((db_numrows($result) < 1) || !($project->userIsSupportAdmin())) {
			//verify permissions
				$feedback .= 'ERROR - permission denied';
				return false;
			}

			// We should assume no update is needed until otherwise
			// verified (imagine all the possible unnecessary queries
			// for a 50 item bug list!) -- G
			$update = false;
			$sql="UPDATE support SET ";

			db_begin();

			/*
				See which fields changed during the modification
			*/
			$sql .= "support_status_id='";
			if ( (db_result($result,0,'support_status_id') != $support_status_id) && ($support_status_id != 100) ) {
				support_data_create_history('support_status_id',db_result($result,0,'support_status_id'),$support_id[$i]);
				$update = true;
				$sql .= $support_status_id;
			} else {
				$sql .= db_result($result,0,'support_status_id');
			}

			$sql .= "', support_category_id='";
			if ( (db_result($result,0,'support_category_id') != $support_category_id) && ($support_category_id != 100) ) { 
				support_data_create_history('support_category_id',db_result($result,0,'support_category_id'),$support_id[$i]);
				$update = true;
				$sql .= $support_category_id;
			} else {
				$sql .= db_result($result,0,'support_category_id');
			}

			$sql .= "', priority='";
			if ( (db_result($result,0,'priority') != $priority) && ($priority != 100)) {
				support_data_create_history('priority',db_result($result,0,'priority'),$support_id[$i]);
				$update = true;
				$sql .= $priority;
			} else {
				$sql .= db_result($result,0,'priority');
			}

			$sql .= "', assigned_to='";
			if ( (db_result($result,0,'assigned_to') != $assigned_to) && ($assigned_to != 100)) { 
				support_data_create_history('assigned_to',db_result($result,0,'assigned_to'),$support_id[$i]);
				$update = true;
				$sql .= $assigned_to;
			} else {
				$sql .= db_result($result,0,'assigned_to');
			}

			$sql .= "'";
			if ( (db_result($result,0,'summary') != stripslashes(htmlspecialchars($summary))) && ($summary != '') ) {
				support_data_create_history('summary',htmlspecialchars(addslashes(db_result($result,0,'summary'))),$support_id[$i]);
				$update = true;
				$sql .= ", summary='".htmlspecialchars($summary)."'";
			}

			/*
				Enter the timestamp if we are changing to closed
			*/
			if ($support_status_id != "1") {
				$now=time();
				$sql .= ", close_date='$now'";
				support_data_create_history('close_date',db_result($result,0,'close_date'),$support_id[$i]);
			}

			/*
				Finally, update the patch itself
			*/
			if ($update){
				$sql .= " WHERE support_id='$support_id[$i]'";

				$result=db_query($sql);

				if (!$result) {
					$feedback .= ' Error - update failed! ';
					db_rollback();
					return false;
				} else {
					$feedback .= " Successfully Modified Support Ticket $support_id[$i] ";
				}
			}

			/*
				Details field is handled a little differently

				Details are comments attached to bugs
				They are still stored in the bug_history (audit 
				trail) system, but they are not shown in the
				 regular audit trail

				Someday, these should technically be split into
				 their own table.
			*/
			if ($details != '') {
				//create the first message for this ticket
			        $myuser = user_get_object_by_name (user_getname ());
				$user_email = $myuser->getEmail ();

				if (!support_data_create_message($details,$support_id[$i],$user_email)) {
					db_rollback();
					return false;
				} else {
					$feedback .= ' Comment added to support request '.$support_id[$i].' ';
					$send_message=true;
				}
			}

			/*
				handle canned responses
			*/
			if ($canned_response != 100) {
				//don't care if this response is for this group - could be hacked
				$sql="SELECT * FROM support_canned_responses WHERE support_canned_id='$canned_response'";
				$result2=db_query($sql);
			        $myuser = user_get_object_by_name (user_getname ());
				$user_email = $myuser->getEmail ();
				if ($result2 && db_numrows($result2) > 0) {
					if (!support_data_create_message(util_unconvert_htmlspecialchars(db_result($result2,0,'body')),$support_id[$i],$user_email)) {
						db_rollback();
						return false;
					} else {
						$feedback .= ' Canned Response Used For Support Request ID ' .$support_id[$i] . '';
						$send_message=true;
					}
				} else {
					$feedback .= ' Unable to Use Canned Response ';
				}
			}

			if ($update || $send_message){
				/*
					see if we're supposed to send all modifications to an address
				*/
				$project=project_get_object($group_id);
				if ($project->sendAllSupportUpdates()) {
					$address=$project->getNewSupportAddress();
				}       

				/*
					now send the email
					it's no longer optional due to the group-level notification address
				*/

				mail_followup($support_id[$i],$address);
				db_commit();
			} else {
				//nothing changed, so cancel the transaction
				db_rollback();
			}
		}
	}

	return true;
}

function mail_followup($support_id,$more_addresses=false) {
	global $sys_datefmt,$feedback;
	/*
		Send a message to the person who opened this support and the person it is assigned to
	*/

	$sql="SELECT support.priority,support.group_id,support.support_id,support.summary,".
		"support_status.status_name,support_category.category_name,support.open_date, ".
		"users.email,user2.email AS assigned_to_email ".
		"FROM support,users,users user2,support_status,support_category ".
		"WHERE user2.user_id=support.assigned_to ".
		"AND support.support_status_id=support_status.support_status_id ".
		"AND support.support_category_id=support_category.support_category_id ".
		"AND users.user_id=support.submitted_by ".
		"AND support.support_id='$support_id'";
		
	$result=db_query($sql);
	
	if ($result && db_numrows($result) > 0) {
		/*
			Set up the body
		*/
		$body = "\n\nSupport Request #".db_result($result,0,'support_id').", was updated on ".
				date($sys_datefmt,db_result($result,0,'open_date')). 
			"\nYou can respond by visiting: ".
			"\nhttp://".$GLOBALS['sys_default_domain']."/support/?func=detailsupport&support_id=".
				db_result($result,0,"support_id")."&group_id=".db_result($result,0,"group_id").
			"\n\nCategory: ".db_result($result,0,'category_name').
			"\nStatus: ".db_result($result,0,'status_name').
			"\nPriority: ".db_result($result,0,'priority').
			"\nSummary: ".util_unconvert_htmlspecialchars(db_result($result,0,'summary'));
			
			
		$subject="[ ".db_result($result,0,"support_id")." ] ".
			util_unconvert_htmlspecialchars(db_result($result,0,"summary"));
			
		/*      
			get all the email addresses that have dealt with this request
		*/

		$email_res=db_query("SELECT distinct from_email FROM support_messages WHERE support_id='$support_id'");
		$rows=db_numrows($email_res);
		if ($email_res && $rows > 0) {
			$mail_arr=result_column_to_array($email_res,0);
			$emails=implode($mail_arr,', ');
		}       
		if ($more_addresses) {
			$emails .= ','.$more_addresses;
		}       
		
		/*
			Now include the two most recent emails
		*/
		$sql="select * ".
			"FROM support_messages ".
			"WHERE support_id='$support_id' ORDER BY date DESC";
		$result2=db_query($sql,2);
		$rows=db_numrows($result2);
		if ($result && $rows > 0) {
			for ($i=0; $i<$rows; $i++) {
				//get the first part of the email address
				$email_arr=explode('@',db_result($result2,$i,'from_email'));
				
				$body .= "\n\nBy: ". $email_arr[0] .
				"\nDate: ".date($sys_datefmt,db_result($result2,$i,'date')).
				"\n\nMessage:".
				"\n".util_unconvert_htmlspecialchars(db_result($result2,$i,'body')).
				"\n\n----------------------------------------------------------------------";
			}       
			$body .= "\nYou can respond by visiting: ".
			"\nhttp://$GLOBALS[HTTP_HOST]/support/?func=detailsupport&support_id=".
				db_result($result,0,'support_id')."&group_id=".db_result($result,0,'group_id');
		}	       
		
		//attach the headers to the body
		
		$body = "To: noreply@$GLOBALS[HTTP_HOST]".
			"\nBCC: $emails".
			"\nSubject: $subject".
			$body;
		/*      
			Send the email
		*/
		exec ("/bin/echo \"". util_prep_string_for_sendmail($body)
			."\" | /usr/sbin/sendmail -fnoreply@$GLOBALS[HTTP_HOST] -t &");
		$feedback .= " Support Request Update Emailed ";
		
	} else {
	
		$feedback .= " Could Not Send Support Request Update ";
		echo db_error();
		
	}       
}       

?>
