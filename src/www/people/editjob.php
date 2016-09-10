<?php
/**
 * Help Wanted
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010-2014,2016, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'people/people_utils.php';

global $HTML;

if (!forge_get_config('use_people')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');

if ($group_id && (forge_check_perm('project_admin', $group_id))) {
	$title = getStringFromRequest('title');
	$description = getStringFromRequest('description');
	$category_id = getIntFromRequest('category_id');
	$status_id = getIntFromRequest('status_id');
	$job_id = getIntFromRequest('job_id');
	$job_inventory_id = getIntFromRequest('job_inventory_id');
	$skill_id = getIntFromRequest('skill_id');
	$skill_level_id = getIntFromRequest('skill_level_id');
	$skill_year_id = getIntFromRequest('skill_year_id');

	if (getStringFromRequest('add_job')) {
		/*
			create a new job
		*/
		if (!$title || !$description || $category_id == 'xzxz') {
			exit_missing_param('',array(_('Title'),_('Description'),_('Category')),'admin');
		}
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('admin');
		}
		$result=db_query_params("INSERT INTO people_job (group_id,created_by,title,description,post_date,status_id,category_id)
					VALUES ($1, $2, $3, $4, $5, $6, $7)",
					array($group_id, user_getid(), htmlspecialchars($title), htmlspecialchars($description), time(), '1',$category_id));
		if (!$result || db_affected_rows($result) < 1) {
			$error_msg .= _('Job skill insert failed')._(': ').db_error();
			form_release_key(getStringFromRequest("form_key"));
		} else {
			$job_id=db_insertid($result,'people_job','job_id');
			$feedback .= _('Job inserted successfully');
		}

	} elseif (getStringFromRequest('update_job')) {
		/*
			update the job's description, status, etc
		*/
		if (!$title || !$description || $category_id== 'xzxz' || $status_id== 'xzxz' || !$job_id) {
			//required info
			exit_missing_param('',array(_('Title'),_('Description'),_('Category'),_('Status'),_('Job')),'admin');
		}

		$result=db_query_params("UPDATE people_job SET title=$1,description=$2,status_id=$3,category_id=$4 WHERE job_id=$5 AND group_id=$6",
			array(htmlspecialchars($title), htmlspecialchars($description), $status_id, $category_id, $job_id, $group_id));
		if (!$result || db_affected_rows($result) < 1) {
			$error_msg = _('Job skill update failed')._(': ').db_error();
		} else {
			$feedback = _('Job updated successfully');
		}

	} elseif (getStringFromRequest('add_to_job_inventory')) {
		/*
			add item to job inventory
		*/
		if ($skill_id == 'xzxz' || $skill_level_id == 'xzxz' || $skill_year_id== 'xzxz'  || !$job_id) {
			//required info
			exit_missing_param('',array(_('Skill'),_('Skill Level'),_('Skill Year'),_('Job')),'admin');
		}

		if (people_verify_job_group($job_id,$group_id)) {
			if (people_add_to_job_inventory($job_id,$skill_id,$skill_level_id,$skill_year_id)) {;
				$feedback .= _('Job updated successfully');
			}
		} else {
			$error_msg .= _('Job update failed')._(': ')._('wrong project_id');
		}

	} elseif (getStringFromRequest('update_job_inventory')) {
		/*
			Change Skill level, experience etc.
		*/
		if ($skill_level_id == 'xzxz' || $skill_year_id == 'xzxz'  || !$job_id || !$job_inventory_id) {
			//required info
			exit_missing_param('',array(_('Skill Level'),_('Skill Year'),_('Job'),_('Job Inventory')),'admin');
		}

		if (people_verify_job_group($job_id,$group_id)) {
			$result=db_query_params("UPDATE people_job_inventory SET skill_level_id=$1,skill_year_id=$2 WHERE job_id=$3 AND job_inventory_id=$4",
				array($skill_level_id, $skill_year_id, $job_id, $job_inventory_id));
			if (!$result || db_affected_rows($result) < 1) {
				$error_msg .= _('Job skill update failed')._(': ').db_error();
			} else {
				$feedback .= _('Job skill updated successfully');
			}
		} else {
			$error_msg .= _('Job skill update failed')._(': ')._('wrong project_id');
		}

	} elseif (getStringFromRequest('delete_from_job_inventory')) {
		/*
			remove this skill from this job
		*/
		if (!$job_id) {
			//required info
			exit_missing_param('',array(_('Job ID')),'admin');
		}

		if (people_verify_job_group($job_id,$group_id)) {
			$result = db_query_params("DELETE FROM people_job_inventory WHERE job_id=$1 AND job_inventory_id=$2", array($job_id, $job_inventory_id));
			if (!$result || db_affected_rows($result) < 1) {
				$error_msg .= _('Job skill delete failed')._(': ').db_error();
			} else {
				$feedback .= _('Job skill deleted successfully');
			}
		} else {
			$error_msg .= _('Job skill delete failed')._(': ')._('wrong project_id');
		}

	}

	/*
		Fill in the info to create a job
	*/
	people_header(array('title'=>_('Edit Job')));

	//for security, include group_id
	$result=db_query_params("SELECT * FROM people_job WHERE job_id=$1 AND group_id=$2", array($job_id, $group_id));
	if (!$result || db_numrows($result) < 1) {
		$error_msg .= _('Posting fetch failed')._(': ').db_error();
		echo '<h2>'._('No such posting for this project').'</h2>';
	} else {

		echo '<p>'
			. _('Now you can edit/change the list of skills attached to this posting. Developers will be able to match their skills with your requirements.')
			.'</p><p>'
			. _('All postings are automatically closed after two weeks.');
		echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
		echo '<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="job_id" value="'.$job_id.'" />
		<strong>'._('Category').'</strong>'.utils_requiredField().'<br />
		'. people_job_category_box('category_id',db_result($result,0,'category_id')) .'
		<p>
		<strong>'._('Status').'</strong>'.utils_requiredField().'<br />
		'. people_job_status_box('status_id',db_result($result,0,'status_id')) .'</p>
		<p>
		<strong>'._('Short Description').utils_requiredField()._(':').'</strong><br />
		<input type="text" name="title" value="'. db_result($result,0,'title') .'" size="40" maxlength="60" required="required" /></p>
		<p>
		<strong>'._('Long Description').utils_requiredField()._(':').'</strong><br />
		<textarea name="description" rows="10" cols="60" required="required" >'. db_result($result,0,'description') .'</textarea></p>
		<p>
		<input type="submit" name="update_job" value="'._('Update Descriptions').'" /></p>';
		echo $HTML->closeForm();
		//now show the list of desired skills
		echo '<p>'.people_edit_job_inventory($job_id,$group_id).'</p>';
		echo $HTML->openForm(array('action' => '/people/', 'method' => 'post'));
		echo '<input type="submit" name="submit" value="'._('Finished').'" />';
		echo $HTML->closeForm();
	}

	people_footer();

} else {
	/*
		Not logged in or insufficient privileges
	*/
	if (!$group_id) {
		exit_no_group();
	} else {
		exit_permission_denied('home');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
