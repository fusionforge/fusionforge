<?php
/**
 * FusionForge Mailing Lists Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2003-2004 (c) Guillaume Smet - Open Wide
 * Portions Copyright 2010 (c) Mélanie Le Bail
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once 'env.inc.php';
require_once 'pre.php';
require_once 'preplugins.php';
require_once 'plugins_utils.php';
require_once '../mailman_utils.php';

$request =& HTTPRequest::instance();
$group_id = $request->get('group_id');

global $HTML, $feedback;

if (!$group_id) {
	exit_no_group();
}
$pm = ProjectManager::instance();
$Group = $pm->getProject($group_id);
if (!$Group || !is_object($Group) || $Group->isError()) {
	exit_no_group();
}
if(isLogged()) {
	if (!$current_user->isMember($group_id,'A')) {
		exit_permission_denied();
	}
}

$ml = new MailmanList($group_id,$request->get('id'));

if ($request->exist('submit')) {
	$sure = $request->get('sure');
	$really_sure = $request->get('really_sure');
	if (!$ml->deleteList($sure,$really_sure)) {
		exit_error('Error',$ml->getErrorMessage());
	} else {
		$feedback = _('Deleted');
		session_redirect('/plugins/mailman/index.php?group_id='.$group_id);
	}
}

mailman_header(array(
	'title' => _('Permanently Delete List')
));

?>
<h3><?php echo $ml->getName(); ?></h3>
<?php echo $HTML->openForm(array('method' => 'post', 'action' => getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&id='.$ml->getID())); ?>
<p>
<input id="sure" type="checkbox" name="sure" value="1">
<label for="sure">
<?php echo _('Confirm Delete'); ?><br />
</label>
<input id="really_sure" type="checkbox" name="really_sure" value="1">
<label for="really_sure">
<?php echo _('Confirm Delete'); ?><br />
</label>
<input type="submit" name="submit" value="<?php echo _('Permanently Delete'); ?>">
</p>
<?php
echo $HTML->closeForm();
mail_footer();
