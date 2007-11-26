<?php
/**
 * Recover lost password page
 *
 * This page is accessed via confirmation URL in email
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
require_once('common/include/account.php');

$passwd = getStringFromRequest('passwd');
$passwd2 = getStringFromRequest('passwd2');
$confirm_hash = getStringFromRequest('confirm_hash');

if (!$confirm_hash) {
	// XXX ogi: What's $ch?
	$confirm_hash = getStringFromRequest('ch');
}
if (!$confirm_hash) {
	exit_missing_param();
}
// Remove noise from hash produced by buggy mail clients
$confirm_hash = html_clean_hash_string($confirm_hash);

$res_user = db_query("SELECT * FROM users WHERE confirm_hash='$confirm_hash'");
if (db_numrows($res_user) > 1) {
	exit_error(
		_('ERROR'),
		_('This confirm hash exists more than once.')
	);
}
if (db_numrows($res_user) < 1) {
	exit_error(
		_('ERROR'),
		_('Invalid confirmation hash')
	);
}
$u =& user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
if (!$u || !is_object($u)) {
	exit_error('Error','Could Not Get User');
} elseif ($u->isError()) {
	exit_error('Error',$u->getErrorMessage());
}

if (getStringFromRequest("submit")) {

	if (strlen($passwd)<6) {
		exit_error(
			_('ERROR'),
			_('You must supply valid password (at least 6 chars).')
		);
	}

	if ($passwd != $passwd2) {
		exit_error(
			_('ERROR'),
			_('New passwords do not match.')
		);
	}

	if ($u->setPasswd($passwd)) {

		// Invalidate confirm hash
		$u->setNewEmailAndHash('', 0);

		$HTML->header(array('title'=>"Password changed"));
		echo _('<h2>Password changed</h2><p>Congratulations, you have re-set your account password.You may <a href="/account/login.php">login</a> to the sitenow.</p>');
		$HTML->footer(array());
		exit();
	}

	$feedback = _('ERROR').': '.$u->getErrorMessage();
}

$title = _("Lost Password Login") ;
$HTML->header(array('title'=>$title));
echo '<h2>'.$title.'</h2><p>' ;
printf (_('Welcome, %s. You may now change your password.'),$u->getUnixName());
echo '</p>';
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
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
