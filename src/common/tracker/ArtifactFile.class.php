<?php
/**
 * FusionForge trackers
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
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

/**
*       Factory method which creates an ArtifactFile from an artifactFile ID
*
*       @param int      The artifactFile ID
*       @param array    The result array, if it's passed in
*       @return object  Artifact object
*/
function &artifactfile_get_object($artifact_file_id,$data=false) {
	global $ARTIFACTFILE_OBJ;
	if (!isset($ARTIFACTFILE_OBJ["_".$artifact_file_id."_"])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res = db_query_params ('SELECT * FROM artifact_file_user_vw WHERE id=$1',
						array ($artifact_file_id)) ;
			if (db_numrows($res) <1 ) {
				$ARTIFACTFILE_OBJ["_".$artifact_file_id."_"]=false;
				return false;
			}
			$data = db_fetch_array($res);
		}
		$Artifact =& artifact_get_object($data["artifact_id"]);
		$ARTIFACTFILE_OBJ["_".$artifact_file_id."_"]= new ArtifactFile($Artifact,$data);
	}
	return $ARTIFACTFILE_OBJ["_".$artifact_file_id."_"];
}


class ArtifactFile extends Error {

	/** 
	 * The artifact object.
	 *
	 * @var		object	$Artifact.
	 */
	var $Artifact; //object

	/**
	 * Array of file data
	 *
	 * @var		array	$data_array
	 */
	var $data_array;

	/**
	 *  ArtifactFile - constructor.
	 *
	 *	@param	object	The Artifact object.
	 *  @param	array	(all fields from artifact_file_user_vw) OR id from database.
	 *  @return	boolean	success.
	 */
	function ArtifactFile(&$Artifact, $data=false) {
		$this->Error(); 

		//was Artifact legit?
		if (!$Artifact || !is_object($Artifact)) {
			$this->setError('ArtifactFile: No Valid Artifact');
			return false;
		}
		//did ArtifactType have an error?
		if ($Artifact->isError()) {
			$this->setError('ArtifactFile: '.$Artifact->getErrorMessage());
			return false;
		}
		$this->Artifact =& $Artifact;

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
	 *	@param	string	Filename of the item.
	 *	@param	string	Item filetype.
	 *	@param	string	Item filesize.
	 *	@param	binary	Binary item data.
	 *	@param	string	Item description.
	 *	@param	array	Array of data to change submitter and time of submit like: array('user' => 127, 'time' => 1234556789)
	 *  	@return id on success / false on failure.
	 */
	function create($filename, $filetype, $filesize, $bin_data, $description='None', $importData = array()) {
		// Some browsers don't supply mime type if they don't know it
		if (!$filetype) {
			// Let's be on safe side?
			$filetype = 'application/octet-stream';
		}

		//
		//	data validation
		//
		if (!$filename || !$filetype || !$filesize || !$bin_data) {
			//echo '<p>|'.$filename.'|'.$filetype.'|'.$filesize.'|'.$bin_data.'|';
			$this->setError(_('ArtifactFile: File name, type, size, and data are required'));
			return false;
		}

		if (array_key_exists('user', $importData)){
			$userid = $importData['user'];
		} else {
			if (session_loggedin()) {
				$userid=user_getid();
			} else {
				$userid=100;
			}
		}

		if (array_key_exists('time',$importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}
		

		// If $filetype is "text/plain", $bin_data convert UTF-8 encoding.
		if (strcasecmp($filetype,"text/plain") === 0 &&
		    function_exists('mb_convert_encoding') &&
		    function_exists('mb_detect_encoding')) {
			$bin_data = mb_convert_encoding($bin_data,'UTF-8',mb_detect_encoding($bin_data, "auto"));
			$filesize = strlen($bin_data);
		}

		db_begin();

		$res = db_query_params ('INSERT INTO artifact_file
			(artifact_id,description,bin_data,filename,filesize,filetype,adddate,submitted_by)
			VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					array ($this->Artifact->getID(),
					       $description,
					       base64_encode($bin_data),
					       $filename,
					       $filesize,
					       $filetype,
					       $time,
					       $userid)) ; 

		$id=db_insertid($res,'artifact_file','id');

		if (!$res || !$id) {
			db_rollback();
			$this->setError('ArtifactFile: '.db_error());
			return false;
		} else {
			db_commit();

			//
			//	Now set up our internal data structures
			//
			if (!$this->fetchData($id)) {
				return false;
			}

			// If time is set, no need to add to history, will be done in batch
			if (!array_key_exists('time', $importData)){
				$this->Artifact->addHistory('File Added',$id.': '.$filename);
			}
			$this->Artifact->UpdateLastModifiedDate();
			$this->clearError();
			return $id;
		}
	}

	/**
	 *	delete - delete this artifact file from the db.
	 *
	 *	@return	boolean	success.
	 */
	function delete() {
		if (!forge_check_perm ('tracker', $this->Artifact->ArtifactType->getID(), 'tech')) {
			$this->setPermissionDeniedError();
			return false;
		}
		$res = db_query_params ('DELETE FROM artifact_file WHERE id=$1',
					array ($this->getID())) ;
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('ArtifactFile: Unable to Delete');
			return false;
		} else {
			$this->Artifact->addHistory('File Deleted',$this->getID().': '.$this->getName());
			return true;
		}
	}

	/**
	 *	fetchData - re-fetch the data for this ArtifactFile from the database.
	 *
	 *	@param	int	The file_id.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM artifact_file_user_vw WHERE id=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactFile: Invalid ArtifactFile ID');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getArtifact - get the Artifact Object this ArtifactFile is associated with.
	 *
	 *	@return object	Artifact.
	 */
	function &getArtifact() {
		return $this->Artifact;
	}
	
	/**
	 *	getID - get this ArtifactFile's ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['id'];
	}

	/**
	 *	getName - get the filename.
	 *
	 *	@return string filename.
	 */
	function getName() {
		return $this->data_array['filename'];
	}

	/**
	 *	getType - get the type.
	 *
	 *	@return string type.
	 */
	function getType() {
		return $this->data_array['filetype'];
	}

	/**
	 *	getData - get the binary data from the db.
	 *
	 *	@return binary.
	 */
	function getData() {
		return base64_decode($this->data_array['bin_data']);
	}

	/**
	 *	getSize - get the size.
	 *
	 *	@return int size.
	 */
	function getSize() {
		return $this->data_array['filesize'];
	}

	/**
	 *	getDescription - get the description.
	 *
	 *	@return string description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 *	getDate - get the date file was added.
	 *
	 *	@return int unix time.
	 */
	function getDate() {
		return $this->data_array['adddate'];
	}

	/**
	 *	getSubmittedBy - get the user_id of the submitter.
	 *
	 *	@return int user_id.
	 */
	function getSubmittedBy() {
		return $this->data_array['submitted_by'];
	}

	/**
	 *	getSubmittedRealName - get the real name of the submitter.
	 *
	 *	@return	string	name.
	 */
	function getSubmittedRealName() {
		return $this->data_array['realname'];
	}

	/**
	 *	getSubmittedUnixName - get the unix name of the submitter.
	 *
	 *	@return	string	unixname.
	 */
	function getSubmittedUnixName() {
		return $this->data_array['user_name'];
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
