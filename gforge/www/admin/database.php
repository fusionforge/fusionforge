<?php
/**
  *
  * Site Admin page for maintaining groups'databases
  *
  * This page allows to:
  *   - browse aggregate numbers of databases of specific type (active,
  *     deleted, etc.)
  *   - list all databases of given type
  *   - edit some database (by going to group's DB Admin page)
  *   - register existing database in system
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/admin/admin_utils.php');

if (!$sys_use_project_database) {
	exit_disabled();
}

session_require(array('group'=>'1','admin_flags'=>'A'));


if ($submit) {

	if ($group_id) {

		$group =& group_get_object_by_name($groupname);
		exit_assert_object($group, 'Group');

		$user =& session_get_user();
		exit_assert_object($user, 'User');

		$res = db_query("
			INSERT INTO prdb_dbs(group_id, dbname, dbusername, dbuserpass, requestdate, dbtype, created_by, state)
			VALUES ($group_id,'$dbname','$dbname','xxx',".time().",1,".$user->getID().",1)
		");

		if (!$res || db_affected_rows($res) < 1) {
			$feedback .= $Language->getText('admin_database','error_adding_database') .db_error();
		} else {
			$feedback .= $Language->getText('admin_database','group'). "<em>".$group->getUnixName()."</em>" .$Language->getText('admin_database','added_already_active_database');
		}

	} else {

		$feedback .=	"<strong>" .$Language->getText('admin_database','unable_to_insert'). "</strong>";

	}

}


site_admin_header(array('title'=>$Language->getText('admin_database','site_admin_groups_maintance')));

$res_db = db_query("
	SELECT stateid,statename,COUNT(*)
	FROM prdb_dbs,prdb_states
	WHERE stateid=state
	GROUP BY statename,stateid
");

echo '<h3>' .$Language->getText('admin_database','statistics_for_project_database').'</h3>';

if (db_numrows($res_db) > 0) {

	$title=array();
	$title[]= $Language->getText('admin_database','type');
	$title[]= $Language->getText('admin_database','count');
	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row_db = db_fetch_array($res_db)) {

		print '<tr><td align="center"><a href="'.$PHP_SELF.'?displaydb=1&dbstate='.$row_db['stateid'].'">'.$row_db['statename'].'</a></td><td align="center">'.$row_db['count'].'</td></tr>';

	}

	echo $GLOBALS['HTML']->listTableBottom();

} else {
	echo '<p>' .$Language->getText('admin_database','no_databases_defined').'</p>';
}


if ($displaydb) {

	$res_db = db_query("
		SELECT statename
		FROM prdb_states
		WHERE stateid=".$dbstate."
	");

	$row_db = db_fetch_array($res_db);

	print '<hr /><h3>' .$Language->getText('admin_database','display_database_type') .$row_db['statename'].' </h3><ul>';

	$res_db = db_query("
		SELECT *
		FROM prdb_dbs
		WHERE state=".$dbstate."
		ORDER BY dbname
	");

	while ($row_db = db_fetch_array($res_db)) {

		print '<li><a href="/project/admin/database.php?group_id='.$row_db['group_id'].'">'.$row_db['dbname']."</a></li>";

	}
	print "</ul>";


}



?>
<hr />

<h3><?php echo $Language->getText('admin_database','add_an_already_active_database'); ?></h3>

<form name="madd" method="post" action="<?php  echo $PHP_SELF; ?>">

<table>

<tr>
<td><?php echo $Language->getText('admin_database','group_unix_name'); ?><?php echo utils_requiredField(); ?></td>
<td><input type="text" name="groupname" /></td>
</tr>

<tr>
<td>Database Name:<?php echo utils_requiredField(); ?></td>
<td><input type="text" name="dbname" /></td>
</tr>

</table>
<input type="submit" name="submit" value="<?php echo $Language->getText('admin','add'); ?>"/>
</form>

<?php

site_admin_footer(array());

?>
