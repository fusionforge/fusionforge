<?php

/*

	Simple way of wrapping our SQL so it can be 
	shared among the XML outputs and the PHP web front-end

	Also abstracts controls to update data

*/

/**
 *	return a result set containing the bug_categories defined for the group
 */
function bug_data_get_categories ($group_id=false) {
	global $bug_data_categories;
	if (!$bug_data_categories["$group_id"]) {
		$sql="select bug_category_id,category_name from bug_category WHERE group_id='$group_id'";
		$bug_data_categories["$group_id"]=db_query($sql);
	}
	return $bug_data_categories["$group_id"];
}

/**
 *	return a result set containing a list of groups that this user
 *	is a bug technician or admin for
 */
function bug_data_get_user_projects ($user_id=false) {
	$sql="SELECT user_group.group_id, groups.group_name ".
		"FROM groups, user_group ".
		"WHERE groups.group_id = user_group.group_id ".
		"AND user_group.bug_flags IN (2,3) ".
		"AND user_group.user_id = '$user_id'";
	return db_query($sql);
}

/**
 *	return a result set containing a list of bug_groups defined for this group
 */
function bug_data_get_groups ($group_id=false) {
	global $bug_data_groups;
	if (!$bug_data_groups["$group_id"]) {
		$sql="select bug_group_id,group_name from bug_group WHERE group_id='$group_id'";
		$bug_data_groups["$group_id"]=db_query($sql);
	}
	return $bug_data_groups["$group_id"];
}

/**
 *	return a result set of bug_resolutions
 */
function bug_data_get_resolutions () {
	global $bug_data_resolutions;
	if (!$bug_data_resolutions) {
		$sql="select resolution_id,resolution_name from bug_resolution";
		$bug_data_resolutions=db_query($sql);
	}
	return $bug_data_resolutions;
}

/**
 *	return a result set of bug technicians for this group
 */
function bug_data_get_technicians ($group_id=false) {
	global $bug_data_technicians;
	if (!$bug_data_technicians["$group_id"]) {
		$sql="SELECT users.user_id,users.user_name ".
		"FROM users,user_group ".
		"WHERE users.user_id=user_group.user_id ".
		"AND user_group.bug_flags IN (1,2) ".
		"AND user_group.group_id='$group_id' ".
		"ORDER BY users.user_name";
		$bug_data_technicians["$group_id"]=db_query($sql);
	}
	return $bug_data_technicians["$group_id"];
}

/**
 *	return a result set of bug_statuses
 */
function bug_data_get_statuses () {
	global $bug_data_statuses;
	if (!$bug_data_statuses) {
		$sql="select * from bug_status";
		$bug_data_statuses=db_query($sql);
	}
	return $bug_data_statuses;
}

/**
 *	return a result set containing the 100 most recent, non-deleted
 *	tasks from the task manager
 */
function bug_data_get_tasks ($group_id=false) {
	/*
		Get the tasks for this project
	*/
	$sql="SELECT project_task.project_task_id,project_task.summary ".
	"FROM project_task,project_group_list ".
	"WHERE project_task.group_project_id=project_group_list.group_project_id ".
	"AND project_task.status_id <> '3' ".
	"AND project_group_list.group_id='$group_id' ORDER BY project_task_id DESC";
	return db_query($sql,100);
}

function bug_data_get_dependent_tasks ($bug_id=false) {
	/*
		Get the list of ids this is dependent on
	*/
	$sql="SELECT is_dependent_on_task_id FROM bug_task_dependencies WHERE bug_id='$bug_id'";
	return db_query($sql);
}

function bug_data_get_valid_bugs ($group_id=false,$bug_id='') {
	$sql="SELECT bug_id,summary ".
		"FROM bug ".
		"WHERE group_id='$group_id' ".
		"AND bug_id <> '$bug_id' AND bug.resolution_id <> '2' ORDER BY bug_id DESC";
	return db_query($sql,100);
}

function bug_data_get_dependent_bugs ($bug_id=false) {
	/*
		Get the list of ids this is dependent on
	*/
	$sql="SELECT is_dependent_on_bug_id FROM bug_bug_dependencies WHERE bug_id='$bug_id'";
	return db_query($sql);
}

function bug_data_get_followups ($bug_id=false) {
	$sql="select bug_history.field_name,bug_history.old_value,bug_history.date,users.user_name ".
		"FROM bug_history,users ".
		"WHERE bug_history.mod_by=users.user_id ".
		"AND bug_history.field_name = 'details' ".
		"AND bug_id='$bug_id' ORDER BY bug_history.date DESC";
	return db_query($sql);
}

