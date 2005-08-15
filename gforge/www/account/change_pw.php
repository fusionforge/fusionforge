<?php
/**
 * Change user's password
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

session_require(array('isloggedin'=>1));

$u =& user_get_object(user_getid());
if (!$u || !is_object($u)) {
	exit_error('Error','Could Not Get User');
} elseif ($u->isError()) {
	exit_error('Error',$u->getErrorMessage());
}

if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}

	$old_passwd = getStringFromRequest('old_passwd');
	$passwd = getStringFromRequest('passwd');
	$passwd2 = getStringFromRequest('passwd2');

	if ($u->getMD5Passwd() != md5($old_passwd)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			$Language->getText('general','error'),
			$Language->getText('account_change_pw','old_password_incorrect')
		);
	}
	
	if (strlen($passwd)<6) {
		form_release_key($_POST['form_key']);
		exit_error(
			$Language->getText('general','error'),
			$Language->getText('account_change_pw','not_valid_password')
		);
	}
	
	if ($passwd != $passwd2) {
		form_release_key($_POST['form_key']);
		exit_error(
			$Language->getText('general','error'),
			$Language->getText('account_change_pw','passwords_dont_match')
		);
	}

	if (!$u->setPasswd($passwd)) {
		form_release_key($_POST['form_key']);
		exit_error(
			$Language->getText('general','error'),
			'Could not change password: '.$u->getErrorMessage()
		);
	}

	site_user_header(array('title'=>$Language->getText('account_change_pw_changed','title')));
	?>

	<?php echo $Language->getText('account_change_pw', 'confirmation', $GLOBALS['sys_name']); ?>

	<p>
	<?php echo $Language->getText('account_change_pw_changed','return_to',array('<a href="/account/">','</a>')) ?>
	</p>
	
	<?php
} else { 
	// Show change form
	site_user_header(array('title'=>$Language->getText('account_change_pw','title')));
	?>

	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>">
	<p><?php echo $Language->getText('account_change_pw','old_password') ?>:
	<br /><input type="password" name="old_passwd" /></p>
	<p><?php echo $Language->getText('account_change_pw','new_password') ?>:
	<br /><input type="password" name="passwd" /></p>
	<p><?php echo $Language->getText('account_change_pw','new_password_repeat') ?>:
	<br /><input type="password" name="passwd2" /></p>
	<p><input type="submit" name="submit" value="<?php echo $Language->getText('account_change_pw','update') ?>" /></p>
	</form>
	<?php
}

site_user_footer(array());

?>
