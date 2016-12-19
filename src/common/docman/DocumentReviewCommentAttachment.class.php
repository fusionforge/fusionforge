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

require_once $gfcommon.'/docman/DocumentReviewStorage.class.php';

class DocumentReviewCommentAttachment extends FFError {

	/**
	 * The data values
	 *
	 * @var	array	$data_array
	 */
	var $data_array = array();

	function __construct($attachid) {
		parent::__construct();
		$res = db_query_params('SELECT * FROM doc_review_attachments WHERE attachid = $1', array($attachid));
		$this->data_array = db_fetch_array($res);
		return true;
	}

	function getID() {
		return $this->data_array['attachid'];
	}

	function getFileName() {
		return $this->data_array['filename'];
	}

	function getFileType() {
		return $this->data_array['filetype'];
	}

	function getFilePath() {
		return DocumentReviewStorage::instance()->get($this->getID());
	}

	function getFileData() {
		return file_get_contents($this->getFilePath());
	}
}
