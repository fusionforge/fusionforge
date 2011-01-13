<?php
/**
 * FusionForge file release system
 *
 * Copyright 2002, Tim Perdue/GForge, LLC
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
 *	  Factory method which creates a FRSFile from an release id
 *
 *	  @param int	  The file id
 *	  @param array	The result array, if it's passed in
 *	  @return object  FRSFile object
 */
function &frsfile_get_object($file_id, $data=false) {
	global $FRSFILE_OBJ;
	if (!isset($FRSFILE_OBJ['_'.$file_id.'_'])) {
		if ($data) {
					//the db result handle was passed in
		} else {
			$res = db_query_params ('SELECT * FROM frs_file WHERE file_id=$1',
						array ($file_id)) ;
			if (db_numrows($res)<1 ) {
				$FRSFILE_OBJ['_'.$file_id.'_']=false;
				return false;
			}
			$data = db_fetch_array($res);
		}
		$FRSRelease =& frsrelease_get_object($data['release_id']);
		$FRSFILE_OBJ['_'.$file_id.'_']= new FRSFile($FRSRelease,$data['file_id'],$data);
	}
	return $FRSFILE_OBJ['_'.$file_id.'_'];
}

class FRSFile extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var  array   $data_array.
	 */
	var $data_array;

	/**
	 * The FRSRelease.
	 *
	 * @var  object  FRSRelease.
	 */
	var $FRSRelease;

	/**
	 *  Constructor.
	 *
	 *  @param  object  The FRSRelease object to which this file is associated.
	 *  @param  int  The file_id.
	 *  @param  array   The associative array of data.
	 *	@return	boolean	success.
	 */
	function FRSFile(&$FRSRelease, $file_id=false, $arr=false) {
		$this->Error();
		if (!$FRSRelease || !is_object($FRSRelease)) {
			$this->setError('FRSFile:: No Valid FRSRelease Object');
			return false;
		}
		if ($FRSRelease->isError()) {
			$this->setError('FRSFile:: '.$FRSRelease->getErrorMessage());
			return false;
		}
		$this->FRSRelease =& $FRSRelease;

		if ($file_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($file_id)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['release_id'] != $this->FRSRelease->getID()) {
					$this->setError('FRSRelease_id in db result does not match FRSRelease Object');
					$this->data_array=null;
					return false;
				}
			}
		}
		return true;
	}

	/**
	 *	create - create a new file in this FRSFileRelease/FRSPackage.
	 *
	 *	@param	string	The name of this file.
	 *	@param	string	The location of this file in the local file system.
	 *	@param	int	The type_id of this file from the frs-file-types table.
	 *	@param	int	The processor_id of this file from the frs-processor-types table.
	 *	@param	int	The release_date of this file in unix time (seconds).
	 *	@return	boolean success.
	 */
	function create($name,$file_location,$type_id,$processor_id,$release_time=false) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSFile Name Must Be At Least 3 Characters'));
			return false;
		}
		if (!util_is_valid_filename($name)) {
			$this->setError(_('Filename can only be alphanumeric and "-" "_" "+" "." "~" characters.'));
			return false;
		}
