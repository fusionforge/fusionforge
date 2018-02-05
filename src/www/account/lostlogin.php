<?php
/**
 * Recover lost password page
 *
 * This page is accessed via confirmation URL in email
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
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
	exit_error(_('Invalid confirmation hash.'),'my');
}
$u = user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'home');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
} elseif (($u->getStatus() == 'S') || ($u->getStatus() == 'D')) {
	exit_error(_('Account is suspended or deleted'),'my');
}

if (getStringFromRequest("submit")) {

	if (strlen($passwd) < 8) {
		$message = _('You must supply valid password (at least 8 characters).').(forge_get_config('check_password_strength') ? html_e('br')._('Must contain at least one uppercase letter, one lowercase, one digit, one non-alphanumeric character.') : '')
		exit_error($message, 'my');
	}

	if ($passwd != $passwd2) {
		exit_error(_('New passwords do not match.'),'my');
	}

	if ($u->setPasswd($passwd)) {

		// Invalidate confirm hash
		$u->setNewEmailAndHash('', 0);
		$feedback = _('Password changed successfully');
		$HTML->header(array('title' => _('Lost Account Password'));
		print '<p>';
		printf (_('Congratulations, you have re-set your account password. You may <a href="%s">login</a> to the site now.'),
			  util_make_url ("/account/login.php"));
		print '</p>';
		$HTML->footer();
		exit();
	}

	$error_msg = _('Error')._(': ').$u->getErrorMessage();
}

$title = _("Lost Password Login") ;
$HTML->header(array('title'=>$title));
echo html_e('p', array(), sprintf(_('Welcome, %s. You may now change your password.'),$u->getUnixName()));
echo $HTML->openForm(array('action' => '/account/lostlogin.php', 'method' => 'post'));
?>
<p><?php echo _('New Password (at least 8 characters)')._(':').html_e('br').(forge_get_config('check_password_strength') ? _('Must contain at least one uppercase letter, one lowercase, one digit, one non-alphanumeric character.').html_e('br') : ''); ?>
<label for="passwd">
	<input id="passwd" type="password" name="passwd"/>
</label>
</p>
<p><?php echo _('New Password (repeat)'); ?>:
<br />
<label for="passwd2">
	<input id="passwd2" type="password" name="passwd2"/>
</label>
<input type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>" /></p>
<p><input type="submit" name="submit" value="<?php echo _('Update'); ?>" /></p>

<?php
echo $HTML->closeForm();
$HTML->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
