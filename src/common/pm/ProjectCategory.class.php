<?php
/**
 * FusionForge project manager
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
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

class ProjectCategory extends Error {

	/**
	 * The ProjectGroup object.
	 *
	 * @var		object	$ProjectGroup.
	 */
	var $ProjectGroup; //object

	/**
	 * Array of data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;

	/**
	 *  ProjectCategory - constructor.
	 *
	 *	@param	object	ProjectGroup object.
	 *  @param	array	(all fields from project_category) OR category_id from database.
	 *  @return	boolean	success.
	 */
	function ProjectCategory(&$ProjectGroup, $data=false) {
		$this->Error();

		//was ProjectGroup legit?
		if (!$ProjectGroup || !is_object($ProjectGroup)) {
			$this->setError('ProjectCategory: No Valid ProjectGroup');
			return false;
		}
		//did ProjectGroup have an error?
		if ($ProjectGroup->isError()) {
			$this->setError('ProjectCategory: '.$ProjectGroup->getErrorMessage());
			return false;
		}
		$this->ProjectGroup =& $ProjectGroup;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
//
//	should verify group_project_id
//
				return true;
			} else {
				if (!$this->fetchData($data)) {
					return false;
				} else {
					return true;
				}
			}
		}
	}

	/**
	 *	create - create a new item in the database.
	 *
	 *	@param	string	Item name.
	 *  @return	boolean success.
	 */
	function create($name) {
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('ProjectCategory: name and assignee are Required'));
			return false;
		}

		if (!forge_check_perm ('pm_admin', $this->ProjectGroup->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		$result = db_query_params ('INSERT INTO project_category (group_project_id,category_name) VALUES ($1,$2)',
					   array ($this->ProjectGroup->getID(),
						  htmlspecialchars($name))) ;

		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}

/*
			//
			//	Now set up our internal data structures
			//
			if (!$this->fetchData($id)) {
				return false;
			}
*/
	}

	/**
	 *	fetchData() - re-fetch the data for this ProjectCategory from the database.
	 *
	 *	@param	int		ID of the category.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM project_category WHERE category_id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ProjectCategory: Invalid ProjectCategory ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getProjectGroup - get the ProjectGroup Object this ProjectCategory is associated with.
	 *
	 *	@return	object	ProjectGroup.
	 */
	function &getProjectGroup() {
		return $this->ProjectGroup;
	}

	/**
	 *	getID - get this ProjectCategory's ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['category_id'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	string	The name.
	 */
	function getName() {
		return $this->data_array['category_name'];
	}

	/**
	 *  update - update a ProjectCategory.
	 *
	 *  @param	string	Name of the category.
	 *  @return	boolean success.
	 */
	function update($name) {
		if (!forge_check_perm ('pm_admin', $this->ProjectGroup->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (!$name) {
			$this->setMissingParamsError();
			return false;
		}
		$result = db_query_params ('UPDATE project_category SET category_name=$1
			WHERE category_id=$2 AND group_project_id=$3',
					   array (htmlspecialchars($name),
						  $this->getID(),
						  $this->ProjectGroup->getID())) ;
		if ($result && db_affected_rows($result) > 0) {
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
