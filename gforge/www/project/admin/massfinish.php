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

require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');
require_once('www/include/role_utils.php');

$group_id = getIntFromRequest('group_id');
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

$accumulated_ids = getStringFromRequest("accumulated_ids");

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
		//plugin webcal
			//change assistant for webcal
			$params[0] = $keys[$i];
			$params[1] = $group_id;
			plugin_hook('change_cal_permission',$params);
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

project_admin_header(array('title'=>_('Edit Role'),'group'=>$group_id));

echo '
<h2>'._('Add Users From List').'</h2>
<p>
'._('Choose the role for each user and then press &quot;Add All&quot;.').'
<p>
<form action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'" method="post">';

if (!$res || db_numrows($res) < 1) {
	echo "No Matching Users Found";
} else {

	$titles[]=_('Real name');
	$titles[]=_('Unix name');
	$titles[]=_('Role');

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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
