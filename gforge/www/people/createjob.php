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
require_once('www/project/admin/project_admin_utils.php');

if (!$sys_use_people) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');

if ($group_id && (user_ismember($group_id, 'A'))) {

	project_admin_header(array());

	/*
		Fill in the info to create a job
	*/
	echo '
		<p>'.$Language->getText('people_createjob','explains').'	</p>
		<p>
		<form action="/people/editjob.php" method="post">
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<strong>'.$Language->getText('people','category').':</strong>'.utils_requiredField().'<br /></p>
		'. people_job_category_box('category_id') .'
		<p>
		<strong>'.$Language->getText('people','short_description').':</strong>'.utils_requiredField().'<br />
		<input type="text" name="title" value="" size="40" maxlength="60" /></p>
		<p>
		<strong>'.$Language->getText('people','long_description').':</strong>'.utils_requiredField().'<br />
		<textarea name="description" rows="10" cols="60" wrap="soft"></textarea></p>
		<p>
		<input type="submit" name="add_job" value="'.$Language->getText('people_createjob','continue').'" />
		</form></p>';

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
