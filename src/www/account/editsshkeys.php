<?php
/**
 * Change user's SSH authorized keys
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

/**
 * Simple function that tries to check the validity of public ssh keys with a regexp.
 * Exits with an error message if an invalid key is found.
 *
 * @param keys A string with a set of keys to check. Each key is delimited by a carriage return.
 */
function checkKeys($keys) {
	$key = strtok($keys,"\n");
	
	while ($key !== false) {
		$key = trim($key);
		if ((strlen($key) > 0) && ($key[0] != '#')) {
			/* The encoded key is made of 0-9, A-Z ,a-z, +, / (base 64) characters,
			 ends with zero or up to three '=' and the length must be >= 512 bits (157 base64 characters).
			 The whole key ends with an optional comment. */
			if ( preg_match("@^(((no-port-forwarding|no-X11-forwarding|no-agent-forwarding|no-pty|command=\"[^\"]+\"|from=\"?[A-Za-z0-9\.-]+\"?),?)*\s+)?ssh-(rsa|dss)\s+[A-Za-z0-9+/]{157,}={0,2}(\s+.*)?$@", $key) === 0 ) { // Warning: we must use === for the test
				$msg = sprintf (_('The following key has a wrong format: |%s|.  Please, correct it by going back to the previous page.'),
						htmlspecialchars($key));
				exit_error($msg,'my');
			}
		}
		$key = strtok("\n");
	}
}

session_require_login () ;

$u =& user_get_object(user_getid());
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'home');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

if (getStringFromRequest('submit')) {
	$authorized_keys = getStringFromRequest('authorized_keys');
	checkKeys ($authorized_keys);

	if (!$u->setAuthorizedKeys($authorized_keys)) {
		exit_error(sprintf(_('Could not update SSH authorized keys: %s'),db_error()),'my');
	}
	session_redirect("/account/");

} else {
	// not valid registration, or first time to page
	site_user_header(array('title'=>'Change Authorized Keys'));

	echo '<p>' . _('To avoid having to type your password every time for your CVS/SSH developer account, you may upload your public key(s) here and they will be placed on the CVS server in your ~/.ssh/authorized_keys file. This is done by a cron job, so it may not happen immediately.  Please allow for a one hour delay.') . '</p>';
	echo '<p>' . _('To generate a public key, run the program \'ssh-keygen\' (you can use both protocol 1 or 2). The public key will be placed at \'~/.ssh/identity.pub\' (protocol version 1) and \'~/.ssh/id_dsa.pub\' or \'~/.ssh/id_rsa.pub\' (protocol version 2). Read the ssh documentation for further information on sharing keys.') . '</p>';
	?>

<form action="<?php echo util_make_url('/account/editsshkeys.php'); ?>" method="post">
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
