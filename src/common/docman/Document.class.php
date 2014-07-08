<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright 2011-2013, Franck Villaume - TrivialDev
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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
require_once $gfcommon.'docman/Parsedata.class.php';
require_once $gfcommon.'docman/DocumentManager.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';
require_once $gfcommon.'docman/DocumentStorage.class.php';

class Document extends Error {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array	$data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * Constructor.
	 *
	 * @param	$Group
	 * @param	bool	$docid
	 * @param	bool	$arr
	 * @internal	param	\The $object Group object to which this document is associated.
	 * @internal	param	\The $int docid.
	 * @internal	param	\The $array associative array of data.
	 * @return	\Document
	 */
	function __construct(&$Group, $docid = false, $arr = false) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('Document:: '. $Group->getErrorMessage());
			return;
		}
		$this->Group =& $Group;

		if ($docid) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($docid)) {
					return;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Document:: '. _('group_id in db result does not match Group Object'));
					$this->data_array = null;
					return;
				}
			}
			if (!$this->isPublic()) {
				$perm =& $this->Group->getPermission();

				if (!$perm || !is_object($perm) || !$perm->isDocEditor()) {
					$this->setPermissionDeniedError();
					$this->data_array = null;
					return;
				}
			}
		}
	}

	/**
	 * create - use this function to create a new entry in the database.
	 *
	 * @param	string	$filename	The filename of this document. Can be a URL.
	 * @param	string	$filetype	The filetype of this document. If filename is URL, this should be 'URL';
	 * @param	string	$data		The absolute path file itself.
	 * @param	int	$doc_group	The doc_group id of the doc_groups table.
	 * @param	string	$title		The title of this document.
	 * @param	string	$description	The description of this document.
	 * @param	int	$stateid	The state id of the document. At creation, cannot be deleted status.
	 * @return	bool	success.
	 */
	function create($filename, $filetype, $data, $doc_group, $title, $description, $stateid = 0) {
		if (strlen($title) < 5) {
			$this->setError(_('Title Must Be At Least 5 Characters'));
			return false;
		}
		if (strlen($description) < 10) {
			$this->setError(_('Document Description Must Be At Least 10 Characters'));
			return false;
		}

		$user_id = ((session_loggedin()) ? user_getid() : 100);

		$doc_initstatus = '3';
		$perm =& $this->Group->getPermission();
		if ($perm && is_object($perm) && $perm->isDocEditor()) {
			if ($stateid && $stateid != 2) {
				$doc_initstatus = $stateid;
			} else {
				$doc_initstatus = '1';
			}
		}

		$result = db_query_params('SELECT filename, doc_group from docdata_vw
						where filename = $1
						and doc_group = $2
						and stateid = $3',
				array($filename, $doc_group, $doc_initstatus));

		if (!$result || db_numrows($result) > 0) {
			$dg = new DocumentGroup($this->getGroup(), $doc_group);
			$this->setError(_('Document already published in this folder').' '.$dg->getPath());
			return false;
		}

		$result = db_query_params('SELECT title FROM docdata_vw where title = $1 AND doc_group = $2',
			array($title, $doc_group));
		if (!$result || db_numrows($result) > 0) {
			$dg = new DocumentGroup($this->getGroup(), $doc_group);
			$this->setError(_('Document already published in this folder').' '.$dg->getPath());
			return false;
		}

		// If $filetype is "text/plain", $body convert UTF-8 encoding.
		if (strcasecmp($filetype, "text/plain") === 0 &&
			function_exists('mb_convert_encoding') &&
			function_exists('mb_detect_encoding')) {
			$data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
		}

		$filesize = filesize($data);
		if (!$filesize) { $filesize = 0; }

		// key words for in-document search
		if ($this->Group->useDocmanSearch() && $filesize) {
			$kw = new Parsedata();
			$kwords = $kw->get_parse_data($data, htmlspecialchars($title), htmlspecialchars($description), $filetype, $filename);
		} else {
			$kwords ='';
		}

		db_begin();
		$result = db_query_params('INSERT INTO doc_data (group_id, title, description, createdate, doc_group,
						stateid, filename, filetype, filesize, data_words, created_by)
						VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)',
						array($this->Group->getId(),
							htmlspecialchars($title),
							htmlspecialchars($description),
							time(),
							$doc_group,
							$doc_initstatus,
							$filename,
							$filetype,
							$filesize,
							$kwords,
							$user_id)
					);

		$docid = db_insertid($result, 'doc_data', 'docid');
		if ($filesize) {
			if (is_file($data)) {
				if (!DocumentStorage::instance()->store($docid, $data)) {
					DocumentStorage::instance()->rollback();
					db_rollback();
					$this->setError(DocumentStorage::instance()->getErrorMessage());
					return false;
				}
			} else {
				$this->setError(_('Error Adding Document:').' '._('Not a file').' '.$filename);
				db_rollback();
				return false;
			}
		}

		if (!$result || !$docid) {
			$this->setError(_('Error Adding Document:').' '.db_error().$result);
			if ($filesize) {
				DocumentStorage::instance()->rollback();
			}
			db_rollback();
			return false;
		}

		if (!$this->fetchData($docid)) {
			$this->setError(_('Error fetching Document'));
			if ($filesize) {
				DocumentStorage::instance()->rollback();
			}
			db_rollback();
			return false;
		}

		if ($perm->isDocEditor()) {
			$localDg = new DocumentGroup($this->Group, $doc_group);
			if (!$localDg->update($localDg->getName(), $localDg->getParentID(), 1)) {
				$this->setError(_('Error updating document group:').$localDg->getErrorMessage());
				if ($filesize) {
					DocumentStorage::instance()->rollback();
				}
				db_rollback();
				return false;
			}
		}
		$this->sendNotice(true);
		$this->SendApprovalNotice();
		db_commit();
		if ($filesize) {
			DocumentStorage::instance()->commit();
		}
		return true;
	}

	/**
	 * fetchData() - re-fetch the data for this document from the database.
	 *
	 * @param	int	$docid	The document id.
	 * @return	boolean	success
	 */
	function fetchData($docid) {
		$res = db_query_params('SELECT * FROM docdata_vw WHERE docid=$1 AND group_id=$2',
					array($docid, $this->Group->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Document: Invalid docid'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getGroup - get the Group object this Document is associated with.
	 *
	 * @return	Object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getID - get this docid.
	 *
	 * @return	int	The docid.
	 */
	function getID() {
		return $this->data_array['docid'];
	}

	/**
	 * getName - get the name of this document.
	 *
	 * @return	string	The name of this document.
	 */
	function getName() {
		return $this->data_array['title'];
	}

	/**
	 * getDescription - the description of this document.
	 *
	 * @return	string	The description.
	 */
	function getDescription() {
		return $this->data_array['description'];
	}

	/**
	 * isURL - whether this document is a URL and not a local file.
	 *
	 * @return	boolean	is_url.
	 */
	function isURL() {
		return ($this->data_array['filetype'] == 'URL');
	}

	/**
	 * isText - whether this document is a text document and not a binary one.
	 *
	 * @return	boolean	is_text.
	 */
	function isText() {
		$doctype = $this->data_array['filetype'];
		if (preg_match('|^text/|i', $doctype)) { // text plain, text html, text x-patch, etc
			return true;
		}
		return false;
	}

	/**
	 * isHtml - whether this document is a html document.
	 *
	 * @return	boolean	is_html.
	 */
	function isHtml() {
		$doctype = $this->data_array['filetype'];
		if (preg_match('/html/i',$doctype)) {
			return true;
		}
		return false;
	}

	/**
	 * isPublic - whether this document is available to the general public.
	 *
	 * @return	boolean	is_public.
	 */
	function isPublic() {
		return (($this->data_array['stateid'] == 1) ? true : false);
	}

	/**
	 * getStateID - get this stateid.
	 *
	 * @return	int	The stateid.
	 */
	function getStateID() {
		return $this->data_array['stateid'];
	}

	/**
	 * getStateName - the statename of this document.
	 *
	 * @return	string	The statename.
	 */
	function getStateName() {
		return $this->data_array['state_name'];
	}

	/**
	 * getDocGroupID - get this doc_group_id.
	 *
	 * @return	int	The doc_group_id.
	 */
	function getDocGroupID() {
		return $this->data_array['doc_group'];
	}

	/**
	 * getDocGroupName - the doc_group_name of this document.
	 *
	 * @return	string	The docgroupname.
	 */
	function getDocGroupName() {
		return $this->data_array['group_name'];
	}

	/**
	 * getCreatorID - get this creator's user_id.
	 *
	 * @return	int	The user_id.
	 */
	function getCreatorID() {
		return $this->data_array['created_by'];
	}

	/**
	 * getCreatorUserName - the unix name of the person who created this document.
	 *
	 * @return	string	The unix name of the creator.
	 */
	function getCreatorUserName() {
		return $this->data_array['user_name'];
	}

	/**
	 * getCreatorRealName - the real name of the person who created this document.
	 *
	 * @return	string	The real name of the creator.
	 */
	function getCreatorRealName() {
		return $this->data_array['realname'];
	}

	/**
	 * getCreatorEmail - the email of the person who created this document.
	 *
	 * @return	string	The email of the creator.
	 */
	function getCreatorEmail() {
		return $this->data_array['email'];
	}

	/**
	 * getFileName - the filename of this document.
	 *
	 * @return	string	The filename.
	 */
	function getFileName() {
		return $this->data_array['filename'];
	}

	/**
	 * getFileType - the filetype of this document.
	 *
	 * @return	string	The filetype.
	 */
	function getFileType() {
		return $this->data_array['filetype'];
	}

	/**
	 * getFileData - the filedata of this document.
	 *
	 * @param	boolean	update the download flag or not. default is true
	 * @return	string	The filedata.
	 */
	function getFileData($download = true) {
		if ($download)
			$this->downloadUp();

		return file_get_contents($this->getFilePath());
	}

	/**
	 * getFilePath - the filepath of this document.
	 *
	 * @return	string	The file where the file is stored.
	 */
	function getFilePath() {
		return DocumentStorage::instance()->get($this->getID());
	}

	/**
	* getFileSize - Return the size of the document
	*
	* @return	int	The file size
	*/
	function getFileSize() {
		return $this->data_array['filesize'];
	}

	/**
	 * getUpdated - get the time this document was updated.
	 *
	 * @return	int	The epoch date this document was updated.
	 */
	function getUpdated() {
		return $this->data_array['updatedate'];
	}

	/**
	 * getDownload - get the number of views of this document.
	 *
	 * @return	int	the number of views
	 */
	function getDownload() {
		return $this->data_array['download'];
	}

	/**
	 * getCreated - get the time this document was created.
	 *
	 * @return	int	The epoch date this document was created.
	 */
	function getCreated() {
		return $this->data_array['createdate'];
	}

	/**
	 * getLocked - get the lock status of this document.
	 *
	 * @return	int	The lock status of this document.
	 */
	function getLocked() {
		return $this->data_array['locked'];
	}

	/**
	 * getLockdate - get the lock time of this document.
	 *
	 * @return	int	The lock time of this document.
	 */
	function getLockdate() {
		return $this->data_array['lockdate'];
	}

	/**
	 * getLockedBy - get the user id who set lock on this document.
	 *
	 * @return	int	The user id who set lock on this document.
	 */
	function getLockedBy() {
		return $this->data_array['locked_by'];
	}

	/**
	 * getReservedBy - get the owner of the reversed status of this document.
	 *
	 * @return	int	The owner of the reversed status of this document.
	 */
	function getReservedBy() {
		return $this->data_array['reserved_by'];
	}

	/**
	 * getReserved - get the reversed status of this document.
	 *
	 * @return	int	The reversed status of this document.
	 */
	function getReserved() {
		return $this->data_array['reserved'];
	}

	/**
	 * getMonitoredUserEmailAddress - get the email addresses of users who monitor this file
	 *
	 * @return	string	The list of emails comma separated
	 */
	function getMonitoredUserEmailAddress() {
		$result = db_query_params('select users.email from users,docdata_monitored_docman where users.user_id = docdata_monitored_docman.user_id and docdata_monitored_docman.doc_id = $1', array ($this->getID()));
		if (!$result || db_numrows($result) < 1) {
			return NULL;
		} else {
			$values = '';
			$comma = '';
			$i = 0;
			while ($arr = db_fetch_array($result)) {
				if ( $i > 0 )
					$comma = ',';

				$values .= $comma.$arr['email'];
				$i++;
			}
		}
		return $values;
	}

	/**
	 * isMonitoredBy - get the monitored status of this document for a specific user id.
	 *
	 * @param	string	$userid
	 * @internal	param	\User $int ID
	 * @return	boolean	true if monitored by this user
	 */
	function isMonitoredBy($userid = 'ALL') {
		if ( $userid == 'ALL' ) {
			$condition = '';
		} else {
			$condition = 'user_id='.$userid.' AND';
		}
		$result = db_query_params('SELECT * FROM docdata_monitored_docman WHERE '.$condition.' doc_id=$1',
						array($this->getID()));

		if (!$result || db_numrows($result) < 1)
			return false;

		return true;
	}

	/**
	 * removeMonitoredBy - remove this document for a specific user id for monitoring.
	 *
	 * @param	int	User ID
	 * @return	boolean	true if success
	 */
	function removeMonitoredBy($userid) {
		$result = db_query_params('DELETE FROM docdata_monitored_docman WHERE doc_id=$1 AND user_id=$2',
						array($this->getID(), $userid));

		if (!$result) {
			$this->setError(_('Unable To Remove Monitor').' : '.db_error());
			return false;
		}
		return true;
	}

	/**
	 * addMonitoredBy - add this document for a specific user id for monitoring.
	 *
	 * @param	int	User ID
	 * @return	boolean	true if success
	 */
	function addMonitoredBy($userid) {
		$result = db_query_params('SELECT * FROM docdata_monitored_docman WHERE user_id=$1 AND doc_id=$2',
						array($userid, $this->getID()));

		if (!$result || db_numrows($result) < 1) {
			$result = db_query_params('INSERT INTO docdata_monitored_docman (doc_id,user_id) VALUES ($1,$2)',
							array($this->getID(), $userid));

			if (!$result) {
				$this->setError(_('Unable To Add Monitor').' : '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
	 * clearMonitor - remove all entries of monitoring for this document.
	 *
	 * @return	boolean	true if success.
	 */
	function clearMonitor() {
		$result = db_query_params('DELETE FROM docdata_monitored_docman WHERE doc_id = $1',
					array($this->getID()));
		if (!$result) {
			$this->setError(_('Unable To Clear Monitor').' : '.db_error());
			return false;
		}
		return true;
	}

	/**
	 * setState - set the stateid of the document.
	 *
	 * @param	int	The state id of the doc_states table.
	 * @return	boolean	success or not.
	 */
	function setState($stateid) {
		return $this->setValueinDB('stateid', $stateid);
	}


	/**
	 * setDocGroupID - set the doc_group of the document.
	 *
	 * @param	int	The group_id of this document.
	 * @return	boolean	success or not.
	 */
	function setDocGroupID($newdocgroupid) {
		return $this->setValueinDB('doc_group', $newdocgroupid);
	}

	/**
	 * setLock - set the locking status of the document.
	 *
	 * @param	$stateLock
	 * @param	time		the epoch time.
	 * @param	int		$thistime
	 * @internal	param		\The $int status of the lock.
	 * @internal	param		\The $int userid who set the lock.
	 * @return	boolean		success or not.
	 */
	function setLock($stateLock, $userid = NULL, $thistime = 0) {
		$res = db_query_params('UPDATE doc_data SET
					locked = $1,
					locked_by = $2,
					lockdate = $3
					WHERE group_id = $4
					AND docid = $5',
					array($stateLock,
						$userid,
						$thistime,
						$this->Group->getID(),
						$this->getID())
					);
		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(_('Document lock failed').' '.db_error());
			return false;
		}
		$this->data_array['locked'] = $stateLock;
		$this->data_array['locked_by'] = $userid;
		$this->data_array['lockdate'] = $thistime;
		return true;
	}

	/**
	 * setReservedBy - set the reserved status of the document and the owner
	 *
	 * @param	int	$statusReserved	The status of the reserved
	 * @param	int	$idReserver	The ID of the owner : by default : noone
	 * @return	boolean	success
	 */
	function setReservedBy($statusReserved, $idReserver = NULL) {
		$res = db_query_params('UPDATE doc_data SET
					reserved = $1,
					reserved_by = $2
					WHERE group_id = $3
					AND docid = $4',
					array($statusReserved,
						$idReserver,
						$this->Group->getID(),
						$this->getID())
					);
		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(_('Document reservation failed').' '.db_error());
			return false;
		}
		$this->sendNotice(false);
		return true;
	}

	/**
	 * getFileTypeImage - return the file image for icon
	 *
	 * @return	string	the file image name
	 * @access	public
	 */
	function getFileTypeImage() {
		switch ($this->getFileType()) {
			case "image/png":
			case "image/jpeg":
			case "image/gif":
			case "image/tiff":
			case "image/vnd.microsoft.icon":
			case "image/svg+xml": {
				$image = 'docman/file_type_image.png';
				break;
			}
			case "audio/x-wav":
			case "audio/x-vorbis+ogg":
			case "audio/mpeg":
			case "audio/x-ms-wma":
			case "audio/vnd.rn-realaudio": {
				$image = "docman/file_type_sound.png";
				break;
			}
			case "application/pdf": {
				$image = 'docman/file_type_pdf.png';
				break;
			}
			case "text/html":
			case "URL": {
				$image = 'docman/file_type_html.png';
				break;
			}
			case "text/plain":
			case "text/x-php":
			case "application/xml":
			case "text/x-c":
			case "text/x-diff":
			case "text/x-shellscript": {
				$image = 'docman/file_type_plain.png';
				break;
			}
			case "application/msword":
			case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
			case "application/vnd.oasis.opendocument.text": {
				$image = 'docman/file_type_writer.png';
				break;
			}
			case "application/vnd.ms-excel":
			case "application/vnd.oasis.opendocument.spreadsheet":
			case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet": {
				$image = 'docman/file_type_spreadsheet.png';
				break;
			}
			case "application/vnd.oasis.opendocument.presentation":
			case "application/vnd.ms-powerpoint":
			case "application/vnd.ms-office":
			case "application/vnd.openxmlformats-officedocument.presentationml.presentation": {
				$image = 'docman/file_type_presentation.png';
				break;
			}
			case "application/zip":
			case "application/x-tar":
			case "application/x-rpm":
			case "application/x-rar-compressed":
			case "application/x-bzip2":
			case "application/x-gzip":
			case "application/x-lzip":
			case "application/x-compress":
			case "application/x-7z-compressed":
			case "application/x-gtar":
			case "application/x-stuffitx":
			case "application/x-lzx":
			case "application/x-lzh":
			case "application/x-gca-compressed":
			case "application/x-apple-diskimage":
			case "application/x-dgc-compressed":
			case "application/x-dar":
			case "application/x-cfs-compressed":
			case "application/vnd.ms-cab-compressed":
			case "application/x-alz-compressed":
			case "application/x-astrotite-afa":
			case "application/x-ace-compressed":
			case "application/x-cpio":
			case "application/x-shar":
			case "application/x-xz": {
				$image = 'docman/file_type_archive.png';
				break;
			}
			default: {
				$image = 'docman/file_type_unknown.png';
			}
		}
		return $image;
	}

	/**
	 * update - use this function to update an existing entry in the database.
	 *
	 * @param	string	$filename	The filename of this document. Can be a URL.
	 * @param	string	$filetype	The filetype of this document. If filename is URL, this should be 'URL';
	 * @param	string	$data		The contents of this document.
	 * @param	int	$doc_group	The doc_group id of the doc_groups table.
	 * @param	string	$title		The title of this document.
	 * @param	string	$description	The description of this document.
	 * @param	int	$stateid	The state id of the doc_states table.
	 * @return	boolean	success.
	 */
	function update($filename, $filetype, $data, $doc_group, $title, $description, $stateid) {

		$perm =& $this->Group->getPermission();
		if (!$perm || !is_object($perm) || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}

		$user = session_get_user();
		if ($this->getLocked() && ($this->getLockedBy() != $user->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}

		if (strlen($title) < 5) {
			$this->setError(_('Title Must Be At Least 5 Characters'));
			return false;
		}

		if (strlen($description) < 10) {
			$this->setError(_('Document Description Must Be At Least 10 Characters'));
			return false;
		}

		/* TODO : NEED REAL CHECK */
		if ($filename) {
			$result = db_query_params('SELECT filename, doc_group FROM docdata_vw WHERE filename = $1 AND doc_group = $2 AND stateid = $3 AND docid != $4',
						array($filename, $doc_group, $stateid, $this->getID()));
			if (!$result || db_numrows($result) > 0) {
				$this->setError(_('Document already published in this folder'));
				return false;
			}
		}

		db_begin();
		$res = db_query_params('UPDATE doc_data SET
					title=$1,
					description=$2,
					stateid=$3,
					doc_group=$4,
					filetype=$5,
					filename=$6,
					updatedate=$7,
					locked=$8,
					locked_by=$9
					WHERE group_id=$10
					AND docid=$11',
					array(htmlspecialchars($title),
						htmlspecialchars($description),
						$stateid,
						$doc_group,
						$filetype,
						$filename,
						time(),
						0,
						NULL,
						$this->Group->getID(),
						$this->getID())
					);

		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(db_error());
			db_rollback();
			return false;
		}

		$localDg = new DocumentGroup($this->Group, $doc_group);
		if (!$localDg->update($localDg->getName(), $localDg->getParentID(), 1)) {
			$this->setOnUpdateError(_('Error updating document group:').$localDg->getErrorMessage());
			db_rollback();
			return false;
		}

		if (filesize($data)) {
			// key words for in-document search
			if ($this->Group->useDocmanSearch()) {
				$kw = new Parsedata();
				$kwords = $kw->get_parse_data($data, htmlspecialchars($title), htmlspecialchars($description), $filetype, $filename);
			} else {
				$kwords = '';
			}

			$res = db_query_params('UPDATE doc_data SET filesize=$1, data_words=$2 WHERE group_id=$3 AND docid=$4',
						array(filesize($data),
							$kwords,
							$this->Group->getID(),
							$this->getID())
						);

			if (!$res || db_affected_rows($res) < 1) {
				$this->setOnUpdateError(db_error());
				db_rollback();
				return false;
			}

			DocumentStorage::instance()->delete($this->getID())->commit();
			DocumentStorage::instance()->store($this->getID(), $data);
		}
		db_commit();
		$this->fetchData($this->getID());
		$this->sendNotice(false);
		return true;
	}

	/**
	 * sendNotice - Notifies of document submissions
	 *
	 * @param	boolean	true = new document (default value)
	 * @return	bool
	 */
	function sendNotice($new = true) {
		$BCC = $this->Group->getDocEmailAddress();
		if ($this->isMonitoredBy('ALL')) {
			$BCC .= $this->getMonitoredUserEmailAddress();
		}
		$dg = new DocumentGroup($this->Group, $this->getDocGroupID());
		if ($dg->isMonitoredBy('ALL')) {
			$BCC .= $dg->getMonitoredUserEmailAddress();
		}
		if (strlen($BCC) > 0) {
			$session = session_get_user();
			if ($new) {
				$status = _('New Document');
			} else {
				$status = _('Updated document').' '._('by').' ' . $session->getRealName();
			}
			$subject = '['.$this->Group->getPublicName().'] '.$status.' - '.$this->getName();
			$body = _('Project')._(': ').$this->Group->getPublicName()."\n";
			$body .= _('Folder')._(': ').$this->getDocGroupName()."\n";
			$body .= _('Document Title')._(': ').$this->getName()."\n";
			$body .= _('Document description')._(': ').util_unconvert_htmlspecialchars($this->getDescription())."\n";
			$body .= _('Submitter')._(': ').$this->getCreatorRealName()." (".$this->getCreatorUserName().") \n";
			$body .= "\n\n-------------------------------------------------------\n".
				_('For more info, visit:').
				"\n\n" . util_make_url('/docman/?group_id='.$this->Group->getID().'&view=listfile&dirid='.$this->getDocGroupID());

			$BCCarray = explode(',',$BCC);
			foreach ($BCCarray as $dest_email) {
				util_send_message($dest_email, $subject, $body, 'noreply@'.forge_get_config('web_host'), '', _('Docman'));
			}
		}
		return true;
	}

	/**
	 * SendApprovalNotice - send email to project admin for pending documents.
	 *
	 * @return	boolean	success.
	 */
	function SendApprovalNotice() {
		if ($this->getStateID() != 3)
			return true;

		$doc_name = $this->getName();
		$desc     = util_unconvert_htmlspecialchars( $this->getDescription() );
		$group_id = $this->Group->getID();
		$name     = $this->getCreatorRealName()." (".$this->getCreatorUserName().")";
		$bcc      = '';

		$subject="[" . forge_get_config('forge_name') ."] ".util_unconvert_htmlspecialchars($doc_name);
		$body = "\nA new document has been uploaded and waiting to be approved by you:".
		"\n".util_make_url('/docman/?group_id='.$group_id.'&view=admin').
		"\nBy: " . $name . "\n";

		$sanitizer = new TextSanitizer();
		$text = $desc;
		if (strstr($text,'<br/>') || strstr($text,'<br />')) {
			$text = preg_replace('/[\n\r]/', '', $text);
		}
		$text = $sanitizer->convertNeededTagsForEmail($text);
		$text =  preg_replace('/\[.+\](.+)\[\/.+\]/','$1',$text);
		$text = $sanitizer->convertExtendedCharsForEmail($text);
		$body .= $text;

		$extra_headers = "Return-Path: <noreply@".forge_get_config('web_host').">\n";
		$extra_headers .= "Errors-To: <noreply@".forge_get_config('web_host').">\n";
		$extra_headers .= "Sender: <noreply@".forge_get_config('web_host').">";

		$groupUsers = $this->Group->getUsers();
		$rbacEngine = RBACEngine::getInstance();
		foreach ($groupUsers as $key => $groupUser) {
			if ($rbacEngine->isActionAllowedForUser($groupUser, 'docman', $group_id, 'approve')) {
				$bcc .= $groupUser->getEmail().',';
			}
		}
		if (strlen($bcc) > 0) {
			util_send_message('',$subject,$body,"noreply@".forge_get_config('web_host'),
				$bcc,'Docman',$extra_headers);
		}
		return true;
	}

	/**
	 * delete - Delete this file
	 *
	 * @return	boolean	success
	 */
	function delete() {
		$perm =& $this->Group->getPermission();
		if (!$perm || !is_object($perm) || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}

		$result = db_query_params('DELETE FROM doc_data WHERE docid=$1',
						array($this->getID()));
		if (!$result) {
			$this->setError(_('Error Deleting Document:').' '.db_error());
			db_rollback();
			return false;
		}

		DocumentStorage::instance()->delete($this->getID())->commit();

		/** we should be able to send a notice that this doc has been deleted .... but we need to rewrite sendNotice
		 * $this->sendNotice(false);
		 * @TODO delete monitoring this file */
		return true;
	}

	/**
	 * trash - move this file to trash
	 *
	 * @return	boolean	success or not.
	 */
	function trash() {
		$this->setState('2');
		$dm = new DocumentManager($this->Group);
		$this->setDocGroupID($dm->getTrashID());
		$this->setLock(0);
		$this->setReservedBy(0);
		$this->sendNotice(false);
		$this->clearMonitor();
		return true;
	}


	/**
	 * downloadUp - insert download stats
	 *
	 */
	function downloadUp() {
		if (session_loggedin()) {
			$s =& session_get_user();
			$us = $s->getID();
		} else {
			$us=100;
		}

		$ip = getStringFromServer('REMOTE_ADDR');
		db_query_params("INSERT INTO docman_dlstats_doc (ip_address, docid, month, day, user_id) VALUES ($1, $2, $3, $4, $5)", array($ip, $this->getID(), date('Ym'), date('d'), $us));
	}

	/**
	 * setValueinDB - private function to update columns in db
	 *
	 * @param	string	$column	the column to update
	 * @param	int	$value	the value to store
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function setValueinDB($column, $value) {
		switch ($column) {
			case 'stateid':
			case 'doc_group': {
				$qpa = db_construct_qpa();
				$qpa = db_construct_qpa($qpa, 'UPDATE doc_data SET ');
				$qpa = db_construct_qpa($qpa, $column);
				$qpa = db_construct_qpa($qpa, '=$1
								WHERE group_id=$2
								AND docid=$3',
								array($value,
									$this->Group->getID(),
									$this->getID()));
				$res = db_query_qpa($qpa);
				if (!$res || db_affected_rows($res) < 1) {
					$this->setOnUpdateError(db_error().print_r($qpa));
					return false;
				}
				break;
			}
			default:
				$this->setOnUpdateError(_('wrong column name'));
				return false;
		}
		$localDg = new DocumentGroup($this->Group, $this->getDocGroupID());
		if (!$localDg->update($localDg->getName(), $localDg->getParentID(), 1)) {
			$this->setError(_('Error updating document group:').$localDg->getErrorMessage());
			return false;
		}
		$this->sendNotice(false);
		return true;
	}

	function createVersion() {

	}

	function deleteVersion() {

	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
