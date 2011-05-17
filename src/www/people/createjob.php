<?php
/**
 * Help Wanted 
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'people/people_utils.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

if (!forge_get_config('use_people')) {
	exit_disabled('home');
}

$group_id = getIntFromRequest('group_id');

if ($group_id && (user_ismember($group_id, 'A'))) {

	project_admin_header(array());

	/*
		Fill in the info to create a job
	*/
	echo '
		<p>'._('Start by filling in the fields below. When you click continue, you will be shown a list of skills and experience levels that this job requires.').'	</p>
		<p>
		<form action="'.util_make_url ('/people/editjob.php').'" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<input type="hidden" name="form_key" value="' . form_generate_key() . '">
		<strong>'._('Category').'</strong>'.utils_requiredField().'<br /></p>
		'. people_job_category_box('category_id') .'
		<p>
		<strong>'._('Short Description').':</strong>'.utils_requiredField().'<br />
		<input type="text" name="title" value="" size="40" maxlength="60" /></p>
		<p>
		<strong>'._('Long Description').':</strong>'.utils_requiredField().'<br />
		<textarea name="description" rows="10" cols="60"></textarea></p>
		<p>
		<input type="submit" name="add_job" value="'._('Continue >>').'" />
		</form></p>';

	people_footer(array());

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
?>
