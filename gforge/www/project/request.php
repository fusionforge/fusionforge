<?php
/**
 * Project Membership Request
 *
 * The rest Copyright 2005 (c) GForge, L.L.C.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once('pre.php');
require_once('common/include/GroupJoinRequest.class.php');

$group_id=getIntFromGet('group_id');
$submit=getStringFromPost('submit');
$comments=getStringFromPost('comments');

if (!$group_id) {
	exit_no_group();
}

if (!session_loggedin()) {
	exit_not_logged_in();
}

if ($submit) {

	$group =& group_get_object($group_id);
	$gjr=new GroupJoinRequest($group);
	$usr=&session_get_user();
	if (!$gjr->create($usr->getId(),$comments)) {
		exit_error('Error',$gjr->getErrorMessage());
	} else {
		$feedback .= _('Your request has been submitted.');
	}
}

site_project_header(array('title'=>_('Request to join project'),'group'=>$group_id,'toptab'=>'summary'));

?>
<p><?php echo _('You can request to join a project by clicking the submit button. An administrator will be emailed to approve or deny your request.'); ?></p>
<form action="<?php echo getStringFromServer('PHP_SELF')."?group_id=$group_id"; ?>" method="post">
<p>
<?php echo _('If you want, you can send a comment to the administrator:'); ?><br>
<textarea name="comments" rows="15" cols="60"></textarea>
</p>
<p>
	<input type="submit" name="submit" value="<?php echo _('Submit'); ?>" />
</p>
</form>
<?php

site_project_footer(array());

?>
