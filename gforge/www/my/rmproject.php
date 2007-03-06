<?php
/**
 * SourceForge User's Self-removal Page
 *
 * Confirmation page for users' removing themselves from project.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group_id = getIntFromRequest('group_id');

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
	exit_error('Error',$group->getErrorMessage());
}

if (getStringFromRequest('confirm')) {

	$user_id = user_getid();

	if (!$group->removeUser($user_id)) {
		exit_error($Language->getText('general','error'), $group->getErrorMessage());
	} else {                    
		session_redirect("/my/");
	}

}

/*
	Main code
*/

$perm =& $group->getPermission(session_get_user());

if ( $perm->isAdmin() ) {
	exit_error(
		$Language->getText('my_rmproject','operation_not_permitted_title'),
		$Language->getText('my_rmproject','operation_not_permitted_text')
	);
}

echo site_user_header(array('title'=>$Language->getText('my_rmproject','title')));

echo '
<h3>'.$Language->getText('my_rmproject','quitting_project').' </h3>
<p>
'.$Language->getText('my_rmproject','quitting_project_text').'
</p>

<table>
<tr><td>

<form action="'.getStringFromServer('PHP_SELF').'" method="post">
<input type="hidden" name="confirm" value="1" />
<input type="hidden" name="group_id" value="'.$group_id.'" />
<input type="submit" value="'.$Language->getText('general','remove').'" />
</form>

</td><td>

<form action="/my/" method="get">
<input type="submit" value="'.$Language->getText('general','cancel').'" />
</form>

</td></tr>
</table>
';

echo site_user_footer(array());

?>
