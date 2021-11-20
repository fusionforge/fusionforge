<?php
/**
 * FusionForge FRS: Release Class
 *
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014,2016, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/FFObject.class.php';
require_once $gfcommon.'frs/FRSFile.class.php';

/**
 * get_frs_releases - get all FRS releases for a specific package
 *
 * @param	FRSPackage	$package
 * @return	array
 */
function get_frs_releases($package) {
	$rs = array();
	$res = db_query_params('SELECT * FROM frs_release WHERE package_id=$1',
				array($package->getID()));
	if (db_numrows($res) > 0) {
		while($arr = db_fetch_array($res)) {
			$rs[] = new FRSRelease($package, $arr['release_id'], $arr);
		}
	}
	return $rs;
}

/**
 * Factory method which creates a FRSRelease from an release id
 *
 * @param	int	$release_id	The release id
 * @param	array	$data	The result array, if it's passed in
 * @return	object|bool		FRSRelease object
 */
function frsrelease_get_object($release_id, $data = array()) {
	global $FRSRELEASE_OBJ;
	if (!isset($FRSRELEASE_OBJ['_'.$release_id.'_'])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res = db_query_params('SELECT * FROM frs_release WHERE release_id=$1',
						array ($release_id));
			if (db_numrows($res)<1 ) {
				$FRSRELEASE_OBJ['_'.$release_id.'_'] = false;
				return false;
			}
			$data = db_fetch_array($res);
		}
		$FRSPackage = frspackage_get_object($data['package_id']);
		$FRSRELEASE_OBJ['_'.$release_id.'_'] = new FRSRelease($FRSPackage, $data['release_id'], $data);
	}
	return $FRSRELEASE_OBJ['_'.$release_id.'_'];
}

class FRSRelease extends FFObject {

	/**
	 * Associative array of data from db.
	 *
	 * @var  array   $data_array.
	 */
	var $data_array;

	/**
	 * The FRSPackage.
	 *
	 * @var  object  FRSPackage.
	 */
	var $FRSPackage;
	var $release_files;
	var $files_count = null;
	var $send_notice = true;

	/**
	 * cached return value of getVotes
	 * @var	bool|array	$votes
	 */
	var $votes = false;

	/**
	 * cached return value of getVoters
	 * @var	bool|array	$voters
	 */
	var $voters = false;

