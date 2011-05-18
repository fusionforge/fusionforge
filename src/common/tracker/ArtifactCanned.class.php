<?php
/**
 * FusionForge trackers
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge, LLC
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

class ArtifactCanned extends Error {

	/** 
	 * The artifact type object.
	 *
	 * @var		object	$ArtifactType.
	 */
	var $ArtifactType; 

	/**
	 * Array of artifact data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;

	/**
	 *  ArtifactCanned - constructor.
	 *
	 *	@param	object	The Artifact Type object.
	 *  @param	array	(all fields from artifact_file_user_vw) OR id from database.
	 *  @return	boolean	success.
	 */
	function ArtifactCanned(&$ArtifactType, $data=false) {
		$this->Error(); 

		//was ArtifactType legit?
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('ArtifactCanned: No Valid ArtifactType');
			return false;
		}
		//did ArtifactType have an error?
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactCanned: '.$Artifact->getErrorMessage());
			return false;
		}
		$this->ArtifactType =& $ArtifactType;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
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
	 *	@param	string	The item title.
	 *	@param	string	The item body.
	 *  @return id on success / false on failure.
	 */
	function create($title, $body) {
		//
		//	data validation
		//
		if (!$title || !$body) {
			$this->setError(_('ArtifactCanned: name and assignee are Required'));
			return false;
		}
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		$result = db_query_params ('INSERT INTO artifact_canned_responses (group_artifact_id,title,body) VALUES ($1,$2,$3)',
					   array ($this->ArtifactType->getID(),
						  htmlspecialchars($title),
						  htmlspecialchars($body))) ;

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
	 *	fetchData - re-fetch the data for this ArtifactCanned from the database.
	 *
	 *	@param int	The ID number.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM artifact_canned_responses WHERE id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactCanned: Invalid ArtifactCanned ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getArtifactType - get the ArtifactType Object this ArtifactCanned message is associated with.
	 *
	 *	@return ArtifactType.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}
	
	/**
	 *	getID - get this ArtifactCanned message's ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['id'];
	}

	/**
	 *	getTitle - get the title.
	 *
	 *	@return	string	The title.
	 */
	function getTitle() {
		return $this->data_array['title'];
	}

	/**
	 *	getBody - get the body of this message.
	 *
	 *	@return	string	The message body.
	 */
	function getBody() {
		return $this->data_array['body'];
	}

	/**
	 *  update - update an ArtifactCanned message.
	 *
	 *  @param	string	Title of the message.
	 *  @param	string	Body of the message.
	 *  @return	boolean	success.
	 */
	function update($title,$body) {
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}   
		if (!$title || !$body) {
			$this->setMissingParamsError();
			return false;
		}   

		$result = db_query_params ('UPDATE artifact_canned_responses
			SET title=$1,body=$2
			WHERE group_artifact_id=$3 AND id=$4',
					   array (htmlspecialchars($title),
						  htmlspecialchars($body),
						  $this->ArtifactType->getID(),
						  $this->getID())) ;

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
