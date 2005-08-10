<?php
/**
 * Finish Mass-adding users.
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-03-16
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
require_once('www/project/admin/project_admin_utils.php');
require_once('www/include/role_utils.php');

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
}

if (getStringFromRequest('finished')) {
	$addrole = getStringFromRequest('addrole');
	$keys=array_keys($addrole);
	for ($i=0; $i<count($keys); $i++) {
		$group->addUser($keys[$i],$addrole[$keys[$i]]);
	}
	Header("Location: index.php?group_id=$group_id&feedback=Successful");
}

if (!$accumulated_ids) {
	exit_error('Error','No IDs Were Passed');
} else {
	$arr=explode(',',$accumulated_ids);
	$res=db_query("SELECT user_id,user_name,realname FROM users
		WHERE status='A' and type_id='1' and user_id IN ('". implode('\',\'',$arr) ."') 
		ORDER BY realname ASC");
}

project_admin_header(array('title'=>$Language->getText('rbac_edit','pgtitle'),'group'=>$group_id));

echo '
<h2>'.$Language->getText('project_admin','addfromlist').'</h2>
<p>
'.$Language->getText('project_admin','addfromlist2').'
<p>
<form action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'" method="post">';

if (!$res || db_numrows($res) < 1) {
	echo "No Matching Users Found";
} else {

	$titles[]=$Language->getText('project_admin','userrealname');
	$titles[]=$Language->getText('project_admin','unix_name');
	$titles[]=$Language->getText('rbac_edit','role');

	echo $HTML->listTableTop($titles);

	//
	//	Everything is built on the multi-dimensial arrays in the Role object
	//
	for ($i=0; $i<db_numrows($res); $i++) {

		echo '<tr '. $HTML->boxGetAltRowStyle($i) . '>
			<td>'.db_result($res,$i,'realname').'</td>
			<td>'.db_result($res,$i,'user_name').'</td>
			<td>'.role_box($group_id,'addrole['. db_result($res,$i,'user_id') .']','').'</td></tr>';

	}

	echo $HTML->listTableBottom();

}

echo '<input type="submit" name="finished" value="Add All">
</form>';

project_admin_footer(array());

?>
