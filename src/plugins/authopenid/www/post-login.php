<?php
/**
 * FusionForge AuthCas login page
 *
 * This is main login page. It takes care of different account states
 * (by disallowing logging in with non-active account, with appropriate
 * notice).
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2011, Roland Mas
 * Copyright 2011 Olivier Berger & Institut Telecom
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// FIXME : WTF ?!?!?!?
Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require_once('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once('../../../www/include/login-form.php');

// from lightopenid (http://code.google.com/p/lightopenid/)
require_once 'openid.php';

$plugin = plugin_get_object('authopenid');

$return_to = getStringFromRequest('return_to');
$login = getStringFromRequest('login');
$openid_identifier = getStringFromRequest('openid_identifier');

$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));
$triggered = getIntFromRequest('triggered');

if (forge_get_config('use_ssl') && !session_issecure()) {
	//force use of SSL for login
	// redirect
	session_redirect_external('https://'.getStringFromServer('HTTP_HOST').getStringFromServer('REQUEST_URI'));
}

try {
	
	// initialize the OpenID lib handler which will read the posted args
	$plugin->openid = new LightOpenID;
	// check the 'openid_mode' that may be set on returning from OpenID provider
	if(!$plugin->openid->mode) {
		
		// We're just called by the login form : redirect to the OpenID provider
        if(isset($_POST['openid_identifier'])) {
            $plugin->openid->identity = $_POST['openid_identifier'];
            session_redirect_external($plugin->openid->authUrl());
        }
        
    // or we are called back by the OpenID provider
    } elseif($plugin->openid->mode == 'cancel') {
        $warning_msg .= _('User has canceled authentication');
    } else {
    	
    	// Authentication should have been attempted by OpenID provider
    	if ($plugin->openid->validate()) {
    		// If user successfully logged in to OpenID provider
    		
    		// initiate session
	    	if ($plugin->isSufficient()) {
	    		$user = False;
	    		
	    		$username = $plugin->getUserNameFromOpenIDIdentity($plugin->openid->identity);
				if ($username) {
					$user = $plugin->startSession($username);
				}
			
				if($user) {
					// redirect to the proper place in the forge
					if ($return_to) {
						validate_return_to($return_to);
	
						session_redirect($return_to);
					} else {
						session_redirect("/my");
					}
				}
				else {
					$warning_msg .= sprintf (_("Unknown user with identity '%s'"),$plugin->openid->identity);
				}
	    	}
		}
    }
    
	// Otherwise, display the login form again
	display_login_page($return_to, $triggered);
        
} catch(ErrorException $e) {
    echo $e->getMessage();
}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
