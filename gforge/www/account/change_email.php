<?php
/**
 * Change user's email page
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

session_require(array('isloggedin'=>1));


if (getStringFromRequest('submit')) {
	if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}

	$newemail = getStringFromRequest('newemail');

	if (!validate_email($newemail)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error($Language->getText('general','error'),$Language->getText('account_change_email','invalid_email'));
	}

	$confirm_hash = substr(md5($session_hash . time()),0,16);

	$u =& user_get_object(user_getid());
	if (!$u || !is_object($u)) {
   		form_release_key(getStringFromRequest('form_key'));
   		exit_error('Error','Could Not Get User');
	} elseif ($u->isError()) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error('Error',$u->getErrorMessage());
	}

	if (!$u->setNewEmailAndHash($newemail, $confirm_hash)) {
		form_release_key(getStringFromRequest('form_key'));
		exit_error(
			'Could Not Complete Operation',
			$u->getErrorMessage()
		);
	}

	$message = stripcslashes($Language->getText('account_change_email', 'message', array(getStringFromServer('HTTP_HOST'), $confirm_hash, $GLOBALS['sys_name'])));

	util_send_message($newemail,$Language->getText('account_change_email', 'subject', $GLOBALS['sys_name']),$message);

	site_user_header(array('title'=>$Language->getText('account_change_email_confirm','title')));

	echo $Language->getText('account_change_email', 'mailsent');

	site_user_footer(array());
	exit();
}


site_user_header(array('title'=>$Language->getText('account_change_email','title')));

echo $Language->getText('account_change_email', 'desc');
?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>"/>
<?php echo $Language->getText('account_change_email','new_address') ?>
<input type="text" name="newemail" maxlength="255" />
<input type="submit" name="submit" value="<?php echo $Language->getText('account_change_email','send_confirmation') ?>" />
</form>

<p><a href="/"><?php echo $Language->getText('general', 'return', $sys_name); ?></a></p>

<?php
site_user_footer(array());

?>
