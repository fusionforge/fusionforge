<?php
/**
 * Skills support functions.
 *
 * Copyright 2002 (c) Silicon and Software Systems (S3)
 * Copyright 2014, Franck Villaume - TrivialDev
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

/**
 * displayUserSkills()
 *
 * @param	int	$user_id
 * @param	bool	$allowEdit
 */

function displayUserSkills($user_id, $allowEdit) {
	global $HTML, $feedback;
	$result=db_query_params("SELECT * FROM skills_data_types ORDER BY type_id ASC", array());
	$rows = db_numrows($result);
	if ($rows >= 1) {
		/* obtain the types keywords... */
		for($i = 0; $i < $rows; $i++) {
			$typesDescs[$i] = db_result($result, $i, 'type_name');
		}
	}

	$result= db_query_params("SELECT * FROM skills_data WHERE user_id=$1 ORDER BY finish DESC, start ASC, skills_data_id DESC",array($user_id));
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo db_error();
		$feedback = _('No skills listed.');
		echo '<tr><td>'._('This user has not entered any skills.').'</td></tr>';
	} else {

		echo '<tr class="tableheading">';				 /* headings for the columns */
		if($allowEdit) {
			echo '<td>'._('Edit').'</td>'.
				'<td>'._('Delete').'</td>';
		}
		echo '<td>'._('Type').'</td>'.
			 '<td>'._('Title').'</td>'.
			 '<td>'._('Start Date').'</td>'.
			 '<td>'._('End Date').'</td>'.
			 '<td>'._('Keywords').'</td>'.
			 '</tr>';

		for ($i = 0; $i < $rows; $i++)  /* for each entry in the database */ {
			/* set up some variables to make things easier.... */
			$typeID = db_result($result, $i, 'type');
			$start = db_result($result, $i, 'start');
			$finish = db_result($result, $i, 'finish');

			$startY = substr($start, 0, 4);
			$startM = substr($start, 4, 2);

			$finishY = substr($finish, 0, 4);
			$finishM = substr($finish, 4, 2);

			if($startM > 0 && $startM < 13) {
				$startStr = date ("M Y", mktime(0,0,0,$startM,1,$startY));
			} else {
				$startStr = $startY;
			}
			if (!(isset($finishtM))) {
				$finishtM = 0;
			}

			if($finishM > 0 && $finishtM < 13) {
				$finishStr = date ("M Y", mktime(0,0,0,$finishM,1,$finishY));
			} else {
				$finishStr = $finishY;
			}

			/* now print out the row, formatted nicely */
			echo '<tr>';
			if($allowEdit) {
				echo '<td><input type="checkbox" name="skill_edit[]" value="'.db_result($result, $i, 'skills_data_id').'" /></td>';
				echo '<td><input type="checkbox" name="skill_delete[]" value="'.db_result($result, $i, 'skills_data_id').'" /></td>';
			}
			if($typesDescs[$typeID]) {
				echo '<td>'.$typesDescs[$typeID]."</td>\n";
			} else {
				echo '<td>'.$typeID ."</td>\n";
			}

			echo '<td>'.db_result($result, $i, 'title') ."</td>\n";
			echo '<td>'.$startStr."</td>\n";
			echo '<td>'.$finishStr."</td>\n";
			echo '<td>'.db_result($result, $i, 'keywords') ."</td>\n";
			echo "</tr>";

		}

		if($allowEdit) {
			echo '<tr>';
			echo '<td><input type="submit" name="MultiEdit" value="'._('Edit').'" /></td>';
			echo '<td><input type="submit" name="MultiDelete" value="'._('Delete').'" /></td>';
			echo '</tr>';
		}

	}
}

function handle_multi_edit($skill_ids = array()) {
	global $HTML, $feedback;
	$result = db_query_params ('SELECT * FROM skills_data WHERE skills_data_id = ANY ($1)',
				   array (db_int_array_to_any_clause ($skill_ids)));
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo db_error();
	} else {
		$skills=db_query_params("SELECT * FROM skills_data_types WHERE type_id > 0", array());
		if (!$skills || db_numrows($skills) < 1) {
			$feedback .= _('No Such User')._(': ').db_error();
			echo '<h2>'._('No Such User').'</h2>';
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

		for($i = 0; $i < $rows; $i++) {
			$start = db_result($result, $i, 'start');
			$finish = db_result($result, $i, 'finish');

			$startY = substr($start, 0, 4);
			$startM = substr($start, 4, 2);

			$finishY = substr($finish, 0, 4);
			$finishM = substr($finish, 4, 2);

			echo '<table>'.
				'<tr>'.
				'<td><h3>'.db_result($result, $i,'title').'</h3></td></tr>'.
				'<tr><td>'.
				'<table class="fullwidth">'.
					'<tr class="tableheading">'.
						'<td >'._('Type').'</td>'.
						'<td >'._('Start Date').'</td>'.
						'<td >'._('End Date').'</td>'.
					'</tr>';
			echo '<tr>'.
						'<td>'.html_build_select_box($skills, 'type[]',db_result($result, $i,'type') , false, '').'</td>'.
						'<td>'.html_build_select_box_from_arrays($monthArrayVals,$monthArray, 'startM[]', $startM, false, '').
							html_build_select_box_from_arrays($yearArray,$yearArray, 'startY[]', $startY, false, '').'</td>'.
						'<td>'.html_build_select_box_from_arrays($monthArrayVals,$monthArray, 'endM[]', $finishM, false, '').
							html_build_select_box_from_arrays($yearArray,$yearArray, 'endY[]', $finishY, false, '').'</td>'.
					'</tr>'.
				'</table>'.
				'</td></tr>'.
				'<tr><td>'.
				'<table>'.
					'<tr class="tableheading">'.
						'<td>'._('Title (max 100 characters)').'</td>'.
					'</tr>'.
					'<tr>'.
						'<td><input type="hidden" name="skill_edit[]" value="'.db_result($result, $i,'skills_data_id').'" />'.
						'<input type="text" name="title[]" size="100" value="'.db_result($result, $i,'title').'" required="required" /></td>'.
					'</tr>'.
					'<tr>'.
						'<td class="tableheading">'._('Keywords (max 255 characters)').'</td>'.
					'</tr>'.
					'<tr>'.
						'<td><textarea name="keywords[]" rows="3" cols="85" required="required" >'.db_result($result, $i,'keywords').'</textarea></td>'.
					'</tr>'.
				 '</table>';
			echo '</td></tr>';
			echo '</table><br />';
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
