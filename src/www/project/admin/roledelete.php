<?php
/**
 * Role Delete Page
 *
 * Copyright 2010 (c) Alcatel-Lucent
 * Copyright 2011, Roland Mas
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfcommon.'include/Role.class.php';

$group_id = getIntFromRequest('group_id');
$role_id = getIntFromRequest('role_id');

session_require_perm ('project_admin', $group_id) ;

global $HTML, $error_msg, $feedback;

if (!$role_id) {
	session_redirect('/project/admin/users.php?group_id='.$group_id);
}

$group = group_get_object($group_id);

$role = RBACEngine::getInstance()->getRoleById($role_id);

if (!$role || !is_object($role)) {
	exit_error(_('Could Not Get Role'),'admin');
} elseif ($role->isError()) {
	exit_error($role->getErrorMessage(),'admin');
}

if ($role->getHomeProject() == NULL) {
	exit_error(_("You can't delete a global role from here."),'admin');
}

if ($role->getHomeProject()->getID() != $group_id) {
	exit_error(_("You can't delete a role belonging to another project."),'admin');
}

if (getStringFromRequest('submit')) {
	if (getIntFromRequest('sure')) {
		$role_name = $role->getName();
		if (!$role->delete()) {
			$error_msg = _('Error')._(': ').$role->getErrorMessage();
		} else {
			$feedback = _('Successfully Deleted Role');
			$group->addHistory(_('Deleted Role'), $role_name);
			session_redirect('/project/admin/users.php?group_id='.$group_id);
		}
	} else {
		$error_msg = _('Error')._(': ')._('Please check “I am Sure” to confirm or return to previous page to cancel.');
	}
	session_redirect('/project/admin/users.php?group_id='.$group_id);
}

$title = sprintf(_('Permanently Delete Role %s'), $role->getName());
project_admin_header(array('title'=>$title,'group'=>$group_id));

printf(_('You are about to permanently delete role %s'), $role->getName()); ?>

<table class="centered">
<tr>
<td>
<fieldset>
<legend><?php echo _('Confirm Delete') ?></legend>
<?php echo $HTML-> openForm(array('action' => getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;role_id='.$role_id, 'method' => 'post')); ?>
<p>
<input id="sure" type="checkbox" value="1" name="sure" />
<label for="sure">
<?php echo _('I am Sure'); ?>
</label>
</p>

<p>
<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
</p>
<?php echo $HTML->closeForm(); ?>
</fieldset>
</td>
</tr>
</table>

<?php
project_admin_footer();
