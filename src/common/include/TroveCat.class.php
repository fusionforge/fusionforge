<?php
/**
 * FusionForge trove categories
 *
 * Copyright 2013, Olivier Berger and Institut Mines-Telecom
 * Copyright 2013, Roland Mas
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

/**
 * TODO: FusionForge Trove Categories
 *
 */
class TroveCat extends Error {

	var $data_array;

	/**
	 * TroveCat() - CONSTRUCTOR.
	 *
	 * @param	bool|int	$cat_id	The cat_id.
	 */
	function TroveCat($cat_id = false) {

		if (!$cat_id) {
			//setting up an empty object
			//probably going to call create()
			return true;
		}
		return $this->fetchData($cat_id);
	}

	/**
	 * fetchData - May need to refresh database fields.
	 *
	 * If an update occurred and you need to access the updated info.
	 *
	 * @return	bool	success;
	 */
	function fetchData($cat_id, &$res = false) {

		unset($this->data_array);

		if(! $res) {
			$res = db_query_params('SELECT * FROM trove_cat WHERE trove_cat_id=$1',
					array ($cat_id)) ;
			if (!$res || db_numrows($res) < 1) {
				$this->setError('TroveCat::fetchData()::'.db_error());
				return false;
			}
		}
		$row =  db_fetch_array($res);
		if($row) {
			$this->data_array = $row;
			return true;
		}
		else {
			return false;
		}
	}

	function getId() {
		return $this->data_array['trove_cat_id'];
	}

	function getShortName() {
		return $this->data_array['shortname'];
	}

	function getFullName() {
		return $this->data_array['fullname'];
	}

	function getParentId() {
		return $this->data_array['parent'];
	}

	function getRootCatId() {
		return $this->data_array['root_parent'];
	}

	function getIdsFullPath() {
		return $this->data_array['fullpath_ids'];

	}

	function getDescription() {
		return $this->data_array['description'];

	}

	function listSubTree() {
		return TroveCat::getSubtree($this->data_array['trove_cat_id']);
	}

	static function getAllRoots() {
		$rootcats = array();

		$res = db_query_params ('
		SELECT *
		FROM trove_cat
		WHERE parent=0
		AND trove_cat_id!=0',
				array());

		do {
			$trovecat = new TroveCat();
			$fetched = $trovecat->fetchData(false, $res);
			if ($fetched) {
				$rootcats[] = $trovecat;
			}
		} while ($fetched);

		return $rootcats;
	}

	static function getSubtree($root_cat_id) {
		$subcats = array();

		$res = db_query_params('
			SELECT *
			FROM trove_cat
			WHERE root_parent=$1
			ORDER BY fullpath',
				array($root_cat_id));

		do {
			$trovecat = new TroveCat();
			$fetched = $trovecat->fetchData(false, $res);
			if ($fetched) {
				$subcats[] = $trovecat;
			}
		} while ($fetched);

		return $subcats;
	}

	static function getProjectCats($group_id) {
		$cats = array();

		$res = db_query_params ('
		SELECT trove_cat.trove_cat_id AS trove_cat_id
		FROM trove_cat,trove_group_link
		WHERE trove_cat.trove_cat_id=trove_group_link.trove_cat_id
		AND trove_group_link.group_id=$1
		ORDER BY trove_cat.fullpath',
			array($group_id));

		$catids = array();
		while($row = db_fetch_array($res)) {
			$catids[] = $row['trove_cat_id'];
		}

		foreach($catids as $catid) {
			$cats[] = new TroveCat($catid);
		}
		return $cats;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
