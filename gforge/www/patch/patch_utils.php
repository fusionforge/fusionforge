<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: patch_utils.php,v 1.41 2000/11/13 19:50:43 pfalcon Exp $

/*

	Patch Manager 
	By Tim Perdue, Sourceforge, Feb 2000
	Heavy Rewrite Tim Perdue, April, 2000

*/

function patch_header($params) {
	global $group_id,$DOCUMENT_ROOT;

	$params['toptab']='patch';
	$params['group']=$group_id;

	//only projects can use the bug tracker, and only if they have it turned on
	$project=project_get_object($group_id);

	if (!$project->isProject()) {
		exit_error('Error','Only Projects Can Use The Patch Manager');
	}
	if (!$project->usesPatch()) {
		exit_error('Error','This Project Has Turned Off The Patch Manager');
	}


	site_project_header($params);

	echo '<P><B><A HREF="/patch/?func=addpatch&group_id='.$group_id.'">Submit A Patch</A>';
	if (user_isloggedin()) {
		echo ' | <A HREF="/patch/?func=browse&group_id='.$group_id.'&set=my">My Patches</A>';
	}
	echo ' | <A HREF="/patch/?func=browse&group_id='.$group_id.'&set=open">Open Patches</A>';
	if (user_isloggedin()) {
		echo ' | <A HREF="/patch/reporting/?group_id='.$group_id.'">Reporting</A>';
	}
	echo ' | <A HREF="/patch/admin/?group_id='.$group_id.'">Admin</A>';

	echo '</B>';
}

function patch_footer($params) {
	site_project_footer($params);
}

function patch_category_box($group_id,$name='patch_category_id',$checked='xzxz',$text_100='None') {
	if (!$group_id) {
		return 'ERROR - no group_id';
	} else {
		/*
			List of possible patch_categories set up for the project
		*/
		$result=patch_data_get_categories($group_id);
		return html_build_select_box($result,$name,$checked,true,$text_100);
	}
}

function patch_technician_box($group_id,$name='assigned_to',$checked='xzxz',$text_100='None') {
	if (!$group_id) {
		return 'ERROR - no group_id';
	} else {
		$result=patch_data_get_technicians($group_id);
		return html_build_select_box($result,$name,$checked,true,$text_100);
	}
}

function patch_status_box($name='status_id',$checked='xzxz',$text_100='None') {
	$result=patch_data_get_statuses();
	return html_build_select_box($result,$name,$checked,true,$text_100);
}

