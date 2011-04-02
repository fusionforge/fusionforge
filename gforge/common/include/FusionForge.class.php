<?php   
/**
 * FusionForge top-level information
 *
 * Copyright 2002, GForge, LLC
 * Copyright 2009, Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';
class FusionForge extends Error {

	var $software_name ;
	var $software_version ;

	/**
	 *	FusionForge - FusionForge object constructor
	 */
	function FusionForge() {
		$this->Error();

		$this->software_name = 'FusionForge' ;
		$this->software_version = '5.0.3' ;

		return true;
	}

	function getNumberOfPublicHostedProjects() {
		$res = db_query_params ('SELECT count(*) AS count FROM groups WHERE status=$1 AND is_public=1',
				      array ('A'));	
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Unable to get hosted project count: '.db_error());
			return false;
		}
		return $this->parseCount($res);
	}

	function getNumberOfHostedProjects() {
		$res = db_query_params ('SELECT count(*) AS count FROM groups WHERE status=$1',
					array ('A'));	
		if (!$res || db_numrows($res) < 1) {
			$this->setError('Unable to get hosted project count: '.db_error());
			return false;
		}
		return $this->parseCount($res);
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
		$res = db_query_params ('SELECT unix_group_name FROM groups WHERE status=$1 AND is_public=1 ORDER BY unix_group_name',
					array ('A'));
		if (!$res) {
			$this->setError('Unable to get list of public projects: '.db_error());
			return false;
		}
		$rows=db_numrows($res);
		$result = array();
		for ($i=0; $i<$rows; $i++) {
			$result[$i] = db_result($res, $i, 'unix_group_name');
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
