<?php
/**
  *
  * SourceForge Project/Task Manager (PM)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: pm_utils.php,v 1.83 2001/05/22 16:58:08 pfalcon Exp $
  *
  */


/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

*/

function pm_header($params) {
	global $group_id,$is_pm_page,$words,$group_project_id,$DOCUMENT_ROOT,$order;

	//required by site_project_header
	$params['group']=$group_id;
	$params['toptab']='pm';

	//only projects can use the bug tracker, and only if they have it turned on
	$project =& group_get_object($group_id);
    if (!$project || !is_object($project)) {
        exit_no_group();
    }   

	if (!$project->isProject()) {
		exit_error('Error','Only Projects Can Use The Task Manager');
	}
	if (!$project->usesPm()) {
		exit_error('Error','This Project Has Turned Off The Task Manager');
	}

	site_project_header($params);

	echo "<P><B>";

	echo "<A HREF=\"/pm/?group_id=$group_id\">Subproject List</A>";
        $need_bar=1;
	if ($group_project_id) {
		if (user_isloggedin()) {
                	if ($need_bar) echo ' | ';
			echo "<A HREF=\"/pm/task.php?group_id=$group_id&group_project_id=$group_project_id&func=addtask\">Add Task</A>";
			echo " | <A HREF=\"/pm/task.php?group_id=$group_id&group_project_id=$group_project_id&func=browse&set=my\">My Tasks</A>";
                	$need_bar=1;
		}
        	if ($need_bar) echo ' | ';
		echo "<A HREF=\"/pm/task.php?group_id=$group_id&group_project_id=$group_project_id&func=browse&set=open\">Browse Open Tasks</A>";
                $need_bar=1;
	}
       	if (user_isloggedin()) {
                if ($need_bar) echo ' | ';
		echo '<A HREF="/pm/reporting/?group_id='.$group_id.'">Reporting</A>';
                $need_bar=1;
       	}
        if ($need_bar) echo ' | ';
	echo " <A HREF=\"/pm/admin/?group_id=$group_id\">Admin</A>";
	echo "</B>";

}

function pm_footer($params) {
	site_project_footer($params);
}

function pm_status_box($name='status_id',$checked='xyxy',$text_100='None') {
	$result=pm_data_get_statuses();
	return html_build_select_box($result,$name,$checked,true,$text_100);
}

function pm_tech_select_box($name='assigned_to',$group_id=false,$checked='xzxz') {
	if (!$group_id) {
		return 'ERROR - no group_id';
	} else {
		$result=pm_data_get_technicians ($group_id);
		return html_build_select_box($result,$name,$checked);
	}
}

function pm_multiple_task_depend_box ($name='dependent_on[]',$group_project_id=false,$project_task_id=false) {
	if (!$group_project_id) {
		return 'ERROR - no group_project_id';
	} else {
		$result=pm_data_get_tasks ($group_project_id);
		if ($project_task_id) {
			$result=pm_data_get_other_tasks ($group_project_id,$project_task_id);
			//get the data so we can mark items as SELECTED
			$result2=pm_data_get_dependent_tasks ($project_task_id);
			return html_build_multiple_select_box ($result,$name,util_result_column_to_array($result2));
		} else {
			return html_build_multiple_select_box ($result,$name,array());
		}
	}
}

function pm_show_subprojects_box($name='group_project_id',$group_id=false,$group_project_id=false) {
	if (!$group_id || !$group_project_id) {
		return 'ERROR - no group_id defined';
	} else {
		$result=pm_data_get_subprojects($group_id);
		return html_build_select_box($result,$name,$group_project_id,false);
	}       
}

function pm_multiple_assigned_box ($name='assigned_to[]',$group_id=false,$project_task_id=false) {
	if (!$group_id) {
		return 'ERROR - no group_id';
	} else {
		$result=pm_data_get_technicians ($group_id);
		if ($project_task_id) {
			//get the data so we can mark items as SELECTED
			$result2=pm_data_get_assigned_to ($project_task_id);
			return html_build_multiple_select_box ($result,$name,util_result_column_to_array($result2));
		} else {
			return html_build_multiple_select_box ($result,$name,array());
		}
	}
}

function pm_show_percent_complete_box($name='percent_complete',$selected=0) {
	echo '
		<select name="'.$name.'">';
	echo '
		<option value="0">Not Started';
	for ($i=5; $i<101; $i+=5) {
		echo '
			<option value="'.$i.'"';
		if ($i==$selected) {
			echo ' SELECTED';
		}	
		echo '>'.$i.'%';
	}
	echo '
		</select>';
}

