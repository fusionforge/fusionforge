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
			$feedback .= 'Error adding databse: '.db_error();
		} else {
			$feedback .= "Group <em>".$group->getUnixName()."</em> added already active database";
		}

	} else {

		$feedback .=	"<strong>Unable to insert already active database.</strong>";

	}
			
}			


site_admin_header(array('title'=>"Site Admin: Groups' DB Maintenance"));

$res_db = db_query("
	SELECT stateid,statename,COUNT(*) 
	FROM prdb_dbs,prdb_states 
	WHERE stateid=state 
	GROUP BY statename,stateid
");

echo '<h3>Statistics for Project Databases</h3>';

if (db_numrows($res_db) > 0) {

	$title=array();
	$title[]='Type';
	$title[]='Count';
	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row_db = db_fetch_array($res_db)) {

		print '<tr><td align="center"><a href="'.$PHP_SELF.'?displaydb=1&dbstate='.$row_db['stateid'].'">'.$row_db['statename'].'</a></td><td align="center">'.$row_db['count'].'</td></tr>';

	}

	echo $GLOBALS['HTML']->listTableBottom();

} else {
	echo '<p>No databases defined</p>';
}


if ($displaydb) {

	$res_db = db_query("
		SELECT statename 
		FROM prdb_states 
		WHERE stateid=".$dbstate."
	");

	$row_db = db_fetch_array($res_db);

	print '<hr /><h3>Displaying Databases of Type: '.$row_db['statename'].' </h3><ul>';

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

<h3>Add an already active database</h3>

<form name="madd" method="post" action="<?php  echo $PHP_SELF; ?>">

<table>

<tr>
<td>Group Unix Name:<?php echo utils_requiredField(); ?></td>
<td><input type="text" name="groupname"></td>
</tr>

<tr>
<td>Database Name:<?php echo utils_requiredField(); ?></td>
<td><input type="text" name="dbname"></td>
</tr>

</table>
<input type="submit" name="submit" value="Add">
</form>

<?php

site_admin_footer(array());

?>
