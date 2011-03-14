<?php
/**
 * FusionForge login page
 *
 * This is main login page. It takes care of different account states
 * (by disallowing logging in with non-active account, with appropriate
 * notice).
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

Header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT"); 
Header( "Cache-Control: no-cache"); 
Header( "Cache-Control: must-revalidate"); 

require_once('../../../www/env.inc.php');
require_once $gfcommon.'include/pre.php';

$plugin = plugin_get_object('authcas');

$return_to = getStringFromRequest('return_to');
$login = getStringFromRequest('login');
$postcas = getStringFromRequest('postcas');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));
$triggered = getIntFromRequest('triggered');

//
//	Validate return_to
//
if ($return_to) {
	$tmpreturn=explode('?',$return_to);
	$rtpath = $tmpreturn[0] ;

	if (@is_file(forge_get_config('url_root').$rtpath)
	    || @is_dir(forge_get_config('url_root').$rtpath)
	    || (strpos($rtpath,'/projects') == 0)
	    || (strpos($rtpath,'/plugins/mediawiki') == 0)) {
		$newrt = $return_to ;
	} else {
		$newrt = '/' ;
	}
	$return_to = $newrt ;
}

if (forge_get_config('use_ssl') && !session_issecure()) {
	//force use of SSL for login
	header('Location: https://'.getStringFromServer('HTTP_HOST').getStringFromServer('REQUEST_URI'));
}

// Start authentication proper
if ($login) {		     // The user just clicked the Login button
	// Let's send them to CAS

	$plugin->initCAS();
	$return_url = util_make_url('/plugins/authcas/post-login.php?postcas=true&return_to='.htmlspecialchars($return_to));

	$GLOBALS['PHPCAS_CLIENT']->setURL($return_url);

	phpCAS::forceAuthentication();

} elseif ($postcas) {		// The user is coming back from CAS
	if (phpCAS::isAuthenticated()) {
		if ($plugin->isSufficient()) {
			$plugin->login(user_get_object_by_name($form_loginname));
		}
		if ($return_to) {
			header ("Location: " . util_make_url($return_to));
			exit;
		} else {
			header ("Location: " . util_make_url("/my"));
			exit;
		}
	}
}

// Otherwise, display the login form again

$HTML->header(array('title'=>'Login'));

$params = array();
$params['return_to'] = $return_to;
plugin_hook('display_auth_form');

$HTML->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