function pm_show_month_box($name,$select_month=0) {

	echo '
		<select name="'.$name.'" size="1">';
	$monthlist = array('1'=>'January',
			'2'=>'February',
			'3'=>'March',
			'4'=>'April',
			'5'=>'May',
			'6'=>'June',
			'7'=>'July',
			'8'=>'August',
			'9'=>'September',
			'10'=>'October',
			'11'=>'November',
			'12'=>'December');

	for ($i=1; $i<=count($monthlist); $i++) {
		if ($i == $select_month) {
			echo '
				<option selected value="'.$i.'">'.$monthlist[$i];
		} else {
			echo '
				<option value="'.$i.'">'.$monthlist[$i];
		}
	}
	echo '
		</SELECT>';

}

function pm_show_day_box($name,$day=1) {

	echo '
		<select name="'.$name.'" size="1">';
	for ($i=1; $i<=31; $i++) {
		if ($i == $day) {
			echo '
				<option selected value="'.$i.'">'.$i;
		} else {
			echo '
				<option value="'.$i.'">'.$i;
		}
	}
	echo '
		</select>';

}

function pm_show_year_box($name,$year=1) {

	echo '
		<select name="'.$name.'" size="1">';
	for ($i=1999; $i<=2013; $i++) {
		if ($i == $year) {
			echo '
				<option selected value="'.$i.'">'.$i;
		} else {
			echo '
				<option value="'.$i.'">'.$i;
		}
	}
	echo '
		</select>';

}

function pm_show_tasklist ($result,$offset,$set='open') {
	global $sys_datefmt,$group_id,$group_project_id,$PHP_SELF;
	/*
		Accepts a result set from the bugs table. Should include all columns from
		the table, and it should be joined to USER to get the user_name.
	*/

	$rows=db_numrows($result);

	$url = "/pm/task.php?group_id=$group_id&group_project_id=$group_project_id&func=browse&set=$set&order=";

	$title_arr=array();
	$title_arr[]='Task ID';
	$title_arr[]='Summary';
	$title_arr[]='Start Date';
	$title_arr[]='End Date';
	$title_arr[]='Percent Complete';

	$links_arr=array();
	$links_arr[]=$url.'project_task_id';
	$links_arr[]=$url.'summary';
	$links_arr[]=$url.'start_date';
	$links_arr[]=$url.'end_date';
	$links_arr[]=$url.'percent_complete';

	echo html_build_list_table_top ($title_arr,$links_arr);

	$now=time();

	for ($i=0; $i < $rows; $i++) {

		echo '
			<TR BGCOLOR="'.get_priority_color(db_result($result, $i, 'priority')).'">'.
			'<TD><A HREF="'.$PHP_SELF.'?func=detailtask'.
			'&project_task_id='.db_result($result, $i, 'project_task_id').
			'&group_id='.$group_id.
			'&group_project_id='.db_result($result, $i, 'group_project_id').'">'.
			db_result($result, $i, 'project_task_id').'</A></TD>'.
			'<TD>'.db_result($result, $i, 'summary').'</TD>'.
			'<TD>'.date('Y-m-d',db_result($result, $i, 'start_date')).'</TD>'.
			'<TD>'. (($now>db_result($result, $i, 'end_date'))?'<B>* ':'&nbsp; ') . date('Y-m-d',db_result($result, $i, 'end_date')).'</TD>'.
			'<TD>'.db_result($result, $i, 'percent_complete').'%</TD></TR>';

	}

	/*
		Show extra rows for <-- Prev / Next -->
	*/
	echo '<TR><TD COLSPAN="2">';
	if ($offset > 0) {
		echo '<A HREF="'.$PHP_SELF.'?func=browse&group_project_id='.
			$group_project_id.'&set='.$set.'&group_id='.$group_id.'&offset='.($offset-50).'">
			<B><-- Previous 50</B></A>';
	} else {
		echo '&nbsp;';
	}
	echo '</TD><TD>&nbsp;</TD><TD COLSPAN="2">';
	
	if ($rows==50) {
		echo '<A HREF="'.$PHP_SELF.'?func=browse&group_project_id='.
			$group_project_id.'&set='.$set.'&group_id='.$group_id.'&offset='.($offset+50).
			'"><B>Next 50 --></B></A>';
	} else {
		echo '&nbsp;';
	}
	echo '</TD></TR></TABLE>';
}

