<?php

/** External authentication via WebID for FusionForge
 * Copyright 2011, Roland Mas
 * Copyright 2011-2012, Olivier Berger & Institut Mines-Telecom
 *
 * This program was initially developped in the frame of the COCLICO project
 * (http://www.coclico-project.org/) with financial support of the Paris
 * Region council.
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

require_once ('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';

session_require_login();

// get global users vars
$u =& user_get_object(user_getid());
if (!$u || !is_object($u)) {
	exit_error(_('Could Not Get User'));
} elseif ($u->isError()) {
	exit_error($u->getErrorMessage(),'my');
}

$plugin = plugin_get_object('authwebid');

// we receive this when addition or deletion is confirmed
$webid_identity = htmlspecialchars(trim(getStringFromRequest('webid_identity', 'http://')));

// When invoked back by the IdP, the request is signed
if (getStringFromRequest('sig') != '') {

	// First, verify that we indeed got invoked back as a callback of the IdP delegated auth
	if ( $plugin->justBeenAuthenticatedByIdP() ) {
		
		// We can then trust the webid set by WebIDDelegatedAuth lib
		$webid_identity = $plugin->getCurrentWebID();

		// Now, if we went back to the IdP in order to confirm a pending binding, it's time to bind it
		if ( $plugin->isStoredPendingWebID($u->getID(), $webid_identity) ) {
			
			$error_msg = $plugin->bindStoredWebID($u->getID(), $webid_identity);
			if ($error_msg) {
				$webid_identity = 'http://';
			} else {
				$feedback = _('The IdP has confirmed that you own this WebID. It is now bound to your account.');
			}
		}
		else {
			// or it's the first time we went to the IdP, and we wait until the confirmation of the binding to really use it 
			$error_msg = $plugin->addStoredPendingWebID($u->getID(), $webid_identity);
			if ($error_msg) {
				$webid_identity = 'http://';
			} else {
				$feedback = _('The IdP has confirmed that you own a WebID. Please confirm you want to bind it to your account.');
			}
		}
	} 
}

// If called to remove an identity
if (getStringFromRequest('delete') != '') {
	
	$error_msg = $plugin->removeStoredWebID($u->getID(), $webid_identity);
	
	if (!$error_msg) {
		$feedback = _('Identity successfully deleted');
		$webid_identity = 'http://';
	}
}

// In all cases, we display the management screen

$title = sprintf(_('Manage WebID identities for user %1$s'), $u->getUnixName());
site_user_header(array('title'=>$title));

echo $HTML->boxTop(_('My WebID identities'));

?>
	<h2><?php echo _('Bind a new WebID'); ?></h2>

		<p><?php 
		
			echo _('You can add your own WebID identities in the form below.') . '<br />'; 
			echo _('Once you have confirmed their binding to your fusionforge account, you may use them to login.') ?></p>

		<?php
		// display a table of WebIDs pending binding 
		$pendingwebids = $plugin->getStoredPendingWebIDs($u->getID());
		if( count($pendingwebids) ) {
			echo $HTML->listTableTop(array(_('Already pending WebIDs you could bind to your account'), ''));

			$i = 0;
			foreach($pendingwebids as $webid_identity) {
				echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
				echo '<td><i>'. $webid_identity .'</i></td>';
				echo '<td><b>'. $plugin->displayAuthentifyViaIdPLink( util_make_url('/plugins/authwebid/index.php'), _('Confirm binding')) . '</b></td>';
				echo '<td><a href="'.util_make_uri ('/plugins/authwebid/').'?webid_identity='. urlencode('pending:'.$webid_identity) .'&delete=1">'. _('remove') . '</a></td>';
				echo '</tr>';
				$i++;
			}
			echo $HTML->listTableBottom();
		}
		?>
		<!-- This form isn't one any more actually, but decorations is nice like this -->		
		<form name="new_identity" action="<?php echo util_make_uri ('/plugins/authwebid/'); ?>" method="post">
			<fieldset>
				<legend><?php echo _('Bind a new WebID'); ?></legend>
				<p>
					<?php 
						echo '</p><p>';
						// redirect link to the IdP
						// This might as well confirm binding just as if using the Confirm link, if user has only one WebID recognized by the IdP
						echo '<b>'. $plugin->displayAuthentifyViaIdPLink( util_make_url('/plugins/authwebid/index.php'), 
																		sprintf( _('Click here to initiate the addition of a new WebID validated via %s'), 
																				 $plugin->delegate_webid_auth_to)) . '</b>';
				?>
				</p>
			</fieldset>
		</form>
		
		<h2><?php echo _('My WebIDs'); ?></h2>

		<?php

		// now display existing bound identities


		$boundwebids = $plugin->getStoredBoundWebIDs($u->getID());

		if(count($boundwebids)) {
			echo $HTML->listTableTop(array(_('WebIDs already bound to your account, which you can use to login'), ''));
			$i = 0;
		
			foreach($boundwebids as $webid_identity) {
				echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
				echo '<td>'. $webid_identity .'</td>';
				echo '<td><a href="'.util_make_uri ('/plugins/authwebid/').'?webid_identity='. urlencode($webid_identity) .'&delete=1">'. _('remove') . '</a></td>';
				echo '</tr>';
				$i++;
			}
		
			echo $HTML->listTableBottom();
		}
		else {
			echo '<p>'. _("You haven't yet bound any WebID to your account") . '</p>';
		}
		
		
		echo $HTML->boxBottom();

site_user_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