function bug_data_get_history ($bug_id=false) {
	$sql="select bug_history.field_name,bug_history.old_value,bug_history.date,users.user_name ".
		"FROM bug_history,users ".
		"WHERE bug_history.mod_by=users.user_id ".
		"AND bug_history.field_name <> 'details' ".
		"AND bug_id='$bug_id' ORDER BY bug_history.date DESC";
	return db_query($sql);
}

function bug_data_add_history ($field_name,$old_value,$bug_id) {
	/*
		handle the insertion of history for these parameters
	*/
	if (!user_isloggedin()) {
		$user=100;
	} else {
		$user=user_getid();
	}

	$sql="insert into bug_history(bug_id,field_name,old_value,mod_by,date) ".
		"VALUES ('$bug_id','$field_name','$old_value','$user','".time()."')";
	return db_query($sql);
}

//
//	Handles Mass Updates
function bug_data_mass_update ($project,$bug_id,$status_id,$priority,$category_id,$assigned_to,$bug_group_id,$resolution_id) {
	global $feedback;

	$group_id=$project->getGroupID();

	//bug_id is an array of bugs that were checked. The other params are not arrays.
	if (!$group_id || !$bug_id || !$status_id || !$priority || !$category_id || !$assigned_to || !$bug_group_id || !$resolution_id) {
		$feedback .= ' ERROR - Insufficient arguments - 
		You need to select at least one bug and change at least one option. ';
		return false;
	}
	$count=count($bug_id);
	if ($count > 0) {
		for ($i=0; $i<$count;$i++) {

			//get this bug from the db
			$sql="SELECT * FROM bug WHERE bug_id='$bug_id[$i]' AND group_id='$group_id'";
			$result=db_query($sql);

			if ((db_numrows($result) < 1) || !($project->userIsBugAdmin())) {
				//verify permissions
				$feedback .= ' ERROR - Permission Denied For Bug ID: '.$bug_id[$i];
				return false;
			}
			
			// Did we have any changed values?
			$update = false;

			db_begin();

			// Start this bug's update SQL string
			$sql='UPDATE bug SET';

			/*
				See which fields changed during the modification
			*/
			$sql .= " category_id='";
			if ((db_result($result,0,'category_id') != $category_id) && ($category_id != '100')){
				 bug_data_add_history ('category_id',db_result($result,0,'category_id'),$bug_id[$i]); 
				$sql .= $category_id;
				$update = true;
			} else {
				$sql .= db_result($result,0,'category_id');
			}

			$sql .= "', status_id='";
			if ((db_result($result,0,'status_id') != $status_id) && ($status_id != '100')){ 
				bug_data_add_history ('status_id',db_result($result,0,'status_id'),$bug_id[$i]);
				$sql .= $status_id;
				$update = true;
			} else {
				$sql .= db_result($result,0,'status_id');
			}

			$sql .= "', priority='";
			if ((db_result($result,0,'priority') != $priority) && ($priority != '100')){
				bug_data_add_history ('priority',db_result($result,0,'priority'),$bug_id[$i]);  
				$sql .= $priority;
				$update = true;
			} else {
				$sql .= db_result($result,0,'priority');
			}

			$sql .= "', assigned_to='";
			if ((db_result($result,0,'assigned_to') != $assigned_to) && ($assigned_to != 100)){
				bug_data_add_history ('assigned_to',db_result($result,0,'assigned_to'),$bug_id[$i]);
				$sql .= $assigned_to;
				$update = true;
			} else {
				$sql .= db_result($result,0,'assigned_to');
			}

			$sql .= "', bug_group_id='";
			if ((db_result($result,0,'bug_group_id') != $bug_group_id) && ($bug_group_id != '100')){ 
				bug_data_add_history ('bug_group_id',db_result($result,0,'bug_group_id'),$bug_id[$i]);  
				$sql .= $bug_group_id;
				$update = true;
			} else {
				$sql .= db_result($result,0,'bug_group_id');
			}

			$sql .= "', resolution_id='";
			if ((db_result($result,0,'resolution_id') != $resolution_id) && ($resolution_id != '100')) { 
				bug_data_add_history ('resolution_id',db_result($result,0,'resolution_id'),$bug_id[$i]);
				$sql .= $resolution_id;
				$update = true;
			} else {
				$sql .= db_result($result,0,'resolution_id');
			}

			$sql .= "'";

			/*
				Enter the timestamp if we are changing to closed
			*/
			if ($status_id == "3") {

				$now=time();
				$sql .= ", close_date='$now'";
				bug_data_add_history ('close_date',db_result($result,0,'close_date'),$bug_id[$i]);

			}

			/*
				Finally, update the bug itself
			*/
			$sql .= " WHERE bug_id='$bug_id[$i]'";
			if ($update){ 
				$result=db_query($sql);

				if (!$result) {
					$feedback .= ' ERROR - UPDATE FAILED ';
					db_rollback();
					return false;
				} else {
					$feedback .= " Successfully Modified Bug ID: $bug_id[$i] ";
					mail_followup($bug_id[$i]);
					db_commit();
				}
			} else {
				//nothing changed so cancel the transaction
				db_rollback();
			}
		}
	}
	return true;
}
//
//       Handles security
//
function bug_data_handle_update ($project,$bug_id,$status_id,$priority,$category_id,
		$assigned_to,$summary,$bug_group_id,$resolution_id,$details,
		$dependent_on_task,$dependent_on_bug,$canned_response,$project_id) {
	global $feedback;

	$group_id=$project->getGroupID();

	if (!$group_id || !$bug_id || !$status_id || !$priority || !$category_id || 
		!$assigned_to || !$summary || !$bug_group_id || !$resolution_id || !$canned_response || !$project_id ) {
		//force inclusion of parameters
		$feedback .= ' ERROR - MISSING REQUIRED FIELDS ';
		return false;
	}

	//get this bug from the db
	$sql="SELECT * FROM bug WHERE bug_id='$bug_id' AND group_id='$group_id'";
	$result=db_query($sql);

	if ((db_numrows($result) < 1) || !($project->userIsBugAdmin())) {
		//verify permissions
		$feedback .= ' ERROR - PERMISSION DENIED ';
		return false;
	}

	/*
		See which fields changed during the modification
	
		And add to audit trail
	*/

	db_begin();

	if (db_result($result,0,'status_id') != $status_id)
		{ bug_data_add_history ('status_id',db_result($result,0,'status_id'),$bug_id);  }
	if (db_result($result,0,'priority') != $priority)
		{ bug_data_add_history ('priority',db_result($result,0,'priority'),$bug_id);  }
	if (db_result($result,0,'group_id') != $project_id)
		{ bug_data_add_history ('group_id',db_result($result,0,'group_id'),$bug_id);  }
	if (db_result($result,0,'category_id') != $category_id)
		{ bug_data_add_history ('category_id',db_result($result,0,'category_id'),$bug_id);  }
	if (db_result($result,0,'assigned_to') != $assigned_to)
		{ bug_data_add_history ('assigned_to',db_result($result,0,'assigned_to'),$bug_id);  }
	if (db_result($result,0,'summary') != stripslashes(htmlspecialchars($summary)))
		{ bug_data_add_history ('summary',addslashes(db_result($result,0,'summary')),$bug_id);  }
	if (db_result($result,0,'bug_group_id') != $bug_group_id)
		{ bug_data_add_history ('bug_group_id',db_result($result,0,'bug_group_id'),$bug_id);  }
	if (db_result($result,0,'resolution_id') != $resolution_id)
		{ bug_data_add_history ('resolution_id',db_result($result,0,'resolution_id'),$bug_id);  }

	/*
		Handle if canned response used
	*/
	if (!$mass_update && ($canned_response != 100)) {
		$sql="SELECT * FROM bug_canned_responses WHERE bug_canned_id='$canned_response'";
		$res3=db_query($sql);

		if ($res3 && db_numrows($res3) > 0) {
			$details = addslashes(util_unconvert_htmlspecialchars(db_result($res3,0,'body')));
			$feedback .= ' Canned Response Used ';
		} else {
			$feedback .= ' Unable to use Canned Response ';
			echo db_error();
			db_rollback();
			return false;
		}
	}

	/*
		Details field is handled a little differently

		Details are comments attached to bugs
		They are still stored in the bug_history (audit trail)
		system, but they are not shown in the regular audit trail

		Someday, these should technically be split into their own table.
	*/
	if ($details != '')
		{ bug_data_add_followup($project,$bug_id,$details); }

	/*
		Enter the timestamp if we are changing to closed
	*/
	if ($status_id == "3") {

		$now=time();
		$close_date="close_date='$now',";
		bug_data_add_history ('close_date',db_result($result,0,'close_date'),$bug_id);

	} else {

		$close_date='';

	}
	
	if (db_result($result,0,'group_id') != $project_id) { 
		/*
			Perform special handling if bug is to be be moved to other project
			Set category_id and bug_group_id to 'None' value 100
			Set assigned_to to 'None' value 100 if the person assigned to is 
			not in project that bug is moved in to.
			DELETE task and bug dependencies for bug to be moved
		*/

		//verify that user is bug admin on new project
		$res=db_query("SELECT * FROM user_group ".
			"WHERE group_id='$project_id' AND user_id='". user_getid() ."' AND bug_flags in (1,2)");
		if ($res && db_numrows($res) >0) {
			//clear out task and bug dependencies
			$dependent_on_task=array();
			$dependent_on_bug=array();
			//reset category and group as they won't exist in new project
			$category_id = '100';
			$bug_group_id = '100';
			$assigned_to = '100';

		} else {
			//user does not have correct permissions to move bug
			$feedback .= ' ERROR - You do not have permission to move this bug to that project - Leaving bug in former project ';
			$project_id=$group_id;
		}

	}
	/*
		DELETE THEN Insert the list of task dependencies
	*/
	if (!bug_data_update_dependent_tasks($dependent_on_task,$bug_id)) {
		$feedback .= ' ERROR updating dependent tasks ';
		db_rollback();
		return false;
	}

	/*
		DELETE THEN Insert the list of bug dependencies
	*/
	if (!bug_data_update_dependent_bugs($dependent_on_bug,$bug_id)) {
		$feedback .= ' ERROR updating dependent bugs ';
		db_rollback();
		return false;
	}


	/*
		Finally, update the bug itself
	*/
	$sql="UPDATE bug SET status_id='$status_id', $close_date priority='$priority', group_id='$project_id', category_id='$category_id', ".
		"assigned_to='$assigned_to', summary='".htmlspecialchars($summary)."',".
		"bug_group_id='$bug_group_id',resolution_id='$resolution_id' WHERE bug_id='$bug_id'";
	$result=db_query($sql);

	if (!$result) {
		$feedback .= " ERROR - UPDATE FAILED ";
		db_rollback();
		return false;
	} else {
		/* 
			see if we're supposed to send all modifications to an address
		*/      
		if ($project->sendAllBugUpdates()) {
			$address=$project->getNewBugAddress();
		}
		/*
			now send the email
			it's no longer optional due to the group-level notification address
		*/
		mail_followup($bug_id,$address);
		$feedback .= " Successfully Modified Bug ";
		db_commit();
		return true;
	}

}

