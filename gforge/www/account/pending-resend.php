<?php
/**
  *
  * Resend account activation email with confirmation URL
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');

$u = user_get_object_by_name($form_user);
exit_assert_object($u, 'User');

if ($u->getStatus() != 'P') {
	exit_error(
		'Invalid action',
		'Your account is already active.'
	);
}

$u->sendRegistrationEmail();

$HTML->header(array(title=>"Account Pending Verification"));

?>

<h2>Pending Account</h2>

<p>
Your email confirmation has been resent. Visit the link
in this email to complete the registration process.
</p>

<p>
<a href="/">[Return to <?php print($GLOBALS['sys_name']) ?>]</a>
</p>

<?php

$HTML->footer(array());

?>
