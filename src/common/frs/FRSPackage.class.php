<?php
/**
 * FusionForge file release system
 *
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012-2014, Franck Villaume - TrivialDev
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
require_once $gfcommon.'frs/FRSRelease.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';

$FRSPACKAGE_OBJ = array();

/**
 * get_frs_packages - get all FRS packages for a specific project
 *
 * @param	Group	$Group
 * @return	array
 */
function get_frs_packages($Group) {
	$ps = array();
	$res = db_query_params('SELECT * FROM frs_package WHERE group_id=$1',
				array($Group->getID()));
	if (db_numrows($res) > 0) {
		while($arr = db_fetch_array($res)) {
			$ps[] = new FRSPackage($Group, $arr['package_id'], $arr);
		}
	}
	return $ps;
}

/**
 * Gets a FRSPackage object from the given package id
 *
 * @param	array	$package_id	the DB handle if passed in (optional)
 * @param	bool	$data
 * @return	object	the FRSPackage object
 */
function frspackage_get_object($package_id, $data = false) {
	global $FRSPACKAGE_OBJ;
	if (!isset($FRSPACKAGE_OBJ['_'.$package_id.'_'])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res = db_query_params('SELECT * FROM frs_package WHERE package_id=$1',
						array($package_id)) ;
			if (db_numrows($res) < 1) {
				return false;
			}
			$data = db_fetch_array($res);
		}
		$Group = group_get_object($data['group_id']);
		$FRSPACKAGE_OBJ['_'.$package_id.'_'] = new FRSPackage($Group, $data['package_id'], $data);
	}
	return $FRSPACKAGE_OBJ['_'.$package_id.'_'];
}

/**
 * frspackage_get_groupid - get the project id from a package id
 *
 * @param	integer	$package_id	the package id
 * @return	integer the project id
 */
function frspackage_get_groupid($package_id) {
	$res = db_query_params('SELECT group_id FROM frs_package WHERE package_id=$1',
				array($package_id));
	if (!$res || db_numrows($res) < 1) {
		return false;
	}
	$arr = db_fetch_array($res);
	return $arr['group_id'];
}

class FRSPackage extends FFError {

	/**
	 * Associative array of data from db.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;
	var $package_releases;

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * @param	$Group
	 * @param	bool	$package_id
	 * @param	bool	$arr
	 */
	function __construct(&$Group, $package_id = false, $arr = false) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('FRSPackage: '.$Group->getErrorMessage());
			return;
		}
		$this->Group =& $Group;

		if ($package_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($package_id)) {
					return;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError(_('group_id in db result does not match Group Object'));
					$this->data_array = null;
					return;
				}
