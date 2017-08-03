<?php
/**
 * Base class for data access objects
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */
class DataAccessObject {
	 var $da;

	/**
	 * @param DataAccess $da Instance of the DataAccess class
	 */
	function __construct(&$da) {
		$this->table_name = 'CLASSNAME_MUST_BE_DEFINE_FOR_EACH_CLASS';
		$this->da=$da;
	}

	//! An accessor
	/**
	 * For SELECT queries
	 *
	 * @param string $sql    The query string
	 * @param array  $params The arguments
	 * @return mixed Either false if error or object DataAccessResult
	 */
	function &retrieve($sql,$params) {
		$result = new DataAccessResult(db_query_params($sql,$params));
		return $result;
	}

	//! An accessor
	/**
	 * For INSERT, UPDATE and DELETE queries
	 * @param string $sql the query string
	 * @param array  $params The arguments
	 * @return bool true if success
	 */
	function update($sql,$params) {
		$result = db_query_params($sql,$params);
		return $result;
	}
}
