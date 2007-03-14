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


require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');

if (!$sys_use_project_database) {
	exit_disabled();
}

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error(_('Error'), _('Error creating group'));
} else if ($group->isError()) {
	exit_error(_('Error'), $group->getErrorMessage());
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

	$sql = "INSERT INTO prdb_dbs(group_id,dbname,dbusername,dbuserpass,requestdate,dbtype,created_by,state)
			VALUES($group_id,'$dbname','$dbname','$randompw','".time()."',$newdbtypeid,".$LUSER->getID().",2)
	";
	$res = db_query($sql);

	if (!$res || db_affected_rows($res) < 1) {
		$feedback .= _('Cannot add database entry').': '.db_error();
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

		$res = db_query("
			UPDATE prdb_dbs 
			SET dbuserpass = '$pw', 
				state = '4'
			WHERE dbid = '$dbid' 
			AND group_id = '$group_id'
		"); 

		if (!$res || db_affected_rows($res) < 1) {
			$feedback .= "Update failure - ".db_error()."";
		} else {
			$res = db_query("
				SELECT * 
				FROM prdb_types 
				WHERE dbtypeid='$newdbtypeid'
			");
			$row_db = db_fetch_array($res);
			group_add_history('Updated database - (type: '.$row_db['dbsoftware'].')','',$group_id);
		}
	} else {

		$feedback .= "Operation failed.  Password and Password Confirm are not the same";

	}

}

if (getStringFromRequest('deletedbconfirm')) {
	$dbid = getIntFromRequest('dbid');

	//schedule for deletion

	$res = db_query("
		UPDATE prdb_dbs 
		SET state=3 
		WHERE dbid='$dbid'
		AND group_id='$group_id'
	");

	if (!$res || db_affected_rows($res) < 1) {
		$feedback .= 'Cannot delete database: '.db_error();
	} else {
		$feedback .= "Database scheduled for deletion";
	}

}

project_admin_header(array('title'=>_('Database Information').'','group'=>$group_id));

// XXX ogi: where's deletedb defined?
if ($deletedb == 1) {

	print "<hr /><strong><div align=\"center\">"._('Click to confirm deletion')."[ <a href=\"".getStringFromServer('PHP_SELF')."?deletedbconfirm=1&amp;group_id=".$group_id."&amp;dbid=$dbid\">'._('Click to confirm deletion').'</a> ] </div></strong> <hr />";

}

$res_db = db_query("
	SELECT * 
	FROM prdb_types 
	WHERE dbsoftware NOT IN (
		SELECT dbsoftware 
		FROM prdb_dbs,prdb_types 
		WHERE dbtypeid=dbtype 
		AND group_id='$group_id'
		AND state IN (1,2,4)
	)
");

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

$sql="
	SELECT * 
	FROM prdb_dbs,prdb_states,prdb_types 
	WHERE group_id='$group_id' 
	AND stateid=state 
	AND dbtype=dbtypeid
";
$res_db = db_query($sql);

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

	print '<strong><span style="text-decoration:underline">'._('Current Databases').'</span></strong><p>'._('Current Databases').'</p>';

}

project_admin_footer(array());

?>
