<?php
/**
 * Change user's SSH authorized keys
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
	$authorized_keys = getStringFromRequest('authorized_keys');

	if (!$u->setAuthorizedKeys($authorized_keys)) {
		exit_error(
			'Error',
			'Could not update SSH authorized keys: '.db_error()
		);
	}
	session_redirect("/account/");

} else {
	// not valid registration, or first time to page
	site_user_header(array('title'=>'Change Authorized Keys'));

	echo $Language->getText('account_editsshkeys', 'intro');
	?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<p><?php echo $Language->getText('account_editsshkeys', 'authorized_keys'); ?>
<br />
<textarea rows="10" name="authorized_keys" style="width:90%;">
<?php echo $u->getAuthorizedKeys(); ?>
</textarea></p>
<p><input type="submit" name="submit" value="<?php echo $Language->getText('general', 'update'); ?>" /></p>
</form>

	<?php
}
site_user_footer(array());

?>
