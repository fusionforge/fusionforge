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
	GLOBAL $HTML;
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
		$feedback .= 'No skills listed ';
		echo '<TR><TD>This user has not entered any skills.</TD></TR>';
	} else {
		
		echo '<TR bgcolor="#D0D0D0">';				 /* headings for the columns */
		if($allowEdit) {
			echo '<TD ALIGN="middle"><B>Edit</b></TD>'.
				 '<TD ALIGN="middle"><B>Delete</B></TD>';
		}
		echo '<TD ALIGN="middle"><B>Type</B></TD>'.
			 '<TD ALIGN="middle"><B>Title</B></TD>'.
			 '<TD ALIGN="middle"><B>Start Date</B></TD>'.
			 '<TD ALIGN="middle"><B>End Date</B></TD>'.
			 '<TD ALIGN="middle"><B>Keywords</B></TD>'.
			 '</TR>';

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
			
			if($finishM > 0 && $finishtM < 13) {
				$finishStr = date ("M Y", mktime(0,0,0,$finishM,1,$finishY));
			} else {
				$finishStr = $finishY;
			}
			
			/* now print out the row, formatted nicely */
			echo '<TR '. $HTML->boxGetAltRowStyle($i+1) . '>';
			if($allowEdit) {
				echo '<TD><INPUT TYPE="CHECKBOX" NAME="skill_edit[]" VALUE="'.db_result($result, $i, 'skills_data_id').'"></TD>';
				echo '<TD><INPUT TYPE="CHECKBOX" NAME="skill_delete[]" VALUE="'.db_result($result, $i, 'skills_data_id').'"></TD>';
			}
			if($typesDescs[$typeID]) {
				echo '<TD>'.$typesDescs[$typeID]."</TD>\n";
			} else {
				echo '<TD>'.$typeID ."</TD>\n";
			}

			echo '<TD>'.db_result($result, $i, 'title') ."</TD>\n";
			echo '<TD>'.$startStr."</TD>\n";
			echo '<TD>'.$finishStr."</TD>\n";
			echo '<TD>'.db_result($result, $i, 'keywords') ."</TD>\n";
			echo "</tr>";

		}

		if($allowEdit) {
			echo '<TR>';
			echo '<TD><INPUT TYPE="Submit" NAME="MultiEdit" VALUE="Edit"></TD>';
			echo '<TD><INPUT TYPE="Submit" NAME="MultiDelete" VALUE="Delete"></TD>';
			echo '</TR>';
		}

	}
}

function handle_multi_edit($skill_ids) {
	GLOBAL $HTML;
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
			$feedback .= ' User fetch FAILED ';
			echo '<H2>No Such User</H2>';
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
							   
			echo "<TABLE BORDER=0>".
				"<TR ".$HTML->boxGetAltRowStyle($i+1).">".
				"<TD><H3>".db_result($result, $i,'title')."</H3></TD></TR>".
				"<TR><TD>".
				"<Table BORDER=0 >".
					"<TR>".
						"<TD BGCOLOR=".$HTML->COLOR_HTMLBOX_TITLE.">Type</TD>".
						"<TD BGCOLOR=".$HTML->COLOR_HTMLBOX_TITLE.">Start Date</TD>".
						"<TD BGCOLOR=".$HTML->COLOR_HTMLBOX_TITLE.">End Date</TD>".
					"</TR>";
			echo "<TR ".$HTML->boxGetAltRowStyle($i+1).">".
						"<TD>".html_build_select_box($skills, "type[]",db_result($result, $i,'type') , false, "")."</TD>".
						"<TD>".html_build_select_box_from_arrays($monthArrayVals,$monthArray, "startM[]", $startM, false, "").
							html_build_select_box_from_arrays($yearArray,$yearArray, "startY[]", $startY, false, "")."</TD>".
						"<TD>".html_build_select_box_from_arrays($monthArrayVals,$monthArray, "endM[]", $finishM, false, "").
							html_build_select_box_from_arrays($yearArray,$yearArray, "endY[]", $finishY, false, "")."</TD>".
					"</TR>".
				"</TABLE>".
				"</TD></TR>".
				
				"<TR ".$HTML->boxGetAltRowStyle($i+1)."><TD>".
				"<TABLE BORDER=0 >".
					"<TR>".
						"<TD BGCOLOR=".$HTML->COLOR_HTMLBOX_TITLE.">Title (max 100 characters)</TD>".
					"</TR>".
					"<TR>".
						"<TD><INPUT TYPE=\"hidden\" name=\"skill_edit[]\" value=\"".db_result($result, $i,'skills_data_id')."\">".
						"<INPUT TYPE=text name=\"title[]\" size=100 value=\"".db_result($result, $i,'title')."\"></TD>".
					"</TR>".
					"<TR>".
						"<TD BGCOLOR=".$HTML->COLOR_HTMLBOX_TITLE.">Keywords (max 255 characters)</TD>".
					"</TR>".
					"<TR>".
						"<TD><textarea name=\"keywords[]\" rows=\"3\" cols=\"85\" wrap=\"soft\">".db_result($result, $i,'keywords')."</TEXTAREA></TD>".
					"</TR>".
					
				 "</table>";
				 "</TD></TR>";
			echo "</TABLE><br>";					
										
		}
	}
}

?>
