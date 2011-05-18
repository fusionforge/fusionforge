<?php
/**
 * Finish Mass-adding users.
 *
 * Copyright 2004 (c) Tim Perdue - GForge LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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
require_once $gfwww.'include/role_utils.php';

$group_id = getIntFromRequest('group_id');
session_require_perm ('project_admin', $group_id) ;

$accumulated_ids = getStringFromRequest("accumulated_ids");

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
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
    $feedback = _('Successful');
	session_redirect('/project/admin/index.php?group_id='.$group_id.'&feedback='.urlencode($feedback));
}

if (!$accumulated_ids) {
	exit_error(_('No IDs Were Passed'),'admin');
} else {
	$arr=explode(',',$accumulated_ids);
	$res=db_query_params("SELECT user_id,user_name,realname FROM users
		WHERE status='A' and type_id='1' and user_id = ANY ($1)
		ORDER BY realname ASC", array(db_int_array_to_any_clause($arr)));
}

project_admin_header(array('title'=>_('Add Users From List'),'group'=>$group_id));

echo '
<p>
'._('Choose the role for each user and then press &quot;Add All&quot;.').'
</p>
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

echo '<p><input type="submit" name="finished" value="'._('Add All').'" /></p>
</form>';

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
