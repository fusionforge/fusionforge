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

require_once $gfcommon.'docman/DocumentReviewStorage.class.php';

class DocumentReviewComment extends FFError {
	/**
	 * The DocumentReview object.
	 *
	 * @var	object	$DocumentReview.
	 */
	var $DocumentReview;

	/**
	 * The data values
	 *
	 * @var	array	$data_array
	 */
	var $data_array = array();

	function __construct(&$DocumentReview, $commentid = false) {
		parent::__construct();
		if (!$DocumentReview || !is_object($DocumentReview)) {
			$this->setError(_('No Valid DocumentReview Object'));
			return false;
		}
		if ($DocumentReview->isError()) {
			$this->setError(_('DocumentReview')._(': ').$DocumentReview->getErrorMessage());
			return false;
		}
		$this->DocumentReview =& $DocumentReview;
		if ($commentid) {
			$this->fetchData($commentid);
		}
		return true;
	}

	function fetchData($commentid) {
		$res = db_query_params('SELECT * FROM doc_review_comments WHERE commentid = $1', array($commentid));
		if ($res) {
			$this->data_array = db_fetch_array($res);
			return true;
		}
		return false;
	}

	function getID() {
		return $this->data_array['commentid'];
	}

	function getCreateDate() {
		return $this->data_array['createdate'];
	}

	function getPosterID() {
		return $this->data_array['userid'];
	}

	function getPosterRealName() {
		$userObject = user_get_object($this->getPosterID());
		return $userObject->getRealName();
	}

	function getPosterUnixName() {
		$userObject = user_get_object($this->getPosterID());
		return $userObject->getUnixName();
	}

	function getReviewComment() {
		$result = util_gen_cross_ref($this->data_array['rcomment'], $this->DocumentReview->Document->Group->getID());
		return nl2br($result);
	}

	function attachFile($filename, $filetype, $createdate, $data) {
		db_begin();
		$filesize = filesize($data);
		$res = db_query_params('INSERT INTO doc_review_attachments (createdate, commentid, filename, filetype, filesize) VALUES ($1, $2, $3, $4, $5)',
					array($createdate, $this->getID(), $filename, $filetype, $filesize));
		$attachid = db_insertid($res, 'doc_review_attachments', 'attachid');
		if ($attachid) {
			if (!DocumentReviewStorage::instance()->store($attachid, $data)) {
				DocumentReviewStorage::instance()->rollback();
				db_rollback();
				$this->setError(DocumentReviewStorage::instance()->getErrorMessage());
				return false;
			}
			DocumentReviewStorage::instance()->commit();
			db_commit();
			return true;
		} else {
			db_rollback();
			$this->setError(db_error());
			return false;
		}
	}

	function getAttachment() {
		$res = db_query_params('SELECT * FROM doc_review_attachments WHERE commentid = $1', array($this->getID()));
		if ($res && (db_numrows($res) > 0)) {
			$attachArr = db_fetch_array($res);
			return util_make_link('/docman/view.php/'.$this->DocumentReview->Document->Group->getID().'/review/'.$attachArr['attachid'], $attachArr['filename']);
		} else {
			return '';
		}
	}

	function getAttachmentID() {
		$attachid = null;
		$res = db_query_params('SELECT attachid FROM doc_review_attachments WHERE commentid = $1', array($this->getID()));
		if ($res && (db_numrows($res) > 0)) {
			$attachArr = db_fetch_array($res);
			$attachid = $attachArr['attachid'];
		}
		return $attachid;
	}

	function create($userid, $reviewid, $rcomment, $createdate) {
		db_begin();
		$res = db_query_params('INSERT INTO doc_review_comments (revid, userid, rcomment, createdate) VALUES ($1, $2, $3, $4)',
					array($reviewid, $userid, $rcomment, $createdate));
		if ($res) {
			$commentid = db_insertid($res, 'doc_review_comments', 'commentid');
			if ($this->fetchData($commentid)) {
				db_commit();
				return true;
			}
		}
		db_rollback();
		$this->setError(db_error());
		return false;
	}
}