function bug_data_insert_dependent_bugs($array,$bug_id) {
	global $feedback;
	/*
		Insert the list of dependencies
	*/
	$depend_count=count($array);
	if ($depend_count < 1) {
		//if no tasks selected, insert task "none"
		$sql="INSERT INTO bug_bug_dependencies (bug_id,is_dependent_on_bug_id) 
			VALUES ('$bug_id','100')";
		$result=db_query($sql);
		if (!$result) {
			return false;
		} else {
			return true;
		}
	} else {
		for ($i=0; $i<$depend_count; $i++) {
			if (($depend_count > 1) && ($array[$i]==100)) {
				//don't insert the row if there's more
				//than 1 item selected and this item is the "none task"
			} else {
				$sql="INSERT INTO bug_bug_dependencies (bug_id,is_dependent_on_bug_id) 
					VALUES ('$bug_id','$array[$i]')";
				//echo "\n$sql";
				$result=db_query($sql);

				if (!$result) {
					$feedback .= ' ERROR inserting dependent_bugs '.db_error();
					return false;
				}
			}
		}
		return true;
	}
}

function bug_data_update_dependent_bugs($array,$bug_id) {
	/*
		DELETE THEN Insert the list of dependencies
	*/
	$toss=db_query("DELETE FROM bug_bug_dependencies WHERE bug_id='$bug_id'");
	return bug_data_insert_dependent_bugs($array,$bug_id);
}

