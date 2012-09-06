<?php
/** External authentication via WebID for FusionForge
 * Copyright 2011, Roland Mas
 * Copyright 2011, Olivier Berger & Institut Telecom
 *
 * This program was developped in the frame of the COCLICO project
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

// FIXME : WTF ?!?!?!?
Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT");
Header( "Cache-Control: no-cache");
Header( "Cache-Control: must-revalidate");

require_once '../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once '../../../www/include/login-form.php';

// WebID framework
require_once 'WebIDDelegatedAuth/lib/Authentication.php';

$plugin = plugin_get_object('authwebid');

$return_to = getStringFromRequest('return_to');
//$login = getStringFromRequest('login');

//$webid_identifier = getStringFromRequest('webid');
$triggered = getIntFromRequest('triggered');

if (forge_get_config('use_ssl') && !session_issecure()) {
	//force use of SSL for login
	// redirect
	session_redirect_external('https://'.getStringFromServer('HTTP_HOST').getStringFromServer('REQUEST_URI'));
}

	// TODO check error param in request
	if ( $plugin->justBeenAuthenticatedByIdP() ) {
		//echo "authenticated as :";
		//print_r($plugin->delegatedAuthentifier);
		//exit(0);

			// initiate session
	    	if ($plugin->isSufficient()) {
	    		$user = False;

	    		$username = $plugin->getUserNameFromWebIDIdentity($plugin->getCurrentWebID());
				if ($username) {
					$user_tmp = user_get_object_by_name($username);
					if($user_tmp->usesPlugin($plugin->name)) {
						$user = $plugin->startSession($username);
					}
					else {
						$warning_msg = _('WebID plugin not activated for the user account');
					}
				}

				if($user) {
					$feedback = _('The IdP has confirmed that you own this WebID bound to your account. Welcome.');
					// redirect to the proper place in the forge
					if ($return_to) {
						validate_return_to($return_to);

						session_redirect($return_to);
					} else {
						session_redirect("/my");
					}
				}
				else {
					$warning_msg = sprintf (_("Unknown user with identity '%s'"),$plugin->getCurrentWebID());
				}
	    	}
		}
		else {
			echo "error :". $plugin->delegatedAuthentifier->authnDiagnostic;
			print_r($plugin->delegatedAuthentifier);
			exit(0);
		}
    //}

	// Otherwise, display the login form again
	display_login_page($return_to, $triggered);
	
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
