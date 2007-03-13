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

require_once('../env.inc.php');
require_once('pre.php');
require_once('people_utils.php');
require_once('skills_utils.php');

if (!$sys_use_people) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');
$job_id = getStringFromRequest('job_id');

if (session_loggedin()) {

	if (getStringFromRequest('update_profile')) {
		$people_view_skills = getStringFromRequest('people_view_skills');

		/*
			update the job's description, status, etc
		*/
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}
		
		$sql="UPDATE users SET people_view_skills='$people_view_skills'".
			"WHERE user_id='".user_getid()."'";
		$result=db_query($sql);
		if (!$result || db_affected_rows($result) < 1) {
			form_release_key(getStringFromRequest("form_key"));
			$feedback .= _('User update FAILED');
			echo db_error();
		} else {
			$feedback .= _('User updated successfully');
		}

	} else if (getStringFromRequest('AddSkill')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}

		$type = getStringFromRequest('type');
		$title = getStringFromRequest('title');
		$startM = getStringFromRequest('startM');
		$startY = getStringFromRequest('startY');
		$endM = getStringFromRequest('endM');
		$endY = getStringFromRequest('endY');
		$keywords = getStringFromRequest('keywords');

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
					form_release_key(getStringFromRequest("form_key"));
					echo db_error();
					$feedback .= _('Failed to add the skill');
					echo '<h2>'._('Failed to add the skill').'<h2>';
				} else {		  
					$feedback = _('Skill added successfully');
				}
			}
		} else {
			form_release_key(getStringFromRequest("form_key"));
			exit_error(_('error - missing info'),_('error - missing info'));
		}
	}
	if (getStringFromRequest('MultiEdit')) {
		$type = getStringFromRequest('type');
		$title = getStringFromRequest('title');
		$startM = getStringFromRequest('startM');
		$startY = getStringFromRequest('startY');
		$endM = getStringFromRequest('endM');
		$endY = getStringFromRequest('endY');
		$keywords = getStringFromRequest('keywords');
		$skill_edit = getStringFromRequest('skill_edit');

		$numItems = count($skill_edit);
		if($numItems == 0) {
			$feedback .= _('No skills selected to edit.');
		} else {
			if (getStringFromRequest('confirmMultiEdit')) {
				if (!form_key_is_valid(getStringFromRequest('form_key'))) {
					exit_form_double_submit();
				}
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
						$feedback = _('Failed to update skills');
						break;
					} else {
						$rowsDone++;
						$feedback = $Language->getText('people_editprofile','update_skills_ok', ($rowsDone>1?"s":""));
					}
				}   /* end for */

			} else	/* not confirmed multiedit */ {
				people_header(array('title'=>_('Skills edit')));
				echo '<span class="important">'._('Edit Skills').'</span>';
				echo _('Change the required fields, and press "Done" at the bottom of the page');
				echo '<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
				echo '<input type="hidden" name="form_key" value="'.form_generate_key().'">';
				handle_multi_edit($skill_edit);
				echo '<input type="hidden" name="confirmMultiEdit" value="1" />';
				echo '<input type="submit" name="MultiEdit" value="'._('Done').'" />';
				echo '<input type="submit" name="cancelMultiEdit" value="'._('Cancel').'" />';
				echo '</form>';
				people_footer(array());
				return;
			}
		}
	} else if (getStringFromRequest('cancelMultiEdit')) {
		$feedback = _('Cancelled skills update');
	}
	
	if (getStringFromRequest('MultiDelete')) {
		$skill_delete = getStringFromRequest('skill_delete');
		$numItems = count($skill_delete);
		if($numItems == 0) {
			$feedback .= _('No skills selected to delete.');
		} else {
			if(getStringFromRequest('confirmMultiDelete')) {
				if (!form_key_is_valid(getStringFromRequest('form_key'))) {
					exit_form_double_submit();
				}

				$sql = "DELETE FROM skills_data where skills_data_id in(".$skill_delete[0];
				for($i = 1; $i < $numItems; $i++) {
					$sql .= ",".$skill_delete[$i];
				}
				$sql .=")";
				$result=db_query($sql);
				if (!$result || db_affected_rows($result) < 1) {
					echo db_error();
					$feedback .= _('Failed to delete any skills');
					echo '<h2>'._('Failed to delete any skills').'<h2>';
				} else {		  
					$feedback = $Language->getText('people_editprofile','skill_delete_successfully',(db_affected_rows($result)>1?"s":" "));
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
					people_header(array('title'=>_('Confirm skill delete')));

					echo '<span class="important">'._('Confirm Delete').'</span>';
					echo $Language->getText('people_editprofile','about_to_delete',($rows > 1?"s":" ")).":<br /><br />";
					for($i = 0; $i < $rows; $i++) {
						echo "<strong>&nbsp;&nbsp;&nbsp;" .db_result($result, $i, 'title') . "</strong><br />";
					}
					echo "<br />"._('from the skills database. This action cannot be undone.')."<br /><br />";
					echo _('Are you <strong>sure</strong> you wish to continue?');
					
					echo '<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
					echo '<input type="hidden" name="form_key" value="'.form_generate_key().'">';
					for($i = 0; $i < $rows; $i ++) {
						echo '<input type="hidden" name="skill_delete[]" value="'.$skill_delete[$i].'">';
					}
					echo '<input type="hidden" name="confirmMultiDelete" value="1" />';
					echo '<input type="submit" name="MultiDelete" value="'._('Confirm').'" />';
					echo '<input type="submit" name="MultiDeleteCancel" value="'._('Cancel').'" />';
					echo '</form>';
					people_footer(array());
				}
				return;
			}
			
		}
	} elseif (getStringFromRequest('MultiDeleteCancel')) {
		$feedback .= _('Skill deletion cancelled');
	}

	people_header(array('title'=>_('Edit Your Profile')));

	html_feedback_top($feedback);
		
	//for security, include group_id
	$sql="SELECT * FROM users WHERE user_id='". user_getid() ."'";
	
	$result=db_query($sql);

	if (!$result || db_numrows($result) < 1) {
		echo db_error();
		$feedback .= _('User fetch FAILED');
		echo '<h2>'._('No Such User').'<h2>';
	} else {

		echo '
		<h2>'._('Edit Public Permissions').'<h2>
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
		'._('The following option determines if others can see your skills. If they can\'t, you can still enter your skills.').'
		<p>
		<strong>'._('Publicly Viewable').':</strong><br />
		<input type="hidden" name="form_key" value="'.form_generate_key().'"> 
		<input type="radio" name="people_view_skills" value="0" '. ((db_result($result,0,'people_view_skills')==0)?'checked="checked"':'') .' /> <strong>'._('No').'</strong><br />
		<input type="radio" name="people_view_skills" value="1" '. ((db_result($result,0,'people_view_skills')==1)?'checked="checked"':'') .' /> <strong>'._('Yes').'</strong><br /></p>
		<p>
		<input type="submit" name="update_profile" value="'._('Update Permissions').'"></p>
		</form>';

		//now show the list of desired skills
		//echo '<p>'.people_edit_skill_inventory( user_getid() );
	   
		$sql="SELECT * FROM skills_data_types WHERE type_id > 0";
		$skills=db_query($sql);
		if (!$skills || db_numrows($skills) < 1) {
			echo db_error();
			$feedback .= _('No skill types in database (skills_data_types table)');
			echo '<h2>'._('No skill types in database - inform system administrator').'<h2>';
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
		echo '<h2>'._('Add a new skill').'</h2>';
		echo _('You can enter new skills you have acquired here. Please enter the start and finish dates as accurately as possible.').'<br />'.
			 '<span class="required-field">'._('All fields are required!').'</span>';
	   	echo '<form action="'.getStringFromServer('PHP_SELF').'" METHOD="POST">';
	   	echo' <input type="hidden" name="form_key" value="'.form_generate_key().'">';
		$cell_data = array();
		$cell_data[] = array(_('Type'));
		$cell_data[] = array(_('Start Date'));
		$cell_data[] = array(_('End Date'));
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
				$cell_data[] = array(_('MISSINGTEXT:people_editprofile/title_max_100_chars:TEXTMISSING'));
				echo $HTML->multiTableRow('',$cell_data,TRUE);

				echo "<tr>".
						"<td><input type=text name=\"title\" size=100></td>".
					"</tr>";
				$cell_data = array();
				$cell_data[] = array(_('Keywords (max 255 characters)'));
				echo $HTML->multiTableRow('',$cell_data,TRUE);
				echo "<tr>".
						"<td><textarea name=\"keywords\" rows=\"3\" cols=\"85\" wrap=\"soft\"></textarea></td>".
					"</tr>".
					"<tr>".
						"<td><input type=submit name=\"AddSkill\" value=\""._('Add This Skill')."\"></td>".
					"</tr>".
				 "</table>";
		
		echo '</form>';
		
		
		echo '<h2>'._('Edit/Delete Your Skills').'</h2>
		<table border="0" width="100%">';
		echo '<form action="'.getStringFromServer('PHP_SELF').'" METHOD="POST">';
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