	/**
	 * @param	object  	$FRSPackage	The FRSPackage object to which this release is associated.
	 * @param	int|bool	$release_id	The release_id.
	 * @param	array		$arr		The associative array of data.
	 */
	function __construct(&$FRSPackage, $release_id = false, $arr = array()) {
		if (!$FRSPackage || !is_object($FRSPackage)) {
			$this->setError(_('Invalid FRS Package Object'));
			return;
		}
		if ($FRSPackage->isError()) {
			$this->setError('FRSRelease: '.$FRSPackage->getErrorMessage());
			return;
		}

		$this->FRSPackage =& $FRSPackage;

		if ($release_id) {
			parent::__construct($release_id, 'FRSRelease');
			if (!$arr || !is_array($arr)) {
				$this->fetchData($release_id);
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['package_id'] != $this->FRSPackage->getID()) {
					$this->setError('FRSPackage_id in db result does not match FRSPackage Object');
					$this->data_array = null;
				}
			}
		} else {
			parent::__construct();
		}
	}

	/**
	 * create - create a new release in the database.
	 *
	 * @param	string	$name		The name of the release.
	 * @param	string	$notes		The release notes for the release.
	 * @param	string	$changes	The change log for the release.
	 * @param	int	$preformatted	Whether the notes/log are preformatted with \n chars (1) true (0) false.
	 * @param	int|bool	$release_date	The unix date of the release.
	 * @param       int $status_id
	 * @param	array	$importData	Array of data to change creator, time of creation, bypass permission check and do not send notification like:
	 *					array('user' => 127, 'time' => 1234556789, 'nopermcheck' => 1, 'nonotice' => 1)
	 * @return	bool	success.
	 */
	function create($name, $notes, $changes, $preformatted, $release_date = false, $status_id = 1, $importData = array()) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSRelease Name Must Be At Least 3 Characters'));
			return false;
		}

		if ($preformatted) {
			$preformatted = 1;
		} else {
			$preformatted = 0;
		}

		if (isset($importData['user'])) {
			$userid = $importData['user'];
		} else {
			$userid = user_getid();
		}

		if (!isset($importData['nopermcheck']) || (isset($importData['nopermcheck']) && !$importData['nopermcheck'])) {
			if (!forge_check_perm_for_user(user_get_object($userid), 'frs', $this->FRSPackage->getID(), 'release')) {
				$this->setPermissionDeniedError();
				return false;
			}
		}

		if (!$release_date || !isset($importData['time'])) {
			$release_date = time();
		} else {
			if (isset($importData['time'])) {
				$release_date = $importData['time'];
			}
		}
		$res = db_query_params('SELECT * FROM frs_release WHERE package_id=$1 AND name=$2',
					array ($this->FRSPackage->getID(),
						   htmlspecialchars($name)));
		if (db_numrows($res)) {
			$this->setError(_('Error Adding Release: ')._('Name Already Exists'));
			return false;
		}

		db_begin();
		$result = db_query_params('INSERT INTO frs_release(package_id,notes,changes,preformatted,name,release_date,released_by,status_id) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					array($this->FRSPackage->getID(),
						htmlspecialchars($notes),
						htmlspecialchars($changes),
						$preformatted,
						htmlspecialchars($name),
						$release_date,
						$userid,
						$status_id));
		if (!$result) {
			$this->setError(_('Error Adding Release: ').db_error());
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
			if ($status_id == 1) {
				$this->sendNotice();
			}
			return true;
		}
	}

	/**
	 * fetchData - re-fetch the data for this Release from the database.
	 *
	 * @param	int	$release_id	The release_id.
	 * @return	bool	success.
	 */
	function fetchData($release_id) {
		$res = db_query_params('SELECT * FROM frs_release WHERE release_id=$1 AND package_id=$2',
					array($release_id, $this->FRSPackage->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Invalid release_id'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getFRSPackage - get the FRSPackage object this release is associated with.
	 *
	 * @return	object	The FRSPackage object.
	 */
	function &getFRSPackage() {
		return $this->FRSPackage;
	}

	/**
	 * getID - get this release_id.
	 *
	 * @return	int	The id of this release.
	 */
	function getID() {
		return $this->data_array['release_id'];
	}

	/**
	 * getName - get the name of this release.
	 *
	 * @return	string	The name of this release.
	 */
	function getName() {
		return $this->data_array['name'];
	}

	/**
	 * getFileName - get the filename of this release.
	 *
	 * @return	string	The filename of this release.
	 */
	function getFileName() {
		return util_secure_filename($this->data_array['name']);
	}

	/**
	 * getStatus - get the status of this release.
	 *
	 * @return	int	The status.
	 */
	function getStatus() {
		return $this->data_array['status_id'];
	}

	/**
	 * getStatusName - get the status name of this release based on his status_id.
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
	 * getNotes - get the release notes of this release.
	 *
	 * @return	string	The release notes.
	 */
	function getNotes() {
		return $this->data_array['notes'];
	}

	/**
	 * getChanges - get the changelog of this release.
	 *
	 * @return	string	The changelog.
	 */
	function getChanges() {
		return $this->data_array['changes'];
	}

	/**
	 * getPreformatted - get the preformatted option of this release.
	 *
	 * @return	bool	preserve_formatting.
	 */
	function getPreformatted() {
		return $this->data_array['preformatted'];
	}

	/**
	 * getReleaseDate - get the releasedate of this release.
	 *
	 * @return	int	The release date in unix time.
	 */
	function getReleaseDate() {
		return $this->data_array['release_date'];
	}
	
	/**
	 * setSendNotice - sets if the notice email should be send
	 * @param $value true/false
	 */
	function setSendNotice($value) {
		$this->send_notice = $value;
	}
	
	/**
	 * getSendNotice - get if the notice email should be send
	 * @return true/false
	 */
	function getSendNotice() {
		return $this->send_notice;
	}

	/**
	 * sendNotice - the logic to send an email notice for a release.
	 *
	 * @return	bool	success.
	 */
	function sendNotice() {
		$arr =& $this->FRSPackage->getMonitorIDs();
		$project_adresses = $this->FRSPackage->Group->getFRSEmailAddress();

		$subject = sprintf(_('[%1$s Release] %2$s'),
					$this->FRSPackage->Group->getUnixName(),
					$this->FRSPackage->getName());
		$text = sprintf(_('Project %1$s (%2$s) has released a new version of package “%3$s”.'),
										$this->FRSPackage->Group->getPublicName(),
										$this->FRSPackage->Group->getUnixName(),
										$this->FRSPackage->getName())
							. "\n\n"
							. _('Release Notes')._(':')
							. "\n\n"
							. $this->getNotes()
							. "\n\n"
							. _('Change Log')._(':')
							. "\n\n"
							. $this->getChanges()
							. "\n\n"
							. _('You can download it by following this link')._(':')
							. "\n\n"
							. util_make_url('/frs/?group_id='.$this->FRSPackage->Group->getID().'&release_id='.$this->getID())
							. "\n\n"
							. sprintf(_('You receive this email because you requested to be notified when new '
										. 'versions of this package were released. If you don\'t wish to be '
										. 'notified in the future, please login to %s and click this link:'),
										forge_get_config('forge_name'))
							. "\n\n"
							. util_make_url('/frs/monitor.php?filemodule_id='.$this->FRSPackage->getID()."&group_id=".$this->FRSPackage->Group->getID()."&stop=1");
		if (count($arr) || strlen($project_adresses) > 0) {
			util_handle_message(array_unique($arr), $subject, $text, $project_adresses);
		}
	}

	/**
	 * newFRSFile - generates a FRSFile (allows overloading by subclasses)
	 *
	 * @param	string	FRS file identifier
	 * @param	array	fetched data from the DB
	 * @return	FRSFile	new FRSFile object.
	 */
	protected function newFRSFile($file_id, $data) {
		return new FRSFile($this, $file_id, $data);
	}

	/**
	 * getFiles - gets all the file objects for files in this release.
	 *
	 * @return	array	Array of FRSFile Objects.
	 */
	function &getFiles() {
		if (!is_array($this->release_files) || count($this->release_files) < 1) {
			$this->release_files = array();
			$res = db_query_params('SELECT * FROM frs_file_vw WHERE release_id=$1',
						array($this->getID())) ;
			while ($arr = db_fetch_array($res)) {
				$this->release_files[$arr['file_id']] = $this->newFRSFile($arr['file_id'], $arr);
			}
		}
		return $this->release_files;
	}

	function hasFiles() {
		if ($this->files_count != null) {
			return $this->files_count;
		}
		$res = db_query_params('select count(file_id) as files_count from frs_file where release_id = $1', array($this->getID()));
		if (db_numrows($res) >= 1) {
			$row = db_fetch_array($res);
			$this->files_count = $row['files_count'];
		}
		return $this->files_count;
	}

	/**
	 * delete - delete this release and all its related data.
	 *
	 * @param	bool	$sure		I'm Sure.
	 * @param	bool	$really_sure	I'm REALLY sure.
	 * @return	bool	true/false;
	 */
	function delete($sure, $really_sure) {
		if (!$sure || !$really_sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if (!forge_check_perm('frs', $this->FRSPackage->getID(), 'release')) {
			$this->setPermissionDeniedError();
			return false;
		}
		$f =& $this->getFiles();

		while($file = current($f)) {
			if (!is_object($file) || $file->isError() || !$file->delete()) {
				$this->setError(_('File Error')._(': ').$file->getName()._(': ').$file->getErrorMessage());
				return false;
			}
			next($f);
		}
		$dir=forge_get_config('upload_dir').'/'.
			$this->FRSPackage->Group->getUnixName() . '/' .
			$this->FRSPackage->getFileName().'/'.
			$this->getFileName().'/';

		// double-check we're not trying to remove root dir
		if (util_is_root_dir($dir)) {
			$this->setError(_('Release delete error')._(': ')._('trying to delete root dir'));
			return false;
		}
		$this->FRSPackage->deleteReleaseFilesAsZip($this->getID());
		rmdir($dir);

		db_query_params('DELETE FROM frs_release WHERE release_id=$1 AND package_id=$2',
				array ($this->getID(),
					$this->FRSPackage->getID()));
		return true;
	}

	/**
	 * update - update a new release in the database.
	 *
	 * @param	int	The status of this release from the frs_status table.
	 * @param	string	The name of the release.
	 * @param	string	The release notes for the release.
	 * @param	string	The change log for the release.
	 * @param	int	Whether the notes/log are preformatted with \n chars (1) true (0) false.
	 * @param	int	The unix date of the release.
	 * @return	bool success.
	 */
	function update($status, $name, $notes, $changes, $preformatted, $release_date) {
		if (strlen($name) < 3) {
			$this->setError(_('FRSRelease Name Must Be At Least 3 Characters'));
			return false;
		}

		if (!forge_check_perm('frs', $this->FRSPackage->getID(), 'release')) {
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
				$this->setError(_('Error On Update')._(': ')._('Name Already Exists'));
				return false;
			}
		}
		db_begin();
		$res = db_query_params('UPDATE frs_release SET name=$1,status_id=$2,notes=$3,
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
						   $this->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$this->setError(_('Error On Update')._(': ').db_error());
			db_rollback();
			return false;
		}

		$oldfilename = $this->getFileName();
		if(!$this->fetchData($this->getID())){
			$this->setError(_('Error Updating Release')._(': ')._("Couldn't fetch data"));
			db_rollback();
			return false;
		}
		$newfilename = $this->getFileName();
		$olddirlocation = forge_get_config('upload_dir').'/'.$this->FRSPackage->Group->getUnixName().'/'.$this->FRSPackage->getFileName().'/'.$oldfilename;
		$newdirlocation = forge_get_config('upload_dir').'/'.$this->FRSPackage->Group->getUnixName().'/'.$this->FRSPackage->getFileName().'/'.$newfilename;

		if (($oldfilename != $newfilename) && is_dir($olddirlocation)) {
			if (is_dir($newdirlocation)) {
				$this->setError(_('Error Updating Release')._(': ')._('Directory Already Exists'));
				db_rollback();
				return false;
			} else {
				if(!rename($olddirlocation, $newdirlocation)) {
					$this->setError(_('Error Updating Release')._(': ')._("Couldn't rename dir"));
					db_rollback();
					return false;
				}
			}
		}
		db_commit();
		if ($this->hasFiles()) {
			$this->FRSPackage->createReleaseFilesAsZip($this->getID());
		}
		if ($this->getSendNotice()) {
			$this->sendNotice();
		}
		return true;
	}

	function isLinkedRoadmapRelease($roadmap_release) {
		$res = db_query_params('SELECT roadmap_id FROM frs_release_tracker_roadmap_link WHERE release_id = $1 and roadmap_release = $2',
					array($this->getID(), $roadmap_release));
		if (!$res) {
			return false;
		}
		return util_result_column_to_array($res);
	}

	function deleteLinkedRoadmap($roadmap_id, $roadmap_release) {
		db_begin();
		$res = db_query_params('DELETE FROM frs_release_tracker_roadmap_link where roadmap_id = $1 and release_id = $2 and roadmap_release = $3',
					array($roadmap_id, $this->getID(), $roadmap_release));
		if (!$res) {
			$this->setError(_('Error Delete Linked Roadmap')._(': ').db_error());
			db_rollback();
			return false;
		}
		db_commit();
		return true;
	}

	function addLinkedRoadmap($roadmap_id, $roadmap_release) {
		db_begin();
		$res = db_query_params('INSERT INTO frs_release_tracker_roadmap_link (roadmap_id, release_id, roadmap_release) VALUES ($1, $2, $3)',
					array($roadmap_id, $this->getID(), $roadmap_release));
		if (!$res) {
			$this->setError(_('Error Adding Linked Roadmap')._(': ').db_error());
			db_rollback();
			return false;
		}
		db_commit();
		return true;
	}

	function getLinkedRoadmaps() {
		$roadmaps = array();
		$res = db_query_params('SELECT roadmap_id, roadmap_release FROM frs_release_tracker_roadmap_link WHERE release_id = $1',
					array($this->getID()));
		if (!$res) {
			return false;
		}
		while ($arr = db_fetch_array($res)) {
			$roadmaps[$arr[0]][] = $arr[1];
		}
		return $roadmaps;
	}

	function getPermalink() {
		return '/frs/r_follow.php/'.$this->getID();
	}

	/**
	 * castVote - Vote on this frs release item or retract the vote
	 * @param	bool	$value	true to cast, false to retract
	 * @return	bool	success (false sets error message)
	 */
	function castVote($value = true) {
		if (!($uid = user_getid()) || $uid == 100) {
			$this->setMissingParamsError(_('User ID not passed'));
			return false;
		}
		if (!$this->canVote()) {
			$this->setPermissionDeniedError();
			return false;
		}
		$has_vote = $this->hasVote($uid);
		if ($has_vote == $value) {
			/* nothing changed */
			return true;
		}
		if ($value) {
			$res = db_query_params('INSERT INTO frs_release_votes (release_id, user_id) VALUES ($1, $2)',
						array($this->getID(), $uid));
		} else {
			$res = db_query_params('DELETE FROM frs_release_votes WHERE release_id = $1 AND user_id = $2',
						array($this->getID(), $uid));
		}
		if (!$res) {
			$this->setError(db_error());
			return false;
		}
		return true;
	}

	/**
	 * hasVote - Check if a user has voted on this frs release item
	 *
	 * @param	int|bool	$uid	user ID (default: current user)
	 * @return	bool	true if a vote exists
	 */
	function hasVote($uid = false) {
		if (!$uid) {
			$uid = user_getid();
		}
		if (!$uid || $uid == 100) {
			return false;
		}
		$res = db_query_params('SELECT * FROM frs_release_votes WHERE release_id = $1 AND user_id = $2',
					array($this->getID(), $uid));
		return (db_numrows($res) == 1);
	}

	/**
	 * getVotes - get number of valid cast and potential votes
	 *
	 * @return	array|bool	(votes, voters, percent)
	 */
	function getVotes() {
		if ($this->votes !== false) {
			return $this->votes;
		}

		$lvoters = $this->getVoters();
		unset($lvoters[0]);	/* just in case */
		unset($lvoters[100]);	/* need users */
		if (($numvoters = count($lvoters)) < 1) {
			$this->votes = array(0, 0, 0);
			return $this->votes;
		}

		$res = db_query_params('SELECT COUNT(*) AS count FROM frs_release_votes WHERE release_id = $1 AND user_id = ANY($2)',
					array($this->getID(), db_int_array_to_any_clause($lvoters)));
		$db_count = db_fetch_array($res);
		$numvotes = $db_count['count'];

		/* check for invalid values */
		if ($numvotes < 0 || $numvoters < $numvotes) {
			$this->votes = array(-1, -1, 0);
		} else {
			$this->votes = array($numvotes, $numvoters, (int)($numvotes * 100 / $numvoters + 0.5));
		}
		return $this->votes;
	}

	/**
	 * canVote - check whether the current user can vote on
	 *		items in this frs release
	 *
	 * @return	bool	true if they can
	 */
	function canVote() {
		if (in_array(user_getid(), $this->getVoters())) {
			return true;
		}
		return false;
	}

	/**
	 * getVoters - get IDs of users that may vote on this frs_release
	 *
	 * @return	array	list of user IDs
	 */
	function getVoters() {
		if ($this->voters !== false) {
			return $this->voters;
		}

		$this->voters = array();
		if (($engine = RBACEngine::getInstance())
			&& ($lvoters = $engine->getUsersByAllowedAction('frs', $this->getID(), 'read'))
			&& (count($lvoters) > 0)) {
			foreach ($lvoters as $voter) {
				$voter_id = $voter->getID();
				$this->voters[$voter_id] = $voter_id;
			}
		}
		return $this->voters;
	}
}
