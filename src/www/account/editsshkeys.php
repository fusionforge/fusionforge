<?php
/**
 * Change user's SSH authorized keys
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
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

global $HTML;

if (!forge_get_config('use_shell')) {
	exit_permission_denied();
}

session_require_login();

$u = user_get_object(user_getid());
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'),'home');
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

use_javascript('/js/sortable.js');
// not valid registration, or first time to page
site_user_header(array('title'=>_('Manage Authorized Keys')));
$sshKeysArray = $u->getAuthorizedKeys();
if (count($sshKeysArray)) {
	echo $HTML->boxTop(_('Available keys'));
	$tabletop = array(_('Name'), _('Algorithm'), _('Fingerprint'), _('Uploaded'), _('Ready ?'), _('Actions'));
	$classth = array('', '', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, array(), 'sortable_sshkeys_listlinks', 'sortable', $classth);
	foreach($sshKeysArray as $sshKey) {
		$cells = array();
		$cells[][] = $sshKey['name'];
		$cells[][] = $sshKey['algorithm'];
		$cells[][] = $sshKey['fingerprint'];
		$cells[][] = date(_('Y-m-d H:i'), $sshKey['upload']);
		if ($sshKey['deploy']) {
			$cells[][] = html_image('docman/validate.png', 22, 22, array('alt'=>_('ssh key is deployed.'),'title'=>_('ssh key is deployed.')));
		} else {
			$cells[][] = html_image('waiting.png', 22, 22, array('alt'=>_('ssh key is not deployed yet.'),'title'=>_('ssh key is not deployed yet.')));
		}
		$cells[][] = util_make_link('/account/?&action=deletesshkey&keyid='.$sshKey['keyid'], html_image('docman/trash-empty.png',22,22,array('alt'=>_('Delete this ssh key.'))), array('title' => _('Delete this ssh key.')));
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
}
echo $HTML->openForm(array('action' => util_make_uri('/account/?action=addsshkey'), 'method' => 'post', 'enctype' => 'multipart/form-data'));
echo '<h2>'. _('Add a new ssh key').'</h2>';
echo '<p>'. _('To avoid having to type your password every time for your CVS/SSH developer account, you may upload your public key(s) here and they will be placed on the server in your ~/.ssh/authorized_keys file. This is done by a cron job, so it may not happen immediately.  Please allow for a one hour delay.') . '</p>';
echo '<p>'. _('To generate a public key, run the program \'ssh-keygen\' (you can use both protocol 1 or 2). The public key will be placed at \'~/.ssh/identity.pub\' (protocol version 1) and \'~/.ssh/id_dsa.pub\' or \'~/.ssh/id_rsa.pub\' (protocol version 2). Read the ssh documentation for further information on sharing keys.') . '</p>';
echo '<p><em>'. _('Important: Make sure there are no line breaks. After submitting, verify that the number of keys in your file is what you expected.').'</em></p>';
?>
<textarea rows="10" cols="80" name="authorized_key" style="width:90%;">
</textarea>
<?php
echo '<p>'. _('Or upload your \'~/.ssh/identity.pub\' (protocol version 1) or \'~/.ssh/id_dsa.pub\' or \'~/.ssh/id_rsa.pub\' (protocol version 2)') .'</p>';
echo '<input type="file" name="uploaded_filekey" />';
?>
<p><input type="submit" name="submit" value="<?php echo _('Add'); ?>" /></p>

<?php
echo $HTML->closeForm();

site_user_footer();
