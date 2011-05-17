<?php
/**
 * Module of support routines for Site Admin
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

function site_admin_header($params, $required_perm = 'forge_admin') {
	session_require_global_perm ($required_perm);

	if (version_compare(PHP_VERSION, '5.1.0', '<')) {
		$GLOBALS['warning_msg'] = 'WARNING: Your php version must not be lower than 5.1.0, please upgrade';
	}
	if (get_magic_quotes_gpc()) {
		$GLOBALS['warning_msg'] = 'WARNING: Your installation is running with php magic_quotes_gpc ON, please change to OFF';
	}
	if (ini_get('register_globals')) {
		$GLOBALS['warning_msg'] = 'WARNING: Your installation is running with php register_globals ON, this is very unsecure, please change to OFF';
	}
	site_header($params);
}

function site_admin_footer($params) {
	site_footer($params);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
