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

require_once('../env.inc.php');
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
			exit_error(_('error - missing info'),_('Fill in all required fields'));
		}
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}
		$sql="INSERT INTO people_job (group_id,created_by,title,description,post_date,status_id,category_id) ".
			"VALUES ('$group_id','". user_getid() ."','".htmlspecialchars($title)."','".htmlspecialchars($description)."','".time()."','1','$category_id')";
		$result=db_query($sql);
		if (!$result || db_affected_rows($result) < 1) {
			$feedback .= _('JOB insert FAILED');
			echo db_error();
			form_release_key(getStringFromRequest("form_key"));
		} else {
			$job_id=db_insertid($result,'people_job','job_id');
			$feedback .= _('JOB inserted successfully');
		}

	} else if (getStringFromRequest('update_job')) {
		/*
			update the job's description, status, etc
		*/
		if (!$title || !$description || $category_id==100 || $status_id==100 || !$job_id) {
			//required info
			exit_error(_('error - missing info'),_('Fill in all required fields'));
		}

		$sql="UPDATE people_job SET title='".htmlspecialchars($title)."',description='".htmlspecialchars($description)."',status_id='$status_id',category_id='$category_id' ".
			"WHERE job_id='$job_id' AND group_id='$group_id'";
		$result=db_query($sql);
		if (!$result || db_affected_rows($result) < 1) {
			$feedback = _('JOB update FAILED');
			echo db_error();
		} else {
			$feedback = _('JOB updated successfully');
		}

} else if (getStringFromRequest('add_to_job_inventory')) {
		/*
			add item to job inventory
		*/
		if ($skill_id == "xyxy" || $skill_level_id==100 || $skill_year_id==100  || !$job_id) {
			//required info
			exit_error(_('error - missing info'),_('Fill in all required fields'));
		}

		if (people_verify_job_group($job_id,$group_id)) {
			people_add_to_job_inventory($job_id,$skill_id,$skill_level_id,$skill_year_id);
			$feedback .= _('JOB updated successfully');
		} else {
			$feedback .= _('JOB update failed - wrong project_id');
		}

	} else if (getStringFromRequest('update_job_inventory')) {
		/*
			Change Skill level, experience etc.
		*/
		if ($skill_level_id==100 || $skill_year_id==100  || !$job_id || !$job_inventory_id) {
			//required info
			exit_error(_('error - missing info'),_('Fill in all required fields'));
		}

		if (people_verify_job_group($job_id,$group_id)) {
			$sql="UPDATE people_job_inventory SET skill_level_id='$skill_level_id',skill_year_id='$skill_year_id' ".
				"WHERE job_id='$job_id' AND job_inventory_id='$job_inventory_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= _('JOB skill update FAILED');
				echo db_error();
			} else {
				$feedback .= _('JOB skill updated successfully');
			}
		} else {
			$feedback .= _('JOB skill update failed - wrong project_id');
		}

	} else if (getStringFromRequest('delete_from_job_inventory')) {
		/*
			remove this skill from this job
		*/
		if (!$job_id) {
			//required info
			exit_error(_('error - missing info'),_('Fill in all required fields'));
		}

		if (people_verify_job_group($job_id,$group_id)) {
			$sql="DELETE FROM people_job_inventory WHERE job_id='$job_id' AND job_inventory_id='$job_inventory_id'";
			$result=db_query($sql);
			if (!$result || db_affected_rows($result) < 1) {
				$feedback .= _('JOB skill delete FAILED');
				echo db_error();
			} else {
				$feedback .= _('JOB skill deleted successfully');
			}
		} else {
			$feedback .= _('JOB skill delete failed - wrong project_id');
		}

	}

	/*
		Fill in the info to create a job
	*/
	people_header(array('title'=>_('Edit Job')));

	//for security, include group_id
	$sql="SELECT * FROM people_job WHERE job_id='$job_id' AND group_id='$group_id'";
	$result=db_query($sql);
	if (!$result || db_numrows($result) < 1) {
		echo db_error();
		$feedback .= _('POSTING fetch FAILED');
		echo '<h2>'._('No Such posting For This Project').'</h2>';
	} else {

		echo _('<p>Now you can edit/change the list of skills attached to this posting. Developers will be able to match their skills with your requirements.</p><p>All postings are automatically closed after two weeks.</p>').'

		<p /><form action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="job_id" value="'.$job_id.'" />
		<strong>'._('Category').':</strong><br />
		'. people_job_category_box('category_id',db_result($result,0,'category_id')) .'
		<p>
		<strong>'._('Status').':</strong><br />
		'. people_job_status_box('status_id',db_result($result,0,'status_id')) .'</p>
		<p>
		<strong>'._('Short Description').':</strong><br />
		<input type="text" name="title" value="'. db_result($result,0,'title') .'" size="40" maxlength="60" /></p>
		<p>
		<strong>'._('Long Description').':</strong><br />
		<textarea name="description" rows="10" cols="60">'. db_result($result,0,'description') .'</textarea></p>
		<p>
		<input type="submit" name="update_job" value="'._('Update Descriptions').'" /></p>
		</form>';

		//now show the list of desired skills
		echo '<p>'.people_edit_job_inventory($job_id,$group_id) . '</p>';
		echo '<p /><form action="/people/" method="post"><input type="submit" name="submit" value="'._('Finished').'" /></form>';

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
