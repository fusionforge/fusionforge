<?php
/**
 * Skills input/update page.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002 (c) Silicon and Software Systems (S3)
 * Copyright 2010 (c) Franck Villaume
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'people/people_utils.php';
require_once $gfwww.'people/skills_utils.php';

if (!forge_get_config('use_people')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');
$job_id = getIntFromRequest('job_id');

if (session_loggedin()) {

	if (getStringFromRequest('update_profile')) {
		$people_view_skills = getStringFromRequest('people_view_skills');

		/*
			update the job's description, status, etc
		*/
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('my');
		}

		$result=db_query_params('UPDATE users SET people_view_skills=$1
WHERE user_id=$2', array($people_view_skills, user_getid()));
		if (!$result || db_affected_rows($result) < 1) {
			form_release_key(getStringFromRequest("form_key"));
			$error_msg .= sprintf(_('User update FAILED: %s'),db_error());
		} else {
			$feedback .= _('User updated successfully');
		}

	} else if (getStringFromRequest('AddSkill')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('my');
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


			$result = db_query_params("SELECT * from skills_data where user_id = $1
				   AND type=$2
				   AND title=$3
				   AND start=$4
				   AND finish=$5
				   AND keywords=$6",
					 array(user_getid(), $type, $title, $start, $finish, $keywords));

			if (db_numrows($result) >= 1) {
				$feedback .= '';	/* don't tell them anything! */
			} else {
				$result = db_query_params("INSERT into skills_data (user_id, type, title, start, finish, keywords) values
($1, $2, $3, $4, $5, $6)",array(user_getid(), $type, $title, $start, $finish, $keywords));

				if (!$result || db_affected_rows($result) < 1) {
					form_release_key(getStringFromRequest("form_key"));
					$error_msg .= sprintf(_('Failed to add the skill %s'),db_error());
					echo '<h2>'._('Failed to add the skill').'</h2>';
				} else {
					$feedback = _('Skill added successfully');
				}
			}
		} else {
			form_release_key(getStringFromRequest("form_key"));
			exit_missing_param('',array(_('Type'),_('Title'),_('Start Month'),_('Start Year'),_('End Month'),_('End Year'),_('Keywords')),'my');
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

		$numItems = 0;
		if (is_array($skill_edit)) {
			$numItems = count($skill_edit);
		}
		if($numItems == 0) {
			$warning_msg .= _('No skills selected to edit.');
		} else {
			if (getStringFromRequest('confirmMultiEdit')) {
				if (!form_key_is_valid(getStringFromRequest('form_key'))) {
					exit_form_double_submit('my');
				}

				for($i = 0; $i < $numItems; $i++) {
					$title[$i] = substr($title[$i], 0, 100);	/* delimit the title to 100 chars */
					$keywords[$i] = substr($keywords[$i], 0, 255); /* ditto the keywords. */

					$keywords[$i] = str_replace("\n", " ", $keywords[$i]);  /* strip out any backspace characters. */
					$title[$i] = str_replace("\n", " ", $title[$i]);
					$result = db_query_params("UPDATE skills_data SET type=$1 ,title=$2 ,start=$3,finish=$4, keywords=$5 WHERE skills_data_id=$6",
																		array($type[$i], $title[$i], $startY[$i].$startM[$i], $endY[$i].$endM[$i], $keywords[$i], $skill_edit[$i]));

					if (!$result || db_affected_rows($result) < 1) {
						$error_msg = sprintf(_('Failed to update skills: %s'),db_error());
						break;
					} else {
						$feedback = ngettext ('Skill updated', 'Skills updated', db_affected_rows($result));
					}
				}   /* end for */

			} else	/* not confirmed multiedit */ {
				people_header(array('title'=>_('Skills edit')));
				echo '<h2>'._('Edit Skills').'</h2>';
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
		$warning_msg = _('Cancelled skills update');
	}

	if (getStringFromRequest('MultiDelete')) {
		$unfiltered_skill_delete_array = getArrayFromRequest('skill_delete');
		$skill_delete = array() ;
		foreach ($unfiltered_skill_delete_array AS $usd) {
			if (is_numeric ($usd)) {
				$skill_delete[] = $usd;
			}
		}
		$numItems = count($skill_delete);
		if($numItems == 0) {
			$warning_msg .= _('No skills selected to delete.');
		} else {
			if(getStringFromRequest('confirmMultiDelete')) {
				if (!form_key_is_valid(getStringFromRequest('form_key'))) {
					exit_form_double_submit();
				}
				$result = db_query_params ('DELETE FROM skills_data where skills_data_id = ANY ($1)',
							   array (db_int_array_to_any_clause ($skill_delete)));
				if (!$result || db_affected_rows($result) < 1) {
					$error_msg .= sprintf(_('Failed to delete any skills: %s'),db_error());
					echo '<h2>'._('Failed to delete any skills').'</h2>';
				} else {
					$feedback = ngettext ('Skill deleted successfully', 'Skills deleted successfully', db_affected_rows($result));
				}
			} else {
				$result = db_query_params ('SELECT title FROM skills_data where skills_data_id = ANY ($1)',
							   array (db_int_array_to_any_clause ($skill_delete)));
				$rows = db_numrows($result);
				if (!$result || $rows < 1) {
					exit_error(db_error(),'my');
				} else {
					people_header(array('title'=>_('Confirm skill delete')));

					echo '<span class="important">'._('Confirm Delete').'</span>';
					print ngettext('You are about to delete the following skill from the skills database:', 'You are about to delete the following skills from the skills database:', $rows) ;
					echo "<br />";
					for($i = 0; $i < $rows; $i++) {
						echo "<strong>&nbsp;&nbsp;&nbsp;" .db_result($result, $i, 'title') . "</strong><br />";
					}
					echo "<br />"._('This action cannot be undone.')."<br /><br />";
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
		$warning_msg .= _('Skill deletion cancelled');
	}

	people_header(array('title'=>_('Edit Your Profile')));

	//for security, include group_id
	$result = db_query_params("SELECT * FROM users WHERE user_id=$1", array(user_getid()));

	if (!$result || db_numrows($result) < 1) {
		$error_msg .= sprintf(_('User fetch FAILED: %s'),db_error());
		echo '<h2>'._('No Such User').'</h2>';
	} else {

		echo '
		<h2>'._('Edit Public Permissions').'</h2>
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

		$skills = db_query_params("SELECT * FROM skills_data_types WHERE type_id > 0", array());
		if (!$skills || db_numrows($skills) < 1) {
			echo db_error();
			$feedback .= _('No skill types in database (skills_data_types table)');
			echo '<h2>'._('No skill types in database - inform system administrator').'</h2>';
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
		echo '<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
	   	echo' <input type="hidden" name="form_key" value="'.form_generate_key().'">';
		$cell_data = array();
		$cell_data[] = array(_('Type'));
		$cell_data[] = array(_('Start Date'));
		$cell_data[] = array(_('End Date'));
		echo "<table border=0 >".

				$HTML->multiTableRow('',$cell_data,TRUE);

		echo	"<tr>
<td>".html_build_select_box($skills, "type", 1, false, "")."</td>
<td>".html_build_select_box_from_arrays($monthArrayVals,$monthArray, "startM", date("m"), false, "").
						html_build_select_box_from_arrays($yearArray,$yearArray, "startY", 0, false, "")."</td>
<td>".html_build_select_box_from_arrays($monthArrayVals,$monthArray, "endM", date("m"), false, "").
						html_build_select_box_from_arrays($yearArray,$yearArray, "endY", 0, false, "")."</td>
</tr>
</table>
<table border=0 >";

				$cell_data = array();
				$cell_data[] = array(_('Title (max 100 characters)'));
				echo $HTML->multiTableRow('',$cell_data,TRUE);

				echo "<tr>
<td><input type=text name=\"title\" size=100></td>
</tr>";
				$cell_data = array();
				$cell_data[] = array(_('Keywords (max 255 characters)'));
				echo $HTML->multiTableRow('',$cell_data,TRUE);
				echo "<tr>
<td><textarea name=\"keywords\" rows=\"3\" cols=\"85\" wrap=\"soft\"></textarea></td>
</tr>
<tr>
<td><input type=submit name=\"AddSkill\" value=\""._('Add This Skill')."\"></td>
</tr>
</table>";

		echo '</form>';


		echo '<h2>'._('Edit/Delete Your Skills').'</h2>
		<table border="0" width="100%">';
		echo '<form action="'.getStringFromServer('PHP_SELF').'" method="post">';
		displayUserSkills(user_getid(), 1);
		echo '</form>';
		echo '</table>';

	}

	people_footer(array());

} else {
	/*
		Not logged in
	*/
	exit_not_logged_in();
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