function pm_show_dependent_tasks ($project_task_id,$group_id,$group_project_id) {
	$sql="SELECT project_task.project_task_id,project_task.summary ".
		"FROM project_task,project_dependencies ".
		"WHERE project_task.project_task_id=project_dependencies.project_task_id ".
		"AND project_dependencies.is_dependent_on_task_id='$project_task_id'";
	$result=db_query($sql);
	$rows=db_numrows($result);

	if ($rows > 0) {
		echo '
		<H3>Tasks That Depend on This Task</H3>
		<P>';

		$title_arr=array();
		$title_arr[]='Task ID';
		$title_arr[]='Summary';

		echo html_build_list_table_top ($title_arr);

		for ($i=0; $i < $rows; $i++) {
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color ($i) .'">
				<TD><A HREF="/pm/task.php?func=detailtask&project_task_id='.
				db_result($result, $i, 'project_task_id').
				'&group_id='.$group_id.
				'&group_project_id='.$group_project_id.'">'.
				db_result($result, $i, 'project_task_id').'</TD>
				<TD>'.db_result($result, $i, 'summary').'</TD></TR>';
		}
		echo '</TABLE>';
	} else {
		echo '
			<H3>No Tasks are Dependent on This Task</H3>';
		echo db_error();
	}
}

function pm_show_task_details ($project_task_id) {
	/*
		Show the details rows from task_history
	*/
	global $sys_datefmt;
	$sql="SELECT project_history.field_name,project_history.old_value,project_history.date,users.user_name ".
		"FROM project_history,users ".
		"WHERE project_history.mod_by=users.user_id AND project_history.field_name = 'details' ".
		"AND project_task_id='$project_task_id' ORDER BY project_history.date DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);

	if ($rows > 0) {
		echo '
		<H3>Followups</H3>
		<P>';

		$title_arr=array();
		$title_arr[]='Comment';
		$title_arr[]='Date';
		$title_arr[]='By';
		
		echo html_build_list_table_top ($title_arr);

		for ($i=0; $i < $rows; $i++) {
			echo '
			<TR BGCOLOR="'. html_get_alt_row_color ($i) .'">
				<TD>'. nl2br(db_result($result, $i, 'old_value')).'</TD>
				<TD VALIGN="TOP">'.date($sys_datefmt,db_result($result, $i, 'date')).'</TD>
				<TD VALIGN="TOP">'.db_result($result, $i, 'user_name').'</TD></TR>';
		}
		echo '</TABLE>';
	} else {
		echo '
			<H3>No Comments Have Been Added</H3>';
	}
	
}

function pm_show_task_history ($project_task_id) {
	/*
		show the project_history rows that are 
		relevant to this project_task_id, excluding details
	*/
	global $sys_datefmt;
	$sql="select project_history.field_name,project_history.old_value,project_history.date,users.user_name ".
		"FROM project_history,users ".
		"WHERE project_history.mod_by=users.user_id AND ".
		"project_history.field_name <> 'details' AND project_task_id='$project_task_id' ORDER BY project_history.date DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);

	if ($rows > 0) {

		echo '
		<H3>Task Change History</H3>
		<P>';

		$title_arr=array();
		$title_arr[]='Field';
		$title_arr[]='Old Value';
		$title_arr[]='Date';
		$title_arr[]='By';

		echo html_build_list_table_top ($title_arr);

		for ($i=0; $i < $rows; $i++) {
			$field=db_result($result, $i, 'field_name');

			echo '
				<TR BGCOLOR="'. html_get_alt_row_color ($i) .'"><TD>'.$field.'</TD><TD>';

			if ($field == 'status_id') {

				echo pm_data_get_status_name(db_result($result, $i, 'old_value'));

			} else if ($field == 'start_date') {

				echo date('Y-m-d',db_result($result, $i, 'old_value'));

			} else if ($field == 'end_date') {

				echo date('Y-m-d',db_result($result, $i, 'old_value'));

			} else {

				echo db_result($result, $i, 'old_value');

			}
			echo '</TD>
				<TD>'.date($sys_datefmt,db_result($result, $i, 'date')).'</TD>
				<TD>'.db_result($result, $i, 'user_name').'</TD></TR>';
		}

		echo '
			</TABLE>';
	
	} else {
		echo '
			<H3>No Changes Have Been Made</H3>';
	}
}

?>
