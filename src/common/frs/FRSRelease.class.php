<?php
/**
 * FusionForge file release system
 *
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
require_once $gfcommon.'frs/FRSFile.class.php';

/**
 *	  Factory method which creates a FRSRelease from an release id
 *
 *	  @param int	  The release id
 *	  @param array	The result array, if it's passed in
 *	  @return object  FRSRelease object
 */
function &frsrelease_get_object($release_id, $data = false) {
	global $FRSRELEASE_OBJ;
	if (!isset($FRSRELEASE_OBJ['_'.$release_id.'_'])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res = db_query_params ('SELECT * FROM frs_release WHERE release_id=$1',
						array ($release_id)) ;
			if (db_numrows($res)<1 ) {
				$FRSRELEASE_OBJ['_'.$release_id.'_']=false;
				return false;
			}
			$data = db_fetch_array($res);
		}
		$FRSPackage = frspackage_get_object($data['package_id']);
		$FRSRELEASE_OBJ['_'.$release_id.'_']= new FRSRelease($FRSPackage,$data['release_id'],$data);
	}
	return $FRSRELEASE_OBJ['_'.$release_id.'_'];
}

class FRSRelease extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var  array   $data_array.
	 */
	var $data_array;

	/**
	 * The FRSPackage.
	 *
	 * @var  object  FRSPacakge.
	 */
	var $FRSPackage;
	var $release_files;

	/**
	 *  Constructor.
	 *
	 *  @param  object  The FRSPackage object to which this release is associated.
	 *  @param  int  The release_id.
	 *  @param  array   The associative array of data.
	 *	@return	boolean	success.
	 */
	function FRSRelease(&$FRSPackage, $release_id = false, $arr = false) {
		$this->Error();
		if (!$FRSPackage || !is_object($FRSPackage)) {
			$this->setError('FRSRelease:: No Valid FRSPackage Object');
			return false;
		}
		if ($FRSPackage->isError()) {
			$this->setError('FRSRelease:: '.$FRSPackage->getErrorMessage());
			return false;
		}
		$this->FRSPackage =& $FRSPackage;

		if ($release_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($release_id)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['package_id'] != $this->FRSPackage->getID()) {
					$this->setError('FRSPackage_id in db result does not match FRSPackage Object');
					$this->data_array=null;
					return false;
				}
			}
		}
		return true;
	}

	/**
	 *	create - create a new release in the database.
	 *
	 *	@param	string	The name of the release.
	 *	@param	string	The release notes for the release.
	 *	@param	string	The change log for the release.
	 *	@param	int	Whether the notes/log are preformatted with \n chars (1) true (0) false.
	 *	@param	int	The unix date of the release.
	 *	@return	boolean success.
	 */
	function create($name,$notes,$changes,$preformatted,$release_date=false) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSPackage Name Must Be At Least 3 Characters'));
			return false;
		}

		if ($preformatted) {
			$preformatted = 1;
		} else {
			$preformatted = 0;
		}

		if (!forge_check_perm ('frs', $this->FRSPackage->Group->getID(), 'write')) {
			$this->setPermissionDeniedError();
			return false;
		}

		if (!$release_date) {
			$release_date=time();
		}
		$res = db_query_params ('SELECT * FROM frs_release WHERE package_id=$1 AND name=$2',
					array ($this->FRSPackage->getID(),
					       htmlspecialchars($name))) ;
		if (db_numrows($res)) {
			$this->setError('FRSRelease::create() Error Adding Release: Name Already Exists');
			return false;
		}

		db_begin();
		$result=db_query_params ('INSERT INTO frs_release(package_id,notes,changes,preformatted,name,release_date,released_by,status_id) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					 array ($this->FRSPackage->getId(),
						htmlspecialchars($notes),
						htmlspecialchars($changes),
						$preformatted,
						htmlspecialchars($name),
						$release_date,
						user_getid(),
						1)) ;
		if (!$result) {
			$this->setError('FRSRelease::create() Error Adding Release: '.db_error());
			db_rollback();
			return false;
		}
		$this->release_id=db_insertid($result,'frs_release','release_id');
		if (!$this->fetchData($this->release_id)) {
			db_rollback();
			return false;
		} else {
			$newdirlocation = forge_get_config('upload_dir').'/'.$this->FRSPackage->Group->getUnixName().'/'.$this->FRSPackage->getFileName().'/'.$this->getFileName();
			if (!is_dir($newdirlocation)) {
				@mkdir($newdirlocation);
			}
			db_commit();
			return true;
		}
	}

	/**
	 *  fetchData - re-fetch the data for this Release from the database.
	 *
	 *  @param  int  The release_id.
	 *  @return	boolean	success.
	 */
	function fetchData($release_id) {
		$res = db_query_params ('SELECT * FROM frs_release WHERE release_id=$1 AND package_id=$2',
					array ($release_id,
					       $this->FRSPackage->getID())) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError('FRSRelease::fetchData()  Invalid release_id');
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *  getFRSPackage - get the FRSPackage object this release is associated with.
	 *
	 *  @return	object	The FRSPackage object.
	 */
	function &getFRSPackage() {
		return $this->FRSPackage;
	}

	/**
	 *  getID - get this release_id.
	 *
	 *  @return	int	The id of this release.
	 */
	function getID() {
		return $this->data_array['release_id'];
	}

	/**
	 *  getName - get the name of this release.
	 *
	 *  @return string  The name of this release.
	 */
	function getName() {
		return $this->data_array['name'];
	}

	/**
	 *  getFileName - get the filename of this release.
	 *
	 *  @return string  The filename of this release.
	 */
	function getFileName() {
		return util_secure_filename($this->data_array['name']);
	}

	/**
	 *  getStatus - get the status of this release.
	 *
	 *  @return int	The status.
	 */
	function getStatus() {
		return $this->data_array['status_id'];
	}

	/**
	 *  getNotes - get the release notes of this release.
	 *
	 *  @return string	The release notes.
	 */
	function getNotes() {
		return $this->data_array['notes'];
	}

	/**
	 *  getChanges - get the changelog of this release.
	 *
	 *  @return string	The changelog.
	 */
	function getChanges() {
		return $this->data_array['changes'];
	}

	/**
	 *  getPreformatted - get the preformatted option of this release.
	 *
	 *  @return	boolean	preserve_formatting.
	 */
	function getPreformatted() {
		return $this->data_array['preformatted'];
	}

	/**
	 *  getReleaseDate - get the releasedate of this release.
	 *
	 *  @return int	The release date in unix time.
	 */
	function getReleaseDate() {
		return $this->data_array['release_date'];
	}

	/**
	 *  sendNotice - the logic to send an email/jabber notice for a release.
	 *
	 *  @return	boolean	success.
	 */
	function sendNotice() {
		$arr =& $this->FRSPackage->getMonitorIDs();

		$date = date('Y-m-d H:i',time());

		$subject = sprintf (_('[%1$s Release] %2$s'),
				    $this->FRSPackage->Group->getUnixName(),
				    $this->FRSPackage->getName());
		$text = stripcslashes(sprintf(_('Project %1$s (%2$s) has released a new version of package "%3$s".

Release note:

%4$s

Change note:

%5$s


You can download it by following this link:

%6$s

You receive this email because you requested to be notified when new
versions of this package were released. If you don\'t wish to be
notified in the future, please login to %7$s and click this link:

%8$s





'
),
					      $this->FRSPackage->Group->getPublicName(),
					      $this->FRSPackage->Group->getUnixName(),
					      $this->FRSPackage->getName(),
					      $this->getNotes(),
					      $this->getChanges(),
					      util_make_url ("/frs/?group_id=". $this->FRSPackage->Group->getID() ."&release_id=". $this->getID()),
					      forge_get_config ('forge_name'),
					      util_make_url ("/frs/monitor.php?filemodule_id=".$this->FRSPackage->getID()."&group_id=".$this->FRSPackage->Group->getID()."&stop=1")));
//		$text = util_line_wrap($text);
		if (count($arr)) {
			util_handle_message(array_unique($arr),$subject,$text);
		}

	}

	/**
	 *	getFiles - gets all the file objects for files in this release.
	 *
	 *	return	array	Array of FRSFile Objects.
	 */
	function &getFiles() {
		if (!is_array($this->release_files) || count($this->release_files) < 1) {
			$this->release_files=array();
			$res = db_query_params ('SELECT * FROM frs_file_vw WHERE release_id=$1',
						array ($this->getID())) ;
			while ($arr = db_fetch_array($res)) {
				$this->release_files[]=new FRSFile($this,$arr['file_id'],$arr);
			}
		}
		return $this->release_files;
	}

	/**
	 *  delete - delete this release and all its related data.
	 *
	 *  @param  bool	I'm Sure.
	 *  @param  bool	I'm REALLY sure.
	 *  @return   bool true/false;
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if (!forge_check_perm ('frs', $this->FRSPackage->Group->getID(), 'write')) {
			$this->setPermissionDeniedError();
			return false;
		}
		$f =& $this->getFiles();
		for ($i=0; $i<count($f); $i++) {
			if (!is_object($f[$i]) || $f[$i]->isError() || !$f[$i]->delete()) {
				$this->setError('File Error: '.$f[$i]->getName().':'.$f[$i]->getErrorMessage());
				return false;
			}
		}
		$dir=forge_get_config('upload_dir').'/'.
			$this->FRSPackage->Group->getUnixName() . '/' .
			$this->FRSPackage->getFileName().'/'.
			$this->getFileName().'/';

		// double-check we're not trying to remove root dir
		if (util_is_root_dir($dir)) {
			$this->setError('Release::delete error: trying to delete root dir');
			return false;
		}
		rmdir($dir);

		db_query_params ('DELETE FROM frs_release WHERE release_id=$1 AND package_id=$2',
				 array ($this->getID(),
					$this->FRSPackage->getID())) ;
		return true;
	}

	/**
	 *	update - update a new release in the database.
	 *
	 *	@param	int	The status of this release from the frs_status table.
	 *	@param	string	The name of the release.
	 *	@param	string	The release notes for the release.
	 *	@param	string	The change log for the release.
	 *	@param	int	Whether the notes/log are preformatted with \n chars (1) true (0) false.
	 *	@param	int	The unix date of the release.
	 *	@return	boolean success.
	 */
	function update($status, $name, $notes, $changes, $preformatted, $release_date) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSPackage Name Must Be At Least 3 Characters'));
			return false;
		}

		if (!forge_check_perm('frs', $this->FRSPackage->Group->getID(), 'write')) {
			$this->setPermissionDeniedError();
			return false;
		}

		if ($preformatted) {
			$preformatted = 1;
		} else {
			$preformatted = 0;
		}

		if($this->getName() != htmlspecialchars($name)) {
			$res = db_query_params ('SELECT * FROM frs_release WHERE package_id=$1 AND name=$2',
						array ($this->FRSPackage->getID(),
						       htmlspecialchars($name))) ;
			if (db_numrows($res)) {
				$this->setError('FRSRelease::update() Error On Update: Name Already Exists');
				return false;
			}
		}
		db_begin();
		$res = db_query_params ('UPDATE frs_release SET	name=$1,status_id=$2,notes=$3,
			changes=$4,preformatted=$5,release_date=$6,released_by=$7
			WHERE package_id=$8 AND release_id=$9',
					array (htmlspecialchars($name),
					       $status,
					       htmlspecialchars($notes),
					       htmlspecialchars($changes),
					       $preformatted,
					       $release_date,
					       user_getid(),
					       $this->FRSPackage->getID(),
					       $this->getID())) ;

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError('FRSRelease::update() Error On Update: '.db_error());
			db_rollback();
			return false;
		}

		$oldfilename = $this->getFileName();
		if(!$this->fetchData($this->getID())){
			$this->setError("FRSRelease::update() Error Updating Release: Couldn't fetch data");
			db_rollback();
			return false;
		}
		$newfilename = $this->getFileName();
		$olddirlocation = forge_get_config('upload_dir').'/'.$this->FRSPackage->Group->getUnixName().'/'.$this->FRSPackage->getFileName().'/'.$oldfilename;
		$newdirlocation = forge_get_config('upload_dir').'/'.$this->FRSPackage->Group->getUnixName().'/'.$this->FRSPackage->getFileName().'/'.$newfilename;

		if (($oldfilename!=$newfilename) && is_dir($olddirlocation)) {
			if (is_dir($newdirlocation)) {
				$this->setError('FRSRelease::update() Error Updating Release: Directory Already Exists');
				db_rollback();
				return false;
			} else {
				if(!rename($olddirlocation,$newdirlocation)) {
					$this->setError("FRSRelease::update() Error Updating Release: Couldn't rename dir");
					db_rollback();
					return false;
				}
			}
		}
		db_commit();
		$this->FRSPackage->createNewestReleaseFilesAsZip();
		return true;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
