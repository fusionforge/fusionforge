<?php
/**
 * Redirect authenticated URL to new ITK scheme
 *
 * Copyright 2015  Inria (Sylvain Beucler)
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// - SVN allows automatically relocating the working copy but is VERY picky:
//   - doesn't automatically relocate on temporary redirects (302), only permanent (301)
//   - doesn't automatically relocate if there are more than 2 redirects
//   - doesn't automatically relocate if the redirect is on PROPFIND (2nd request), only on OPTIONS (1st request)

// Don't try to connect to the DB, just redirecting SVN URL
putenv('FUSIONFORGE_NO_DB=true');

require_once '../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

# Force authentication so we get the username
$auth = @$_SERVER['PHP_AUTH_USER'];
if (empty($auth)) {
	header('WWW-Authenticate: Basic realm="'.forge_get_config('apache_auth_realm', 'scmsvn').'"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'Authorization required [this text ignored by SVN]';
	exit;
}

if ($_SERVER['PHP_AUTH_USER'] == forge_get_config('anonsvn_login', 'scmsvn')) {
    header('Location: https://' . forge_get_config('scm_host') . '/anonscm/'
    . $_SERVER['REQUEST_URI'], true, 301);
} else {
	header('Location: https://' . forge_get_config('scm_host') . '/authscm/'
	. $_SERVER['PHP_AUTH_USER'] . $_SERVER['REQUEST_URI'], true, 301);
}