//
//	Can't really use is_uploaded_file() or move_uploaded_file()
//	since we want this to be generalized code
//	This is potentially exploitable if you do not validate 
//	before calling this function
//
		if (!is_file($file_location) || !file_exists($file_location)) {
			$this->setError(_('FRSFile Appears to be invalid'));
			return false;
		}

		if (!forge_check_perm ('frs', $this->FRSRelease->FRSPackage->Group->getID(), 'write')) {
			$this->setPermissionDeniedError();
			return false;
		}

		//
		//	Filename must be unique in this release
		//
		$resfile = db_query_params ('SELECT filename FROM frs_file WHERE filename=$1 AND release_id=$2',
					    array ($name,
						   $this->FRSRelease->getId())) ;
		if (!$resfile || db_numrows($resfile) > 0) {
			$this->setError(_('That filename already exists in this project space').' '.db_error());
			return false;
		}


		$path_name=forge_get_config('upload_dir').'/'.$this->FRSRelease->FRSPackage->Group->getUnixName();
		if (!is_dir($path_name)) {
			mkdir($path_name,0755);
		} else {
			if ( fileperms($path_name) != 0x4755 ) {
				chmod($path_name,0755);
			}
		}
		$path_name=$path_name.'/'.$this->FRSRelease->FRSPackage->getFileName();
		if (!is_dir($path_name)) {
			mkdir($path_name,0755);
		} else {
			if ( fileperms($path_name) != 0x4755 ) {
				chmod($path_name,0755);
			}
		}
		$path_name=$path_name.'/'.$this->FRSRelease->getFileName();
		if (!is_dir($path_name)) {
			mkdir($path_name,0755);
		} else {
			if ( fileperms($path_name) != 0x4755 ) {
				chmod($path_name,0755);
			}
		}

		$newfilelocation = forge_get_config('upload_dir').'/'.
			$this->FRSRelease->FRSPackage->Group->getUnixName().'/'.
			$this->FRSRelease->FRSPackage->getFileName().'/'.
			$this->FRSRelease->getFileName().'/';

		$ret = rename($file_location, $newfilelocation.$name);
		if (!$ret) {
			$this->setError(_('File cannot be moved to the permanent location').': '.$newfilelocation.$name);
			return false;
		}

		if (!$release_time) {
			$release_time=time();
		}
		$file_size=filesize("$newfilelocation$name");
		$sql="INSERT INTO frs_file(release_id,filename,release_time,
				type_id,processor_id,file_size,post_date)
			VALUES ('".$this->FRSRelease->getId()."','$name','$release_time',
				'$type_id','$processor_id','$file_size','".time()."')";

		db_begin();
		$result = db_query_params ('INSERT INTO frs_file(release_id,filename,release_time,type_id,processor_id,file_size,post_date) VALUES ($1,$2,$3,$4,$5,$6,$7)',
					   array ($this->FRSRelease->getId(),
						  $name,
						  $release_time,
						  $type_id,
						  $processor_id,
						  $file_size,
						  time ())) ;
		if (!$result) {
			$this->setError('FRSFile::create() Error Adding Release: '.db_error());
			db_rollback();
			return false;
		}
		$this->file_id=db_insertid($result,'frs_file','file_id');
		if (!$this->fetchData($this->file_id)) {
			db_rollback();
			return false;
		} else {
			db_commit();
			$this->FRSRelease->FRSPackage->createNewestReleaseFilesAsZip();
			return true;
		}
	}

	/**
	 *  fetchData - re-fetch the data for this FRSFile from the database.
	 *
	 *  @param  int  The file_id.
	 *  @return boolean	success.
	 */
	function fetchData($file_id) {
		$res = db_query_params ('SELECT * FROM frs_file_vw WHERE file_id=$1 AND release_id=$2',
					array ($file_id,
					       $this->FRSRelease->getID())) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('FRSFile::fetchData()  Invalid file_id');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *  getFRSRelease - get the FRSRelease object this file is associated with.
	 *
	 *  @return	object	The FRSRelease object.
	 */
	function &getFRSRelease() {
		return $this->FRSRelease;
	}

	/**
	 *  getID - get this file_id.
	 *
	 *  @return	int	The id of this file.
	 */
	function getID() {
		return $this->data_array['file_id'];
	}

	/**
	 *  getName - get the name of this file.
	 *
	 *  @return string  The name of this file.
	 */
	function getName() {
		return $this->data_array['filename'];
	}

	/**
	 *  getSize - get the size of this file.
	 *
	 *  @return int	The size.
	 */
	function getSize() {
		return $this->data_array['file_size'];
	}

	/**
	 *  getTypeID - the filetype id.
	 *
	 *  @return int the filetype id.
	 */
	function getTypeID() {
		return $this->data_array['type_id'];
	}

	/**
	 *  getTypeName - the filetype name.
	 *
	 *  @return string	The filetype name.
	 */
	function getFileType() {
		return $this->data_array['filetype'];
	}

	/**
	 *  getProcessorID - the processor id.
	 *
	 *  @return int the processor id.
	 */
	function getProcessorID() {
		return $this->data_array['processor_id'];
	}

	/**
	 *  getProcessor - the processor name.
	 *
	 *  @return string	The processor name.
	 */
	function getProcessor() {
		return $this->data_array['processor'];
	}

	/**
	 *  getDownloads - the number of downloads.
	 *
	 *  @return int  The number of downloads.
	 */
	function getDownloads() {
		return $this->data_array['downloads'];
	}

	/**
	 *  getReleaseTime - get the releasetime of this file.
	 *
	 *  @return int	The release time in unix time.
	 */
	function getReleaseTime() {
		return $this->data_array['release_time'];
	}

	/**
	 *  getPostDate - get the post date of this file.
	 *
	 *  @return int	The post date in unix time.
	 */
	function getPostDate() {
		return $this->data_array['post_date'];
	}

	/**
	 *  delete - Delete this file from the database and file system.
	 *
	 *  @return	boolean	success.
	 */
	function delete() {
		if (!forge_check_perm ('frs', $this->FRSRelease->FRSPackage->Group->getID(), 'write')) {
			$this->setPermissionDeniedError();
			return false;
		}

		$file=forge_get_config('upload_dir').'/'. 
			$this->FRSRelease->FRSPackage->Group->getUnixName() . '/' . 
			$this->FRSRelease->FRSPackage->getFileName().'/'.
			$this->FRSRelease->getFileName().'/'.
			$this->getName();
			if (file_exists($file))
				unlink($file);
		$result = db_query_params ('DELETE FROM frs_file WHERE file_id=$1',
					   array ($this->getID())) ;
		if (!$result || db_affected_rows($result) < 1) {
			$this->setError("frsDeleteFile()::2 ".db_error());
			return false;
		} else {
			$res = db_query_params ('DELETE FROM frs_dlstats_file WHERE file_id=$1',
						array ($this->getID())) ;
			$res = db_query_params ('DELETE FROM frs_dlstats_filetotal_agg WHERE file_id=$1',
						array ($this->getID())) ;
			$this->FRSRelease->FRSPackage->createNewestReleaseFilesAsZip();
			return true;
		}
	}

	/**
	 *	update - update an existing file in this FRSFileRelease/FRSPackage.
	 *
	 *	@param	int	The type_id of this file from the frs-file-types table.
	 *	@param	int	The processor_id of this file from the frs-processor-types table.
	 *	@param	int	The release_date of this file in unix time (seconds).
	 *	@param	int	The release_id of the release this file belongs to (if not set, defaults to the release id of this file).
	 *	@return	boolean success.
	 */
	function update($type_id,$processor_id,$release_time,$release_id=false) {
		if (!forge_check_perm ('frs', $this->FRSRelease->FRSPackage->Group->getID(), 'write')) {
			$this->setPermissionDeniedError();
			return false;
		}

		// Sanity checks
		if ( $release_id ) {
			// Check that the new FRSRelease id exists
			if ($FRSRelease=frsrelease_get_object($release_id)) {
				// Check that the new FRSRelease id belongs to the group of this FRSFile
				if ($FRSRelease->FRSPackage->Group->getID()!=$this->FRSRelease->FRSPackage->Group->getID()) {
					$this->setError('FRSFile:: No Valid Group Object');
					return false;
				}
			} else {
				$this->setError('FRSFile:: No Valid FRSRelease Object');
				return false;
			}
		} else {
			// If release_id is not set, defaults to the release id of this file
			$release_id = $this->FRSRelease->getID();
		}

		// Update database
		db_begin();
		$res = db_query_params ('UPDATE frs_file SET type_id=$1,processor_id=$2,release_time=$3,release_id=$4 WHERE file_id=$5',
					array ($type_id,
					       $processor_id,
					       $release_time,
					       $release_id,
					       $this->getID())) ;

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('FRSFile::update() Error On Update: '.db_error());
			db_rollback();
			return false;
		}

		// Move physically file if needed
		if ($release_id != $this->FRSRelease->getID()) {
			$old_file_location = forge_get_config('upload_dir').'/'.
				$this->FRSRelease->FRSPackage->Group->getUnixName().'/'.
				$this->FRSRelease->FRSPackage->getFileName().'/'.
				$this->FRSRelease->getFileName().'/'.
				$this->data_array['filename'];
			$new_file_location = forge_get_config('upload_dir').'/'.
				$FRSRelease->FRSPackage->Group->getUnixName().'/'.
				$FRSRelease->FRSPackage->getFileName().'/'.
				$FRSRelease->getFileName().'/'.
				$this->data_array['filename'];
			if (file_exists($new_file_location)) {
				$this->setError(_('That filename already exists in this project space'));
				db_rollback();
				return false;
			}
			$ret = rename($old_file_location, $new_file_location);
			if (!$ret) {
				$this->setError(_('File cannot be moved to the permanent location').': '.$new_file_location);
				db_rollback();
				return false;
			}
		}
		db_commit();
		$this->FRSRelease->FRSPackage->createNewestReleaseFilesAsZip();
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
