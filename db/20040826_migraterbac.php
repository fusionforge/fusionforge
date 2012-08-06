#! /usr/bin/php
<?php
/**
 * GForge Group Role Generator
 *
 * Copyright 2004 GForge, LLC
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

//system library
//Required by Role.class.php to update system
require_once $gfcommon.'include/System.class.php';
// Right now the nss tables don't exist so if sys_account_manager_type=pgsql
// This will fail UNIX should be safe
//if (!forge_get_config('account_manager_type')) {
	$sys_account_manager_type='UNIX';
//}
require_once $gfcommon.'include/system/'.forge_get_config('account_manager_type').'.class.php';
$amt = forge_get_config('account_manager_type') ;
$SYS=new $amt();

require_once $gfcommon.'include/Role.class.php';

//
//	Set up this script to run as the site admin
//

$res = db_query_params ('SELECT user_id FROM user_group WHERE admin_flags=$1 AND group_id=$2',
			array('A',
			'1')) ;


if (!$res) {
	echo db_error();
	exit(1);
}

if (db_numrows($res) == 0) {
	// There are no Admins yet, aborting without failing
	echo "SUCCESS\n";
	exit(0);
}

$id=db_result($res,0,0);
session_set_new($id);

//
//	Clear out role settings in case this was run before
//
db_begin();
db_query_params ('UPDATE user_group SET role_id=1',
			array()) ;

db_query_params ('DELETE FROM role_setting',
			array()) ;

db_query_params ('DELETE FROM role WHERE role_id>1',
			array()) ;


$res=db_query_params ('SELECT group_id FROM groups WHERE status != $1',
			array('P')) ;

$arr = util_result_column_to_array($res);

for ($i=0; $i<count($arr); $i++) {
	$g = group_get_object($arr[$i]);
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
			exit(2);
		}
	}

	$roleid = db_query_params ('SELECT role_id FROM role WHERE group_id=$1  AND role_name=$2',
				   array ($arr[$i],
					  'Admin'));
	$admin_roleid=db_result($roleid,0,0);
	$roleid = db_query_params ('SELECT role_id FROM role WHERE group_id=$1 AND role_name=$2',
				   array ($arr[$i],
					  'Junior Developer')) ;
	$junior_roleid=db_result($roleid,0,0);

	$rrole = db_query_params ('SELECT user_id,admin_flags FROM user_group WHERE group_id=$1',
				  array ($arr[$i])) ;
	while ($arrole = db_fetch_array($rrole)) {

		$role_id= (( trim($arrole['admin_flags']) == 'A' ) ? $admin_roleid : $junior_roleid );
		$user_id=$arrole['user_id'];

		$role = new Role($g,$role_id);
		if (!$role || !is_object($role)) {
			echo 'Error Getting Role Object';
			db_rollback();
			exit(3);
		} elseif ($role->isError()) {
			echo $role->getErrorMessage();
			db_rollback();
			exit(4);
		}
		if (!$role->setUser($user_id)) {
			echo $role->getErrorMessage();
			db_rollback();
			exit(5);
		}
	}
}
db_commit();
echo "SUCCESS\n";
exit(0);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
