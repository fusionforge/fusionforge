<?php
/**
 * FusionForge top-level information
 *
 * Copyright 2002, GForge, LLC
 * Copyright 2009-2011, Roland Mas
 * Copyright 2015, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/FFError.class.php';

class FusionForge extends FFError {

	var $software_name = "FusionForge";
	var $software_version;

	public static $instance;

	/**
	 *	FusionForge - FusionForge object constructor
	 */
	function __construct() {
		parent::__construct();

		$pkg = dirname(dirname(__FILE__)).'/pkginfo.inc.php';
		if (file_exists($pkg)) {
			include $pkg;
		}

		if (isset($forge_pkg_version)) {
			$this->software_version = $forge_pkg_version;
		} else {
			$this->software_version = trim(file_get_contents(dirname(__FILE__).'/../../VERSION'));
		}

		self::$instance = $this;
		return true;
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * List full number of hosted projects, public and private
	 *
	 * @param	array	$params		array of columns and values to filter query: $params['status'] = 'A' ...
	 * @param	string	$extended_qpa	string of SQL to be part of the QPA query
	 */
	function getNumberOfProjects($params = array(), $extended_qpa = null) {
		$qpa = db_construct_qpa(false, 'SELECT count(*) as count FROM groups');
		if (count($params) > 1) {
			$qpa = db_construct_qpa($qpa, ' WHERE ');
			$i = 0;
			foreach ($params as $key => $value) {
				$i++;
				$qpa = db_construct_qpa($qpa, $key.' = $1 ', array($value));
				if ($i < count($params)) {
					$qpa = db_construct_qpa($qpa, ' AND ');
				}
			}
		}
		if (strlen($extended_qpa) > 1) {
			if (!strpos($qpa[0], 'WHERE')) {
				$qpa = db_construct_qpa($qpa, ' WHERE ');
			}
			if (strpos($qpa[0], 'AND')) {
				$qpa = db_construct_qpa($qpa, ' AND ');
			}
			$qpa = db_construct_qpa($qpa, $extended_qpa);
		}
		$res = db_query_qpa($qpa);
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Unable to get hosted project count: '.db_error());
			return false;
		}
		return $this->parseCount($res);
	}

	function getNumberOfActiveProjects() {
		return $this->getNumberOfProjects(array('status' => 'A'));
	}

	function getNumberOfDeletedProjects() {
		return $this->getNumberOfProjects(array('status' => 'D'));
	}

	function getNumberOfSuspendedProjects() {
		return $this->getNumberOfProjects(array('status' => 'S'));
	}

	function getNumberOfActiveUsers() {
		return $this->getNumberOfUsers('A');
	}

	function getNumberOfDeletedUsers() {
		return $this->getNumberOfUsers('D');
	}

	function getNumberOfSuspendedUsers() {
		return $this->getNumberOfUsers('S');
	}

	function getNumberOfProjectsFilteredByGroupName($filter) {
		$res = db_query_params('SELECT count(*) as count FROM groups WHERE lower(group_name) LIKE $1', array(strtolower("$filter%")));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Unable to get project count: '.db_error());
			return false;
		}
		return $this->parseCount($res);
	}

	function getNumberOfUsersUsingAPlugin($plugin_name) {
		$res = db_query_params ('SELECT count(u.user_id) AS count FROM plugins p, user_plugin up, users u WHERE p.plugin_name = $1 and up.user_id = u.user_id and p.plugin_id = up.plugin_id and users.user_id != 100',
					array($plugin_name));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Unable to get user count: '.db_error());
			return false;
		}
		return $this->parseCount($res);
	}

	function getNumberOfUsers($status) {
		$qpa = db_construct_qpa(false, 'SELECT count(*) AS count FROM users WHERE user_id != 100');
		if ($status) {
			$qpa = db_construct_qpa($qpa, ' and status = $1', array($status));
		}
		$res = db_query_qpa($qpa);
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

	function getNumberOfProjectsUsingTags($params = array(), $extended_qpa = null) {
		$qpa = db_construct_qpa(false, 'SELECT count(*) as count FROM groups, project_tags WHERE groups.group_id = project_tags.group_id ');
		if (count($params) > 1) {
			$qpa = db_construct_qpa($qpa, ' AND ');
			$i = 0;
			foreach ($params as $key => $value) {
				$i++;
				$qpa = db_construct_qpa($qpa, $key.' = $1 ', array($value));
				if ($i < count($params)) {
					$qpa = db_construct_qpa($qpa, ' AND ');
				}
			}
		}
		if (strlen($extended_qpa) > 1) {
			if (strpos($qpa[0], 'AND')) {
				$qpa = db_construct_qpa($qpa, ' AND ');
			}
			$qpa = db_construct_qpa($qpa, $extended_qpa);
		}
		$res = db_query_qpa($qpa);
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Unable to get hosted project count: '.db_error());
			return false;
		}
		return $this->parseCount($res);
	}


	function parseCount($res) {
		$row_count = db_fetch_array($res);
		return (int)$row_count['count'];
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
