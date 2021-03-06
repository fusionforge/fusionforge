<?php
/**
 * FusionForge trackers
 *
 * Copyright 2004, GForge, LLC
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

require_once $gfcommon.'include/FFError.class.php';

class ArtifactMessage extends FFError {

	/**
	 * The artifact object.
	 *
	 * @var	object	$Artifact.
	 */
	var $Artifact; //object

	/**
	 * Array of artifact data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * @param	object		$Artifact	Artifact object.
	 * @param	array|bool	$data		(all fields from artifact_history_user_vw) OR id from database.
	 */
	function __construct(&$Artifact, $data = false) {
		parent::__construct();

		//was Artifact legit?
		if (!$Artifact || !is_object($Artifact)) {
			$this->setError(_('Invalid Artifact'));
			return;
		}
		//did Artifact have an error?
		if ($Artifact->isError()) {
			$this->setError('ArtifactMessage: '.$Artifact->getErrorMessage());
			return;
		}
		$this->Artifact =& $Artifact;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
			} else {
				$this->fetchData($data);
			}
		}
	}

	/**
	 * create - create a new item in the database.
	 *
	 * @param	string		$body	Body.
	 * @param	string|bool	$by	Email of submitter (obsolete?).
	 * @param	array	$importData
	 * @return	int|bool	id on success / false on failure.
	 */
	function create($body, $by = false, $importData = array()) {
		if (!$body) {
			$this->setMissingParamsError();
			return false;
		}

		if(array_key_exists('user', $importData)) {
			$user_id = $importData['user'];
			$user = user_get_object($user_id);
			if (!$user || !is_object($user)) {
				$this->setError('Error: Logged In User But Could Not Get User Object');
				return false;
			}
			$by = $user->getEmail();
		} else {
			if (session_loggedin()) {
				$user_id = user_getid();
				$user = user_get_object($user_id);
				if (!$user || !is_object($user)) {
					$this->setError('Error: Logged In User But Could Not Get User Object');
					return false;
				}
				//	we'll store this email even though it will likely never be used -
				//	since we have their correct user_id, we can join the USERS table to get email
				$by = $user->getEmail();
			} else {
				$user_id = 100;
				if (!$by || !validate_email($by)) {
					$this->setMissingParamsError();
					return false;
				}
			}
		}
		if(array_key_exists('time', $importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}

		$res = db_query_params ('INSERT INTO artifact_message (artifact_id, submitted_by, from_email, adddate, body)
			VALUES ($1, $2, $3, $4, $5)',
					array ($this->Artifact->getID(),
					       $user_id,
					       $by,
					       $time,
					       htmlspecialchars($body))) ;
		if (!$res) {
			$this->setError(db_error());
			return false;
		} else {
			$id = db_insertid($res,'artifact_message','id');
		}

		//
		//	Now set up our internal data structures
		//
		if (!$this->fetchData($id)) {
			return false;
		}
		return $id;
	}

	/**
	 * fetchData - re-fetch the data for this ArtifactMessage from the database.
	 *
	 * @param	int	$id	ID of the category.
	 * @return	bool	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM artifact_message_user_vw WHERE id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Invalid ArtifactMessage ID'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getArtifact - get the Artifact Object this ArtifactMessage is associated with.
	 *
	 * @return	object	Artifact.
	 */
	function &getArtifact() {
		return $this->Artifact;
	}

	/**
	 * getID - get this ArtifactMessage's ID.
	 *
	 * @return	int	The id #.
	 */
	function getID() {
		return $this->data_array['id'];
	}

	/**
	 * getBody - get the message body.
	 *
	 * @return	string	The message body.
	 */
	function getBody() {
		return $this->data_array['body'];
	}

	/**
	 * getAddDate - get the date this message was added.
	 *
	 * @return	int	adddate.
	 */
	function getAddDate() {
		return $this->data_array['adddate'];
	}

	/**
	 * getUserID - get the ID of the person who posted this.
	 *
	 * @return	int	user_id.
	 */
	function getUserID() {
		return $this->data_array['user_id'];
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
