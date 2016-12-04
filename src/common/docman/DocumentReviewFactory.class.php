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
	var $reviews;

	/**
	 * @param	$Document
	 * @internal	param		\The $object Document object to which this review factory is associated.
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
				$serialid = $arr['serialid'];
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
		if ($this->getReviewsCounter() > 0) {
			$titleArr = array('ID', _('Version'), _('Title'), _('Created By'), _('Status'), _('End date'), _('Progress'), _('Comments'), _('Actions'));
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
				$overdue = '';
				if (time() > $dr->getEnddate()) {
					$overdue = $HTML->getErrorPic(_('Review overdue'), 'overdue');
				}
				$cells[][] = strftime(_('%Y-%m-%d'), $dr->getEnddate()).$overdue;
				$cells[][] = $dr->getProgressbar();
				$cells[][] = $dr->getNbComments();
				$actions = '';
				$user = session_get_user();
				$users = $dr->getUsers(array(1, 2));
				if ($user->getID() == $dr->getCreatedBy()) {
					$actions .= $dr->getReminderAction().$dr->getEditAction();
					if ($dr->isCompleted()) {
						$actions .= $dr->getCompleteAction();
					}
				}
				if (($dr->getStatusID() == 1) && (($user->getID() == $dr->getCreatedBy()) || in_array($user->getID(), $users))) {
					$actions .= $dr->getCommentAction();
				}
				if ($user->getID() == $dr->getCreatedBy()) {
					$actions .= $dr->getDeleteAction();
				}

				$cells[][] = $actions;
				$return .= $HTML->multiTableRow(array('id' => 'docreview'.$thereview[0]), $cells);
			}
			$return .= $HTML->listTableBottom();
		} else {
			$return = $HTML->information(_('No Reviews.'));
		}
		$return .= html_e('button', array('id' => 'doc_review_addbutton', 'type' => 'button', 'onclick' => 'javascript:controllerListFile.toggleAddReviewView()'), _('Add new review'));
		return $return;
	}


	function getReviewsCounter() {
		return count($this->reviews);
	}
}
