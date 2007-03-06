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
require_once('common/include/GroupJoinRequest.class');

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
		$feedback .= $Language->getText('project_joinrequest','submitted');
	}
}

site_project_header(array('title'=>$Language->getText('project_joinrequest','title'),'group'=>$group_id,'toptab'=>'summary'));

?>
<p><?php echo $Language->getText('project_joinrequest', 'joining'); ?></p>
<form action="<?php echo "$PHP_SELF?group_id=$group_id"; ?>" method="post">
<p>
<?php echo $Language->getText('project_joinrequest', 'comments'); ?><br>
<textarea name="comments" rows="15" cols="60"></textarea>
</p>
<p>
	<input type="submit" name="submit" value="<?php echo $Language->getText('general', 'submit'); ?>" />
</p>
</form>
<?php

site_project_footer(array());

?>