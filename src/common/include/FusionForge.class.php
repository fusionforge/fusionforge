<?php   
/**
 * FusionForge top-level information
 *
 * Copyright 2002, GForge, LLC
 * Copyright 2009-2011, Roland Mas
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

require_once $gfcommon.'include/Error.class.php';
include_once $gfcommon.'pkginfo.inc.php';

class FusionForge extends Error {

	var $software_name ;
	var $software_type ;
	var $software_version ;

	/**
	 *	FusionForge - FusionForge object constructor
	 */
	function FusionForge() {
		global $forge_pkg_name, $forge_pkg_version;

		$this->Error();

		$this->software_name = 'FusionForge' ;
		$this->software_version = '5.1.50' ;

		if (isset($forge_pkg_name) && isset($forge_pkg_version)) {
			$this->software_name = $forge_pkg_name;
			$this->software_version = $forge_pkg_version;
		}

		$this->software_type = $this->software_name;
		if (isset($forge_pkg_type)) {
			$this->software_type = $forge_pkg_type;
		}

		if (isset($forge_pkg_name) && isset($forge_pkg_version)) {
			$this->software_name = $forge_pkg_name;
			$this->software_version = $forge_pkg_version;
		}

		$this->software_type = $this->software_name;
		if (isset($forge_pkg_type)) {
			$this->software_type = $forge_pkg_type;
		}

		return true;
	}

	function getNumberOfPublicHostedProjects() {
		$res = db_query_params ('SELECT group_id FROM groups WHERE status=$1',
				      array ('A'));	
		if (!$res) {
			$this->setError('Unable to get hosted project count: '.db_error());
			return false;
		}
		$count = 0;
		$ra = RoleAnonymous::getInstance() ;
		while ($row = db_fetch_array($res)) {
			if ($ra->hasPermission('project_read', $row['group_id'])) {
				$count++;
			}
		}
		return $count;
	}

	function getNumberOfHostedProjects() {
		$res = db_query_params ('SELECT group_id FROM groups WHERE status=$1',
					array ('A'));	
		if (!$res) {
			$this->setError('Unable to get hosted project count: '.db_error());
			return false;
		}
		$count = 0;
		$ra = RoleAnonymous::getInstance() ;
		while ($row = db_fetch_array($res)) {
			if ($ra->hasPermission('project_read', $row['group_id'])) {
				$count++;
			}
		}
		return $count;
	}

	function getNumberOfActiveUsers() {
		$res = db_query_params ('SELECT count(*) AS count FROM users WHERE status=$1 and user_id != 100',
					array ('A'));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Unable to get user count: '.db_error());
			return false;
		}
		return $this->parseCount($res);
	}


	function getPublicProjectNames() {
		$res = db_query_params ('SELECT unix_group_name, group_id FROM groups WHERE status=$1 ORDER BY unix_group_name',
					array ('A'));
		if (!$res) {
			$this->setError('Unable to get list of public projects: '.db_error());
			return false;
		}
		$result = array();
		$ra = RoleAnonymous::getInstance() ;
		while ($row = db_fetch_array($res)) {
			if ($ra->hasPermission('project_read', $row['group_id'])) {
				$result[] = $row['unix_group_name'];
			}
		}
		return $result;
	}
	
	function parseCount($res) {
		$row_count = db_fetch_array($res);
		return $row_count['count'];
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
