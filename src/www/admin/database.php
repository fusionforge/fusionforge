<?php
/**
 * Site Admin page for maintaining groups'databases
 *
 * This page allows to:
 *   - browse aggregate numbers of databases of specific type (active,
 *	 deleted, etc.)
 *   - list all databases of given type
 *   - edit some database (by going to group's DB Admin page)
 *   - register existing database in system
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
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
require_once $gfwww.'admin/admin_utils.php';

if (!forge_get_config('use_project_database')) {
	exit_disabled('home');
}

session_require_global_perm ('forge_admin');

if (getStringFromRequest('submit')) {
	$group_id = getIntFromRequest('group_id');
	$groupname = getStringFromRequest('groupname');
	$dbname = getStringFromRequest('dbname');

	if ($groupname) {

		$group = group_get_object_by_name($groupname);
		if (!$group || !is_object($group)) {
            exit_no_group();
		} elseif ($group->isError()) {
			exit_error($group->getErrorMessage(),'home');
		}

		$group_id = $group->getID();

		$user =& session_get_user();
		if (!$user || !is_object($user)) {
			exit_error(_('Could Not Get User'),'home');
		} elseif ($user->isError()) {
			exit_error($u->getErrorMessage(),'home');
		}


		$res = db_query_params ('
			INSERT INTO prdb_dbs(group_id, dbname, dbusername, dbuserpass, requestdate, dbtype, created_by, state)
			VALUES ($group_id,$1,$2,$3,$4,1,$5,1)
		',
			array($dbname,
				$dbname,
				'xxx',
				time(),
				$user->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$error_msg .= _('Error Adding Database: ') .db_error();
		} else {
			$feedback .= _('Project'). " <em>".$group->getUnixName()."</em>" ._('added already active database');
		}
	} else {
		$error_msg .= _('Unable to insert already active database.');
	}
}

site_admin_header(array('title'=>_('Site Admin: Groups\' DB Maintenance')));

$res_db = db_query_params ('
	SELECT stateid,statename,COUNT(*) AS count
	FROM prdb_dbs,prdb_states
	WHERE stateid=state
	GROUP BY statename,stateid
',
			array()) ;


echo '<h3>' ._('Statistics for Project Databases').'</h3>';

if (db_numrows($res_db) > 0) {

	$title=array();
	$title[]= _('Type');
	$title[]= _('Count');
	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row_db = db_fetch_array($res_db)) {

		print '<tr><td style="text-align:center"><a href="'.getStringFromServer('PHP_SELF').'?displaydb=1&dbstate='.$row_db['stateid'].'">'.$row_db['statename'].'</a></td><td style="text-align:center">'.$row_db['count'].'</td></tr>';

	}

	echo $GLOBALS['HTML']->listTableBottom();

} else {
	echo '<p>' ._('No databases defined').'</p>';
}


if ($displaydb) {

	$res_db = db_query_params ('
		SELECT statename
		FROM prdb_states
		WHERE stateid=$1
	',
			array($dbstate));

	$row_db = db_fetch_array($res_db);

	print '<hr /><h3>' ._('Displaying Databases of Type:') .$row_db['statename'].' </h3><ul>';

	$res_db = db_query_params ('
		SELECT *
		FROM prdb_dbs
		WHERE state=$1
		ORDER BY dbname
	',
			array($dbstate));

	while ($row_db = db_fetch_array($res_db)) {
		print '<li>'.util_make_link ('/project/admin/database.php?group_id='.$row_db['group_id'],$row_db['dbname']).'</li>';
	}
	print "</ul>";


}



?>
<hr />

<h3><?php echo _('Add an already active database'); ?></h3>

<form name="madd" method="post" action="<?php  echo getStringFromServer('PHP_SELF'); ?>">

<table>

<tr>
<td><?php echo _('Project Unix Name:'); ?><?php echo utils_requiredField(); ?></td>
<td><input type="text" name="groupname" /></td>
</tr>

<tr>
<td>Database Name:<?php echo utils_requiredField(); ?></td>
<td><input type="text" name="dbname" /></td>
</tr>

</table>
<input type="submit" name="submit" value="<?php echo _('Add'); ?>"/>
</form>

<?php

site_admin_footer(array());

?>
