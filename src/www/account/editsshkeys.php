<?php
/**
 * Change user's SSH authorized keys
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright 2012,2014, Franck Villaume - TrivialDev
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
site_user_header(array('title'=> _('Manage Authorized Keys')));
$sshKeysArray = $u->getAuthorizedKeys();
if (count($sshKeysArray)) {
	echo $HTML->boxTop(_('Available keys'));
	$tabletop = array(_('Name'), _('Algorithm'), _('Fingerprint'), _('Uploaded'), _('Actions'));
	$classth = array('', '', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, array(), 'sortable_sshkeys_listlinks', 'sortable', $classth);
	foreach($sshKeysArray as $sshKey) {
		$cells = array();
		$cells[][] = $sshKey['name'];
		$cells[][] = $sshKey['algorithm'];
		$cells[][] = $sshKey['fingerprint'];
		$cells[][] = date(_('Y-m-d H:i'), $sshKey['upload']);
		$cells[][] = util_make_link('/account/?&action=deletesshkey&keyid='.$sshKey['keyid'], html_image('docman/trash-empty.png',22,22,array('alt'=>_('Delete this ssh key.'))), array('title' => _('Delete this ssh key.')));
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
}
echo $HTML->openForm(array('action' => util_make_uri('/account/?action=addsshkey'), 'method' => 'post', 'enctype' => 'multipart/form-data'));
echo html_e('h2', array(), _('Add a new ssh key'));
echo html_e('p', array(), _('To avoid having to type your password every time for your SSH developer account, you may upload your public key(s) here and they will be placed on the server in your ~/.ssh/authorized_keys file. Uploaded SSH keys are effective <em>immediately</em>.'));
echo html_e('p', array(), _('To generate a public key, run the program \'ssh-keygen\' (you can use both protocol 1 or 2). The public key will be placed at \'~/.ssh/identity.pub\' (protocol version 1) and \'~/.ssh/id_dsa.pub\' or \'~/.ssh/id_rsa.pub\' (protocol version 2). Read the ssh documentation for further information on sharing keys.'));
echo html_e('p', array(), html_e('em', array(), _('Important: Make sure there are no line breaks. After submitting, verify that the number of keys in your file is what you expected.')));
echo html_e('textarea', array('rows' => 10,  'cols' => 80, 'name' => 'authorized_key', 'style' => 'width:90%;'), '', false);
echo html_e('p', array(), _('Or upload your \'~/.ssh/identity.pub\' (protocol version 1) or \'~/.ssh/id_dsa.pub\' or \'~/.ssh/id_rsa.pub\' (protocol version 2)'));
echo html_e('input', array('type' => 'file', 'name' => 'uploaded_filekey'));
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Add'))));
echo $HTML->closeForm();

site_user_footer();
