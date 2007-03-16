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
require_once('www/admin/admin_utils.php');

if (!$sys_use_project_database) {
	exit_disabled();
}

session_require(array('group'=>'1','admin_flags'=>'A'));

if (getStringFromRequest('submit')) {
	$group_id = getIntFromRequest('group_id');
	$groupname = getStringFromRequest('groupname');
	$dbname = getStringFromRequest('dbname');

	if ($groupname) {

		$group =& group_get_object_by_name($groupname);
		if (!$group || !is_object($group)) {
			exit_error('Error','Could Not Get Group');
		} elseif ($group->isError()) {
			exit_error('Error',$group->getErrorMessage());
		}
		
		$group_id = $group->getID();

		$user =& session_get_user();
		if (!$user || !is_object($user)) {
			exit_error('Error','Could Not Get User');
		} elseif ($user->isError()) {
			exit_error('Error',$u->getErrorMessage());
		}


		$res = db_query("
			INSERT INTO prdb_dbs(group_id, dbname, dbusername, dbuserpass, requestdate, dbtype, created_by, state)
			VALUES ($group_id,'$dbname','$dbname','xxx',".time().",1,".$user->getID().",1)
		");

		if (!$res || db_affected_rows($res) < 1) {
			$feedback .= _('Error Adding Database') .db_error();
		} else {
			$feedback .= _('Group'). " <em>".$group->getUnixName()."</em>" ._('Group');
		}

	} else {

		$feedback .=	"<strong>" ._('Unable to insert already active database.'). "</strong>";

	}

}

site_admin_header(array('title'=>_('Site Admin: Groups\' DB Maintenance')));

$res_db = db_query("
	SELECT stateid,statename,COUNT(*) AS count
	FROM prdb_dbs,prdb_states
	WHERE stateid=state
	GROUP BY statename,stateid
");

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

	$res_db = db_query("
		SELECT statename
		FROM prdb_states
		WHERE stateid=".$dbstate."
	");

	$row_db = db_fetch_array($res_db);

	print '<hr /><h3>' ._('Displaying Databases of Type:') .$row_db['statename'].' </h3><ul>';

	$res_db = db_query("
		SELECT *
		FROM prdb_dbs
		WHERE state=".$dbstate."
		ORDER BY dbname
	");

	while ($row_db = db_fetch_array($res_db)) {

		print '<li><a href="'.$GLOBALS['sys_urlprefix'].'/project/admin/database.php?group_id='.$row_db['group_id'].'">'.$row_db['dbname']."</a></li>";

	}
	print "</ul>";


}



?>
<hr />

<h3><?php echo _('Add an already active database'); ?></h3>

<form name="madd" method="post" action="<?php  echo getStringFromServer('PHP_SELF'); ?>">

<table>

<tr>
<td><?php echo _('Group Unix Name:'); ?><?php echo utils_requiredField(); ?></td>
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
