<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2016-2017, Franck Villaume - TrivialDev
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
require_once $gfcommon.'docman/DocumentVersion.class.php';

class DocumentVersionFactory extends FFError {
	/**
	 * The Document object.
	 *
	 * @var	object	$Document.
	 */
	var $Document;

	/**
	 * @param	$Document
	 * @internal	param		\The $object Document object to which this version factory is associated.
	 */
	function __construct(&$Document) {
		parent::__construct();
		if (!$Document || !is_object($Document)) {
			$this->setError(_('No Valid Document Object'));
			return false;
		}
		if ($Document->isError()) {
			$this->setError(_('Document')._(': ').$Document->getErrorMessage());
			return false;
		}
		$this->Document =& $Document;
		return true;
	}

	/**
	 * getHTMLVersions - retrieve a limited number of version of a document
	 *
	 * @param	int	$limit	the number of versions to retrieve. Default is 0 = No limit
	 * @param	int	$start	Paging the retrieve. Start point. Default is 0.
	 * @return	array	Array of enriched version datas from database.
	 */
	function getHTMLVersions($limit = 0, $start = 0) {
		global $HTML;
		$versions = array();
		// everything but data_words! Too much memory consumption.
		$res = db_query_params('SELECT serial_id, \'_\'||version as version, docid, current_version, title, updatedate, createdate, created_by, description, filename, filetype, filesize, vcomment FROM doc_data_version WHERE docid = $1 ORDER by version DESC',
					array($this->Document->getID()), $limit, $start);
		if ($res) {
			$numrows = db_numrows($res);
			while ($arr = db_fetch_array($res)) {
				$user = user_get_object($arr['created_by']);
				$arr['created_by_username'] = util_display_user($user->getUnixName(), $user->getID(), $user->getRealName());
				$arr['filesize_readable'] = human_readable_bytes($arr['filesize']);
				if ($arr['updatedate']) {
					$arr['lastdate'] = date(_('Y-m-d H:i'), $arr['updatedate']);
				} else {
					$arr['lastdate'] = date(_('Y-m-d H:i'), $arr['createdate']);
				}
				$isURL = 0;
				if ($arr['filetype'] == 'URL') {
					$isURL = 1;
				}
				$isText = 0;
				if (preg_match('|^text/|i', $arr['filetype'])) { // text plain, text html, text x-patch, etc
					$isText = 1;
				}
				$isHtml = 0;
				if (preg_match('/html/i', $arr['filetype'])) { // text plain, text html, text x-patch, etc
					$isHtml = 1;
				}
				$new_description = util_gen_cross_ref($arr['description'], $this->Document->Group->getID());
				$arr['new_description'] = str_replace(array("\r\n", "\r", "\n"), "\\n", $new_description);
				$arr['description'] = str_replace(array("\r\n", "\r", "\n"), "\\n", $arr['description']);
				$arr['vcomment'] = str_replace(array("\r\n", "\r", "\n"), "\\n", $arr['vcomment']);
				$arr['versionactions'][] = util_make_link('#', $HTML->getEditFilePic(_('Edit this version'), 'editversion'), array('id' => 'version_action_edit', 'onclick' => 'javascript:controllerListFile.toggleEditVersionView({title: \''.addslashes($arr['title']).'\', description: \''.addslashes($arr['description']).'\', new_description: \''.addslashes($arr['new_description']).'\', version: '.ltrim($arr['version'], '_').', current_version: '.$arr['current_version'].', isURL: '.$isURL.', isText: '.$isText.', isHtml: '.$isHtml.', filename: \''.addslashes($arr['filename']).'\', vcomment: \''.addslashes($arr['vcomment']).'\', docid: '.$arr['docid'].', groupId: '.$this->Document->Group->getID().'})'), true);
				if ($numrows > 1) {
					$arr['versionactions'][] = util_make_link('#', $HTML->getRemovePic(_('Permanently delete this version'), 'delversion'), array('id' => 'version_action_delete', 'onclick' => 'javascript:controllerListFile.deleteVersion({version: '.ltrim($arr['version'], '_').', docid: '.$arr['docid'].', groupId: '.$this->Document->Group->getID().'})'), true);
				}
				$versions[$arr['version']] = $arr;
			}
		}
		db_free_result($res);
		return $versions;
	}

	function getVersions() {
		$versions = array();
		// everything but data_words! Too much memory consumption.
		$res = db_query_params('SELECT serial_id, version as version, docid, current_version, title, updatedate, createdate, created_by, description, filename, filetype, filesize, vcomment FROM doc_data_version WHERE docid = $1 ORDER by version DESC',
					array($this->Document->getID()));
		if ($res) {
			$numrows = db_numrows($res);
			while ($arr = db_fetch_array($res)) {
				$versions[] = $arr;
			}
		}
		db_free_result($res);
		return $versions;
	}

	function getSerialIDs() {
		$serialids = array();
		$res = db_query_params('SELECT serial_id FROM doc_data_version WHERE docid = $1', array($this->Document->getID()));
		if ($res) {
			while ($arr = db_fetch_array($res)) {
				$serialids[] = $arr[0];
			}
			$this->serialids = $serialids;
		}
		return $serialids;
	}

	function getDBResVersionSerialIDs() {
		return db_query_params('SELECT serial_id, version FROM doc_data_version WHERE docid = $1 ORDER BY version DESC', array($this->Document->getID()));
	}
}
