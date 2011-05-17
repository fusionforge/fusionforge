<?php
/**
 * Role Editing Page
 *
 * Copyright 2004 (c) Tim Perdue - GForge LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
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

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'admin');
}

$sw = getStringFromRequest('sw', 'A');

$res = db_query_params('SELECT user_id,user_name,lastname,firstname FROM users WHERE status=$1 and type_id=1 and lower(lastname) LIKE $2 ORDER BY lastname,firstname ASC',
		       array('A',
			     strtolower ($sw."%")));

$accumulated_ids = getStringFromRequest('accumulated_ids');
if (!$accumulated_ids) {
	$accumulated_ids=array();
} else {
	$accumulated_ids =& explode(',',$accumulated_ids);
}

$newids = getArrayFromRequest('newids');
if (count($newids) > 0) {
	if (count($accumulated_ids) > 0) {
		$accumulated_ids = array_merge($accumulated_ids,$newids);
	} else {
		$accumulated_ids=$newids;
	}
}
$accumulated_ids = array_unique($accumulated_ids);

if (getStringFromRequest('finished')) {
    session_redirect('/project/admin/massfinish.php?group_id='.$group_id.'&accumulated_ids='.implode(',',$accumulated_ids));
}

project_admin_header(array('title'=>_('Add Users From List'),'group'=>$group_id));

echo '
<p>
'._('Check the box next to the name of the user(s) you want to add. Your choices will be preserved if you click any of the letters below. When done, click "Finish" to choose the roles for the users you are adding.').'
</p>
<form action="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'" method="post">
<input type="hidden" name="accumulated_ids" value="'. implode(',',$accumulated_ids) .'" />';

$abc_array = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
echo '<p>' . _('Choose the <strong>First Letter</strong> of the name of the person you wish to add.') . '</p>';
for ($i=0; $i<count($abc_array); $i++) {
    if ($sw == $abc_array[$i]) {
        echo '<strong>'.$abc_array[$i].'</strong>&nbsp;';
    } else {
        echo '<input type="submit" name="sw" value="'.$abc_array[$i].'" />&nbsp;';
    }
}

if (!$res || db_numrows($res) < 1) {
	echo '<p>' . _('No Matching Users Found') . '</p>';
} else {

	$titles[]=_('Real name');
	$titles[]=_('Unix name');
	$titles[]=_('Add user');

	echo $HTML->listTableTop($titles);

	//
	//	Everything is built on the multi-dimensial arrays in the Role object
	//
	for ($i=0; $i<db_numrows($res); $i++) {
		$uid = db_result($res,$i,'user_id');
		echo '<tr '. $HTML->boxGetAltRowStyle($i) . '>
			<td>'.db_result($res,$i,'lastname').', '.db_result($res,$i,'firstname').'</td>
			<td>'.db_result($res,$i,'user_name').'</td>
			<td><input type="checkbox" name="newids[]" value="'. $uid .'"';
		if (in_array($uid, $accumulated_ids)) {
			echo ' checked="checked" ';
		}
		echo ' /></td></tr>';

	}

	echo $HTML->listTableBottom();

}

echo '<p><input type="submit" name="finished" value="'._('Finish').'" /></p>
</form>';

project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
