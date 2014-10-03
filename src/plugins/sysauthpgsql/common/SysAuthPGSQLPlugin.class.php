<?php
/**
 * FusionForge sysauthpgsql plugin
 *
 * Copyright 2012, Roland Mas
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

class SysAuthPGSQLPlugin extends SysAuthPlugin {
	function SysAuthPGSQLPlugin () {
		$this->SysAuthPlugin() ;
		$this->name = "sysauthpgsql" ;
		$this->text = "System authentication via PostgreSQL";
	}

	function user_create($params) {
		error_log ("user_create" . print_r($params, true));
	}

	function user_update($params) {
		error_log ("user_update" . print_r($params, true));
	}

	function user_delete($params) {
		error_log ("user_delete" . print_r($params, true));
	}

	function group_create($params) {
		error_log ("group_create" . print_r($params, true));
	}

	function group_update($params) {
		error_log ("group_update" . print_r($params, true));
	}

	function group_delete($params) {
		error_log ("group_delete" . print_r($params, true));
	}

	function group_update_members($params) {
		error_log ("group_update_members" . print_r($params, true));
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
