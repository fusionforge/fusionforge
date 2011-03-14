<?php
/**
 * FusionForge login page
 *
 * This is main login page. It takes care of different account states
 * (by disallowing logging in with non-active account, with appropriate
 * notice).
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

Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

$return_to = getStringFromRequest('return_to');
$triggered = getIntFromRequest('triggered');

if (isset($session_hash)) {
	//nuke their old session
	session_logout();
}

$HTML->header(array('title'=>'Login'));

echo '<p>';

if ($triggered) {
	echo '<div class="warning">' ;
	echo _('You\'ve been redirected to this login page because you have tried accessing a page that was not available to you as an anonymous user.');
	echo '</div> ' ;
}
echo _('Cookies must be enabled past this point.');
echo '</p>';

plugin_hook('display_auth_form');
?>
<p><a href="lostpw.php"><?php echo _('[Lost your password?]'); ?></a></p>
<?php
// hide "new account" item if restricted to admin
if (!forge_get_config ('user_registration_restricted')) {
	echo '<p><a href="register.php">'._('[New Account]').'</a></p>';
}
?>
<p><a href="pending-resend.php"><?php echo _('[Resend confirmation email to a pending account]'); ?></a></p>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
