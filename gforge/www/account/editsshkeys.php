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

require_once('../env.inc.php');
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

	echo _('<p>To avoid having to type your password every time for your CVS/SSH developer account, you may upload your public key(s) here and they will be placed on the CVS server in your ~/.ssh/authorized_keys file. This is done by a cron job, so it may not happen immediately.  Please allow for a one hour delay.</p><p>To generate a public key, run the program \'ssh-keygen\' (you can use both protocol 1 or 2). The public key will be placed at \'~/.ssh/identity.pub\' (protocole 1) and \'~/.ssh/id_dsa.pub\' or \'~/.ssh/id_rsa.pub\' (protocole 2). Read the ssh documentation for further information on sharing keys.</p>');
	?>

<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<p><?php echo _('Authorized keys:<br /><em>Important: Make sure there are no line breaks except between keys. After submitting, verify that the number of keys in your file is what you expected.</em>'); ?>
<br />
<textarea rows="10" cols="80" name="authorized_keys" style="width:90%;">
<?php echo $u->getAuthorizedKeys(); ?>
</textarea></p>
<p><input type="submit" name="submit" value="<?php echo _('Update'); ?>" /></p>
</form>

	<?php
}
site_user_footer(array());

?>