function bug_data_insert_dependent_tasks($array,$bug_id) {
	global $feedback;
	/*
		Insert the list of dependencies
	*/
	$depend_count=count($array);
	if ($depend_count < 1) {
		//if no tasks selected, insert task "none"
		$sql="INSERT INTO bug_task_dependencies (bug_id,is_dependent_on_task_id) 
			VALUES ('$bug_id','100')";
		$result=db_query($sql);
		if (!$result) {
			return false;
		} else {
			return true;
		}
	} else {
		for ($i=0; $i<$depend_count; $i++) {
			if (($depend_count > 1) && ($array[$i]==100)) {
				//don't insert the row if there's more
				//than 1 item selected and this item is the "none task"
			} else {
				$sql="INSERT INTO bug_task_dependencies (bug_id,is_dependent_on_task_id) 
					VALUES ('$bug_id','$array[$i]')";
				//echo "\n$sql";
				$result=db_query($sql);

				if (!$result) {
					$feedback .= ' ERROR inserting dependent_tasks '.db_error();
					return false;
				}
			}
		}
		return true;
	}
}

function bug_data_update_dependent_tasks($array,$bug_id) {
	/*
		DELETE THEN Insert the list of dependencies
	*/
	$toss=db_query("DELETE FROM bug_task_dependencies WHERE bug_id='$bug_id'");
	return bug_data_insert_dependent_tasks($array,$bug_id);
}