//
//	Add an is_public check here
//
			}
		}
	}

	/**
	 * create - create a new FRSPackage in the database.
	 *
	 * @param	$name
	 * @return	boolean	success.
	 */
	function create($name) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSPackage Name Must Be At Least 3 Characters'));
			return false;
		}
		if (!util_is_valid_filename($name)) {
			$this->setError(_('Package Name can only be alphanumeric'));
		}
		if (!forge_check_perm('frs_admin', $this->Group->getID(), 'admin')) {
			$this->setPermissionDeniedError();
			return false;
		}

		$res = db_query_params('SELECT * FROM frs_package WHERE group_id=$1 AND name=$2',
					array($this->Group->getID(),
						htmlspecialchars($name)));
		if (db_numrows($res)) {
			$this->setError(_('Error Adding Package')._(': ')._('Name Already Exists'));
			return false;
		}

		db_begin();
		$result = db_query_params('INSERT INTO frs_package(group_id, name, status_id) VALUES ($1, $2, $3)',
					array($this->Group->getID(),
						htmlspecialchars($name),
						1));
		if (!$result) {
			$this->setError(_('Error Adding Package')._(': ').db_error());
			db_rollback();
			return false;
		}
		$this->package_id = db_insertid($result, 'frs_package', 'package_id');
		if (!$this->fetchData($this->package_id)) {
			db_rollback();
			return false;
		} else {

			//make groupdir if it doesn't exist
			$groupdir = forge_get_config('upload_dir').'/'.$this->Group->getUnixName();
			if (!is_dir($groupdir)) {
				@mkdir($groupdir);
			}

			$newdirlocation = $groupdir.'/'.$this->getFileName();
			if (!is_dir($newdirlocation)) {
				@mkdir($newdirlocation);
			}

			// this 2 should normally silently fail (because it's called with the apache user) but if it's root calling the create() method, then the owner and group for the directory should be changed
			@chown($newdirlocation, forge_get_config('apache_user'));
			@chgrp($newdirlocation, forge_get_config('apache_group'));

			// add role entry
			$this->Group->normalizeAllRoles();
			$this->sendNotice(true);
			db_commit();
			return true;
		}
	}

	/**
	 * fetchData - re-fetch the data for this Package from the database.
	 *
	 * @param	int	$package_id	The package_id.
	 * @return	boolean	success.
	 */
	function fetchData($package_id) {
		$res = db_query_params('SELECT * FROM frs_package WHERE package_id=$1 AND group_id=$2',
					array($package_id, $this->Group->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Invalid package_id'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getGroup - get the Group object this FRSPackage is associated with.
	 *
	 * @return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getID - get this package_id.
	 *
	 * @return	int	The id of this package.
	 */
	function getID() {
		return $this->data_array['package_id'];
	}

	/**
	 * getName - get the name of this package.
	 *
	 * @return	string	The name of this package.
	 */
	function getName() {
		return $this->data_array['name'];
	}

	/**
	 * getFileName - get the filename of this package.
	 *
	 * @return	string	The name of this package.
	 */
	function getFileName() {
		return util_secure_filename($this->data_array['name']);
	}

	/**
	 * getStatus - get the status of this package.
	 *
	 * @return	int	The status.
	 */
	function getStatus() {
		return $this->data_array['status_id'];
	}

	/**
	 * getStatusName - get the status name of this package based on his status_id.
	 *
	 * @return	string	The status name.
	 */
	function getStatusName() {
		$res = db_query_params('SELECT * FROM frs_status', array());
		while ($arr = db_fetch_array($res)) {
			if ($arr['status_id'] == $this->getStatus()) {
				return $arr['name'];
			}
		}
		return NULL;
	}

	/**
	 * isPublic - whether non-group-members can view.
	 *
	 * @return	boolean	is_public.
	 */
	function isPublic() {
		$ra = RoleAnonymous::getInstance();
		return $ra->hasPermission('frs', $this->getID(), 'read');
	}

	function getPublicLabel() {
		if ($this->isPublic()) {
			return _('public');
		}
		return _('private');
	}

	/**
	 * setMonitor - Add the current user to the list of people monitoring this package.
	 *
	 * @return	boolean	success.
	 */
	function setMonitor() {
		if (!session_loggedin()) {
			$this->setError(_('You can only monitor if you are logged in.'));
			return false;
		}
		$MonitorElementObject = new MonitorElement('frspackage');
		if (!$MonitorElementObject->enableMonitoringByUserId($this->getID(), user_getid())) {
			$this->setError($MonitorElementObject->getErrorMessage());
			return false;
		}
		return true;
	}

	/**
	 * stopMonitor - Remove the current user from the list of people monitoring this package.
	 *
	 * @return	boolean	success.
	 */
	function stopMonitor() {
		if (!session_loggedin()) {
			$this->setError(_('You can only monitor if you are logged in.'));
			return false;
		}
		$MonitorElementObject = new MonitorElement('frspackage');
		if (!$MonitorElementObject->disableMonitoringByUserId($this->getID(), user_getid())) {
			$this->setError($MonitorElementObject->getErrorMessage());
			return false;
		}
		return true;
	}

	function clearMonitor() {
		$MonitorElementObject = new MonitorElement('frspackage');
		if (!$MonitorElementObject->clearMonitor($this->getID())) {
			$this->setError($MonitorElementObject->getErrorMessage());
			return false;
		}
		return true;
	}

	/**
	 * getMonitorCount - Get the count of people monitoring this package
	 *
	 * @return	int	the count
	 */
	function getMonitorCount() {
		$MonitorElementObject = new MonitorElement('frspackage');
		$getMonitorCounterInteger = $MonitorElementObject->getMonitorCounterInteger($this->getID());
		if ($getMonitorCounterInteger !== false) {
			return $getMonitorCounterInteger;
		}
		$this->setError($MonitorElementObject->getErrorMessage());
		return false;
	}

	/**
	 * isMonitoring - Is the current user in the list of people monitoring this package.
	 *
	 * @return	boolean	is_monitoring.
	 */
	function isMonitoring() {
		if (!session_loggedin()) {
			return false;
		}
		return $this->isMonitoredBy(user_getid());
	}

	function isMonitoredBy($userid = 'ALL') {
		$MonitorElementObject = new MonitorElement('frspackage');
		if ( $userid == 'ALL' ) {
			return $MonitorElementObject->isMonitoredByAny($this->getID());
		} else {
			return $MonitorElementObject->isMonitoredByUserId($this->getID(), $userid);
		}
	}

	/**
	 * getMonitorIDs - Return an array of user_id's of the list of people monitoring this package.
	 *
	 * @return	array	The array of user_id's.
	 */
	function getMonitorIDs() {
		$MonitorElementObject = new MonitorElement('frspackage');
		return $MonitorElementObject->getMonitorUsersIdsInArray($this->getID());
	}

	/**
	 * update - update an FRSPackage in the database.
	 *
	 * @param	string	$name		The name of this package.
	 * @param	int	$status		The status_id of this package from frs_status table.
	 * @return	boolean success.
	 */
	function update($name, $status) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSPackage Name Must Be At Least 3 Characters'));
			return false;
		}

		if (!forge_check_perm('frs', $this->getID(), 'admin')) {
			$this->setPermissionDeniedError();
			return false;
		}
		if($this->getName() != htmlspecialchars($name)) {
			$res = db_query_params ('SELECT * FROM frs_package WHERE group_id=$1 AND name=$2',
						array ($this->Group->getID(),
						       htmlspecialchars($name))) ;
			if (db_numrows($res)) {
				$this->setError(_('Error Updating Package')._(': ')._('Name Already Exists'));
				return false;
			}
		}
		db_begin();
		$res = db_query_params('UPDATE frs_package SET name=$1, status_id=$2 WHERE group_id=$3 AND package_id=$4',
					array (htmlspecialchars($name),
					       $status,
					       $this->Group->getID(),
					       $this->getID()));
		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error On Update')._(': ').db_error());
			db_rollback();
			return false;
		}

		$olddirname = $this->getFileName();
		if(!$this->fetchData($this->getID())){
			$this->setError(_('Error Updating Package')._(': ')._("Couldn't fetch data"));
			db_rollback();
			return false;
		}
		$newdirname = $this->getFileName();
		$olddirlocation = forge_get_config('upload_dir').'/'.$this->Group->getUnixName().'/'.$olddirname;
		$newdirlocation = forge_get_config('upload_dir').'/'.$this->Group->getUnixName().'/'.$newdirname;

		if(($olddirname!=$newdirname)){
			if(is_dir($newdirlocation)){
				$this->setError(_('Error Updating Package')._(': ')._('Directory Already Exists'));
				db_rollback();
				return false;
			} else {
				if(!@rename($olddirlocation,$newdirlocation)) {
					$this->setError(_('Error Updating Package')._(': ')._("Couldn't rename dir"));
					db_rollback();
					return false;
				}
			}
		}
		db_commit();
		$this->createReleaseFilesAsZip($this->getNewestReleaseID());
		$this->sendNotice();
		return true;
	}

	/**
	 * getReleases - gets Release objects for all the releases in this package.
	 *
	 * @return	array	Array of FRSRelease Objects.
	 */
	function &getReleases($include_hidden = true) {
		if (!is_array($this->package_releases) || count($this->package_releases) < 1) {
			$this->package_releases=array();
			$res = db_query_params('SELECT * FROM frs_release WHERE package_id=$1 ORDER BY release_date DESC',
						array($this->getID()));
			while ($arr = db_fetch_array($res)) {
				if ($include_hidden) {
					$this->package_releases[] = $this->newFRSRelease($arr['release_id'], $arr);
				} else {
					if ($arr['status_id'] == 1) {
						$this->package_releases[] = $this->newFRSRelease($arr['release_id'], $arr);
					}
				}
			}
		}
		return $this->package_releases;
	}

	/**
	 * newFRSRelease - generates a FRSRelease (allows overloading by subclasses)
	 *
	 * @param	string		FRS release identifier
	 * @param	array		fetched data from the DB
	 * @return	FRSRelease	new FRSFile object.
	 */
	protected function newFRSRelease($release_id, $data) {
		return new FRSRelease($this,$release_id, $data);
	}

	/**
	 * delete - delete this package and all its related data.
	 *
	 * @param	bool	$sure		I'm Sure.
	 * @param	bool	$really_sure	I'm REALLY sure.
	 * @return	bool
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if (!forge_check_perm('frs', $this->getID(), 'admin')) {
			$this->setPermissionDeniedError();
			return false;
		}
		$r =& $this->getReleases();
		for ($i = 0; $i<count($r); $i++) {
			if (!is_object($r[$i]) || $r[$i]->isError() || !$r[$i]->delete($sure, $really_sure)) {
				$this->setError(_('Release Error')._(': ').$r[$i]->getName()._(': ').$r[$i]->getErrorMessage());
				return false;
			}
		}
		$dir = forge_get_config('upload_dir').'/'.
			$this->Group->getUnixName() . '/' .
			$this->getFileName().'/';

		// double-check we're not trying to remove root dir
		if (util_is_root_dir($dir)) {
			$this->setError(_('Package delete error: trying to delete root dir'));
			return false;
		}

		if (is_dir($dir))
			rmdir($dir);

		$this->clearMonitor();
		db_query_params('DELETE FROM frs_package WHERE package_id=$1 AND group_id=$2',
				 array ($this->getID(),
					$this->Group->getID()));
		return true;
	}

	/**
	 * getNewestReleaseID - return the newest release_id of a package
	 * The newest release is the release with the highest ID
	 *
	 * @return	integer	release id
	 */
	public function getNewestReleaseID() {
		$result = db_query_params('SELECT MAX(release_id) AS release_id FROM frs_release WHERE package_id = $1',
					  array($this->getID()));

		if ($result && db_numrows($result) == 1) {
			$row = db_fetch_array($result);
			return $row['release_id'];
		} else {
			$this->setError(_('No valid max release id'));
			return false;
		}
	}

	public function getReleaseZipPath($release) {
		return forge_get_config('upload_dir').'/'.$this->Group->getUnixName().'/'.$this->getFileName().'/'.$this->getReleaseZipName($release);
	}

	public function getReleaseZipName($release) {
		$frsr = frsrelease_get_object($release);
		return $this->getFileName().'-'.$frsr->getName().'.zip';
	}

	public function getNewestReleaseZipName() {
		return $this->getFileName().'-latest.zip';
	}

	/**
	 * createReleaseFilesAsZip - create the Zip Archive of the release
	 *
	 * @param	integer	release id.
	 * @return	bool	true on success even if the php ZipArchive does not exist
	 */
	public function createReleaseFilesAsZip($release_id) {
		if ($release_id && class_exists('ZipArchive')) {
			$zip = new ZipArchive();
			$zipPath = $this->getReleaseZipPath($release_id);
			$release = frsrelease_get_object($release_id);
			$filesPath = forge_get_config('upload_dir').'/'.$this->Group->getUnixName().'/'.$this->getFileName().'/'.$release->getFileName();
			if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) != true) {
				$this->setError(_('Cannot open the file archive')._(': ').$zipPath.'.');
				return false;
			}
			$files = $release->getFiles();
			foreach ($files as $f) {
				$filePath = $filesPath.'/'.$f->getName();
				if ($zip->addFile($filePath, $f->getName()) !== true) {
					$this->setError(_('Cannot add file to the file archive')._(': ').$zipPath.'.');
					return false;
				}
			}
			if ($zip->close() !== true) {
				$this->setError(_('Cannot close the file archive')._(': ').$zipPath.'.');
				return false;
			}
		}
		return true;
	}

	public function deleteReleaseFilesAsZip($release_id) {
		if (file_exists($this->getReleaseZipPath($release_id)))
			unlink($this->getReleaseZipPath($release_id));
		return true;
	}

	/**
	 * sendNotice - Notifies of package actions
	 *
	 * @param	boolean	true = new package (default value)
	 * @return	bool
	 */
	function sendNotice($new = true) {
		$BCC = $this->Group->getFRSEmailAddress();
		if ($this->isMonitoredBy('ALL')) {
			$BCC .= $this->getMonitoredUserEmailAddress();
		}
		if (strlen($BCC) > 0) {
			$session = session_get_user();
			if ($new) {
				$status = _('New Package');
			} else {
				$status = _('Updated Package').' '._('by').' ' . $session->getRealName();
			}
			$subject = '['.$this->Group->getPublicName().'] '.$status.' - '.$this->getName();
			$body = _('Project')._(': ').$this->Group->getPublicName()."\n";
			$body .= _('Package')._(': ').$this->getName()."\n";
			$body .= "\n\n-------------------------------------------------------\n".
				_('For more info, visit')._(':').
				"\n\n" . util_make_url('/frs/?group_id='.$this->Group->getID());

			$BCCarray = explode(',',$BCC);
			foreach ($BCCarray as $dest_email) {
				util_send_message($dest_email, $subject, $body, 'noreply@'.forge_get_config('web_host'), '', _('FRS'));
			}
		}
		return true;
	}

	/**
	 * getMonitoredUserEmailAddress - get the email addresses of users who monitor this file
	 *
	 * @return	string	The list of emails comma separated
	 */
	function getMonitoredUserEmailAddress() {
		$MonitorElementObject = new MonitorElement('frspackage');
		return $MonitorElementObject->getAllEmailsInCommatSeparated($this->getID());
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
