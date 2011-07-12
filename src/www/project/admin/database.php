<?php
/**
 *
 * Project Admin page to manage group's databases
 *
 * This page allows to request, change password of, and delete database.
 * Group may have single database of each type that provided (e.g., mysql,
 * pgsql, etc).
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
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


require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

if (!forge_get_config('use_project_database')) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');
session_require_perm ('project_admin', $group_id) ;

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
} else if ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
}

if (getStringFromRequest('createdb')) {
	$newdbtypeid = getIntFromRequest('newdbtypeid');

	//mysql takes issue with database names that have dashes in them - so strip the dashes, replace with ""
	//e.g. free-mysql becomes freemysql (it's a workaround)
	//if there is a dash in the groupname
	$dbname = str_replace("-", "", $group->getUnixName());

	//check there is no name double up - if there is - add an incrementing to the number to the end

	$dbname = prdb_namespace_seek($dbname);
	$randompw = random_pwgen();

	$res = db_query_params("INSERT INTO prdb_dbs(group_id,dbname,dbusername,dbuserpass,requestdate,dbtype,created_by,state)
			VALUES($1, $2, $2, $3, $4, $5, $6 ,2)", array($group_id, $dbname, $randompw, time(), $newdbtypeid, $LUSER->getID()));

	if (!$res || db_affected_rows($res) < 1) {
		$error_msg .= _('Cannot add database entry').': '.db_error();
	} else {

		$feedback .= _('Database scheduled for creation');
		group_add_history('Created database '.$dbname.' type '.$row_db['dbsoftware'].' ','',$group_id);

	}
}

if (getStringFromRequest('updatedbrec')) {
	$dbid = getIntFromRequest('dbid');
	$pw = getStringFromRequest('pw');
	$pwconfirm = getStringFromRequest('pwconfirm');
	$newdbtypeid = getIntFromRequest('newdbtypeid');

	if ($pw == $pwconfirm) {

		//sync new password, and flag it as 'pending (an) update'

		$res = db_query_params ('
			UPDATE prdb_dbs
			SET dbuserpass = $1,
				state = $2
			WHERE dbid = $3
			AND group_id = $4
		',
			array($pw,
				'4',
				$dbid,
				$group_id));

		if (!$res || db_affected_rows($res) < 1) {
			$error_msg .= "Update failure - ".db_error()."";
		} else {
			$res = db_query_params ('
				SELECT *
				FROM prdb_types
				WHERE dbtypeid=$1
			',
			array($newdbtypeid));
			$row_db = db_fetch_array($res);
			group_add_history('Updated database - (type: '.$row_db['dbsoftware'].')','',$group_id);
		}
	} else {

		$error_msg .= "Operation failed.  Password and Password Confirm are not the same";

	}

}

if (getStringFromRequest('deletedbconfirm')) {
	$dbid = getIntFromRequest('dbid');

	//schedule for deletion

	$res = db_query_params ('
		UPDATE prdb_dbs
		SET state=3
		WHERE dbid=$1
		AND group_id=$2
	',
			array($dbid,
				$group_id));

	if (!$res || db_affected_rows($res) < 1) {
		$error_msg .= 'Cannot delete database: '.db_error();
	} else {
		$feedback .= "Database scheduled for deletion";
	}

}

project_admin_header(array('title'=>_('Database Information').'','group'=>$group_id));

// XXX ogi: where's deletedb defined?
if ($deletedb == 1) {

	print "<hr /><strong><div align=\"center\">"._('Click to confirm deletion')."[ <a href=\"".getStringFromServer('PHP_SELF')."?deletedbconfirm=1&amp;group_id=".$group_id."&amp;dbid=$dbid\">'._('CONFIRM DELETE').'</a> ] </div></strong> <hr />";

}

$res_db = db_query_params ('
	SELECT *
	FROM prdb_types
	WHERE dbsoftware NOT IN (
		SELECT dbsoftware
		FROM prdb_dbs,prdb_types
		WHERE dbtypeid=dbtype
		AND group_id=$1
		AND state IN (1,2,4)
	)
',
			array($group_id));

if (db_numrows($res_db) > 0) {

	print '

		<p><strong><span style="text-decoration:underline">'._('Add New Database').'</span></strong></p>
		<p><em>'._('Clicking on "create" will schedule the creation of the database, and email the	details to the project administrators').'</em></p>

		<p><strong>'._('Database Type').':</strong></p>
		<p><form action="'.getStringFromServer('PHP_SELF').'" method="post">
		<input type="hidden" name="createdb" value="1" />
		<input type="hidden" name="group_id" value="'.$group_id.'" />
		<select name="newdbtypeid">

	';

	while ($res_row = db_fetch_array($res_db)) {

		print "<option value=\"".$res_row['dbtypeid']."\">".$res_row['dbsoftware']."</option>";
	}

	print '

		</select>
		&nbsp; <input type="submit" name="Create" value="'._('Create').'" />
		</form></p>
	';

} else {
?>
<?php echo _('Maximum number of databases of all types have been allocated') ?>
 <p>

<?php
}

$res_db = db_query_params("
	SELECT *
	FROM prdb_dbs,prdb_states,prdb_types
	WHERE group_id=$1
	AND stateid=state
	AND dbtype=dbtypeid
", array($group_id));

if (db_numrows($res_db) > 0) {

	$title=array();
	$title[]=_('DB Type');
	$title[]=_('State');
	$title[]=_('New Password');
	$title[]=_('Confirm New');
	$title[]=_('Operations');

	echo $GLOBALS['HTML']->listTableTop($title);

	while ($row_db = db_fetch_array($res_db)) {

		print '
			<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>

			<td>'.$row_db['dbsoftware'].'</td>
			<td>'.$row_db['statename'].'</td>
			';

		//if database is active or pending update allow the record to be deleted or password changed

		if (($row_db['state'] == 1) || ($row_db['state'] == 4) || ($row_db['state'] == 2)) {

			print '<form name="dbupdate" method="post" action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'">
				     <input type="hidden" name="dbid" value="'.$row_db['dbid'].'" />
				     <input type="hidden" name="updatedbrec" value="1" />
				     <td><input type="text" name="pw" size="8" maxlength="16" /></td>
				     <td><input type="text" name="pwconfirm" size="8" maxlength="16" /></td>
				     <td>
				       <input type="submit" name="submit" value="'._('Update').'" />
				     </td>
				  </form>
			';

		} else {
			print '
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			';
		}

		print '</tr>';

	}

	echo $GLOBALS['HTML']->listTableBottom();

} else {

	print '<strong><span style="text-decoration:underline">'._('Current Databases').'</span></strong><p>'._('There are no databases currently allocated to this group').'</p>';

}

project_admin_footer(array());

?>