/**
 *	bug_data_create_bug()
 *	add a bug to this project's bug tracker
 *
 *	@param $project object
 *	@param $summary of this bug
 *	@param
 */
function bug_data_create_bug($project,$summary,$details,$category_id,$bug_group_id,$priority,$assigned_to) {
	global $feedback;
	$group_id=$project->getGroupID();

	if (!$category_id) {
		//default category
		$category_id=100;
	}
	if (!$bug_group_id) {
		//default group
		$bug_group_id=100;
	}
	if (!$assigned_to) {
		//default assignment
		$assigned_to=100;
	}
	if (!$priority) {
		//default priority
		$priority=5;
	}

	//we don't force them to be logged in to submit a bug
	if (!user_isloggedin()) {
		$user=100;
	} else {
		$user=user_getid();
	}

	if (!$group_id || !$summary || !$details) {
		$feedback .= ' ERROR - Missing Params ';
		return false;
	}

	//first check to make sure this wasn't double-submitted
	$res=db_query("SELECT * FROM bug WHERE submitted_by='$user' AND summary='$summary'");
	if ($res && db_numrows($res) > 0) {
		$feedback = ' ERROR - DOUBLE SUBMISSION. You are trying to double-submit this bug. Please don\'t do that ';
		return false;		
	}

	$sql="INSERT INTO bug (close_date,group_id,status_id,priority,category_id,".
		"submitted_by,assigned_to,date,summary,details,bug_group_id,resolution_id) ".
		"VALUES ('0','$group_id','1','$priority','$category_id','$user','$assigned_to','".time()."','".
		htmlspecialchars($summary)."','".htmlspecialchars($details)."','$bug_group_id','100')";

	db_begin();

	$result=db_query($sql);
	$bug_id=db_insertid($result,'bug','bug_id');

	if (!$bug_id) {
		$feedback .= ' ERROR getting bug_id ';
		db_rollback();
		return false;
	}

	/*
		set up the default rows in the dependency table
		both rows will be dependent on id=100
	*/
	if (!bug_data_insert_dependent_bugs($array,$bug_id)) {
		$feedback .= ' ERROR inserting dependent bugs ';
		db_rollback();
		return false;
	}
	if (!bug_data_insert_dependent_tasks($array,$bug_id)) {
		$feedback .= ' ERROR inserting dependent tasks ';
		db_rollback();
		return false;
	}

	//mail a followup
	mail_followup($bug_id,$project->getNewBugAddress());

	//now return the bug_id
	db_commit();
	return $bug_id;
}

function bug_data_get_status_name($string) {
	/*
		simply return status_name from bug_status
	*/
	$sql="select * from bug_status WHERE status_id='$string'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'status_name');
	} else {
		return 'Error - Not Found';
	}
}

function bug_data_get_category_name($string) {
	/*
		simply return the category_name from bug_category
	*/
	$sql="select * from bug_category WHERE bug_category_id='$string'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'category_name');
	} else {
		return 'Error - Not Found';
	}
}

function bug_data_get_resolution_name($resolution_id) {
	/*
		Simply return the resolution name for this id
	*/

	$sql="select * from bug_resolution WHERE resolution_id='$resolution_id'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'resolution_name');
	} else {
		return 'Error - Not Found';
	}
}

