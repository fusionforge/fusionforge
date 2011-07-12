<?php
/**
 * Role Delete Page
 *
 * Copyright 2010 (c) Alcatel-Lucent
 *
 * @author Alain Peyrat
 * @date 2010-05-18
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
require_once $gfcommon.'include/Role.class.php';

$group_id = getIntFromRequest('group_id');
$role_id = getIntFromRequest('role_id');

session_require_perm ('project_admin', $group_id) ;

if (!$role_id) {
	session_redirect('/project/admin/users.php?group_id='.$group_id);
}

$group = group_get_object($group_id);

$role = new Role($group,$role_id);
if (!$role || !is_object($role)) {
	exit_error(_('Could Not Get Role'),'admin');
} elseif ($role->isError()) {
	exit_error($role->getErrorMessage(),'admin');
}

if (getStringFromRequest('submit')) {
	if (getIntFromRequest('sure')) {
		if (!$role->delete()) {
			$error_msg = _('ERROR: ').$role->getErrorMessage();
		} else {
			$feedback = _('Successfully Deleted Role');
		}
	} else {
		$error_msg = _('Error: Please check "I\'m Sure" to confirm or return to previous page to cancel.');
	}

	//plugin webcal
	//change assistant for webcal
	$params = getIntFromRequest('group_id');
	plugin_hook('change_cal_permission_auto',$params);

	if (!isset($error_msg)) {
		session_redirect('/project/admin/users.php?group_id='.$group_id.'&error_msg='.urlencode($error_msg));
	}
}

$title = sprintf(_('Permanently Delete Role %s'), $role->getName());
project_admin_header(array('title'=>$title,'group'=>$group_id));

printf(_('You are about to permanently delete role %s'), $role->getName()); ?>

<form action="<?php echo getStringFromServer('PHP_SELF') ?>?group_id=<?php echo $group_id ?>&amp;role_id=<?php echo $role_id ?>" method="post">
<p>
<input name="sure" value="1" type="checkbox" /><?php echo _("I'm Sure") ?><br />
</p>

<p>
<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
</p>
</form>

<?php project_admin_footer(array()) ?>
