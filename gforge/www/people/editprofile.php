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
			$feedback .= $Language->getText('people_editprofile','update_failed');
			echo db_error();
		} else {
			$feedback .= $Language->getText('people_editprofile','update_ok');
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
					$feedback .= $Language->getText('people_editprofile','failed_to_add_skill');
					echo '<h2>'.$Language->getText('people_editprofile','failed_to_add_skill').'<h2>';
				} else {		  
					$feedback = $Language->getText('people_editprofile','skill_added_ok');
				}
			}
		} else {
			form_release_key(getStringFromRequest("form_key"));
			exit_error($Language->getText('people_editprofile','error'),$Language->getText('people_editprofile','fill_all_required_fields'));
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
			$feedback .= $Language->getText('people_editprofile','no_skills_selected');
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
						$feedback = $Language->getText('people_editprofile','failed_update_skills');
						break;
					} else {
						$rowsDone++;
						$feedback = $Language->getText('people_editprofile','update_skills_ok', ($rowsDone>1?"s":""));
					}
				}   /* end for */

			} else	/* not confirmed multiedit */ {
				people_header(array('title'=>$Language->getText('people_editprofile','skills_edit')));
				echo '<span class="important">'.$Language->getText('people_editprofile','edit_skills').'</span>';
				echo $Language->getText('people_editprofile','change_required_fields');
				echo '<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
				echo '<input type="hidden" name="form_key" value="'.form_generate_key().'">';
				handle_multi_edit($skill_edit);
				echo '<input type="hidden" name="confirmMultiEdit" value="1" />';
				echo '<input type="submit" name="MultiEdit" value="'.$Language->getText('general','done').'" />';
				echo '<input type="submit" name="cancelMultiEdit" value="'.$Language->getText('general','cancel').'" />';
				echo '</form>';
				people_footer(array());
				return;
			}
		}
	} else if (getStringFromRequest('cancelMultiEdit')) {
		$feedback = $Language->getText('people_editprofile','cancel_skills_update');
	}
	
	if (getStringFromRequest('MultiDelete')) {
		$skill_delete = getStringFromRequest('skill_delete');
		$numItems = count($skill_delete);
		if($numItems == 0) {
			$feedback .= $Language->getText('people_editprofile','no_skills_selected_to_delete');
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
					$feedback .= $Language->getText('people_editprofile','failed_delete_skills');
					echo '<h2>'.$Language->getText('people_editprofile','failed_delete_skills').'<h2>';
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
					people_header(array('title'=>$Language->getText('people_editprofile','confirm_skill_delete')));

					echo '<span class="important">'.$Language->getText('people_editprofile','confirm_delete').'</span>';
					echo $Language->getText('people_editprofile','about_to_delete',($rows > 1?"s":" ")).":<br /><br />";
					for($i = 0; $i < $rows; $i++) {
						echo "<strong>&nbsp;&nbsp;&nbsp;" .db_result($result, $i, 'title') . "</strong><br />";
					}
					echo "<br />".$Language->getText('people_editprofile','from_skills_database')."<br /><br />";
					echo $Language->getText('people_editprofile','are_you_sure');
					
					echo '<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
					echo '<input type="hidden" name="form_key" value="'.form_generate_key().'">';
					for($i = 0; $i < $rows; $i ++) {
						echo '<input type="hidden" name="skill_delete[]" value="'.$skill_delete[$i].'">';
					}
					echo '<input type="hidden" name="confirmMultiDelete" value="1" />';
					echo '<input type="submit" name="MultiDelete" value="'.$Language->getText('general','confirm').'" />';
					echo '<input type="submit" name="MultiDeleteCancel" value="'.$Language->getText('general','cancel').'" />';
					echo '</form>';
					people_footer(array());
				}
				return;
			}
			
		}
	} elseif (getStringFromRequest('MultiDeleteCancel')) {
		$feedback .= $Language->getText('people_editprofile','skill_deletion_cancelled');
	}

	people_header(array('title'=>$Language->getText('people_editprofile','edit_your_profile')));

	html_feedback_top($feedback);
		
	//for security, include group_id
	$sql="SELECT * FROM users WHERE user_id='". user_getid() ."'";
	
	$result=db_query($sql);

	if (!$result || db_numrows($result) < 1) {
		echo db_error();
		$feedback .= $Language->getText('people_editprofile','user_fetch_failed');
		echo '<h2>'.$Language->getText('people_editprofile','no_such_user').'<h2>';
	} else {

		echo '
		<h2>'.$Language->getText('people_editprofile','edit_public_permissions').'<h2>
		<form action="'.getStringFromServer('PHP_SELF').'" method="post">
		'.$Language->getText('people_editprofile','following_options').'
		<p>
		<strong>'.$Language->getText('people_editprofile','publicly_viewable').':</strong><br />
		<input type="hidden" name="form_key" value="'.form_generate_key().'"> 
		<input type="radio" name="people_view_skills" value="0" '. ((db_result($result,0,'people_view_skills')==0)?'checked="checked"':'') .' /> <strong>'.$Language->getText('general','no').'</strong><br />
		<input type="radio" name="people_view_skills" value="1" '. ((db_result($result,0,'people_view_skills')==1)?'checked="checked"':'') .' /> <strong>'.$Language->getText('general','yes').'</strong><br /></p>
		<p>
		<input type="submit" name="update_profile" value="'.$Language->getText('people_editprofile','update_permission').'"></p>
		</form>';

		//now show the list of desired skills
		//echo '<p>'.people_edit_skill_inventory( user_getid() );
	   
		$sql="SELECT * FROM skills_data_types WHERE type_id > 0";
		$skills=db_query($sql);
		if (!$skills || db_numrows($skills) < 1) {
			echo db_error();
			$feedback .= $Language->getText('people_editprofile','no_skill_types_in_database');
			echo '<h2>'.$Language->getText('people_editprofile','no_skill_types_in_database_inform').'<h2>';
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
		echo '<h2>'.$Language->getText('people_editprofile','add_new_skill').'</h2>';
		echo $Language->getText('people_editprofile','you_can_enter_new_skills').'<br />'.
			 '<span class="required-field">'.$Language->getText('people_editprofile','all_fields_required').'</span>';
	   	echo '<form action="'.getStringFromServer('PHP_SELF').'" METHOD="POST">';
	   	echo' <input type="hidden" name="form_key" value="'.form_generate_key().'">';
		$cell_data = array();
		$cell_data[] = array($Language->getText('people_editprofile','type'));
		$cell_data[] = array($Language->getText('people_editprofile','start_date'));
		$cell_data[] = array($Language->getText('people_editprofile','end_date'));
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
				$cell_data[] = array($Language->getText('people_editprofile','title_max_100_chars'));
				echo $HTML->multiTableRow('',$cell_data,TRUE);

				echo "<tr>".
						"<td><input type=text name=\"title\" size=100></td>".
					"</tr>";
				$cell_data = array();
				$cell_data[] = array($Language->getText('people_editprofile','keywords_max_255_chars'));
				echo $HTML->multiTableRow('',$cell_data,TRUE);
				echo "<tr>".
						"<td><textarea name=\"keywords\" rows=\"3\" cols=\"85\" wrap=\"soft\"></textarea></td>".
					"</tr>".
					"<tr>".
						"<td><input type=submit name=\"AddSkill\" value=\"".$Language->getText('people_editprofile','add_this_skill')."\"></td>".
					"</tr>".
				 "</table>";
		
		echo '</form>';
		
		
		echo '<h2>'.$Language->getText('people_editprofile','edit_delete_your_skills').'</h2>
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
