<?php
/**
 * Help Wanted 
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'people/people_utils.php';

if (!forge_get_config('use_people')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');
$job_id = getIntFromRequest('job_id');

if (user_ismember(1,'A')) {

	if (getStringFromRequest('post_changes')) {
		/*
			Update the database
		*/

		if (getStringFromRequest('people_cat')) {
			$cat_name = getStringFromRequest('cat_name');
			if (!form_key_is_valid(getStringFromRequest('form_key'))) {
				exit_form_double_submit('admin');
			}
			$result=db_query_params('INSERT INTO people_job_category (name) VALUES ($1)', array($cat_name));
			if (!$result) {
				form_release_key(getStringFromRequest("form_key"));
				$error_msg .= _(' Error inserting value: ').db_error();
			}

			$feedback .= _('Category Inserted');

		} else if (getStringFromRequest('people_skills')) {
			$skill_name = getStringFromRequest('skill_name');
			if (!form_key_is_valid(getStringFromRequest('form_key'))) {
				exit_form_double_submit('admin');
			}
			$result=db_query_params('INSERT INTO people_skill (name) VALUES ($1)', array($skill_name));
			if (!$result) {
				form_release_key(getStringFromRequest("form_key"));
				$error_msg .= _('Error inserting value: ').db_error();
			}

			$feedback .= _('Skill Inserted');
		}

	} 
	/*
		Show UI forms
	*/

	if (getStringFromRequest('people_cat')) {
		/*
			Show categories and blank row
		*/
		people_header(array ('title'=>'Add/Change Categories'));

		/*
			List of possible categories for this group
		*/
		$result=db_query_params('SELECT category_id,name FROM people_job_category', array());
		echo "<p>";
		if ($result && db_numrows($result) > 0) {
			ShowResultSet($result,'Existing Categories','people_cat');
		} else {
			echo '<p class="error">No job categories</p>';
			echo db_error();
		}
		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
		<input type="hidden" name="people_cat" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>">
		<h4>New Category Name:</h4>
		<input type="text" name="cat_name" value="" size="15" maxlength="30" /><br />
		<div class="warning">Once you add a category, it cannot be deleted</div>
		<p>
		<input type="submit" name="submit" value="SUBMIT"></p>
		</form></p>
		<?php

		people_footer(array());

	} else if (getStringFromRequest('people_skills')) {
		/*
			Show people_groups and blank row
		*/
		people_header(array ('title'=>'Add/Change People Skills'));

		/*
			List of possible people_groups for this group
		*/
		$result=db_query_params('SELECT skill_id,name FROM people_skill', array());
		echo "<p>";
		if ($result && db_numrows($result) > 0) {
			ShowResultSet($result,"Existing Skills","people_skills");
		} else {
			echo db_error();
			echo "\n<h2>No Skills Found</h2>";
		}
		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
		<input type="hidden" name="people_skills" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>">
		<h4>New Skill Name:</h4>
		<input type="text" name="skill_name" value="" size="15" maxlength="30" /><br />
		<div class="warning">Once you add a skill, it cannot be deleted</div>
		<p>
		<input type="submit" name="submit" value="SUBMIT"></p>
		</form></p>
		<?php

		people_footer(array());

	} else {
		/*
			Show main page
		*/

		people_header(array ('title'=>'People Administration'));

		echo '<p>
			<a href="'.getStringFromServer('PHP_SELF').'?people_cat=1">Add Job Categories</a><br />';
	//	echo "\nAdd categories of bugs like, 'mail module','gant chart module','interface', etc<p>";

		echo "\n<a href=\"".getStringFromServer('PHP_SELF')."?people_skills=1\">Add Job Skills</a><br />";
	//	echo "\nAdd Groups of bugs like 'future requests','unreproducible', etc<p>";

		people_footer(array());
	}

} else {
	exit_permission_denied('home');
}
?>
