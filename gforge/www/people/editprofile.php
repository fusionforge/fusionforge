<?php
/**
 *
 * Skills input/update page.
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002 (c) Silicon and Software Systems (S3)
 *
 * @version   $Id$
 *
 */

require_once('pre.php');
require_once('people_utils.php');
require_once('skills_utils.php');

if (session_loggedin()) {

	if ($update_profile) {
		/*
			update the job's description, status, etc
		*/
		$sql="UPDATE users SET people_view_skills='$people_view_skills'".
			"WHERE user_id='".user_getid()."'";
		$result=db_query($sql);
		if (!$result || db_affected_rows($result) < 1) {
			$feedback .= ' User update FAILED ';
			echo db_error();
		} else {
			$feedback .= ' User updated successfully ';
		}

	} else if($AddSkill) {
	
		if($type && $title && $startM && $startY && $endM && $endY && $keywords) {			 
			$start = $startY.$startM;
			$finish = $endY.$endM;
			
			$title = substr($title, 0, 100);	/* delimit the title to 100 chars */
			$keywords = substr($keywords, 0, 255); /* ditto the keywords. */
			
			$keywords = str_replace("\n", " ", $keywords);  /* strip out any backspace characters. */
			$title = str_replace("\n", " ", $title);
			
				 
			$sql = "SELECT * from skills_data where user_id = ".user_getid().
				   " AND type=".$type.
				   " AND title='".$title."'".
				   " AND start=".$start.
				   " AND finish=".$finish.
				   " AND keywords='".$keywords."'";
				   
			$result=db_query($sql);
			if (db_numrows($result) >= 1) {
				$feedback .= '';	/* don't tell them anything! */
			} else {		  
				$sql = "INSERT into skills_data (user_id, type, title, start, finish, keywords) values".
					   "(".user_getid().",".$type.",'".$title."',".$start.",".$finish.",'".$keywords."')";
			   
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					echo db_error();
					$feedback .= 'Failed to add the skill ';
					echo '<h2>Failed to add the skill<h2>';
				} else {		  
					$feedback = "Skill added successfully";
				}
			}
		} else {
			exit_error('error - missing info','Fill in all required fields. Press the back button to continue.');
		}
	}
	if ($MultiEdit) {
		$numItems = count($skill_edit);
		if($numItems == 0) {
			$feedback .= "No skills selected to edit.";
		} else {
			if($confirmMultiEdit) {
			   $rowsDone = 0;
				
				for($i = 0; $i < $numItems; $i++) {
					$title[$i] = substr($title[$i], 0, 100);	/* delimit the title to 100 chars */
					$keywords[$i] = substr($keywords[$i], 0, 255); /* ditto the keywords. */
			
					$keywords[$i] = str_replace("\n", " ", $keywords[$i]);  /* strip out any backspace characters. */
					$title[$i] = str_replace("\n", " ", $title[$i]);
			
					$sql="UPDATE skills_data SET type='$type[$i]',title='$title[$i]',start='$startY[$i]$startM[$i]',".
						 "finish='$endY[$i]$endM[$i]',keywords='$keywords[$i]' ".
						"WHERE skills_data_id='$skill_edit[$i]'";
						
					$result=db_query($sql);
					if (!$result || db_affected_rows($result) < 1) {
						echo db_error();
						$feedback = 'Failed to update skills ';
						break;
					} else {	  
					   $rowsDone++; 
					   $feedback = "Skill". ($rowsDone>1?"s":"")." updated successfully";
					}						
				}   /* end for */
				
			} else	/* not confirmed multiedit */ {
				people_header(array('title'=>'Skills edit','pagename'=>'people_editskills'));
				echo '<h2><span style="color:red">Edit Skills</span><h2>';
				echo 'Change the required fields, and press "Done" at the bottom of the page';
				echo '<form action="'.$PHP_SELF.'" method="post">';
				handle_multi_edit($skill_edit);
				echo '<input type="hidden" name="confirmMultiEdit" value="1" />';
				echo '<input type="submit" name="MultiEdit" value="Done" />';
				echo '<input type="submit" name="cancelMultiEdit" value="Cancel" />';
				echo '</form>';
				people_footer(array());
				return;
			}
		}
	} else if($cancelMultiEdit) {
		$feedback = "Cancelled skills update";
	}
	
	if($MultiDelete) {
		$numItems = count($skill_delete);
		if($numItems == 0) {
			$feedback .= "No skills selected to delete.";
		} else {
			if($confirmMultiDelete) {
				$sql = "DELETE FROM skills_data where skills_data_id in(".$skill_delete[0];
				for($i = 1; $i < $numItems; $i++) {
					$sql .= ",".$skill_delete[$i];
				}
				$sql .=")";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					echo db_error();
					$feedback .= 'Failed to delete any skills ';
					echo '<h2>Failed to delete the skill<h2>';
				} else {		  
					$feedback = "Skill".(db_affected_rows($result)>1?"s":"")." deleted successfully ";
				}
			} else {
				$sql = "SELECT title FROM skills_data where skills_data_id in(".$skill_delete[0];
				for($i = 1; $i < $numItems; $i++) {
					$sql .= ",".$skill_delete[$i];
				}
				$sql .=")";
				
				$result=db_query($sql);
				$rows = db_numrows($result);
				if (!$result || $rows < 1) {
					echo db_error();
				} else {		  
					people_header(array('title'=>'Confirm skill delete','pagename'=>'people_editskills'));

					echo '<h2><span style="color:red">Confirm Delete</span><h2>';
					echo "You are about to delete the following skill".($rows > 1?"s":"").":<br /><br />";
					for($i = 0; $i < $rows; $i++) {
						echo "<strong>&nbsp;&nbsp;&nbsp;" .db_result($result, $i, 'title') . "</strong><br />";
					}
					echo "<br />from the skills database. This action cannot be undone.<br /><br />";
					echo "Are you <strong>sure</strong> you wish to continue? ";
					
					echo '<form action="'.$PHP_SELF.'" method="post">';
					for($i = 0; $i < $rows; $i ++) {
						echo '<input type="hidden" name="skill_delete[]" value="'.$skill_delete[$i].'">';
					}
					echo '<input type="hidden" name="confirmMultiDelete" value="1" />';
					echo '<input type="submit" name="MultiDelete" value="Confirm" />';
					echo '<input type="submit" name="MultiDeleteCancel" value="Cancel" />';
					echo '</form>';
					people_footer(array());
				}
				return;
			}
			
		}
	} elseif($MultiDeleteCancel) {
		$feedback .= "Skill deletion cancelled";
	}

	people_header(array('title'=>'Edit Your Profile','pagename'=>'people_editskills'));

	html_feedback_top($feedback);
		
	//for security, include group_id
	$sql="SELECT * FROM users WHERE user_id='". user_getid() ."'";
	
	$result=db_query($sql);

	if (!$result || db_numrows($result) < 1) {
		echo db_error();
		$feedback .= ' User fetch FAILED ';
		echo '<h2>No Such User<h2>';
	} else {

		echo '
		<h2>Edit Public Permissions<h2>
		<form action="'.$PHP_SELF.'" method="post">
		The following option determines if others can see your skills. If they can\'t, you
		can still enter your skills.
		<p>
		<strong>Publicly Viewable:</strong><br />
		<input type="radio" name="people_view_skills" value="0" '. ((db_result($result,0,'people_view_skills')==0)?'checked="checked"':'') .' /> <strong>No</strong><br />
		<input type="radio" name="people_view_skills" value="1" '. ((db_result($result,0,'people_view_skills')==1)?'checked="checked"':'') .' /> <strong>Yes</strong><br /></p>
		<p>
		<input type="submit" name="update_profile" value="Update Permissions"></p>
		</form>';

		//now show the list of desired skills
		//echo '<p>'.people_edit_skill_inventory( user_getid() );
	   
		$sql="SELECT * FROM skills_data_types WHERE type_id > 0";
		$skills=db_query($sql);
		if (!$skills || db_numrows($skills) < 1) {
			echo db_error();
			$feedback .= ' No skill types in database (skills_data_types table) ';
			echo '<h2>No skill types in database - inform system administrator<h2>';
		}
		
		$yearArray = array();
		for($years = date("Y"); $years >= 1980; $years--) {
			array_push($yearArray,$years);
		}

		$monthArray = array();
		$monthArrayVals = array();
		for($i = 1; $i <= 12; $i++) {
			array_push($monthArrayVals,($i<10?"0".$i:$i));
			array_push($monthArray,date("M", mktime(0,0,0,$i,1,1980)));
		}
	   
		
		/* add skills. */
		echo "<h2>Add a new skill</h2>";
		echo "You can enter new skills you have acquired here. Please enter the start and finish dates as accurately as possible.<br />".
			 "<FONT COLOR=\"#ff0000\"><em><strong>All fields are required!</em></strong></FONT>";
	   	echo '<form action="'.$PHP_SELF.'" METHOD="POST">';
		$cell_data = array();
		$cell_data[] = array('Type');
		$cell_data[] = array('Start Date');
		$cell_data[] = array('End Date');
		echo "<table border=0 >".

				$HTML->multiTableRow('',$cell_data,TRUE);

		echo	"<tr>".
					"<td>".html_build_select_box($skills, "type", 1, false, "")."</td>".
					"<td>".html_build_select_box_from_arrays($monthArrayVals,$monthArray, "startM", date("m"), false, "").
						html_build_select_box_from_arrays($yearArray,$yearArray, "startY", 0, false, "")."</td>".
					"<td>".html_build_select_box_from_arrays($monthArrayVals,$monthArray, "endM", date("m"), false, "").
						html_build_select_box_from_arrays($yearArray,$yearArray, "endY", 0, false, "")."</td>".
				"</tr>".
			"</TABLE>".
				
				"<table border=0 >";

				$cell_data = array();
				$cell_data[] = array('Title (max 100 chaacters)');
				echo $HTML->multiTableRow('',$cell_data,TRUE);

				echo "<tr>".
						"<td><input type=text name=\"title\" size=100></td>".
					"</tr>";
				$cell_data = array();
				$cell_data[] = array('Keywords (max 255 chaacters)');
				echo $HTML->multiTableRow('',$cell_data,TRUE);
				echo "<tr>".
						"<td><textarea name=\"keywords\" rows=\"3\" cols=\"85\" wrap=\"soft\"></textarea></td>".
					"</tr>".
					"<tr>".
						"<td><input type=submit name=\"AddSkill\" value=\"Add This Skill\"></td>".
					"</tr>".
				 "</table>";
		
		echo '</form>';
		
		
		echo '<h2>Edit/Delete Your Skills</h2>
		<table border="0" width="100%">';
		echo '<form action="'.$PHP_SELF.'" METHOD="POST">';
		displayUserSkills(user_getid(), 1); 
		echo '</form>';				
		echo '</TABLE>';

	}

	people_footer(array());

} else {
	/*
		Not logged in
	*/
	exit_not_logged_in();
}

?>
