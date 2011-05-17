<?php
/**
 * Recover lost password page
 *
 * This page is accessed via confirmation URL in email
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
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
require_once $gfcommon.'include/account.php';

$passwd = getStringFromRequest('passwd');
$passwd2 = getStringFromRequest('passwd2');
$confirm_hash = getStringFromRequest('confirm_hash');

if (!$confirm_hash) {
	$confirm_hash = getStringFromRequest('ch');
}
if (!$confirm_hash) {
	exit_missing_param('',array(_('Confirm Hash')),'my');
}
// Remove noise from hash produced by buggy mail clients
$confirm_hash = html_clean_hash_string($confirm_hash);

$res_user = db_query_params ('SELECT * FROM users WHERE confirm_hash=$1',
			array($confirm_hash)) ;

if (db_numrows($res_user) > 1) {
	exit_error(_('This confirm hash exists more than once.'),'my');
}
if (db_numrows($res_user) < 1) {
	exit_error(_('Invalid confirmation hash'),'my');
}
$u =& user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'home');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

if (getStringFromRequest("submit")) {

	if (strlen($passwd)<6) {
		exit_error(_('You must supply valid password (at least 6 chars).'),'my');
	}

	if ($passwd != $passwd2) {
		exit_error(_('New passwords do not match.'),'my');
	}

	if ($u->setPasswd($passwd)) {

		// Invalidate confirm hash
		$u->setNewEmailAndHash('', 0);

		$HTML->header(array('title'=>"Password changed"));
		print '<h2>' . _('Password changed') . '</h2>';
		print '<p>';
		printf (_('Congratulations, you have re-set your account password. You may <a href="%1$s">login</a> to the site now.'),
			  util_make_url ("/account/login.php"));
		print '</p>';
		$HTML->footer(array());
		exit();
	}

	$error_msg = _('ERROR').': '.$u->getErrorMessage();
}

$title = _("Lost Password Login") ;
$HTML->header(array('title'=>$title));
echo '<p>' ;
printf (_('Welcome, %s. You may now change your password.'),$u->getUnixName());
echo '</p>';
?>

<form action="<?php echo util_make_url('/account/lostlogin.php'); ?>" method="post">
<p><?php echo _('New Password (min. 6 chars)'); ?>:
<br /><input type="password" name="passwd" /></p>
<p><?php echo _('New Password (repeat)'); ?>:
<br /><input type="password" name="passwd2" />
<input type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>" /></p>
<p><input type="submit" name="submit" value="<?php echo _('Update'); ?>" /></p>
</form>

<?php

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
