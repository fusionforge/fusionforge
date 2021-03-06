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

require_once $gfcommon.'docman/DocumentReview.class.php';
require_once $gfcommon.'docman/DocumentReviewCommentFactory.class.php';

class DocumentReviewFactory extends FFError {
	/**
	 * The Document object.
	 *
	 * @var	object	$Document
	 */
	var $Document;

	/**
	 * @var array	$reviews Reviews of this document
	 */
	var $reviews = array();

	/**
	 * @param	$Document
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

	function getReviews($serialids = array()) {
		$res = db_query_params('SELECT doc_review.revid as revid, created_by, statusid, docid, startdate, enddate, title, description, doc_review_version.serialid as serialid FROM doc_review, doc_review_version
					WHERE doc_review.revid = doc_review_version.revid AND doc_review_version.serialid = ANY ($1) AND doc_review.docid = $2 ORDER BY enddate DESC',
					array(db_int_array_to_any_clause($serialids), $this->Document->getID()));
		if ($res) {
			while ($arr = db_fetch_array($res)) {
				$dr = new DocumentReview($this->Document, $arr['revid']);
				$drcf = new DocumentReviewCommentFactory($dr);
				$arr['comments'] = $drcf->getComments();
				foreach ($arr['comments'] as $comment) {
					unset($comment->DocumentReview);
				}
				$arr['users'] = $dr->getUsers();
				$this->reviews[] = $arr;
			}
		}
		db_free_result($res);
		return $this->reviews;
	}

	function getReviewsHTML($serialids = array()) {
		global $HTML;
		$return = '';
		$this->getReviews($serialids);
		$add_button = true;
		if ($this->getReviewsCounter() > 0) {
			$titleArr = array('ID', _('Version'), _('Title'), _('Created By'), _('Status'), _('Start date'), _('End date'), _('Progress'), _('Comments'), _('Actions'));
			$classth = array('', '', '', '', '', 'unsortable');
			$return .= $HTML->listTableTop($titleArr, array(), 'full sortable', 'sortable_docman_listreview', $classth);
			foreach ($this->reviews as $thereview) {
				$dr = new DocumentReview($this->Document, $thereview[0]);
				$cells = array();
				$cells[][] = $thereview[0];
				$cells[] = array($dr->getVersion(), 'id' => 'docversionreview'.$dr->getVersion());
				$cells[] = array($dr->getTitle(), 'title' => $dr->getDescription());
				$cells[][] = $dr->getCreateByRealNameLink();
				$cells[][] = $dr->getStatusIcon();
				$cells[][] = strftime(_('%Y-%m-%d'), $dr->getStartdate());
				$overdue = '';
				if (($dr->getStatusID() != 2 && $dr->getStatusID() != 4) && (time() > $dr->getEnddate())) {
					$overdue = $HTML->getErrorPic(_('Review overdue'), 'overdue');
				}
				$cells[][] = strftime(_('%Y-%m-%d'), $dr->getEnddate()).$overdue;
				$cells[][] = $dr->getProgressbar();
				$drcf = new DocumentReviewCommentFactory($dr);
				$cells[][] = $drcf->getNbComments();
				$actions = '';
				$user = session_get_user();
				$users = $dr->getUsers(array(1, 2));
				if ($dr->getStatusID() != 2 && $dr->getStatusID() != 4) {
					if ($user->getID() == $dr->getCreatedBy()) {
						$actions .= $dr->getReminderAction().$dr->getEditAction().$dr->getCompleteAction();
					}
					$userfound = false;
					foreach ($users as $ruser) {
						if ($user->getID() == $ruser['userid']) {
							$userfound = true;
						}
					}
					if (($dr->getStatusID() == 1) && (($user->getID() == $dr->getCreatedBy()) || $userfound)) {
						$actions .= $dr->getCommentAction();
					}
					if ($user->getID() == $dr->getCreatedBy()) {
						$actions .= $dr->getDeleteAction();
					}
				} else {
					$actions .= $dr->getReadAction();
				}

				$cells[][] = $actions;
				$return .= $HTML->multiTableRow(array('id' => 'docreview'.$thereview[0]), $cells);
				if ($dr->getStatusID() == 1) {
					$add_button = false;
				}
			}
			$return .= $HTML->listTableBottom();
		} else {
			$return = $HTML->information(_('No Reviews.'));
		}
		if ($add_button) {
			$return .= html_e('button', array('id' => 'doc_review_addbutton', 'type' => 'button', 'onclick' => 'javascript:controllerListFile.toggleAddReviewView()'), _('Add new review'));
		}
		return $return;
	}


	function getReviewsCounter() {
		return count($this->reviews);
	}
}