function show_patchlist ($result,$offset,$set='open') {
	global $sys_datefmt,$group_id;
	/*
		Accepts a result set from the patch table. Should include all columns from
		the table, and it should be joined to USER to get the user_name.
	*/

	$IS_PATCH_ADMIN=user_ismember($group_id,'C2');

	echo '
	<FORM ACTION="'. $PHP_SELF .'" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">
		<INPUT TYPE="HIDDEN" NAME="func" VALUE="massupdate">';

	$rows=db_numrows($result);
	$url = "/patch/?group_id=$group_id&set=$set&order=";
	$title_arr=array();
	$title_arr[]='Patch ID';
	$title_arr[]='Summary';
	$title_arr[]='Date';
	$title_arr[]='Assigned To';
	$title_arr[]='Submitted By';

	$links_arr=array();
	$links_arr[]=$url.'patch_id';
	$links_arr[]=$url.'summary';
	$links_arr[]=$url.'date';
	$links_arr[]=$url.'assigned_to_user';
	$links_arr[]=$url.'submitted_by';

	echo html_build_list_table_top ($title_arr,$links_arr);

	for ($i=0; $i < $rows; $i++) {
		echo '
			<TR BGCOLOR="'. html_get_alt_row_color($i) .'">'.
			'<TD NOWRAP>'.
			($IS_PATCH_ADMIN?'<INPUT TYPE="CHECKBOX" NAME="patch_id[]" VALUE="'. db_result($result, $i, 'patch_id') .'"> ':'').
			db_result($result, $i, 'patch_id').
			'</TD>'.
			'<TD><A HREF="'.$PHP_SELF.'?func=detailpatch&patch_id='.db_result($result, $i, 'patch_id').
			'&group_id='.db_result($result, $i, 'group_id').'">'.
			db_result($result, $i, 'summary').'</TD>'.
			'<TD>'.date($sys_datefmt,db_result($result, $i, 'date')).'</TD>'.
			'<TD>'.db_result($result, $i, 'assigned_to_user').'</TD>'.
			'<TD>'.db_result($result, $i, 'submitted_by').'</TD></TR>';

	}

	/*
		Show extra rows for <-- Prev / Next -->
	*/
	echo '
		<TR><TD COLSPAN="2">';
	if ($offset > 0) {
		echo '<A HREF="'.$PHP_SELF.'?func=browse&group_id='.$group_id.'&set='.$set.'&offset='.($offset-50).'"><B><-- Previous 50</B></A>';
	} else {
		echo '&nbsp;';
	}
	echo '</TD><TD>&nbsp;</TD><TD COLSPAN="2">';
	
	if ($rows==50) {
		echo '<A HREF="'.$PHP_SELF.'?func=browse&group_id='.$group_id.'&set='.$set.'&offset='.($offset+50).'"><B>Next 50 --></B></A>';
	} else {
		echo '&nbsp;';
	}
	echo '</TD></TR>';
       /*
		Mass Update Code
	*/     
	if ($IS_PATCH_ADMIN) {
		echo '<TR><TD COLSPAN="5">
		<FONT COLOR="#FF0000"><B>Patch Admin:</B></FONT>  If you wish to apply changes to all patches selected above, use these controls to change their properties and click once on "Mass Update".
		<TABLE WIDTH="100%" BORDER="0">

		<TR><TD><B>Assigned To:</B><BR>'. patch_technician_box ($group_id,'assigned_to',$group_id,'No Change') .'</TD>
		<TD><B>Status:</B><BR>'. patch_status_box ('patch_status_id','xyz','No Change') .'</TD>
		<TD><B>Category:</B><BR>'. patch_category_box ($group_id,'patch_category_id','xyz','No Change') . '</TD></TR>
		<TR><TD COLSPAN="3" ALIGN="MIDDLE"><INPUT TYPE="SUBMIT" name="submit" VALUE="Mass Update"></TD></TR>

		</TABLE>
	    </TD></TR>';
	}
	echo '</TABLE>';
}

function show_patch_details ($patch_id) {
	/*
		Show the details rows from patch_history
	*/
	global $sys_datefmt;
	$result=patch_data_get_details($patch_id);
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
			echo '<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD>'.
				nl2br( db_result($result, $i, 'old_value') ) .'</TD>'.
				'</TD>'.
				'<TD VALIGN="TOP">'.date($sys_datefmt,db_result($result, $i, 'date')).'</TD>'.
				'<TD VALIGN="TOP">'.db_result($result, $i, 'user_name').'</TD></TR>';
		}
		echo '</TABLE>';
	} else {
		echo '
			<H3>No Followups Have Been Posted</H3>';
	}
}

function show_patchhistory ($patch_id) {
	/*
		show the patch_history rows that are relevant to this patch_id, excluding details
	*/
	global $sys_datefmt;
	$result=patch_data_get_history($patch_id);
	$rows=db_numrows($result);

	if ($rows > 0) {

		echo '
		<H3>Patch Change History</H3>
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
				<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD>'.$field.'</TD><TD>';

			if ($field == 'patch_status_id') {

				echo get_patch_status_name(db_result($result, $i, 'old_value'));

			} else if ($field == 'patch_category_id') {

				echo get_patch_category_name(db_result($result, $i, 'old_value'));

			} else if ($field == 'assigned_to') {

				echo user_getname(db_result($result, $i, 'old_value'));

			} else if ($field == 'close_date') {

				echo date($sys_datefmt,db_result($result, $i, 'old_value'));

			} else {

				echo db_result($result, $i, 'old_value');

			}
			echo '</TD>'.
				'<TD>'.date($sys_datefmt,db_result($result, $i, 'date')).'</TD>'.
				'<TD>'.db_result($result, $i, 'user_name').'</TD></TR>';
		}

		echo '
			</TABLE>';
	
	} else {
		echo '
			<H3>No Changes Have Been Made to This Patch</H3>';
	}
}

?>
