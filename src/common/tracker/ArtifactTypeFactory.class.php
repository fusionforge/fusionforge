<?php
/**
 * FusionForge trackers
 *
 * Copyright 2002, GForge, LLC
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
require_once $gfcommon.'tracker/ArtifactType.class.php';

class ArtifactTypeFactory extends Error {

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The ArtifactTypes array.
	 *
	 * @var	 array	ArtifactTypes.
	 */
	var $ArtifactTypes;

	/**
	 * The data type (DAO)
	 *
  	 * @var 	string dataType
	 */
	var $dataType;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this ArtifactTypeFactory is associated
	 *	@return	boolean	success.
	 */
	function ArtifactTypeFactory(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError('ArtifactTypeFactory:: No Valid Group Object');
			return false;
		}
		if ($Group->isError()) {
			$this->setError('ArtifactTypeFactory:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 *	getGroup - get the Group object this ArtifactType is associated with.
	 *
	 *	@return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getAllArtifactTypeIds - return a list of tracker ids.
	 *
	 *	@return	array	The array of ArtifactType objects.
	 */
	function &getAllArtifactTypeIds() {
		$result = array () ;
		$res = db_query_params ('SELECT * FROM artifact_group_list_vw
			WHERE group_id=$1
			ORDER BY group_artifact_id ASC',
					array ($this->Group->getID())) ;
		if (!$res) {
			return $result ;
		}
		while ($arr = db_fetch_array($res)) {
			$result[] = $arr['group_artifact_id'] ;
		}
		return $result ;
	}

	/**
	 *	getArtifactTypes - return an array of ArtifactType objects.
	 *
	 *	@return	array	The array of ArtifactType objects.
	 */
	function &getArtifactTypes() {
		if ($this->ArtifactTypes) {
			return $this->ArtifactTypes;
		}

		$this->ArtifactTypes = array () ;
		$ids = $this->getAllArtifactTypeIds() ;

		foreach ($ids as $id) {
			if (forge_check_perm ('tracker', $id, 'read')) {
				$artifactType = new ArtifactType($this->Group, $id);
				if($artifactType->isError()) {
					$this->setError($artifactType->getErrorMessage());
				} else {
					$this->ArtifactTypes[] = $artifactType;
				}
			}
		}
		return $this->ArtifactTypes;
	}

	/**
	 * getPublicFlag - a utility method to load up the current user's permissions
 	 *
	 * @return 	string 	The public_flag field to plug into a SQL string
	 */
	function &getPublicFlag() {
		return $public_flag;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
