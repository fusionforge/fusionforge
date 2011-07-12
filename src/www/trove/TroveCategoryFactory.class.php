<?php
/**
 * Trove Browsing Facility
 *
 * Copyright 2004 Guillaume Smet / Open Wide
 * http://fusionforge.org/
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

require_once('TroveCategory.class.php');

class TroveCategoryFactory {

	/**
	 *	getRootCategories - get an array of root TroveCategory objects
	 *
	 * @return	array	The array of TroveCategory objects.
	 */
	function & getRootCategories() {
		$result = db_query_params("
			SELECT *
			FROM trove_cat
			WHERE parent = 0
			AND trove_cat_id != 0
			ORDER BY fullname
		", array());

		if(!$result) {
			$this->setError();
			return false;
		} else {
			$rootCategories = array();
			while ($array = db_fetch_array($result)) {
				$rootCategories[] = new TroveCategory($array['trove_cat_id'], $array);
			}
			return $rootCategories;
		}
	}

	function & getCategories($ids) {
		$result = db_query_params("
			SELECT *
			FROM trove_cat
			WHERE trove_cat_id = ANY ($1)
			ORDER BY fullname
		", array(db_int_array_to_any_clause($ids)));
		if(!$result) {
			$this->setError();
			return false;
		} else {
			$categories = array();
			while ($array = db_fetch_array($result)) {
				$categories[] = new TroveCategory($array['trove_cat_id'], $array);
			}
			return $categories;
		}
	}
}

?>
