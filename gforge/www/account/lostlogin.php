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

require_once('pre.php');
require_once('common/include/account.php');

if (!$confirm_hash) {
	$confirm_hash = $ch;
}
if (!$confirm_hash) {
	exit_missing_param();
}
// Remove noise from hash produced by buggy mail clients
$confirm_hash = html_clean_hash_string($confirm_hash);

$res_user = db_query("SELECT * FROM users WHERE confirm_hash='$confirm_hash'");
if (db_numrows($res_user) > 1) {
	exit_error(
		$Language->getText('global','error'),
		$Language->getText('account_lostlogin','severalconfirm')
	);
}
if (db_numrows($res_user) < 1) {
	exit_error(
		$Language->getText('global','error'),
		$Language->getText('account_lostlogin','invalidconfirm')
	);
}
$u =& user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
if (!$u || !is_object($u)) {
	exit_error('Error','Could Not Get User');
} elseif ($u->isError()) {
	exit_error('Error',$u->getErrorMessage());
}

if ($submit) {

	if (strlen($passwd)<6) {
		exit_error(
			$Language->getText('global','error'),
			$Language->getText('account_lostlogin','sixchars')
		);
	}

	if ($passwd != $passwd2) {
		exit_error(
			$Language->getText('global','error'),
			$Language->getText('account_lostlogin','notmatch')
		);
	}

	if ($u->setPasswd($passwd)) {

		// Invalidate confirm hash
		$u->setNewEmailAndHash('', 0);

		$HTML->header(array('title'=>"Password changed"));
		echo $Language->getText('account_lostlogin','passwdchanged');
		$HTML->footer(array());
		exit();
	}

	$feedback = $Language->getText('global','error').': '.$u->getErrorMessage();
}

$HTML->header(array('title'=>"Lost Password Login"));
echo $Language->getText('account_lostlogin','welcome',$u->getUnixName());
?>

<form action="<?php echo $PHP_SELF; ?>" method="post">
<p><?php echo $Language->getText('account_lostlogin','newpasswd'); ?>:
<br /><input type="password" name="passwd" /></p>
<p><?php echo $Language->getText('account_lostlogin','newpasswd2'); ?>:
<br /><input type="password" name="passwd2" />
<input type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>" /></p>
<p><input type="submit" name="submit" value="<?php echo $Language->getText('account_lostlogin','update'); ?>" /></p>
</form>

<?php

$HTML->footer(array());

?>
