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
		return array();
	}
}
