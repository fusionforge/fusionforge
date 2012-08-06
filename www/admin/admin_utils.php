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

function check_system() {
	$result = array();

	if (version_compare(PHP_VERSION, '5.1.0', '<')) {
		$result[] = 'WARNING: Your php version must not be lower than 5.1.0, please upgrade';
	}
	if (get_magic_quotes_gpc()) {
		$result[] = 'ERROR: Your installation is running with PHP magic_quotes_gpc ON, please change to OFF';
	}
	if (ini_get('register_globals')) {
		$result[] = 'ERROR: Your installation is running with PHP register_globals ON, this is very unsecure, please change to OFF';
	}
	if (util_ini_get_bytes('post_max_size') < 8*1024*1024) {
		$result[] = 'WARNING: PHP value "post_max_size" is low, recommended is at least 8M';
	}
	if (util_ini_get_bytes('upload_max_filesize') < 8*1024*1024) {
		$result[] = 'WARNING: PHP value "upload_max_filesize" is low, recommended is at least 8M';
	}
	if (!function_exists("pg_pconnect")) {
		$result[] = 'ERROR: Missing Postgresql support in PHP, please install/compile php-pg.';
	}
	return $result;
}

function site_admin_header($params, $required_perm = 'forge_admin') {
	session_require_global_perm ($required_perm);

	if ($msg = check_system()) {
		$GLOBALS['warning_msg'] = join('<br/> ', $msg);
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
