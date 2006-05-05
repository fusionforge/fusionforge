<?php
/**
 * GForge Help Wanted 
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function people_header($params) {
	global $group_id,$job_id,$HTML;

	if ($group_id) {
		$params['toptab']='people';
		$params['group']=$group_id;
		echo site_project_header($params);
	} else {
		echo $HTML->header($params);
	}

	if ($group_id && $job_id) {
		echo ' | <a href="/people/editjob.php?group_id='. $group_id .'&amp;job_id='. $job_id .'">Edit Job</a>';
	}
}

function people_footer($params) {
	global $feedback, $HTML;
	html_feedback_bottom($feedback);
	$HTML->footer($params);
}

function people_skill_box($name='skill_id',$checked='xyxy') {
	global $PEOPLE_SKILL;
	if (!$PEOPLE_SKILL) {
		//will be used many times potentially on a single page
		$sql="SELECT * FROM people_skill ORDER BY name ASC";
		$PEOPLE_SKILL=db_query($sql);
	}
	return html_build_select_box($PEOPLE_SKILL,$name,$checked);
}

function people_skill_level_box($name='skill_level_id',$checked='xyxy') {
	global $PEOPLE_SKILL_LEVEL;
	if (!$PEOPLE_SKILL_LEVEL) {
		//will be used many times potentially on a single page
		$sql="SELECT * FROM people_skill_level";
		$PEOPLE_SKILL_LEVEL=db_query($sql);
	}
	return html_build_select_box ($PEOPLE_SKILL_LEVEL,$name,$checked);
}

function people_skill_year_box($name='skill_year_id',$checked='xyxy') {
	global $PEOPLE_SKILL_YEAR;
	if (!$PEOPLE_SKILL_YEAR) {
		//will be used many times potentially on a single page
		$sql="SELECT * FROM people_skill_year";
		$PEOPLE_SKILL_YEAR=db_query($sql);
	}
	return html_build_select_box ($PEOPLE_SKILL_YEAR,$name,$checked);
}

function people_job_status_box($name='status_id',$checked='xyxy') {
	$sql="SELECT * FROM people_job_status";
	$result=db_query($sql);
	return html_build_select_box ($result,$name,$checked);
}

function people_job_category_box($name='category_id',$checked='xyxy') {
	$sql="SELECT category_id,name FROM people_job_category WHERE private_flag=0";
	$result=db_query($sql);
	return html_build_select_box ($result,$name,$checked);
}

function people_add_to_skill_inventory($skill_id,$skill_level_id,$skill_year_id) {
	global $feedback, $Language;
	if (session_loggedin()) {
		// check required fields
		if (!$skill_id || $skill_id == "xyxy") {
			$feedback .= $Language->getText('people','must_select_a_skill');
		} else {
		//check if they've already added this skill
		$sql="SELECT * FROM people_skill_inventory WHERE user_id='". user_getid() ."' AND skill_id='$skill_id'";
		$result=db_query($sql);
		if (!$result || db_numrows($result) < 1) {
			//skill not already in inventory
			$sql="INSERT INTO people_skill_inventory (user_id,skill_id,skill_level_id,skill_year_id) ".
				"VALUES ('". user_getid() ."','$skill_id','$skill_level_id','$skill_year_id')";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= $Language->getText('people','error_inserting');
				echo db_error();
			} else {
				$feedback .= $Language->getText('people','added_skill');
			}
		} else {
			$feedback .= $Language->getText('people','error_skill_already');
		}
		}
	} else {
		echo '<h1>'.$Language->getText('people','must_be_loggin').'</h1>';
	}
}

function people_show_skill_inventory($user_id) {
	global $Language;
	$sql="SELECT people_skill.name AS skill_name, people_skill_level.name AS level_name, people_skill_year.name AS year_name ".
		"FROM people_skill_year,people_skill_level,people_skill,people_skill_inventory ".
		"WHERE people_skill_year.skill_year_id=people_skill_inventory.skill_year_id ".
		"AND people_skill_level.skill_level_id=people_skill_inventory.skill_level_id ".
		"AND people_skill.skill_id=people_skill_inventory.skill_id ".
		"AND people_skill_inventory.user_id='$user_id'";
	$result=db_query($sql);

	$title_arr=array();
	$title_arr[]=$Language->getText('people','skill');
	$title_arr[]=$Language->getText('people','level');
	$title_arr[]=$Language->getText('people','experience');


	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<h2>'.$Language->getText('people','no_skill_inventory_setup_up').'</h2>';
		echo db_error();
	} else {
		for ($i=0; $i < $rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td>'.db_result($result,$i,'skill_name').'</td>
				<td>'.db_result($result,$i,'level_name').'</td>
				<td>'.db_result($result,$i,'year_name').'</td></tr>';

		}
	}

	echo $GLOBALS['HTML']->listTableBottom();
}

function people_edit_skill_inventory($user_id) {
	global $Language;
	$sql="SELECT * FROM people_skill_inventory WHERE user_id='$user_id'";
	$result=db_query($sql);

	$title_arr=array();
	$title_arr[]=$Language->getText('people','skill');
	$title_arr[]=$Language->getText('people','level');
	$title_arr[]=$Language->getText('people','experience');
	$title_arr[]=$Language->getText('people','action');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<TH><td colspan="4">'.$Language->getText('people','no_skill_setupup').'</h2></td></TH>';
		echo db_error();
	} else {
		for ($i=0; $i < $rows; $i++) {
			echo '
			<form action="'.getStringFromServer('PHP_SELF').'" method="post">
			<input type="hidden" name="skill_inventory_id" value="'.db_result($result,$i,'skill_inventory_id').'" />
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td>'. people_get_skill_name(db_result($result,$i,'skill_id')) .'</td>
				<td>'. people_skill_level_box('skill_level_id',db_result($result,$i,'skill_level_id')). '</td>
				<td>'. people_skill_year_box('skill_year_id',db_result($result,$i,'skill_year_id')). '</td>
				<td nowrap="nowrap"><input type="submit" name="update_skill_inventory" value="'.$Language->getText('general','update').'" /> &nbsp;
					<input type="submit" name="delete_from_skill_inventory" value="'.$Language->getText('general','delete').'" /></td>
				</tr></form>';
		}

	}
	//add a new skill
	$i++; //for row coloring

	echo '
	<tr class="tableheading"><td colspan="4">'.$Language->getText('people','add_new_skill').'/td></tr>
	<form action="'.getStringFromServer('PHP_SELF').'" method="post">
	<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
		<td>'. people_skill_box('skill_id'). '</td>
		<td>'. people_skill_level_box('skill_level_id'). '</td>
		<td>'. people_skill_year_box('skill_year_id'). '</td>
		<td nowrap="nowrap"><input type="submit" name="add_to_skill_inventory" value="'.$Language->getText('people','add_skill').'" /></td>
	</tr></form>';

	echo $GLOBALS['HTML']->listTableBottom();

}


function people_add_to_job_inventory($job_id,$skill_id,$skill_level_id,$skill_year_id) {
	global $feedback, $Language;
	if (session_loggedin()) {
		//check if they've already added this skill
		$sql="SELECT * FROM people_job_inventory WHERE job_id='$job_id' AND skill_id='$skill_id'";
		$result=db_query($sql);
		if (!$result || db_numrows($result) < 1) {
			//skill isn't already in this inventory
			$sql="INSERT INTO people_job_inventory (job_id,skill_id,skill_level_id,skill_year_id) ".
				"VALUES ('$job_id','$skill_id','$skill_level_id','$skill_year_id')";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= $Language->getText('people','error_inserting');
				echo db_error();
			} else {
				$feedback .= $Language->getText('people','added_skill');
			}
		} else {
			$feedback .= $Language->getText('people','error_skill_already');
		}

	} else {
		echo '<h1>'.$Language->getText('people','must_be_loggin').'</h1>';
	}
}

function people_show_job_inventory($job_id) {
	global $Language;
	$sql="SELECT people_skill.name AS skill_name, people_skill_level.name AS level_name, people_skill_year.name AS year_name ".
		"FROM people_skill_year,people_skill_level,people_skill,people_job_inventory ".
		"WHERE people_skill_year.skill_year_id=people_job_inventory.skill_year_id ".
		"AND people_skill_level.skill_level_id=people_job_inventory.skill_level_id ".
		"AND people_skill.skill_id=people_job_inventory.skill_id ".
		"AND people_job_inventory.job_id='$job_id'";
	$result=db_query($sql);

	$title_arr=array();
	$title_arr=array();
	$title_arr[]=$Language->getText('people','skill');
	$title_arr[]=$Language->getText('people','level');
	$title_arr[]=$Language->getText('people','experience');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<tr><td><h2>'.$Language->getText('people','no_skill_inventory_setup_up').'</h2></td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i < $rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td>'.db_result($result,$i,'skill_name').'</td>
				<td>'.db_result($result,$i,'level_name').'</td>
				<td>'.db_result($result,$i,'year_name').'</td></tr>';

		}
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

function people_verify_job_group($job_id,$group_id) {
	$sql="SELECT * FROM people_job WHERE job_id='$job_id' AND group_id='$group_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		return false;
	} else {
		return true;
	}
}

function people_get_skill_name($skill_id) {
	global $Language;
	$sql="SELECT name FROM people_skill WHERE skill_id='$skill_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		return $Language->getText('people','invalid_id');
	} else {
		return db_result($result,0,'name');
	}
}

function people_get_category_name($category_id) {
	$sql="SELECT name FROM people_job_category WHERE category_id='$category_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		return 'Invalid ID';
	} else {
		return db_result($result,0,'name');
	}
}

// FIXME
// This function does not produce valid XHTML; however, I could not
// think of a way of turning into valid XHTML without the resulting
// table looking like poo.
function people_edit_job_inventory($job_id,$group_id) {
	global $Language, $HTML;
	$sql="SELECT * FROM people_job_inventory WHERE job_id='$job_id'";
	$result=db_query($sql);

	$title_arr=array();
	$title_arr[]=$Language->getText('people','skill').utils_requiredField();
	$title_arr[]=$Language->getText('people','level').utils_requiredField();
	$title_arr[]=$Language->getText('people','experience').utils_requiredField();
	$title_arr[]=$Language->getText('people','action');

	echo $HTML->listTableTop ($title_arr);

	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
			<tr><td colspan="4"><h2>'.$Language->getText('people','no_skill_inventory_setup_up').'</h2></td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i < $rows; $i++) {
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) . '>
			<form action="'.getStringFromServer('PHP_SELF').'" method="post">
			<input type="hidden" name="job_inventory_id" value="'. db_result($result,$i,'job_inventory_id') .'" />
			<input type="hidden" name="job_id" value="'. db_result($result,$i,'job_id') .'" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
				<td width="25%">'. people_get_skill_name(db_result($result,$i,'skill_id')) . '</td>
				<td width="25%">'. people_skill_level_box('skill_level_id',db_result($result,$i,'skill_level_id')). '</td>
				<td width="25%">'. people_skill_year_box('skill_year_id',db_result($result,$i,'skill_year_id')). '</td>
				<td width="25%" nowrap="nowrap"><input type="submit" name="update_job_inventory" value="'.$Language->getText('general','update').'" /> &nbsp;
					<input type="submit" name="delete_from_job_inventory" value="'.$Language->getText('general','delete').'" /></td>
				</form></tr>';
		}

	}
	//add a new skill
	$i++; //for row coloring

	echo '
	<tr><td colspan="4"><h3>'.$Language->getText('people','add_new_skill').'</h3></td></tr>
	<tr '. $HTML->boxGetAltRowStyle($i) . '>
	<form action="'.getStringFromServer('PHP_SELF').'" method="post">
	<input type="hidden" name="job_id" value="'. $job_id .'" />
	<input type="hidden" name="group_id" value="'.$group_id.'" />
		<td width="25%">'. people_skill_box('skill_id'). '</td>
		<td width="25%">'. people_skill_level_box('skill_level_id'). '</td>
		<td width="25%">'. people_skill_year_box('skill_year_id'). '</td>
		<td width="25%" nowrap="nowrap"><input type="submit" name="add_to_job_inventory" value="'.$Language->getText('people','add_skill').'" /></td>
	</form></tr>';

	echo $HTML->listTableBottom();
}

function people_show_category_table() {
	global $Language;

	//show a list of categories in a table
	//provide links to drill into a detail page that shows these categories

	$title_arr=array();
	$title_arr[]=$Language->getText('people','category');;

	$return = $GLOBALS['HTML']->listTableTop ($title_arr);

/*
	$sql="SELECT pjc.category_id, pjc.name, count(*) as total ". 
		"FROM people_job_category pjc,people_job pj ".
		"WHERE pjc.category_id=pj.category_id ".
		"AND pj.status_id=1 ".
		"GROUP BY pjc.category_id, pjc.name";
*/
	$sql="SELECT pjc.category_id, pjc.name, COUNT(pj.category_id) AS total ". 
		"FROM people_job_category pjc LEFT JOIN people_job pj ".
                "ON pjc.category_id=pj.category_id ".
                "WHERE pjc.private_flag=0 ".
		"AND (pj.status_id=1 OR pj.status_id IS NULL) ".
		"GROUP BY pjc.category_id, pjc.name";

	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		$return .= '<tr><td><h2>'.$Language->getText('people','no_categories_found').'</h2></td></tr>';
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo db_error();
			$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td><a href="/people/?category_id='.
				db_result($result,$i,'category_id') .'">'.
				db_result($result,$i,'name') .'</a> ('. db_result($result,$i,'total') .')</td></tr>';
		}
	}
	$return .= $GLOBALS['HTML']->listTableBottom();
	return $return;
}

