<?php
/**
 * User's Self-removal Page
 *
 * Confirmation page for users' removing themselves from project.
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group_id = getIntFromRequest('group_id');

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
    exit_no_group();
} elseif ($group->isError()) {
	exit_error($group->getErrorMessage(),'my');
}

/*
	Main code
*/

$roles = RBACEngine::getInstance()->getAvailableRolesForUser (session_get_user()) ;

$isadmin = false ;
foreach ($roles as $r) {
	if ($r instanceof RoleExplicit
	    && $r->getHomeProject() != NULL
	    && $r->getHomeProject()->getID() == $group_id
	    && $r->hasPermission ('project_admin', $group_id)) {
		$isadmin = true ;
	}
}


if ($isadmin) {
	exit_error(
		sprintf (_('You cannot remove yourself from this project, because you are admin of it. You should ask other admin to reset your admin privilege first. If you are the only admin of the project, please consider posting availability notice to <a href="%s">Help Wanted Board</a> and be ready to pass admin privilege to interested party.'),
			 util_make_url ("/people/")
			) ,'my');
}

if (getStringFromRequest('confirm')) {

	$user_id = user_getid();

	if (!$group->removeUser($user_id)) {
		exit_error($group->getErrorMessage(),'my');
	} else {
		session_redirect("/my/");
	}

}

site_user_header(array('title'=>_('Quitting Project')));

echo '
<h3>'._('Quitting Project').' </h3>
<p>
'._('You are about to remove yourself from the project. Please confirm your action:').'
</p>

<table>
<tr><td>

<form action="'.getStringFromServer('PHP_SELF').'" method="post">
<input type="hidden" name="confirm" value="1" />
<input type="hidden" name="group_id" value="'.$group_id.'" />
<input type="submit" value="'._('Remove').'" />
</form>

</td><td>

<form action="/my/" method="get">
<input type="submit" value="'._('Cancel').'" />
</form>

</td></tr>
</table>
';

site_user_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
