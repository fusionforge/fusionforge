<?php
/**
  *
  * SourceForge User's Personal Page
  *
  * Confirmation page for users' removing themselves from project.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

if (!session_loggedin()) {
	exit_not_logged_in();
}

$group =& group_get_object($group_id);
exit_assert_object($group,'Group');

if ($confirm) {

	$user_id = user_getid();

	if (!$group->removeUser($user_id)) {
		exit_error('ERROR', $group->getErrorMessage());
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
		'Operation Not Permitted',
		'You cannot remove yourself from this project, because '
		.'you are admin of it. You should ask other admin to reset '
		.'your admin privilege first. If you are the only admin of '
		.'the project, please consider posting availability notice to '
		.'<a href="/people/">Help Wanted Board</a> and be ready to '
		.'pass admin privilege to interested party.'
	);
}

echo site_user_header(array('title'=>'Quitting Project'));

echo '
<h3>Quitting Project</h3>
<p>
You are about to remove yourself from the project. Please
confirm your action:
</p>

<table>
<tr><td>

<form action="'.$PHP_SELF.'" method="post">
<input type="hidden" name="confirm" value="1" />
<input type="hidden" name="group_id" value="'.$group_id.'" />
<input type="submit" value="Remove" />
</form>

</td><td>

<form action="/my/" method="get">
<input type="submit" value="Cancel" />
</form>

</td></tr>
</table>
';

echo site_user_footer(array());

?>
