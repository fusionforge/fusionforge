#! /usr/bin/php4 -f
<?php
/**
 * GForge Group Role Generator
 *
 * Copyright 2004 GForge, LLC
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once('www/include/squal_pre.php');
require_once('common/include/Role.class');

//
//	Set up this script to run as the site admin
//

$res = db_query("SELECT user_id FROM user_group WHERE admin_flags='A' AND group_id='1'");

if (!$res) {
	echo db_error();
	exit();
}

if (db_numrows($res) == 0) {
	// There are no Admins yet, aborting without failing
	echo "SUCCESS\n";
	exit();
}

$id=db_result($res,0,0);
session_set_new($id);

//
//	Clear out role settings in case this was run before
//
db_begin();
db_query("UPDATE user_group SET role_id=1");
db_query("DELETE FROM role_setting");
db_query("DELETE FROM role WHERE role_id>1");

$res=db_query("SELECT group_id FROM groups WHERE status != 'P'");
$arr = util_result_column_to_array($res);

for ($i=0; $i<count($arr); $i++) {
	$g =& group_get_object($arr[$i]);
	//
	//
	//  Set Default Roles
	//
	//
	$role = new Role($g);
	$todo = array_keys($role->defaults);
	for ($c=0; $c<count($todo); $c++) {
		$role = new Role($g);
		if (!$role->createDefault($todo[$c])) {
			echo $role->getErrorMessage();
			db_rollback();
			echo "Could Not Create Default Roles: ".$arr[$i];
			exit(1);
		}
	}

	$roleid=db_query("SELECT role_id FROM role WHERE group_id='".$arr[$i]."' AND role_name='Admin'");
	$admin_roleid=db_result($roleid,0,0);
	$roleid=db_query("SELECT role_id FROM role WHERE group_id='".$arr[$i]."' AND role_name='Junior Developer'");
	$junior_roleid=db_result($roleid,0,0);

	$rrole=db_query("SELECT user_id,admin_flags FROM user_group WHERE group_id='".$arr[$i]."'");
	while ($arrole = db_fetch_array($rrole)) {

		$role_id= (( trim($arrole['admin_flags']) == 'A' ) ? $admin_roleid : $junior_roleid );
		$user_id=$arrole['user_id'];

		$role = new Role($g,$role_id);
		if (!$role || !is_object($role)) {
			echo 'Error Getting Role Object';
			db_rollback();
			exit(1);
		} elseif ($role->isError()) {
			echo $role->getErrorMessage();
			db_rollback();
			exit(1);
		}
		if (!$role->setUser($user_id)) {
			echo $role->getErrorMessage();
			db_rollback();
			exit(1);
		}
	}
}
db_commit();
echo "SUCCESS\n";
return true;
?>
