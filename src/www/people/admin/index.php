<?php
/**
 * Help Wanted
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'people/people_utils.php';

global $HTML;

if (!forge_get_config('use_people')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');
$job_id = getIntFromRequest('job_id');

if (forge_check_global_perm('forge_admin')) {
	if (getStringFromRequest('post_changes')) {
		/*
			Update the database
		*/
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('admin');
		}
		form_release_key(getStringFromRequest('form_key'));
		if (getStringFromRequest('people_cat')) {
			$cat_name = getStringFromRequest('cat_name');
			if (!empty($cat_name)) {
				$result = db_query_params('INSERT INTO people_job_category (name) VALUES ($1)', array($cat_name));
				if (!$result  || db_affected_rows($result) < 1) {
					$error_msg .= _('Insert Error')._(': ').db_error();
				} else {
					$feedback .= _('Category Inserted');
				}
			} else {
				$error_msg .= _('Missing category name.');
			}

		} elseif (getStringFromRequest('people_skills')) {
			$skill_name = getStringFromRequest('skill_name');
			if (!empty($skill_name)) {
				$result = db_query_params('INSERT INTO people_skill (name) VALUES ($1)', array($skill_name));
				if (!$result  || db_affected_rows($result) < 1) {
					$error_msg .= _('Insert Error')._(': ').db_error();
				} else {
					$feedback .= _('Skill Inserted');
				}
			} else {
				$error_msg .= _('Missing skill name.');
			}
		}

	}
	/*
		Show UI forms
	*/

	if (getStringFromRequest('people_cat')) {
		/*
			Show categories and blank row
		*/
		people_header(array ('title'=>_('Add/Change Categories')));

		/*
			List of possible categories for this group
		*/
		$result=db_query_params('SELECT category_id,name FROM people_job_category', array());
		echo "<p>";
		if ($result && db_numrows($result) > 0) {
			ShowResultSet($result,_('Existing Categories'), 'people_cat');
		} else {
			echo $HTML->error_msg(_('No job categories').db_error());
		}

		echo $HTML->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
		?>
		<p>
		<input type="hidden" name="people_cat" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>">
		<label for="cat_name">
			<strong><?php echo _('New Category Name').utils_requiredField()._(':'); ?></strong>
		</label>
		<input type="text" id="cat_name" name="cat_name" value="" size="15" maxlength="30" required="required" />
		</p>
		<?php
		echo $HTML->warning_msg(_('Once you add a category, it cannot be deleted'));
		?>
		<p>
		<input type="submit" name="submit" value="<?php echo _('Submit'); ?>"></p>
		<?php
		echo $HTML->closeForm();
		people_footer();

	} elseif (getStringFromRequest('people_skills')) {
		/*
			Show people_groups and blank row
		*/
		people_header(array('title' => _('Add/Change People Skills')));

		/*
			List of possible people_groups for this group
		*/
		$result=db_query_params('SELECT skill_id,name FROM people_skill', array());
		echo "<p>";
		if ($result && db_numrows($result) > 0) {
			ShowResultSet($result,_('Existing Skills'), 'people_skills');
		} else {
			echo db_error();
			echo "\n<h2>"._('No Skills Found').'</h2>';
		}

		echo $HMTL->openForm(array('action' => getStringFromServer('PHP_SELF'), 'method' => 'post'));
		?>
		<p>
		<input type="hidden" name="people_skills" value="y" />
		<input type="hidden" name="post_changes" value="y" />
		<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>">
		<label for="skill_name">
			<strong><?php echo _('New Skill Name')._(':'); ?></strong>
		</label>
		<input type="text" id="skill_name" name="skill_name" value="" size="15" maxlength="30" />
		</p>
		<?php
		echo $HTML->warning_msg(_('Once you add a skill, it cannot be deleted'));
		?>
		<p>
		<input type="submit" name="submit" value="<?php echo _('Submit'); ?>"></p>
		<?php
		echo $HTML->closeForm();
		people_footer();
	} else {
		/*
			Show main page
		*/
		people_header(array('title' => _('People Administration')));
		echo '<p>
			'.util_make_link('/people/admin/?people_cat=1', _('Add Job Categories')).'<br />';
	//	echo "\nAdd categories of bugs like, 'mail module','gant chart module','interface', etc<p>";
		echo "\n".util_make_link('/people/admin/?people_skills=1', _('Add Job Skills')).'<br />';
	//	echo "\nAdd Groups of bugs like 'future requests','unreproducible', etc<p>";
		echo '</p>';
		people_footer();
	}
} else {
	exit_permission_denied('home');
}
