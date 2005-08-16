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

require_once('pre.php');
require_once('www/people/people_utils.php');

if (!$sys_use_people) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');

if ($group_id && (user_ismember($group_id, 'A'))) {
	$title = getStringFromRequest('title');
	$description = getStringFromRequest('description');
	$category_id = getStringFromRequest('category_id');
	$status_id = getStringFromRequest('status_id');
	$job_id = getStringFromRequest('job_id');
	$skill_id = getStringFromRequest('skill_id');
	$skill_level_id = getStringFromRequest('skill_level_id');
	$skill_year_id = getStringFromRequest('skill_year_id');

	if (getStringFromRequest('add_job')) {
		/*
			create a new job
		*/
		if (!$title || !$description || $category_id==100) {
			exit_error($Language->getText('people_editjob','error_missing'),$Language->getText('people_editjob','fill_in'));
		}
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}
		$sql="INSERT INTO people_job (group_id,created_by,title,description,post_date,status_id,category_id) ".
			"VALUES ('$group_id','". user_getid() ."','".htmlspecialchars($title)."','".htmlspecialchars($description)."','".time()."','1','$category_id')";
		$result=db_query($sql);
		if (!$result || db_affected_rows($result) < 1) {
			$feedback .= $Language->getText('people_editjob','job_insert_failed');
			echo db_error();
			form_release_key($_POST['form_key']);
		} else {
			$job_id=db_insertid($result,'people_job','job_id');
			$feedback .= $Language->getText('people_editjob','job_insert_ok');
		}

	} else if (getStringFromRequest('update_job')) {
		/*
			update the job's description, status, etc
		*/
		if (!$title || !$description || $category_id==100 || $status_id==100 || !$job_id) {
			//required info
			exit_error($Language->getText('people_editjob','error_missing'),$Language->getText('people_editjob','fill_in'));
		}

		$sql="UPDATE people_job SET title='".htmlspecialchars($title)."',description='".htmlspecialchars($description)."',status_id='$status_id',category_id='$category_id' ".
			"WHERE job_id='$job_id' AND group_id='$group_id'";
		$result=db_query($sql);
		if (!$result || db_affected_rows($result) < 1) {
			$feedback = $Language->getText('people_editjob','job_update_no');
			echo db_error();
		} else {
			$feedback = $Language->getText('people_editjob','job_update_ok');
		}

} else if (getStringFromRequest('add_to_job_inventory')) {
		/*
			add item to job inventory
		*/
		if ($skill_id == "xyxy" || $skill_level_id==100 || $skill_year_id==100  || !$job_id) {
			//required info
			exit_error($Language->getText('people_editjob','error_missing'),$Language->getText('people_editjob','fill_in'));
		}

		if (people_verify_job_group($job_id,$group_id)) {
			people_add_to_job_inventory($job_id,$skill_id,$skill_level_id,$skill_year_id);
			$feedback .= $Language->getText('people_editjob','job_update_ok');
		} else {
			$feedback .= $Language->getText('people_editjob','job_update_no_wrong_id');
		}

	} else if (getStringFromRequest('update_job_inventory')) {
		/*
			Change Skill level, experience etc.
		*/
		if ($skill_level_id==100 || $skill_year_id==100  || !$job_id || !$job_inventory_id) {
			//required info
			exit_error($Language->getText('people_editjob','error_missing'),$Language->getText('people_editjob','fill_in'));
		}

		if (people_verify_job_group($job_id,$group_id)) {
			$sql="UPDATE people_job_inventory SET skill_level_id='$skill_level_id',skill_year_id='$skill_year_id' ".
				"WHERE job_id='$job_id' AND job_inventory_id='$job_inventory_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= $Language->getText('people_editjob','job_skill_update_no');
				echo db_error();
			} else {
				$feedback .= $Language->getText('people_editjob','job_skill_update_ok');
			}
		} else {
			$feedback .= $Language->getText('people_editjob','job_skill_update_no_wrong_id');
		}

	} else if (getStringFromRequest('delete_from_job_inventory')) {
		/*
			remove this skill from this job
		*/
		if (!$job_id) {
			//required info
			exit_error($Language->getText('people_editjob','error_missing'),$Language->getText('people_editjob','fill_in'));
		}

		if (people_verify_job_group($job_id,$group_id)) {
			$sql="DELETE FROM people_job_inventory WHERE job_id='$job_id' AND job_inventory_id='$job_inventory_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= $Language->getText('people_editjob','job_skill_delete_no');
				echo db_error();
			} else {
				$feedback .= $Language->getText('people_editjob','job_skill_delete_ok');
			}
		} else {
			$feedback .= $Language->getText('people_editjob','job_skill_delete_no_wrong_id');
		}

	}

	/*
		Fill in the info to create a job
	*/
	people_header(array('title'=>$Language->getText('people_editjob','title')));

	//for security, include group_id
	$sql="SELECT * FROM people_job WHERE job_id='$job_id' AND group_id='$group_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		echo db_error();
		$feedback .= $Language->getText('people_editjob','posting_fetch_failed');
		echo '<h2>'.$Language->getText('people_editjob','no_such').'</h2>';
	} else {

		echo $Language->getText('people_editjob','skill_explains').'

		<p /><form action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="job_id" value="'.$job_id.'" />
		<strong>'.$Language->getText('people','category').':</strong><br />
		'. people_job_category_box('category_id',db_result($result,0,'category_id')) .'
		<p>
		<strong>'.$Language->getText('people','status').':</strong><br />
		'. people_job_status_box('status_id',db_result($result,0,'status_id')) .'</p>
		<p>
		<strong>'.$Language->getText('people','short_description').':</strong><br />
		<input type="text" name="title" value="'. db_result($result,0,'title') .'" size="40" maxlength="60" /></p>
		<p>
		<strong>'.$Language->getText('people','long_description').':</strong><br />
		<textarea name="description" rows="10" cols="60">'. db_result($result,0,'description') .'</textarea></p>
		<p>
		<input type="submit" name="update_job" value="'.$Language->getText('people_editjob','update_description').'" /></p>
		</form>';

		//now show the list of desired skills
		echo '<p>'.people_edit_job_inventory($job_id,$group_id) . '</p>';
		echo '<p /><form action="/people/" method="post"><input type="submit" name="submit" value="'.$Language->getText('people_editjob','finished').'" /></form>';

	}

	people_footer(array());

} else {
	/*
		Not logged in or insufficient privileges
	*/
	if (!$group_id) {
		exit_no_group();
	} else {
		exit_permission_denied();
	}
}

?>
