<?php
/**
  *
  * SourceForge Project/Task Manager (PM)
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/pm/pm_utils.php');

/*

	Project / Task Manager Admin
	By Tim Perdue Nov. 1999

*/

if ($group_id && user_ismember($group_id,'P2')) {

	if ($post_changes) {
		/*
			Update the database
		*/

		if ($projects) {
			/*
				Insert a new project
			*/
			$sql="INSERT INTO project_group_list (group_id,project_name,is_public,description) ".
				"VALUES ('$group_id','". htmlspecialchars($project_name) ."','$is_public','". htmlspecialchars($description) ."')";
			$result=db_query($sql);
			if (!$result) {
				$feedback .= " Error inserting value ";
				echo db_error();
			}

			$feedback .= " Subproject Inserted ";

	       } else if ($change_status) {
			/*
				Change a project to public/private
			*/
		       $sql="UPDATE project_group_list SET is_public='$is_public',project_name='". htmlspecialchars($project_name) ."', ".
				"description='". htmlspecialchars($description) ."' ".
				"WHERE group_id='$group_id' AND group_project_id='$group_project_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= " Error Updating Status ";
				echo db_error();
			} else {
				$feedback .= " Status Updated Successfully ";
			}
		}
	} 
	/*
		Show UI forms
	*/

	if ($projects) {
		/*
			Show categories and blank row
		*/

		pm_header(array ('title'=>'Add Projects','pagename'=>'pm_admin_projects','sectionvals'=>group_getname($group_id)));

		/*
			List of possible categories for this group
		*/
		$sql="SELECT group_project_id,project_name FROM project_group_list WHERE group_id='$group_id'";
		$result=db_query($sql);
		echo "<P>";
		if ($result && db_numrows($result) > 0) {
			ShowResultSet($result,"Existing Subprojects");
		} else {
			echo "\n<H1>No Subprojects in this group</H1>";
		}
		?>
		<P>
		Add a new project to the Project/Task Manager. <B>This is different than
		 adding a task to a project.</B>
		<P>
		<FORM ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
		<INPUT TYPE="HIDDEN" NAME="projects" VALUE="y">
		<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="<?php echo $group_id; ?>">
		<INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
		<P>
		<B>Is Public?</B><BR>
		<INPUT TYPE="RADIO" NAME="is_public" VALUE="1" CHECKED> Yes<BR>
		<INPUT TYPE="RADIO" NAME="is_public" VALUE="0"> No<P>
		<P>
		<H3>New Project Name:</H3>
		<P>
		<INPUT TYPE="TEXT" NAME="project_name" VALUE="" SIZE="15" MAXLENGTH="30">
		<P>
		<B>Description:</B><BR>
		<INPUT TYPE="TEXT" NAME="description" VALUE="" SIZE="40" MAXLENGTH="80">
		<P>
		<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="SUBMIT">
		</FORM>
		<?php
		pm_footer(array());

	} else if ($change_status) {
		/*
			Change a project to public/private
		*/
		pm_header(array('title'=>'Change Project/Task Manager Status','pagename'=>'pm_admin_change_status','sectionvals'=>group_getname($group_id)));

		$sql="SELECT project_name,group_project_id,is_public,description ".
			"FROM project_group_list ".
			"WHERE group_id='$group_id'";
		$result=db_query($sql);
		$rows=db_numrows($result);

		if (!$result || $rows < 1) {
			echo '
				<H2>No Subprojects Found</H2>
				<P>
				None found for this project';
			echo db_error();
		} else {
			echo '
			<P>
			You can make subprojects in the Project/Task Manager private from here. Please note that private subprojects
			can still be viewed by members of your project, but not the general public.<P>';

			$title_arr=array();
			$title_arr[]='Status';
			$title_arr[]='Name';
			$title_arr[]='Update';

			echo html_build_list_table_top ($title_arr);

			for ($i=0; $i<$rows; $i++) {
				echo '
					<FORM ACTION="'.$PHP_SELF.'" METHOD="POST">
					<INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
					<INPUT TYPE="HIDDEN" NAME="change_status" VALUE="y">
					<INPUT TYPE="HIDDEN" NAME="group_project_id" VALUE="'.db_result($result,$i,'group_project_id').'">
					<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">';
				echo '
					<TR BGCOLOR="'. html_get_alt_row_color($i) .'"><TD>
						<FONT SIZE="-1">
						<B>Is Public?</B><BR>
						<INPUT TYPE="RADIO" NAME="is_public" VALUE="1"'.((db_result($result,$i,'is_public')=='1')?' CHECKED':'').'> Yes<BR>
						<INPUT TYPE="RADIO" NAME="is_public" VALUE="0"'.((db_result($result,$i,'is_public')=='0')?' CHECKED':'').'> No<BR>
						<INPUT TYPE="RADIO" NAME="is_public" VALUE="9"'.((db_result($result,$i,'is_public')=='9')?' CHECKED':'').'> Deleted<BR>
					</TD><TD>
						<INPUT TYPE="TEXT" NAME="project_name" VALUE="'. db_result($result, $i, 'project_name') .'">
					</TD><TD>
						<FONT SIZE="-1">
						<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Update">
					</TD></TR>
					<TR BGCOLOR="'.html_get_alt_row_color($i) .'"><TD COLSPAN="3">
						<B>Description:</B><BR>
						<INPUT TYPE="TEXT" NAME="description" VALUE="'.
						db_result($result,$i,'description') .'" SIZE="40" MAXLENGTH="80"><BR>
					</TD></TR>
					</FORM>';
			}
			echo '</TABLE>';
		}

		pm_footer(array());

	} else {

		/*
			Show main page
		*/
		pm_header(array('title'=>'Project/Task Manager Administration','pagename'=>'pm_admin','sectionvals'=>group_getname($group_id)));

		echo '
			<P>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&projects=1">Add A Subproject</A><BR>
			Add a project, which can contain a set of tasks. This is different than creating a new task.
			<BR>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&change_status=1">Update Information</A><BR>
			Determine whether non-project-members can view Subprojects in the Project/Task Manager, update name and description';

		pm_footer(array());
	}

} else {

	//browse for group first message

	if (!$group_id) {
		exit_no_group();
	} else {
		exit_permission_denied();
	}

}
?>
