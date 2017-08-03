<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'docman/DocumentStorage.class.php';

$DOCUMENTVERSION_OBJ = array();

function &documentversion_get_object($ver_id, $docid, $group_id, $res = false) {
	global $DOCUMENTVERSION_OBJ;
	if (!isset($DOCUMENTVERSION_OBJ['_'.$ver_id.'-'.$docid.'_'])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			// data_words is not retrieve. Too much memory consumption.
			$res = db_query_params('SELECT serial_id, version, docid, current_version, title, updatedate, createdate, created_by, description, filename, filetype, filesize FROM doc_data_version WHERE version = $1 AND docid = $2',
						array($ver_id, $docid));
		}
		if (!$res || db_numrows($res) < 1) {
			$DOCUMENTVERSION_OBJ['_'.$ver_id.'-'.$docid.'_'] = false;
		} else {
			$DOCUMENTVERSION_OBJ['_'.$ver_id.'-'.$docid.'_'] = new DocumentVersion(document_get_object($docid, $group_id), $ver_id, db_fetch_array($res));
		}
	}
	return $DOCUMENTVERSION_OBJ['_'.$ver_id.'-'.$docid.'_'];
}

function &documentversion_get_object_by_serialid($serial_id, $docid, $group_id, $res = false) {
	$res = db_query_params('SELECT serial_id, version, docid, current_version, title, updatedate, createdate, created_by, description, filename, filetype, filesize FROM doc_data_version WHERE serial_id = $1 AND docid = $2',
						array($serial_id, $docid));
	if ($res && (db_numrows($res) == 1)) {
		$arr = db_fetch_array($res);
		return documentversion_get_object($arr['version'], $docid, $group_id, $res);
	}
	return false;
}

class DocumentVersion extends FFError {
	/**
	 * Associative array of data from db.
	 *
	 * @var	 array	$data_array.
	 */
	var $data_array;

	/**
	 * The Document object.
	 *
	 * @var	object	$Document.
	 */
	var $Document;

	/**
	 * @param	$Document
	 * @param	bool			$verid
	 * @param	bool			$arr
	 */
	function __construct(&$Document, $verid = false, $arr = false) {
		parent::__construct();
		if (!$Document || !is_object($Document)) {
			$this->setError(_('No Valid Document Object'));
			return;
		}
		if ($Document->isError()) {
			$this->setError(_('Document Version')._(': ').$Document->getErrorMessage());
			return;
		}
		$this->Document =& $Document;
		if ($verid) {
			if ($arr && is_array($arr) && ($arr['docid'] == $this->Document->getID()) && ($arr['version'] == $verid)) {
					$this->data_array =& $arr;
				} else {
					if (!$this->fetchData($verid)) {
						return false;
				}
			}
		}
		return true;
	}

	function getID() {
		return $this->data_array['serial_id'];
	}

	function getFileName() {
		return $this->data_array['filename'];
	}

	function getFileType() {
		return $this->data_array['filetype'];
	}

	function getFileSize() {
		return $this->data_array['filesize'];
	}

	function getTitle() {
		return $this->data_array['title'];
	}

	function getDescription() {
		return $this->data_array['description'];
	}

	function getComment() {
		return $this->data_array['vcomment'];
	}

