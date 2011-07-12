<?php
/**
 * Project Membership Request
 *
 * Copyright 2005 (c) GForge, L.L.C.
 * http://fusionforge.org/
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/GroupJoinRequest.class.php';

$group_id=getIntFromGet('group_id');
$submit=getStringFromPost('submit');
$comments=getStringFromPost('comments');

if (!$group_id) {
	exit_no_group();
}

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group = group_get_object($group_id);

if ($submit) {

	$gjr=new GroupJoinRequest($group);
	$usr=&session_get_user();
	if (!$gjr->create($usr->getId(),$comments)) {
		exit_error($gjr->getErrorMessage(),'summary');
	} else {
		$feedback = _('Your request has been submitted.');
	}
}

$title = _('Request to join project') . ' '.$group->getPublicName();

site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'summary'));

plugin_hook ("blocks", "request_join");

?>
<p><?php
$nbadmins = count($group->getAdmins());
echo ngettext('You can request to join a project by clicking the submit button. The administrator will be emailed to approve or deny your request.', 'You can request to join a project by clicking the submit button. The administrators will be emailed to approve or deny your request.', $nbadmins); ?></p>
<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id"; ?>" method="post">
<p>
<?php echo ngettext('You must send a comment to the administrator:', 'You must send a comment to the administrators:',$nbadmins); echo utils_requiredField(); ?>
</p>
<textarea name="comments" rows="15" cols="60"><?php echo $comments ?></textarea>
<p>
	<input type="submit" name="submit" value="<?php echo _('Submit'); ?>" />
</p>
</form>
<?php

site_project_footer(array());

?>
