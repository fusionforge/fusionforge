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
$category_id = getIntFromRequest('category_id');

if ($group_id) {
	
	project_admin_header(array());

	echo '
	<p>'._('Here is a list of positions available for this project.').'</p>
	<p>';

	echo people_show_project_jobs($group_id) . '</p>';

 } else if ($category_id && is_numeric($category_id)) {

	people_header(array('title'=>_('Help Wanted System')));

	echo '
		<p>'._('Click job titles for more detailed descriptions.').'</p>
';
	echo people_show_category_jobs($category_id);

} else {

	people_header(array('title'=>_('Help Wanted System')));

	print '<p>';
	printf(_('The %1$s Project Help Wanted board is for non-commercial, project volunteer openings. Commercial use is prohibited.'), forge_get_config ('forge_name'));
	print '</p>';

	print '<p>';
	print _('Project listings remain live for two weeks, or until closed by the poster, whichever comes first. (Project administrators may always re-post expired openings.)');
	print '</p>';

	print '<p>';
	print _('Browse through the category menu to find projects looking for your help.');
	print '</p>';

	print '<p>';
	print _('If you\'re a project admin, log in and submit help wanted requests through your project administration page.');
	print '</p>';

	print '<p>';
	print _('To suggest new job categories, submit a request via the support manager.');
	print '</p>';

	echo people_show_category_table();

	echo '<h2>'._('Last posts').'</h2>';

	$result=db_query_params('SELECT people_job.group_id,people_job.job_id,groups.group_name,groups.unix_group_name,people_job.title,people_job.post_date,people_job_category.name AS category_name 
FROM people_job,people_job_category,groups 
WHERE people_job.group_id=groups.group_id 
AND people_job.category_id=people_job_category.category_id 
AND people_job.status_id=1 
ORDER BY post_date DESC', array(), 5);
        echo people_show_job_list($result);
        echo '<p><a href="helpwanted-latest.php">['._('more latest posts').']</a></p>';

}

people_footer(array());

?>
