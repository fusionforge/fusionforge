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

<form action="'.$PHP_SELF.'" method="post">
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
