<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: massupdate_support.php,v 1.3 2000/11/15 21:00:26 pfalcon Exp $

//support_id is an array of patches that were checked. The other params are not arrays.
function support_data_update ($group_id,$support_id,$priority,$support_status_id,
	$support_category_id,$assigned_to,$summary,$canned_response,$details) {
	global $feedback;

	if (!$group_id || !$support_id || !$assigned_to || !$status_id || !$support_category_id || !$canned_response) {
		$feedback .= "ERROR: Missing required parameters to support_data_update<BR>";
		return false;
	}
	$count=count($support_id);
	if ($count > 0) {
		for ($i=0; $i<$count;$i++) {

			//get this patch from the db
			$sql="SELECT * FROM support WHERE support_id='$support_id[$i]'";
			$result=db_query($sql);
			$group_id=db_result($result,0,'group_id');

			if (!((db_numrows($result) > 0) && (user_ismember($group_id,'C2')))) {
				//verify permissions
				$feedback .= ' ERROR - permission denied ';
				return false;
			}

			// We should assume no update is needed until otherwise
			// verified (imagine all the possible unnecessary queries
			// for a 50 item bug list!) -- G
			$update = false;
			$sql="UPDATE support SET ";

			/*
				See which fields changed during the modification
			*/
			$sql .= "support_status_id='";
			if ( (db_result($result,0,'support_status_id') != $status_id) && ($status_id != 100) ) {
				support_data_create_history('support_status_id',db_result($result,0,'support_status_id'),$support_id[$i]);
				$update = true;
				$sql .= $status_id;
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
					$feedback .= 'Error - update failed!<BR>';
					return false;
				} else {
					$feedback .= " Successfully Modified Support Ticket $support_id[$i]<BR>\n";
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
				/*
					handle canned responses
				*/
				if ($canned_response != 100) {
					//don't care if this response is for this group - could be hacked
					$sql="SELECT * FROM support_canned_responses WHERE support_canned_id='$canned_response'";
					$result2=db_query($sql);
					if ($result2 && db_numrows($result2) > 0) {
						support_data_create_message(util_unconvert_htmlspecialchars(db_result($result2,0,'body')),$support_id[$i],user_getname().'@'.$GLOBALS['sys_users_host']);
						$feedback .= ' Canned Response Used<BR> ';
					} else {
						$feedback .= ' Unable to Use Canned Response ';
					}
				}

				mail_followup($support_id[$i],$address);

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
					support_data_create_message($details,$support_id,user_getname().'@'.$GLOBALS['sys_users_host']);
					$feedback .= " Comment added to support request $support_id[$i]<BR>";
				}

			} else {
				$feedback .= "Support ticket $support_id[$i] was not modified<BR>\n";
			}

		}
	}

	return true;
}
?>
