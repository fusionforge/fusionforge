<?php
/**
 *
 * Skills support functions.
 *
 * Copyright 2002 (c) Silicon and Software Systems (S3)
 *
 * @version   $Id$
 *
 */

function displayUserSkills($user_id, $allowEdit) {
	GLOBAL $HTML, $Language;
	$sql = "SELECT * FROM skills_data_types ORDER BY type_id ASC";
	$result=db_query($sql);
	$rows = db_numrows($result);
	if ($rows >= 1) {
		/* obtain the types keywords... */
		for($i = 0; $i < $rows; $i++) {
			$typesDescs[$i] = db_result($result, $i, 'type_name');
		}
	}
	
	$sql="SELECT * FROM skills_data WHERE user_id='$user_id' ORDER BY finish DESC, start ASC, skills_data_id DESC";
	$result=db_query($sql);
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo db_error();
		$feedback = 'No skills listed ';
		echo '<tr><td>This user has not entered any skills.</td></tr>';
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
			if (!(isset($finishtM))){
				$finishtM=0;			}
		
			if($finishM > 0 && $finishtM < 13) {
				$finishStr = date ("M Y", mktime(0,0,0,$finishM,1,$finishY));
			} else {
				$finishStr = $finishY;
			}
			
			/* now print out the row, formatted nicely */
			echo '<tr '. $HTML->boxGetAltRowStyle($i+1) . '>';
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

function handle_multi_edit($skill_ids) {
	GLOBAL $HTML, $Language;
	$numSkills = count($skill_ids);
	$SQL = "select * from skills_data where skills_data_id in(".$skill_ids[0];
	for($i = 1; $i < $numSkills; $i++) {
		$SQL .= ", ".$skill_ids[$i];
	}
	$SQL .= ")";
	
	$result=db_query($SQL);
	$rows = db_numrows($result);
	if (!$result || $rows < 1) {
		echo db_error();
	} else {
		$sql="SELECT * FROM skills_data_types WHERE type_id > 0";
		$skills=db_query($sql);
		if (!$skills || db_numrows($skills) < 1) {
			echo db_error();
			$feedback .= _('User fetch FAILED');
			echo '<h2>'._('No Such User').'<h2>';
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
							   
			echo '<table border="0">'.
				'<tr '.$HTML->boxGetAltRowStyle($i+1).'>'.
				'<td><h3>'.db_result($result, $i,'title').'</h3></td></tr>'.
				'<tr><td>'.
				'<table border="0" >'.
					'<tr class="tableheading">'.
						'<td >'._('Type').'</td>'.
						'<td >'._('Start Date').'</td>'.
						'<td >'._('End Date').'</td>'.
					'</tr>';
			echo '<tr '.$HTML->boxGetAltRowStyle($i+1).'>'.
						'<td>'.html_build_select_box($skills, 'type[]',db_result($result, $i,'type') , false, '').'</td>'.
						'<td>'.html_build_select_box_from_arrays($monthArrayVals,$monthArray, 'startM[]', $startM, false, '').
							html_build_select_box_from_arrays($yearArray,$yearArray, 'startY[]', $startY, false, '').'</td>'.
						'<td>'.html_build_select_box_from_arrays($monthArrayVals,$monthArray, 'endM[]', $finishM, false, '').
							html_build_select_box_from_arrays($yearArray,$yearArray, 'endY[]', $finishY, false, '').'</td>'.
					'</tr>'.
				'</table>'.
				'</td></tr>'.
				
				'<tr '.$HTML->boxGetAltRowStyle($i+1).'><td>'.
				'<table border="0">'.
					'<tr class="tableheading">'.
						'<td>'._('Title (max 100 characters)').'</td>'.
					'</tr>'.
					'<tr>'.
						'<td><input type="hidden" name="skill_edit[]" value="'.db_result($result, $i,'skills_data_id').'" />'.
						'<input type="text" name="title[]" size="100" value="'.db_result($result, $i,'title').'" /></td>'.
					'</tr>'.
					'<tr>'.
						'<td class="tableheading">'._('Keywords (max 255 characters)').'</td>'.
					'</tr>'.
					'<tr>'.
						'<td><textarea name="keywords[]" rows="3" cols="85" wrap="soft">'.db_result($result, $i,'keywords').'</textarea></td>'.
					'</tr>'.
					
				 '</table>';
				 '</td></tr>';
			echo '</table><br />';
		}
	}
}

?>
