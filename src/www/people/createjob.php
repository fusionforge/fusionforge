<?php
/**
 * Help Wanted
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2014-2015, Franck Villaume - TrivialDev
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
require_once $gfwww.'project/admin/project_admin_utils.php';

if (!forge_get_config('use_people')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');

if ($group_id && (forge_check_perm('project_admin', $group_id))) {

	project_admin_header(array());

	/*
		Fill in the info to create a job
	*/
	echo html_e('p', array(), _('Start by filling in the fields below. When you click continue, you will be shown a list of skills and experience levels that this job requires.'));
	echo $HTML->openForm(array('action' => '/people/editjob.php', 'method' => 'post'));
	echo html_e('input', array('type' => 'hidden', 'name' => 'group_id', 'value' => $group_id));
	echo html_e('input', array('type' => 'hidden', 'name' => 'form_key', 'value' => form_generate_key()));
	echo html_e('strong', array(), _('Category')).utils_requiredField().html_e('br').people_job_category_box('category_id');
	echo html_e('p', array(), html_e('strong', array(), _('Short Description').utils_requiredField()._(':')).html_e('br').
				html_e('input', array('type' => 'text', 'required' => 'required', 'name' => 'title', 'value' => '', 'size' => 40, 'maxlength' => 60)));
	echo html_e('p', array(), html_e('strong', array(), _('Long Description').utils_requiredField()._(':')).html_e('br').
				html_e('textarea', array('required' => 'required', 'name' => 'description', 'rows' => 10, 'cols' =>60), '', false));
	echo html_e('input', array('type' => 'submit', 'name' => 'add_job', 'value' => _('Continue').'>>'));
	echo $HTML->closeForm();

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
