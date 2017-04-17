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

require_once $gfcommon.'docman/DocumentReviewComment.class.php';
require_once $gfcommon.'docman/DocumentReviewCommentAttachment.class.php';

class DocumentReviewCommentFactory extends FFError {
	/**
	 * The DocumentReview object.
	 *
	 * @var	object	$DocumentReview
	 */
	var $DocumentReview;

	function __construct(&$DocumentReview) {
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
		return true;
	}

	function getNbComments() {
		$res = db_query_params('SELECT COUNT(commentid) FROM doc_review_comments WHERE revid = $1', array($this->DocumentReview->getID()));
		if ($res) {
			return db_result($res, 0, 0);
		}
		return null;
	}

	function getComments() {
		$commentsArr = array();
		$res = db_query_params('SELECT commentid FROM doc_review_comments WHERE revid = $1 ORDER BY createdate DESC', array($this->DocumentReview->getID()));
		if ($res && (db_numrows($res) > 0)) {
			$i = 0;
			while ($arr = db_fetch_array($res)) {
				$commentsArr[$i] = new DocumentReviewComment($this->DocumentReview, $arr['commentid']);
				$attachid = $commentsArr[$i]->getAttachmentID();
				if ($attachid) {
					$drca = new DocumentReviewCommentAttachment($attachid);
					$commentsArr[$i]->storageref = $drca->getFilePath();
				} else {
					$commentsArr[$i]->storageref = null;
				}
				$i++;
			}
		}
		return $commentsArr;
	}
}