	/**
	 * getFileData - the filedata of this document.
	 *
	 * @return	string	The filedata.
	 */
	function getFileData() {
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

	function fetchData($verid) {
		// everything but data_words. Too much memory consumption.
		$res = db_query_params('SELECT serial_id, version, docid, current_version, title, updatedate, createdate, created_by, description, filename, filetype, filesize, vcomment FROM doc_data_version WHERE version = $1 AND docid = $2',
					array($verid, $this->Document->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('DocumentVersion')._(': ')._('Invalid version id'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * create - create a new version of a document
	 *
	 * @param	int	$docid			The linked document to which we create a new version
	 * @param	string	$title			The title of the new version
	 * @param	string	$description		The description of the new version
	 * @param	int	$created_by		The user id who creates the version
	 * @param	string	$filetype		The filetype of the content
	 * @param	string	$filename		The name of the file (content)
	 * @param	int	$filesize		The size of the file (content)
	 * @param	string	$kwords			The parsed words of the file (content)
	 * @param	int	$createtimetamp		timestamp of creation of this version
	 * @param	int	$version		The version id to create. Default is 1 (the first version)
	 * @param	int	$current_version	Is it the current version? Defaut is 1 (yes)
	 * @param	string $vcomment
	 * @return	bool	true on success
	 */
	function create($docid, $title, $description, $created_by, $filetype, $filename, $filesize, $kwords, $createtimetamp, $version = 1, $current_version = 1, $vcomment = '') {
		db_begin();
		$res = db_query_params('INSERT INTO doc_data_version (docid, title, description, created_by, filetype, filename, filesize, data_words, version, current_version, createdate, vcomment)
					VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)',
					array($docid, htmlspecialchars($title), htmlspecialchars($description), $created_by, $filetype, $filename, $filesize, $kwords, $version, $current_version, $createtimetamp, htmlspecialchars($vcomment)));
		$serial_id = db_insertid($res, 'doc_data_version', 'serial_id');
		if (!$res || !$serial_id) {
			$this->setError(_('Document Version')._(': ')._('Cannot create version.').' '.db_error());
			db_rollback();
			return false;
		}
		if ($current_version) {
			$res = db_query_params('UPDATE doc_data_version SET current_version = $1 WHERE docid = $2 AND version != $3',
						array(0, $this->Document->getID(), $version));
			if  (!$res) {
				$this->setOnUpdateError(_('Cannot update current version on creation.').' '.db_error());
				db_rollback();
				return false;
			}
		}
		db_commit();
		return $serial_id;
	}

	/**
	 * delete - delete a specific version
	 *
	 * @param	int	$verid	The version to delete
	 * @return	bool	true on success
	 */
	function delete($verid) {
		db_begin();
		$res = db_query_params('DELETE FROM doc_data_version WHERE docid = $1 and version = $2',
					array($this->Document->getID(), $verid));
		if (!$res) {
			$this->setError(_('DocumentVersion')._(': ')._('Invalid version id').' '.db_error());
			db_rollback();
			return false;
		}
		if ($this->getNumberOfVersions() == 1) {
			$this->getMaxVersionData();
			$this->update($this->data_array['version'], $this->data_array['title'], $this->data_array['description'], $this->data_array['filetype'],
					$this->data_array['filename'], $this->data_array['filesize'], $this->data_array['updatedate'], 1, $this->data_array['vcomment']);
		}
		db_commit();
		db_free_result($res);
		return true;
	}

	/**
	 * getVersion - retrieve the data of a specific version
	 *
	 * @param	int	$version	The version to retrive
	 * @return	array	the content of a version
	 */
	function getVersion($version) {
		$this->fetchData($version);
		return $this->data_array;
	}

	/**
	 * getMaxVersionID - get the highest value of version of a document
	 *
	 * @return	int	The max value.
	 */
	function getMaxVersionID() {
		$res = db_query_params('SELECT MAX(version) FROM doc_data_version WHERE docid = $1',
					array($this->Document->getID()));
		if ($res) {
			$arr = db_fetch_array($res);
			return (int)$arr[0];
		}
		return 0;
	}

	/**
	 * getMaxVersionData - retrieve the content of the highest version of the document
	 *
	 * @return	array	the content of a version.
	 */
	function getMaxVersionData() {
		return $this->fetchData($this->getMaxVersionID());
	}

	/**
	 * getNumberOfVersions - get the number of versions of this document
	 *
	 * @return	int	Number of versions
	 */
	function getNumberOfVersions() {
		$res = db_query_params('SELECT COUNT(version) FROM doc_data_version WHERE docid = $1',
					array($this->Document->getID()));
		if ($res) {
			$arr = db_fetch_array($res);
			return (int)$arr[0];
		}
	}

	/**
	 * isURL - whether this document is a URL and not a local file.
	 *
	 * @return	boolean	is_url.
	 */
	function isURL() {
		return ($this->data_array['filetype'] == 'URL');
	}

	function isCurrent() {
		return $this->data_array['current_version'];
	}

	/**
	 * update - Update an existing version of a document
	 *
	 * @param	int	$version		The version id to update
	 * @param	string	$title			The new title
	 * @param	string	$description		The new description
	 * @param	string	$filetype		The new filetype
	 * @param	string	$filename		The new filename
	 * @param	int	$filesize		The new filesize
	 * @param	int	$updatetimestamp	timestamp of this update
	 * @param	int	$current_version	Is the current version to set? Default is yes.
	 * @param	string $vcomment
	 * @return	bool	true on success
	 */
	function update($version, $title, $description, $filetype, $filename, $filesize, $updatetimestamp, $current_version = 1, $vcomment = '') {
		db_begin();
		$colArr = array('title', 'description', 'filetype', 'filename', 'filesize', 'current_version', 'updatedate', 'vcomment');
		$valArr = array(htmlspecialchars($title), htmlspecialchars($description), $filetype, $filename, $filesize, $current_version, $updatetimestamp, htmlspecialchars($vcomment));
		if (!$this->setValueinDB($version, $colArr, $valArr)) {
			db_rollback();
			return false;
		}
		if ($current_version) {
			$res = db_query_params('UPDATE doc_data_version SET current_version = $1 WHERE docid = $2 AND version != $3',
						array(0, $this->Document->getID(), $version));
			if (!$res) {
				$this->setOnUpdateError(_('Cannot set current_version.').' '.db_error());
				db_rollback();
				return false;
			}
		}
		db_commit();
		return true;
	}

	/**
	 * updateDataWords - update the indexation of content of a version of the document
	 *
	 * @param	int	$version	The version to update
	 * @param	string	$data_words	The content of the document as parsed
	 * @return	bool	true on success
	 */
	function updateDataWords($version, $data_words) {
		db_begin();
		$colArr = array('data_words');
		$valArr = array($data_words);
		if (!$this->setValueinDB($version, $colArr, $valArr)) {
			db_rollback();
			return false;
		}
		db_commit();
		return true;
	}

	/**
	 * setValueinDB - private function to update columns in db
	 *
	 * @param	int	$version	the version id to update
	 * @param	array	$colArr		the columns to update in array form array('col1', col2')
	 * @param	array	$valArr		the values to store in array form array('val1', 'val2')
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function setValueinDB($version, $colArr, $valArr) {
		if ((count($colArr) != count($valArr)) || !count($colArr) || !count($valArr)) {
			$this->setOnUpdateError(_('wrong parameters'));
			return false;
		}

		$qpa = db_construct_qpa(false, 'UPDATE doc_data_version SET ');
		for ($i = 0; $i < count($colArr); $i++) {
			$qpa_string = '';
			switch ($colArr[$i]) {
				case 'filesize':
				case 'title':
				case 'description':
				case 'filetype':
				case 'filename':
				case 'updatedate':
				case 'data_words':
				case 'current_version':
				case 'vcomment': {
					if ($i) {
						$qpa_string .= ',';
					}
					$qpa = db_construct_qpa($qpa, $qpa_string.$colArr[$i].' = $1 ', array($valArr[$i]));
					break;
				}
				default: {
					$this->setOnUpdateError(_('wrong column name'));
					return false;
				}
			}
		}
		$qpa = db_construct_qpa($qpa, ' WHERE version = $1 AND docid = $2',
						array($version, $this->Document->getID()));
		$res = db_query_qpa($qpa);
		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(db_error());
			return false;
		}
		for ($i = 0; $i < count($colArr); $i++) {
			switch ($colArr[$i]) {
				// we do not store data_words in memory!
				case 'filesize':
				case 'title':
				case 'description':
				case 'filetype':
				case 'filename':
				case 'updatedate':
				case 'current_version':
				case 'vcomment': {
					$this->data_array[$colArr[$i]] = $valArr[$i];
				}
			}
		}
		return true;
	}

	function hasValidatedReview() {
		$res = db_query_params('SELECT statusid FROM doc_review, doc_review_version
					WHERE statusid = $1 AND doc_review.revid = doc_review_version.revid AND doc_review_version.serialid = $2',
					array(4, $this->getID()));
		if ($res && (db_numrows($res) > 0)) {
			return true;
		}
		return false;
	}
}