function bug_data_get_group_name($bug_group_id) {
	/*
		Simply return the resolution name for this id
	*/

	$sql="select * from bug_group WHERE bug_group_id='$bug_group_id'";
	$result=db_query($sql);
	if ($result && db_numrows($result) > 0) {
		return db_result($result,0,'group_name');
	} else {
		return 'Error - Not Found';
	}
}

function bug_data_get_canned_responses ($group_id) {
	/*
		Show defined and site-wide responses
	*/
	$sql="SELECT bug_canned_id,title,body FROM bug_canned_responses WHERE ".
		"(group_id='$group_id' OR group_id='0')";

	// return handle for use by select box
	return db_query($sql);
}

/**
 *	Attach a followup to an existing bug
 *
 *	@param $project object
 *	@param $bug_id
 *	@param $details - the comment being attached
 */
function bug_data_add_followup($project,$bug_id,$details) {
	global $feedback;
	if ($details != '') {
		if (!bug_data_add_history ('details',htmlspecialchars($details),$bug_id)) {
			return false;
		} else {
			$feedback .= "Comment added to bug<br>\n";
			return true;
		}
	} else {
		$feedback .= "No message attached<br>\n";
		return false;
	}
}

function mail_followup($bug_id,$more_addresses=false) {
	global $sys_datefmt,$feedback;
	/*
		Send a message to the person who opened this bug and the person it is assigned to
	*/

	$sql="SELECT bug.date,bug.details,bug.group_id,bug.priority,bug.bug_id,bug.summary,".
		"bug_resolution.resolution_name,bug_group.group_name,".
		"bug.date,bug_category.category_name,bug_status.status_name,users.user_name,".
		"users.email,user2.email AS assigned_to_email, groups.group_name AS project_name, ".
                "user2.user_name AS assignee_name ".
		"FROM bug,users,users user2,bug_category,bug_status,bug_group,bug_resolution,groups ".
		"WHERE user2.user_id=bug.assigned_to ".
		"AND bug.status_id=bug_status.status_id ".
		"AND bug_resolution.resolution_id=bug.resolution_id ".
		"AND bug_group.bug_group_id=bug.bug_group_id ".
		"AND bug.category_id=bug_category.bug_category_id ".
		"AND users.user_id=bug.submitted_by ".
		"AND bug.bug_id='$bug_id' ".
		"AND groups.group_id = bug.group_id";
		
	$result=db_query($sql);
	
	
	
	if ($result && db_numrows($result) > 0) {
			
			
		$body = 'Bug #'.db_result($result,0,'bug_id').', was updated on '.date($sys_datefmt,db_result($result,0,'date')).
		"\nHere is a current snapshot of the bug.".
		"\n\nProject: ".db_result($result,0,'project_name').
		"\nCategory: ".db_result($result,0,'category_name').
		"\nStatus: ".db_result($result,0,'status_name').
		"\nResolution: ".db_result($result,0,'resolution_name').
		"\nBug Group: ".db_result($result,0,'group_name').
		"\nPriority: ".db_result($result,0,'priority').
                "\nSubmitted by: ".db_result($result,0,'user_name').
                "\nAssigned to : ".db_result($result,0,'assignee_name').
		"\nSummary: ".util_unconvert_htmlspecialchars(db_result($result,0,'summary')).
		"\n\nDetails: ".util_unconvert_htmlspecialchars(db_result($result,0,'details'));
		
		$sql="SELECT users.email,users.user_name,bug_history.date,bug_history.old_value ".
			"FROM bug_history,users ".
			"WHERE users.user_id=bug_history.mod_by ".
			"AND bug_history.field_name='details' ".
			"AND bug_history.bug_id='$bug_id' ORDER BY bug_history.date DESC";

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
		$body .= "\n\nFor detailed info, follow this link:";
		$body .= "\nhttp://$GLOBALS[sys_default_domain]/bugs/?func=detailbug&bug_id=$bug_id&group_id=".
			db_result($result,0,'group_id');
		
		$subject='[Bug #'.db_result($result,0,'bug_id').'] '.
			util_unconvert_htmlspecialchars(db_result($result,0,'summary'));
		
		$to=db_result($result,0,'email').','.db_result($result,0,'assigned_to_email');
		
		if ($more_addresses) {
			$to .= ','.$more_addresses;
		}
		
		$more='From: noreply@'.$GLOBALS['sys_default_domain'];
		
		mail($to,$subject,$body,$more);
		
		$feedback .= ' Bug Update Sent '; //to '.$to;
	
	} else {
		
		$feedback .= ' Could Not Send Bug Update ';
	
	}
}

?>
