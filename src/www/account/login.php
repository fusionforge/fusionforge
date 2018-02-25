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

header( "Expires: Wed, 11 Nov 1998 11:11:11 GMT");
header( "Cache-Control: no-cache");
header( "Cache-Control: must-revalidate");

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/login-form.php';


// remove the url_prefix
$url_prefix = forge_get_config('url_prefix');
$return_to = getStringFromRequest('return_to');
if (substr($return_to, 0, strlen($url_prefix)) == $url_prefix) {
	$return_to = '/'.substr($return_to, strlen($url_prefix));
}

$triggered = getIntFromRequest('triggered');

if (isset($session_hash)) {
	//nuke their old session
	session_logout();
}

display_login_page($return_to, $triggered);

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
