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
$category_id = getIntFromRequest('category_id');

if ($group_id) {
	
	project_admin_header(array());

	echo '
	<p>'.$Language->getText('people','here_is_list_position').'</p>
	<p>';

	echo people_show_project_jobs($group_id) . '</p>';

} else if ($category_id) {

	people_header(array('title'=>$Language->getText('people','title'),'pagename'=>'people_cat','titlevals'=>array(people_get_category_name($category_id))));

	echo '
		<p>'.$Language->getText('people','click_job_titles').'</p>
';
	echo people_show_category_jobs($category_id);

} else {

	people_header(array('title'=>$Language->getText('people','title'),'pagename'=>'people'));

	echo $Language->getText('people','about_blurb', $GLOBALS['sys_name']);

	echo people_show_category_table();

        echo '<h4>'.$Language->getText('people','last_posts').'</h4>';

	$sql="SELECT people_job.group_id,people_job.job_id,groups.group_name,groups.unix_group_name,people_job.title,people_job.post_date,people_job_category.name AS category_name ".
		"FROM people_job,people_job_category,groups ".
		"WHERE people_job.group_id=groups.group_id ".
		"AND people_job.category_id=people_job_category.category_id ".
		"AND people_job.status_id=1 ".
                "ORDER BY post_date DESC";
	$result=db_query($sql,5);
        echo people_show_job_list($result);
        echo '<p><a href="helpwanted-latest.php">['.$Language->getText('people','more_latest_posts').']</a></p>';

}

people_footer(array());

?>