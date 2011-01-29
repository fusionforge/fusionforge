<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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
require_once $gfcommon.'docman/Parsedata.class.php';

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
	 * The Search engine path.
	 *
	 * @var	string	$engine_path
	 */
	var $engine_path;

	/**
	 * Constructor.
	 *
	 * @param	object	The Group object to which this document is associated.
	 * @param	int	The docid.
	 * @param	array	The associative array of data.
	 * @return	boolean	success.
	 */
	function Document(&$Group, $docid = false, $arr = false, $engine = '') {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setNotValidGroupObjectError();
			return false;
		}
		if ($Group->isError()) {
			$this->setError('Document:: '. $Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($docid) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($docid)) {
					return false;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('Document:: '. _('Group_id in db result does not match Group Object'));
					$this->data_array = null;
					return false;
				}
			}
			if (!$this->isPublic()) {
				$perm =& $this->Group->getPermission();

				if (!$perm || !is_object($perm) || !$perm->isMember()) {
					$this->setPermissionDeniedError();
					$this->data_array = null;
					return false;
				}
			}
		}
		$this->engine_path = $engine;
		return true;
	}

	/**
	 *	create - use this function to create a new entry in the database.
	 *
	 *	@param	string	The filename of this document. Can be a URL.
	 *	@param	string	The filetype of this document. If filename is URL, this should be 'URL';
	 *	@param	string	The contents of this document.
	 *	@param	int	The doc_group id of the doc_groups table.
	 *	@param	string	The title of this document.
	 *	@param	string	The description of this document.
	 *	@return	boolean	success.
	 */
	function create($filename, $filetype, $data, $doc_group, $title, $description) {
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
		// If Editor - uploaded Documents are ACTIVE
		if (session_loggedin()) {
			$perm =& $this->Group->getPermission();
			if ($perm && is_object($perm) && $perm->isDocEditor()) {
				$doc_initstatus = '1';
			}
		}

		$result = db_query_params('SELECT filename, doc_group from docdata_vw
						where filename = $1
						and doc_group = $2
						and stateid = $3',
				array($filename, $doc_group, $doc_initstatus));

		if (!$result || db_numrows($result) > 0) {
			$this->setError(_('Document already published in this directory'));
			return false;
		}

		// If $filetype is "text/plain", $body convert UTF-8 encoding.
		if (strcasecmp($filetype,"text/plain") === 0 &&
			function_exists('mb_convert_encoding') &&
			function_exists('mb_detect_encoding')) {
			$data = mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
		}
		$data1 = $data;

		// key words for in-document search
		if ($this->Group->useDocmanSearch()) {
			$kw = new Parsedata($this->engine_path);
			$kwords = $kw->get_parse_data($data1, htmlspecialchars($title), htmlspecialchars($description), $filetype);
		} else {
			$kwords ='';
		}

		$filesize = strlen($data);

		db_begin();
		$result = db_query_params('INSERT INTO doc_data (group_id,title,description,createdate,doc_group,
						stateid,filename,filetype,filesize,data_words,created_by)
						VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11)',
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
						$user_id));
		if (!$result) {
			$this->setError(_('Error Adding Document:').' '.db_error().$result);
			db_rollback();
			return false;
		}

		$docid = db_insertid($result,'doc_data','docid');

		switch ($this->Group->getStorageAPI()) {
			case 'DB': {
				$result = db_query_params('UPDATE doc_data set data = $1 where docid = $2',
								array(base64_encode($data),$docid));
				if (!$result) {
					$this->setError(_('Error Adding Document:').' '.db_error().$result);
					db_rollback();
					return false;
				}
				break;
			}
			default: {
				$this->setError(_('Error Adding Document: No Storage API'));
				db_rollback();
				return false;
			}
		}

		if (!$this->fetchData($docid)) {
			db_rollback();
			return false;
		}
		$this->sendNotice(true);
		db_commit();
		return true;
	}

	/**
	 * fetchData() - re-fetch the data for this document from the database.
	 *
	 * @param	int	The document id.
	 * @return	boolean	success
	 */
	function fetchData($docid) {
		$res = db_query_params('SELECT * FROM docdata_vw WHERE docid=$1 AND group_id=$2',
					array($docid, $this->Group->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Document:: Invalid docid'));
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
	 * @return	string	The filedata.
	 */
	function getFileData() {
		//
		//	Because this could be a large string, we only fetch if we actually need it
		//
		$res = db_query_params('SELECT data FROM doc_data WHERE docid=$1', array($this->getID()));
		return base64_decode(db_result($res, 0, 'data'));
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
	 * @param	int	User ID
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
	 * setState - set the stateid of the document.
	 *
	 * @param	int	The state id of the doc_states table.
	 * @return	boolean	success.
	 */
	function setState($stateid) {
		$res = db_query_params('UPDATE doc_data SET
					stateid=$1
					WHERE group_id=$2
					AND docid=$3',
					array($stateid,
						$this->Group->getID(),
						$this->getID())
					);
		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(db_error());
			return false;
		}
		$this->sendNotice(false);
		return true;
	}

	/**
	 * setLock - set the locking status of the document
	 *
	 * @param	int	The status of the lock
	 * @param	int	The userid who set the lock
	 * @param	time	the epoch time
	 * @return	boolean	success
	 */
	function setLock($stateLock, $userid = NULL, $thistime = 0) {
		$res = db_query_params('UPDATE doc_data SET
					locked=$1,
					locked_by=$2,
					lockdate=$3
					WHERE group_id=$4
					AND docid=$5',
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
	 * @param	int	The status of the reserved
	 * @param	int	The ID of the owner : by default : noone
	 * @return	boolean	success
	 */
	function setReservedBy($statusReserved, $idReserver = NULL) {
		$res = db_query_params('UPDATE doc_data SET
					reserved=$1,
					reserved_by=$2
					WHERE group_id=$3
					AND docid=$4',
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
			case "text/x-c": {
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
	 * @param	string	The filename of this document. Can be a URL.
	 * @param	string	The filetype of this document. If filename is URL, this should be 'URL';
	 * @param	string	The contents of this document.
	 * @param	int	The doc_group id of the doc_groups table.
	 * @param	string	The title of this document.
	 * @param	string	The description of this document.
	 * @param	int	The state id of the doc_states table.
	 * @return	boolean	success.
	 */
	function update($filename, $filetype, $data, $doc_group, $title, $description, $stateid) {
		global $LUSER;

		$perm =& $this->Group->getPermission();
		if (!$perm || !is_object($perm) || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}

		if ($this->getLockedBy() != $LUSER->getID()) {
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

		if ($filename) {
			$result = db_query_params('SELECT filename, doc_group FROM docdata_vw WHERE filename = $1 and doc_group = $2 and stateid = $3',
						array($filename, $doc_group, $stateid));
			if (!$result || db_numrows($result) > 0) {
				$this->setError(_('Document already published in this directory'));
				return false;
			}
		}

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
			return false;
		}

		if ($data) {
			$data1 = $data;

			// key words for in-document search
			if ($this->Group->useDocmanSearch()) {
				$kw = new Parsedata($this->engine_path);
				$kwords = $kw->get_parse_data($data1, htmlspecialchars($title), htmlspecialchars($description), $filetype);
			} else {
				$kwords = '';
			}

			$res = db_query_params('UPDATE doc_data SET filesize=$1, data_words=$2 WHERE group_id=$3 AND docid=$4',
						array(strlen($data),
							$kwords,
							$this->Group->getID(),
							$this->getID())
						);

			if (!$res || db_affected_rows($res) < 1) {
				$this->setOnUpdateError(db_error());
				return false;
			}

			switch ($this->Group->getStorageAPI()) {
				case 'DB': {
					$res = db_query_params('UPDATE doc_data SET data = $1 where group_id = $2 and docid = $3',
								array(base64_encode($data),
									$this->Group->getID(),
									$this->getID())
								);

					if (!$res || db_affected_rows($res) < 1) {
						$this->setOnUpdateError(db_error());
						return false;
					}
					break;
				}
				default: {
					$this->setOnUpdateError(_('No Storage API'));
					return false;
				}
			}
		}

		$this->sendNotice(false);
		return true;
	}

	/**
	 * sendNotice - Notifies of document submissions
	 *
	 * @param	boolean	true = new document (default value)
	 */
	function sendNotice ($new=true) {
		$BCC = $this->Group->getDocEmailAddress();
		if ($this->isMonitoredBy('ALL')) {
			$BCC .= $this->getMonitoredUserEmailAddress();
		}
		if (strlen($BCC) > 0) {
			if ($new) {
				$status = _('New document');
			} else {
				$status = _('Updated document');
			}
			$subject = '['.$this->Group->getPublicName().'] '.$status.' - '.$this->getName();
			$body = _('Project:').' '.$this->Group->getPublicName()."\n";
			$body .= _('Directory:').' '.$this->getDocGroupName()."\n";
			$body .= _('Document title:').' '.$this->getName()."\n";
			$body .= _('Document description:').' '.util_unconvert_htmlspecialchars($this->getDescription())."\n";
			$body .= _('Submitter:').' '.$this->getCreatorRealName()." (".$this->getCreatorUserName().") \n";
			$body .= "\n\n-------------------------------------------------------\n".
				_('For more info, visit:').
				"\n\n" . util_make_uri('/docman/?group_id='.$this->Group->getID().'&view=listfile&dirid='.$this->getDocGroupID());

			util_send_message('', $subject, $body, '', $BCC);
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

		switch ($this->Group->getStorageAPI()) {
			case 'DB': {
				break;
			}
			default: {
				$this->setError(_('Error Deleting Document: No Storage API'));
				db_rollback();
				return false;
			}
		}

		// we should be able to send a notice that this doc has been deleted .... but we need to rewrite sendNotice
		//$this->sendNotice(false);
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