function people_show_project_jobs($group_id) {
	//show open jobs for this project
	$sql="SELECT people_job.group_id,people_job.job_id,groups.group_name,groups.unix_group_name,people_job.title,people_job.post_date,people_job_category.name AS category_name ".
		"FROM people_job,people_job_category,groups ".
		"WHERE people_job.group_id='$group_id' ".
		"AND people_job.group_id=groups.group_id ".
		"AND people_job.category_id=people_job_category.category_id ".
		"AND people_job.status_id=1 ORDER BY post_date DESC";
	$result=db_query($sql);

	return people_show_job_list($result);
}

function people_show_category_jobs($category_id) {
	//show open jobs for this category
	$sql="SELECT people_job.group_id,people_job.job_id,groups.unix_group_name,groups.group_name,people_job.title,people_job.post_date,people_job_category.name AS category_name ".
		"FROM people_job,people_job_category,groups ".
		"WHERE people_job.category_id='$category_id' ".
		"AND people_job.group_id=groups.group_id ".
		"AND people_job.category_id=people_job_category.category_id ".
		"AND people_job.status_id=1 ORDER BY post_date DESC";
	$result=db_query($sql);

	return people_show_job_list($result);
}

function people_show_job_list($result) {
	global $sys_datefmt, $Language;
	//takes a result set from a query and shows the jobs

	//query must contain 'group_id', 'job_id', 'title', 'category_name' and 'status_name'

	$title_arr=array();
	$title_arr[]=$Language->getText('people','title_array');
	$title_arr[]=$Language->getText('people','category');
	$title_arr[]=$Language->getText('people','date_opened');
	$title_arr[]= $Language->getText('people','project',$GLOBALS['sys_name']);

	$return = $GLOBALS['HTML']->listTableTop ($title_arr);

	$rows=db_numrows($result);
	if (!isset($i)){$i=1;}
	if ($rows < 1) {
		$return .= '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td class="error" colspan="4">'.$Language->getText('people','none_found'). db_error() .'</td></tr>';
	} else {
		for ($i=0; $i < $rows; $i++) {
			$return .= '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .
					'><td><a href="/people/viewjob.php?group_id='.
					db_result($result,$i,'group_id') .'&amp;job_id='.
					db_result($result,$i,'job_id') .'">'.
					db_result($result,$i,'title') .'</a></td><td>'.
					db_result($result,$i,'category_name') .'</td><td>'.
					date($sys_datefmt,db_result($result,$i,'post_date')) .
					'</td><td><a href="/projects/'.strtolower(db_result($result,$i,'unix_group_name')).'/">'.
					db_result($result,$i,'group_name') .'</a></td></tr>';
		}
	}

	$return .= $GLOBALS['HTML']->listTableBottom();

	return $return;
}

?>
