<?php
/**
 * FusionForge trackers
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
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

require_once $gfcommon.'tracker/ArtifactStorage.class.php';
require_once $gfcommon.'include/Error.class.php';

/**
* Factory method which creates an ArtifactFile from an artifactFile ID
*
* @param	int		$artifact_file_id	The artifactFile ID
* @param	array|bool	$data			The result array, if it's passed in
* @return	Artifact	object
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
	 * @var	object	$Artifact.
	 */
	var $Artifact; //object

	/**
	 * Array of file data
	 *
	 * @var	array	$data_array
	 */
	var $data_array;

	/**
	 *  __construct - ArtifactFile constructor.
	 *
	 * @param	Artifact	$Artifact	The Artifact object.
	 * @param	array|bool	$data		(all fields from artifact_file_user_vw) OR id from database.
	 */
	function __construct(&$Artifact, $data=false) {
		$this->Error();

		// Was Artifact legit?
		if (!$Artifact || !is_object($Artifact)) {
			$this->setError('ArtifactFile: No Valid Artifact');
			return;
		}
		// Did ArtifactType have an error?
		if ($Artifact->isError()) {
			$this->setError('ArtifactFile: '.$Artifact->getErrorMessage());
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
	 * @param	string	$filename	Filename of the item.
	 * @param	string	$filetype	filetype.
	 * @param	string	$filesize	filesize.
	 * @param	string	$file		file to store.
	 * @param	string	$description	Description.
	 * @param	array	$importData	Array of data to change submitter and time of submit like:
	 *						array('user' => 127, 'time' => 1234556789)
	 * @return	int|bool		Identifier on success / false on failure.
	 */
	function create($filename, $filetype, $filesize, $file, $description='None', $importData = array()) {
		// Some browsers don't supply mime type if they don't know it
		if (!$filetype) {
			// Let's be on safe side?
			$filetype = 'application/octet-stream';
		}

		//
		//	data validation
		//
		if (!$filename || !$filetype || !$filesize || !$file) {
			//echo '<p>|'.$filename.'|'.$filetype.'|'.$filesize.'|'.$file.'|';
			$this->setError(_('ArtifactFile: File, name, type, size are required'));
			return false;
		}

		if (array_key_exists('user', $importData)){
			$user_id = $importData['user'];
		} else {
			if (session_loggedin()) {
				$user_id=user_getid();
			} else {
				$user_id=100;
			}
		}

		if (array_key_exists('time',$importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}

		db_begin();

		$res = db_query_params ('INSERT INTO artifact_file
			(artifact_id,description,bin_data,filename,filesize,filetype,adddate,submitted_by)
			VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					array ($this->Artifact->getID(),
					       $description,
					       '',
					       $filename,
					       $filesize,
					       $filetype,
					       $time,
					       $user_id)) ;

		$id=db_insertid($res,'artifact_file','id');

		ArtifactStorage::instance()->store($id, $file);

		if (!$res || !$id) {
			db_rollback();
			ArtifactStorage::instance()->rollback();
			$this->setError('ArtifactFile: '.db_error());
			return false;
		} else {
			db_commit();
			ArtifactStorage::instance()->commit();

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
	 * delete - delete this artifact file from the db.
	 *
	 * @return	boolean	success.
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
			ArtifactStorage::instance()->delete($this->getID())->commit();

			$this->Artifact->addHistory('File Deleted',$this->getID().': '.$this->getName());
			return true;
		}
	}

	/**
	 * fetchData - re-fetch the data for this ArtifactFile from the database.
	 *
	 * @param	int	$id	The file_id.
	 * @return	boolean	success.
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
	 * getArtifact - get the Artifact Object this ArtifactFile is associated with.
	 *
	 * @return	object	Artifact.
	 */
	function &getArtifact() {
		return $this->Artifact;
	}

	/**
	 * getID - get this ArtifactFile's ID.
	 *
	 * @return	int	The id #.
	 */
	function getID() {
		return $this->data_array['id'];
	}

	/**
	 * getName - get the filename.
	 *
	 * @return	string	filename.
	 */
	function getName() {
		return $this->data_array['filename'];
	}

	/**
	 * getType - get the type.
	 *
	 * @return	string	type.
	 */
	function getType() {
		return $this->data_array['filetype'];
	}

	/**
	 * getData - return the content of the attached file.
	 *
	 * @return	string	content of file.
	 */
	function getData() {
		return file_get_contents($this->getFile());
	}

	/**
	 * getFile - get the file.
	 *
	 * @return	string	full pathname of file in storage.
	 */
	function getFile() {
		return ArtifactStorage::instance()->get($this->getID());
	}

	/**
	 * getSize - get the size.
	 *
	 * @return	int	size.
	 */
	function getSize() {
		return $this->data_array['filesize'];
	}

	/**
	 * getDescription - get the description.
	 *
	 * @return	string	description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 * getDate - get the date file was added.
	 *
	 * @return	int	unix time.
	 */
	function getDate() {
		return $this->data_array['adddate'];
	}

	/**
	 * getSubmittedBy - get the user_id of the submitter.
	 *
	 * @return	int	user_id.
	 */
	function getSubmittedBy() {
		return $this->data_array['submitted_by'];
	}

	/**
	 * getSubmittedRealName - get the real name of the submitter.
	 *
	 * @return	string	name.
	 */
	function getSubmittedRealName() {
		return $this->data_array['realname'];
	}

	/**
	 * getSubmittedUnixName - get the unix name of the submitter.
	 *
	 * @return	string	unixname.
	 */
	function getSubmittedUnixName() {
		return $this->data_array['user_name'];
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
