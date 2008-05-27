<?php
/**
 * ArtifactTypes.class.php - Class to handle artifact types
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';

class ArtifactTypes extends Error {

	/** 
	 * The artifact type object.
	 *
	 * @var		object	$ArtifactType.
	 */
	var $Group; //group object

	/**
	 * Array of artifactTypes data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;

	/**
	 *	ArtifactTypes - constructor.
	 *
	 *	@param	object	The Group object.
	 *	@return	boolean	success.
	 */
	function ArtifactTypes(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError('No Valid Group Object');
			return false;
		}
		if ($Group->isError()) {
			$this->setError('ArtifactType: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;
		return true;
	}

	/**
	 *	createTrackers - creates all the standard trackers for a given Group.
	 *
	 *	@return	boolean	success.
	 */
	function createTrackers() {

		// first, check if trackers already exist
		$res=db_query("SELECT * FROM artifact_group_list 
			WHERE group_id='".$this->Group->getID()."' AND datatype > 0");
		if (db_numrows($res) > 0) {
			return true;
		}

		include $GLOBALS['gfcommon'].'tracker/artifact_type_definitions.php';
		db_begin();
		foreach ($trackers as $trk) {
			$at = new ArtifactType($this->Group);
			if (!$at || !is_object($at)) {
				db_rollback();
				$this->setError('Error Getting Tracker Object');
				return false;
			}
			//
			//	Create a tracker
			//
			if (!$at->create(addslashes($trk[0]), addslashes($trk[1]), $trk[2], $trk[3], $trk[4], $trk[5], $trk[6], $trk[7], $trk[8], $trk[9], $trk[10])) {
				db_rollback();
				$this->setError('Error Creating Tracker: '.$at->getErrorMessage());
				return false;
			} else {
				//
				//	Create each field in the tracker
				//
				foreach ($trk[11] AS $fld) {
					$aef = new ArtifactExtraField($at);
//print($fld[0])."***|";
					if (!$aef->create(addslashes($fld[0]), $fld[1], $fld[2], $fld[3], $fld[4])) {
						db_rollback();
						$this->setError('Error Creating Extra Field: '.$aef->getErrorMessage());
						return false;
					} else {
						//
						//	create each element in the field
						//
						foreach ($fld[5] AS $el) {
//print($el)."**";

							$aefe = new ArtifactExtraFieldElement($aef);
						/*	 Allow us to provide a list as an element
							 value - in doing so, we can provide a
							 status field value for people wanting to
							 set up custom statuses. The first element
							 of any given array is the name, the second
							 is the status_id (0, 1 or 2)*/
							$el_name = $el;
							$el_status = 0;
							if (is_array($el) && $fld[1] == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
								$el_name = $el[0];
								$el_status = $el[1];
							}
							if (!$aefe->create(addslashes($el_name),$el_status)) {
								db_rollback();
								$this->setError('Error Creating Extra Field Element: '.$aefe->getErrorMessage());
								return false;
							}
						}
					}
				}
			}

		}
		db_commit();
		return true;
	}

}

?>
