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

require_once $gfcommon.'docman/DocumentVersion.class.php';
require_once $gfcommon.'docman/DocumentVersionFactory.class.php';
require_once $gfcommon.'docman/include/constants.php';

class DocumentReview extends FFError {
	/**
	 * The Document object.
	 *
	 * @var	object	$Document.
	 */
	var $Document;

	/**
	 * The data values
	 *
	 * @var	array	$data_array
	 */
	var $data_array = array();

	var $version    = null;
	var $serialid   = null;

	function __construct(&$Document, $revid = false, $arr = false) {
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
		if ($revid) {
			$this->fetchData($revid);
		}
		return true;
	}

	function fetchData($revid) {
		$res = db_query_params('SELECT revid, created_by, doc_review.statusid as statusid, startdate, enddate, title, doc_review.description, doc_review_status.name as statusname
					FROM doc_review, doc_review_status WHERE doc_review.statusid = doc_review_status.statusid AND doc_review.revid = $1', array($revid));
		$this->data_array = db_fetch_array($res);
		$this->getVersion();
	}

	function getID() {
		return $this->data_array['revid'];
	}

	function getTitle() {
		return $this->data_array['title'];
	}

	function getDescription() {
		return $this->data_array['description'];
	}

	function getStatusID() {
		return $this->data_array['statusid'];
	}

	function getStatusName() {
		return $this->data_array['statusname'];
	}

	function getCreatedBy() {
		return $this->data_array['created_by'];
	}

	function getEnddate() {
		return $this->data_array['enddate'];
	}

	function getStartdate() {
		return $this->data_array['startdate'];
	}

	function getUsers($statusid = array(), $type = false) {
		$qpa = db_construct_qpa(false, 'SELECT revid, userid, typeid, doc_review_users.statusid, updatedate, doc_review_users_status.name as statusname
						FROM doc_review_users, doc_review_users_status
						WHERE doc_review_users.statusid = doc_review_users_status.statusid AND revid = $1', array($this->getID()));
		if ($statusid && is_array($statusid)) {
			$qpa = db_construct_qpa($qpa, ' AND doc_review_users.statusid = ANY ($1)', array(db_int_array_to_any_clause($statusid)));
		}
		if ($type) {
			$qpa = db_construct_qpa($qpa, ' AND doc_review_users.typeid = $1', array($type));
		}
		$res = db_query_qpa($qpa);
		$users = array();
		if ($res && (db_numrows($res) > 0)) {
			while ($arr = db_fetch_array($res)) {
				$users[] = $arr;
			}
		}
		return $users;
	}

	function getMandatoryUsers() {
		return $this->getUsers(false, 1);
	}

	function getOptionalUsers() {
		return $this->getUsers(false, 2);
	}

	function getStatusIcon() {
		global $HTML;
		switch ($this->getStatusID()) {
			case 1:
				$img = $HTML->getOpenTicketPic(_('Open'), _('Open'));
				break;
			case 2:
				$img = $HTML->getClosedTicketPic(_('Closed'), _('Closed'));
				break;
			case 4:
				$img = $HTML->getTagPic(_('Validated'), _('Validated'));
				break;
			case 3:
			default:
				$img = $HTML->getErrorPic(_('On error'), _('On error'));
				break;
		}
		return $img;
	}

	function getCreateByRealNameLink() {
		$user = user_get_object($this->getCreatedBy());
		if (is_object($user) && !$user->isError()) {
			return util_display_user($user->getUnixName(), $user->getID(), $user->getRealName());
		}
		return _('Unknown user');
	}

	function getVersion() {
		if (isset($this->version) && $this->version) {
			return $this->version;
		}
		$res = db_query_params('SELECT version FROM doc_data_version, doc_review_version WHERE doc_data_version.serial_id = doc_review_version.serialid AND doc_review_version.revid = $1',
					array($this->getID()));
		if ($res) {
			$this->version = db_result($res, 0, 0);
			return $this->version;
		} else {
			return db_error();
		}
	}

	function getSerialID() {
		if (isset($this->serialid) && $this->serialid) {
			return $this->serialid;
		}
		$res = db_query_params('SELECT serialid FROM doc_review_version WHERE revid = $1', array($this->getID()));
		if ($res) {
			$this->serialid = db_result($res, 0, 0);
			return $this->serialid;
		} else {
			return db_error();
		}
	}

	function getDeleteAction() {
		global $HTML;
		return util_make_link('#', $HTML->getRemovePic(_('Permanently delete this review'), 'delreview'), array('id' => 'review_action_delete', 'onclick' => 'javascript:controllerListFile.deleteReview({review: '.$this->getID().'})'), true);
	}

	function getReadAction() {
		global $HTML;
		return util_make_link('#', $HTML->getEditFilePic(_('View comments of this review'), 'viewreview'), array('id' => 'review_action_view', 'onclick' => 'javascript:controllerListFile.toggleCommentReviewView({review: '.$this->getID().', groupId: '.$this->Document->Group->getID().', docid: '.$this->Document->getID().'})'), true);
	}

	function getEditAction($edit = true) {
		global $HTML;
		$enddate = strftime(_('%Y-%m-%d'), $this->getEnddate());
		$users = $this->getUsers(array(1, 2));
		$mandatoryUsers = array();
		$optionalUsers = array();
		foreach ($users as $user) {
			if ($user['typeid'] == 1) {
				$mandatoryUsers[] = $user['userid'];
			} else {
				$optionalUsers[] = $user['userid'];
			}
		}
		if ($edit) {
			return util_make_link('#', $HTML->getConfigurePic(_('Edit this review'), 'editreview'),
					array('id' => 'review_action_edit', 'onclick' => 'javascript:controllerListFile.toggleEditReviewView({review: '.$this->getID().', title: \''.addslashes($this->getTitle()).'\',
																		description: \''.addslashes(str_replace(array("\r\n", "\r", "\n"), "\\n", $this->getDescription())).'\',
																		endreviewdate: \''.util_html_encode($enddate).'\',
																		serialid: '.$this->getSerialID().',
																		mandatoryusers: '.json_encode($mandatoryUsers).',
																		optionalusers: '.json_encode($optionalUsers).',
																		docid: '.$this->Document->getID().',
																		groupId: '.$this->Document->Group->getID().'})'), true);
		} else {
			return util_make_link('#', $HTML->getClosedTicketPic(_('Complete this review'), 'completereview'),
					array('id' => 'review_action_complete', 'onclick' => 'javascript:controllerListFile.toggleEditReviewView({review: '.$this->getID().', title: \''.addslashes($this->getTitle()).'\',
																		description: \''.addslashes(str_replace(array("\r\n", "\r", "\n"), "\\n", $this->getDescription())).'\',
																		endreviewdate: \''.util_html_encode($enddate).'\',
																		serialid: '.$this->getSerialID().',
																		mandatoryusers: '.json_encode($mandatoryUsers).',
																		optionalusers: '.json_encode($optionalUsers).',
																		docid: '.$this->Document->getID().',
																		groupId: '.$this->Document->Group->getID().',
																		complete: 1})'), true);
		}
	}

	function getReminderAction() {
		global $HTML;
		return util_make_link('#', $HTML->getMailNotifyPic(_('Send reminder to pending reviewers'), 'reminderreview'), array('id' => 'review_action_reminder', 'onclick' => 'javascript:controllerListFile.reminderReview({review: '.$this->getID().'})'), true);
	}

	function getCompleteAction() {
		return $this->getEditAction(false);
	}

	function getCommentAction() {
		global $HTML;
		return util_make_link('#', $HTML->getEditFilePic(_('Comment the review and/or view the existing comments if any.'), 'remindercomment'), array('id' => 'review_action_comment', 'onclick' => 'javascript:controllerListFile.toggleCommentReviewView({review: '.$this->getID().', groupId: '.$this->Document->Group->getID().', docid: '.$this->Document->getID().'})'), true);
	}

	function showCompleteFormHTML() {
		global $HTML;
		$return = $HTML->listTableTop();
		$cells = array();
		$cells[] = array(_('Close the review')._(':'), 'style' => 'width: 30%;');
		$cells[][] = html_e('input', array('type' => 'checkbox', 'name' => 'review-completedchecked', 'value' => 1));
		$return .= $HTML->multiTableRow(array(), $cells);
		if ($this->Document->getStateID() == 3) {
			$cells = array();
			$cells[][] = _('Validate the document?')._(':');
			$cells[][] = html_e('input', array('type' => 'checkbox', 'name' => 'review-validatedocument', 'value' => 1 ,'title' => _('Tick if you want to move from pending to active status this document.')));
			$return .= $HTML->multiTableRow(array(), $cells);
		}
		$dv = documentversion_get_object($this->getVersion(), $this->Document->getID(), $this->Document->Group->getID());
		if (!$dv->isCurrent()) {
			$cells = array();
			$cells[][] = _('Make this version as current')._(':');
			$cells[][] = html_e('input', array('type' => 'checkbox', 'name' => 'review-currentversion', 'value' => 1 ,'title' => _('Tick if you want to make this document version as the current version.')));
			$return .= $HTML->multiTableRow(array(), $cells);
		}
		$cells = array();
		$cells[][] = _('Final Status')._(':');
		$cells[][] = html_build_select_box_from_arrays(array(2,4), array(_('Closed'), _('Validated')), 'review-finalstatus', false, false, '', false, '', false, array('id' => 'review-finalstatus'));
		$return .= $HTML->multiTableRow(array(), $cells);
		$cells = array();
		$cells[][] = _('Conclusion Comment')._(':');
		$cells[][] = html_e('textarea', array('id' => 'review-completedcomment', 'name' => 'review-completedcomment', 'style' => 'width: 100%; box-sizing: border-box;', 'rows' => 3, 'required' => 'required', 'pattern' => '.{10,}', 'placeholder' => _('Final comment').' '.sprintf(_('(at least %s characters)'), DOCMAN__REVIEW_DESCRIPTION_MIN_SIZE), 'maxlength' => DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE), '', false);
		$return .= $HTML->multiTableRow(array(), $cells);
		$return .= $HTML->listTableBottom();
		$return .= html_ac(html_ap() -1);
		return $return;
	}

	function showCreateFormHTML() {
		global $HTML;
		$dvf = new DocumentVersionFactory($this->Document);
		$return = '';
		if (is_object($dvf)) {
			$userObjects = $this->Document->Group->getUsers();
			$userNameArray = array();
			$userIDArray = array();
			foreach ($userObjects as $userObject) {
				if (forge_check_perm_for_user($userObject, 'docman', $this->Document->Group->getID(), 'approve')) {
					$userNameArray[] = $userObject->getRealName();
					$userIDArray[] = $userObject->getID();
				}
			}
			$date_format_js = _('yy-mm-dd');
			$return = html_ao('div', array('style' => 'display: none;', 'id' => 'editfile-createreview'));
			$return .= $HTML->listTableTop();
			$cells = array();
			$cells[] = array(_('Title').utils_requiredField()._(':'), 'style' => 'width: 30%;');
			$cells[][] = html_e('input', array('type' => 'text', 'id' => 'review-title', 'name' => 'review-title', 'style' => 'width: 100%; box-sizing: border-box;', 'required' => 'required', 'pattern' => '.{5,}', 'placeholder' => _('Title').' '.sprintf(_('(at least %s characters)'), DOCMAN__REVIEW_TITLE_MIN_SIZE), 'maxlength' => DOCMAN__REVIEW_TITLE_MAX_SIZE));
			$return .= $HTML->multiTableRow(array(), $cells);
			$cells = array();
			$cells[][] = _('Description').utils_requiredField()._(':');
			$cells[][] = html_e('textarea', array('id' => 'review-description', 'name' => 'review-description', 'style' => 'width: 100%; box-sizing: border-box;', 'rows' => 3, 'required' => 'required', 'pattern' => '.{10,}', 'placeholder' => _('Description').' '.sprintf(_('(at least %s characters)'), DOCMAN__REVIEW_DESCRIPTION_MIN_SIZE), 'maxlength' => DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE), '', false);
			$return .= $HTML->multiTableRow(array(), $cells);
			$cells = array();
			$cells[][] = _('Select Version to review').utils_requiredField()._(':');
			$cells[][] = html_build_select_box($dvf->getDBResVersionSerialIDs(), 'review-serialid', false, false, '', false, '', false, array('id' => 'review-serialid'));
			$return .= $HTML->multiTableRow(array(), $cells);
			$cells = array();
			$cells[][] = _('Select End date of this review').utils_requiredField()._(':');
			$cells[][] = html_e('input', array('id' => 'datepicker_end_review_date', 'name' => 'review-enddate', 'size' => 10, 'maxlength' => 10, 'required' => 'required'));
			$return .= $HTML->multiTableRow(array(), $cells);
			$cells = array();
			$cells[][] =_('Add mandatory reviewers').utils_requiredField()._(':');
			$cells[][] = html_e('p', array(), html_build_multiple_select_box_from_arrays($userIDArray, $userNameArray, 'review-select-mandatory-users[]', array(), 8, false, 'none', false, array('id' => 'review-select-mandatory-users')));
			$return .= $HTML->multiTableRow(array('id' => 'tr-mandatory-reviewers'), $cells);
			$cells = array();
			$cells[][] = _('Add optional reviewers')._(':');
			$cells[][] = html_e('p', array(), html_build_multiple_select_box_from_arrays($userIDArray, $userNameArray, 'review-select-optional-users[]', array(), 8, false, 'none', false, array('id' => 'review-select-optional-users')));
			$return .= $HTML->multiTableRow(array('id' => 'tr-optional-reviewers'), $cells);
			$cells = array();
			$cells[][] = _('Notification comment')._(':');
			$cells[][] = html_e('textarea', array('id' => 'review-notificationcomment', 'name' => 'review-notificationcomment', 'style' => 'width: 100%; box-sizing: border-box;', 'rows' => 3, 'pattern' => '.{10,}', 'placeholder' => _('Add a specific comment for the mail notification here'), 'maxlength' => DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE), '', false);
			$return .= $HTML->multiTableRow(array('id' => 'review-notificationcomment-row'), $cells);
			$cells = array();
			$return .= $HTML->listTableBottom();
			$return .= $HTML->addRequiredFieldsInfoBox();
			$return .= html_e('input', array('type' => 'hidden', 'id' => 'new_review', 'name' => 'new_review', 'value' => 0));
			$return .= html_e('input', array('type' => 'hidden', 'id' => 'review_id', 'name' => 'review_id', 'value' => 0));
			$return .= html_e('input', array('type' => 'hidden', 'id' => 'review_complete', 'name' => 'review_complete', 'value' => 0));
			$return .= html_e('input', array('type' => 'hidden', 'id' => 'review_newcomment', 'name' => 'review_newcomment', 'value' => 0));
			$return .= html_ac(html_ap() -1);
			$return .= html_e('div', array('id' => 'editfile-userstatusreview'), '', false);
			$return .= html_e('div', array('id' => 'editfile-completedreview'), '', false);
			$return .= html_e('div', array('id' => 'editfile-commentreview'), '', false);
			$return .= html_e('div', array('id' => 'editfile-remindernotification', 'style' => 'display:none'), _('Notification reminder comment')._(':').html_e('textarea', array('id' => 'review-remindernotification', 'name' => 'review-remindernotification', 'style' => 'width: 100%; box-sizing: border-box;', 'rows' => 3), '', false));
			$javascript = 'jQuery("#datepicker_end_review_date").datepicker({dateFormat: "'.$date_format_js.'"});';
			$return .= html_e('script', array( 'type'=>'text/javascript', 'id' => 'editfile-datepickerreview-script'), '//<![CDATA['."\n".$javascript."\n".'//]]>');
		} else {
			$return = $HTML->error_msg(_('Cannot get Document Versions'));
		}
		return $return;
	}

	function showCommentFormHTML() {
		global $HTML;
		$return = $HTML->listTableTop();
		$cells = array();
		$cells[] = array(_('Comment').utils_requiredField()._(':'), 'style' => 'width: 30%;');
		$cells[][] = html_e('textarea', array('id' => 'review-comment', 'name' => 'review-comment', 'style' => 'width: 100%; box-sizing: border-box;', 'rows' => 3, 'required' => 'required', 'pattern' => '.{10,}', 'placeholder' => _('Add your comment').' '.sprintf(_('(at least %s characters)'), DOCMAN__REVIEW_DESCRIPTION_MIN_SIZE), 'maxlength' => DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE), '', false);
		$return .= $HTML->multiTableRow(array(), $cells);
		$cells = array();
		$cells[][] = _('Review done')._(':');
		$cells[][] = html_e('input', array('type' => 'checkbox', 'name' => 'review-done', 'value' => 1 ,'title' => _('Tick if you want to set your pending review done as reviewer.')));
		$return .= $HTML->multiTableRow(array(), $cells);
		$cells = array();
		$cells[][] = _('Attachment')._(':');
		$cells[][] = html_e('input', array('type' => 'file', 'name' => 'review-attachment')).html_e('br').'('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')';
		$return .= $HTML->multiTableRow(array(), $cells);
		$return .= $HTML->listTableBottom();
		return $return;
	}

	function showCommentsHTML() {
		global $HTML;
		$return = '';
		$drcf = new DocumentReviewCommentFactory($this);
		if ($drcf->getNbComments()) {
			$return .= $HTML->listTableTop(array(_('Date'), _('Author'), _('Comment'), _('Attachment')));
			$comments = $drcf->getComments();
			foreach ($comments as $comment) {
				$cells = array();
				$cells[][] = strftime(_('%Y-%m-%d'),$comment->getCreateDate());
				$cells[][] = util_display_user($comment->getPosterUnixName(), $comment->getPosterID(), $comment->getPosterRealName());
				$cells[][] = $comment->getReviewComment();
				$cells[][] = $comment->getAttachment();
				$return .= $HTML->multiTableRow(array(), $cells);
			}
			$return .= $HTML->listTableTop();
		} else {
			$return .= $HTML->information(_('No comment posted.'));
		}
		return $return;
	}

	function showUsersStatusHTML() {
		global $HTML;
		$mandatoryUsers = $this->getMandatoryUsers();
		$optionalUsers = $this->getOptionalUsers();
		$return = $HTML->listTableTop(array(_('Reviewer Name'), _('M/O'), _('Status')));
		foreach ($mandatoryUsers as $mandatoryUser) {
			$cells = array();
			$userObject = user_get_object($mandatoryUser['userid']);
			$cells[][] = util_display_user($userObject->getUnixName(), $userObject->getID(), $userObject->getRealName());
			$cells[][] = _('mandatory');
			$cells[][] = $mandatoryUser['statusname'];
			$return .= $HTML->multiTableRow(array(), $cells);
		}
		foreach ($optionalUsers as $optionalUser) {
			$cells = array();
			$userObject = user_get_object($optionalUser['userid']);
			$cells[][] = util_display_user($userObject->getUnixName(), $userObject->getID(), $userObject->getRealName());
			$cells[][] = _('optional');
			$cells[][] = $optionalUser['statusname'];
			$return .= $HTML->multiTableRow(array(), $cells);
		}
		$return .= $HTML->listTableBottom();
		return $return;
	}

	function create($reviewversionserialid, $reviewtitle, $reviewdescription, $reviewenddate, $reviewmandatoryusers, $reviewoptionalusers = array(), $reviewnotificationcomment = false, $importData = array()) {
		$now = time();
		if (!is_int($reviewversionserialid) && $reviewversionserialid < 1) {
			$this->setError(_('Missing Version ID to create review'));
			return false;
		}
		if (strlen($reviewtitle) < DOCMAN__REVIEW_TITLE_MIN_SIZE || strlen($reviewtitle) > DOCMAN__REVIEW_TITLE_MAX_SIZE) {
			$this->setError(sprintf(_('Review Title must be %d characters minimum and %d characters maximum'), DOCMAN__REVIEW_TITLE_MIN_SIZE, DOCMAN__REVIEW_TITLE_MAX_SIZE));
			return false;
		}
		if (strlen($reviewdescription) < DOCMAN__REVIEW_DESCRIPTION_MIN_SIZE || strlen($reviewdescription) > DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE) {
			$this->setError(sprintf(_('Review Description must be %d characters minimum and %d characters maximum'), DOCMAN__REVIEW_DESCRIPTION_MIN_SIZE, DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE));
			return false;
		}
		if (!isset($importData['enddate']) && $reviewenddate < $now) {
			$this->setError(_('Review End date is in the past. Please set it the future'));
			return false;
		}
		if ((is_array($reviewmandatoryusers) && count($reviewmandatoryusers) == 0) || (!is_array($reviewmandatoryusers))) {
			$this->setError(_('Missing mandatory reviewers'));
			return false;
		}
		if (!is_array($reviewoptionalusers)) {
			$this->setError(_('Wrong parameter type for optional reviewers'));
			return false;
		}

		if (isset($importData['user'])) {
			$user_id = $importData['user'];
		} else {
			$user_id = ((session_loggedin()) ? user_getid() : DOCMAN__INFAMOUS_USER_ID);
		}
		if (isset($importData['startdate'])) {
			$now = $importData['startdate'];
		}

		$user = user_get_object($user_id);
		db_begin();
		$res = db_query_params('INSERT INTO doc_review (created_by, statusid, docid, startdate, enddate, title, description) VALUES ($1, $2, $3, $4, $5, $6, $7)',
					array($user->getID(), 1, $this->Document->getID(), $now, $reviewenddate, $reviewtitle, $reviewdescription));
		if ($res) {
			$notifyUsers = array();
			$revid = db_insertid($res, 'doc_review', 'revid');
			$this->fetchData($revid);
			db_query_params('INSERT INTO doc_review_version (revid, serialid) VALUES ($1, $2)', array($revid, $reviewversionserialid));
			$args = array();
			foreach ($reviewmandatoryusers as $reviewmandatoryuser) {
				$args[] = array($revid, $reviewmandatoryuser, 1, 1);
			}
			if (count($reviewoptionalusers) > 0) {
				foreach ($reviewoptionalusers as $reviewoptionaluser) {
					$args[] = array($revid, $reviewoptionaluser, 2, 1);
				}
			}
			foreach ($args as $arg) {
				db_query_params('INSERT INTO doc_review_users (revid, userid, typeid, statusid) VALUES ($1, $2, $3, $4)', $arg);
				$notifyUsers[] = $arg;

			}
			if (!isset($importData['nonotice'])) {
				$this->sendNotice($notifyUsers, true, $reviewnotificationcomment);
			}
			db_commit();
			return true;
		} else {
			db_rollback();
		}
		$this->setError(_('Unable to create review'));
		return false;
	}

	/**
	 * sendNotice - Notifies users of review.
	 *
	 * @param	array	$users array of users where IDs to be notify is arr[1].
	 * @param	bool	$new true = new review, false = reminder
	 * @param	bool	$reviewnotificationcomment
	 * @return	bool
	 */
	function sendNotice($users, $new = false, $reviewnotificationcomment = false) {
		if (count($users) > 0) {
			$createdbyuser = user_get_object($this->getCreatedBy());
			if ($new) {
				$title = _('New review started');
			} else {
				$title = _('Review still open');
			}
			$subject = '['.$this->Document->Group->getPublicName().'] '.$title.' '._('for the document').' - '.$this->Document->getName().' - '._('version ID')._(': ').$this->getVersion();
			$body = _('Project')._(': ').$this->Document->Group->getPublicName()."\n";
			$body .= _('Folder')._(': ').$this->Document->getDocGroupName()."\n";
			$body .= _('Document version')._(': ').$this->getVersion()."\n";
			$dv = documentversion_get_object($this->getVersion(), $this->Document->getID(), $this->Document->Group->getID());
			$body .= _('Document version title')._(': ').$dv->getTitle()."\n";
			$body .= _('Document version description')._(': ').util_unconvert_htmlspecialchars($dv->getDescription())."\n";
			$body .= _('Review submitter')._(': ').$createdbyuser->getRealName().' ('.$createdbyuser->getUnixName().") \n";
			$body .= _('Review due date')._(': ').strftime(_('%Y-%m-%d'), $this->getEnddate())."\n";
			if ($reviewnotificationcomment) {
				$body .= _('Specific comment from submitter')._(':')."\n";
				$body .= '-------------------------------------------------------'."\n";
				$body .= $reviewnotificationcomment;
			}
			$body2 = "\n\n-------------------------------------------------------\n".
				_('Please review, visit')._(':').
				"\n\n" . util_make_url($this->Document->getPermalink());

			foreach ($users as $user) {
				$userObject = user_get_object($user[1]);
				if ($user[2] == 1) {
					$sub_label = "\n\n"._('Your review is MANDATORY')."\n";
				} elseif ($user[2] == 2) {
					$sub_label = "\n\n"._('Your review is optional')."\n";
				}
				util_send_message($userObject->getEmail(), $subject, $body.$sub_label.$body2, 'noreply@'.forge_get_config('web_host'), '', _('Docman'));
			}
			return true;
		}
		return false;
	}

	function sendDeleteNotice($users) {
		if (count($users) > 0) {
			$deletedbyuser = session_get_user();
			$createdbyuser = user_get_object($this->getCreatedBy());
			$subject = '['.$this->Document->Group->getPublicName().'] '._('Review deleted for the document').' - '.$this->Document->getName().' - '._('version ID')._(': ').$this->getVersion();
			$body = _('Project')._(': ').$this->Document->Group->getPublicName()."\n";
			$body .= _('Folder')._(': ').$this->Document->getDocGroupName()."\n";
			$body .= _('Document version')._(': ').$this->getVersion()."\n";
			$dv = documentversion_get_object($this->getVersion(), $this->Document->getID(), $this->Document->Group->getID());
			$body .= _('Document version title')._(': ').$dv->getTitle()."\n";
			$body .= _('Document version description')._(': ').util_unconvert_htmlspecialchars($dv->getDescription())."\n";
			$body .= _('Review submitter')._(': ').$createdbyuser->getRealName().' ('.$createdbyuser->getUnixName().") \n";
			$body .= "\n\n-------------------------------------------------------\n".
				sprintf(_('This review ID %d has been deleted by %s (%s)'), $this->getID(), $deletedbyuser->getRealName(), $deletedbyuser->getUnixName());

			foreach ($users as $user) {
				$userObject = user_get_object($user[1]);
				util_send_message($userObject->getEmail(), $subject, $body, 'noreply@'.forge_get_config('web_host'), '', _('Docman'));
			}
			return true;
		}
		return false;
	}

	function getProgressbar() {
		$doneMandatoryUsers = array();
		$mandatoryUsers = $this->getMandatoryUsers();
		foreach ($mandatoryUsers as $mandatoryUser) {
			if ($mandatoryUser['statusid'] == 2) {
				$doneMandatoryUsers[] = $mandatoryUser;
			}
		}
		$percentDoneMandatoryUsers = ((count($doneMandatoryUsers) / count($mandatoryUsers)) * 100).'%';
		$doneOptionalUsers = array();
		$optionalUsers = $this->getOptionalUsers();
		foreach ($optionalUsers as $optionalUser) {
			if ($optionalUser['statusid'] == 2) {
				$doneOptionalUsers[] = $optionalUser;
			}
		}
		if (count($optionalUsers) > 0) {
			$percentDoneOptionalUsers = ((count($doneOptionalUsers) / count($optionalUsers)) * 100).'%';
		} else {
			$percentDoneOptionalUsers = _('n/a');
		}
		return html_e('span', array('title' => _('% of mandatory users with status done - % of optional users with status done')), $percentDoneMandatoryUsers.' - '.$percentDoneOptionalUsers);
	}

	function delete() {
		$users = $this->getUsers(array(1));
		$res = db_query_params('DELETE FROM doc_review WHERE revid = $1', array($this->getID()));
		if ($res) {
			if (count($users) > 0) {
				$this->sendDeleteNotice($users);
			}
			return true;
		}
		$this->setError(db_error());
		return false;
	}

	function update($reviewversionserialid, $reviewtitle, $reviewdescription, $reviewenddate, $reviewmandatoryusers, $reviewoptionalusers = array()) {
		if (!is_int($reviewversionserialid) && $reviewversionserialid < 1) {
			$this->setError(_('Missing Version ID to create review'));
			return false;
		}
		if (strlen($reviewtitle) < DOCMAN__REVIEW_TITLE_MIN_SIZE || strlen($reviewtitle) > DOCMAN__REVIEW_TITLE_MAX_SIZE) {
			$this->setError(sprintf(_('Review Title must be %d characters minimum and %d characters maximum'), DOCMAN__REVIEW_TITLE_MIN_SIZE, DOCMAN__REVIEW_TITLE_MAX_SIZE));
			return false;
		}
		if (strlen($reviewdescription) < DOCMAN__REVIEW_DESCRIPTION_MIN_SIZE || strlen($reviewdescription) > DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE) {
			$this->setError(sprintf(_('Review Description must be %d characters minimum and %d characters maximum'), DOCMAN__REVIEW_DESCRIPTION_MIN_SIZE, DOCMAN__REVIEW_DESCRIPTION_MAX_SIZE));
			return false;
		}
		if ($reviewenddate < time()) {
			$this->setError(_('Review End date is in the past. Please set it the future'));
			return false;
		}
		if ((is_array($reviewmandatoryusers) && count($reviewmandatoryusers) == 0) || (!is_array($reviewmandatoryusers))) {
			$this->setError(_('Missing mandatory reviewers'));
			return false;
		}
		if (!is_array($reviewoptionalusers)) {
			$this->setError(_('Wrong parameter type for optional reviewers'));
			return false;
		}

		db_begin();
		$res = db_query_params('UPDATE doc_review SET (enddate, title, description) = ($1, $2, $3) WHERE revid = $4', array($reviewenddate, $reviewtitle, $reviewdescription, $this->getID()));
		if ($res) {
			if ($reviewversionserialid != $this->getSerialID()) {
				db_query_params('UPDATE doc_review_version SET serialid = $1 WHERE revid = $2', array($reviewversionserialid, $this->getID()));
			}
			$mandatoryUsers = $this->getMandatoryUsers();
			$mandatoryUserIDs = array();
			foreach ($mandatoryUsers as $mandatoryUser) {
				$mandatoryUserIDs[] = $mandatoryUser['userid'];
			}
			$optionalUsers = $this->getOptionalUsers();
			$optionalUserIDs = array();
			foreach ($optionalUsers as $optionalUser) {
				$optionalUserIDs[] = $optionalUser['userid'];
			}
			foreach ($reviewmandatoryusers as $reviewmandatoryuser) {
				if (in_array($reviewmandatoryuser, $mandatoryUserIDs)) {
					unset($mandatoryUserIDs[array_search($reviewmandatoryuser, $mandatoryUserIDs)]);
				} elseif (in_array($reviewmandatoryuser, $optionalUserIDs)) {
					db_query_params('UPDATE doc_review_users SET typeid = $1 WHERE userid = $2 AND revid = $3', array(1, $reviewmandatoryuser, $this->getID()));
					unset($optionalUserIDs[array_search($reviewmandatoryuser, $optionalUserIDs)]);
				} else {
					db_query_params('INSERT INTO doc_review_users (revid, userid, typeid, statusid) VALUES ($1, $2, $3, $4)', array($this->getID(), $reviewmandatoryuser, 1, 1));
				}
			}
			foreach ($reviewoptionalusers as $reviewoptionaluser) {
				if (in_array($reviewoptionaluser, $mandatoryUserIDs)) {
					db_query_params('UPDATE doc_review_users SET typeid = $1 WHERE userid = $2 AND revid = $3', array(2, $reviewoptionaluser, $this->getID()));
					unset($mandatoryUserIDs[array_search($reviewoptionaluser, $mandatoryUserIDs)]);
				} elseif (in_array($reviewoptionaluser, $optionalUserIDs)) {
					unset($optionalUserIDs[array_search($reviewoptionaluser, $optionalUserIDs)]);
				} else {
					db_query_params('INSERT INTO doc_review_users (revid, userid, typeid, statusid) VALUES ($1, $2, $3, $4)', array($this->getID(), $reviewoptionaluser, 2, 1));
				}
			}
			foreach ($mandatoryUserIDs as $mandarotyUserID) {
				db_query_params('DELETE from doc_review_users WHERE userid = $1 AND revid = $2', array($mandarotyUserID, $this->getID()));
			}
			foreach ($optionalUserIDs as $optionalUserID) {
				db_query_params('DELETE from doc_review_users WHERE userid = $1 AND revid = $2', array($optionalUserID, $this->getID()));
			}
			db_commit();
			return true;
		}
		db_rollback();
		$this->setError(db_error());
		return false;
	}

	function close($reviewversionserialid, $reviewtitle, $reviewdescription, $reviewfinalstatus, $reviewvalidatedocument, $reviewcurrentversion) {
		db_begin();
		$res = db_query_params('UPDATE doc_review SET (enddate, title, description, statusid) = ($1, $2, $3, $4) WHERE revid = $5',
					array(time(), $reviewtitle, $reviewdescription, $reviewfinalstatus, $this->getID()));
		if ($res) {
			if ($reviewversionserialid != $this->getSerialID()) {
				unset($this->version);
				db_query_params('UPDATE doc_review_version SET serialid = $1 WHERE revid = $2', array($reviewversionserialid, $this->getID()));
			}
			if ($reviewvalidatedocument && !$this->Document->setState('1')) {
				$this->setError($this->Document->getErrorMessage());
				db_rollback();
				return false;
			}

			if ($reviewcurrentversion) {
				$dv = documentversion_get_object($this->getVersion(), $this->Document->getID(), $this->Document->Group->GetID());
				if (!$dv->update($this->getVersion(), $dv->getTitle(), $dv->getDescription(), $dv->getFiletype(), $dv->getFilename(), $dv->getFilesize(), time(), 1, $dv->getComment())) {
					$this->setError($dv->getErrorMessage());
					db_rollback();
					return false;
				}
			}
			db_commit();
			return true;
		} else {
			$this->setError(db_error());
			db_rollback();
			return false;
		}
	}

	function setUserDone($userid, $updatedate) {
		$res = db_query_params('UPDATE doc_review_users SET (statusid, updatedate) = ($1, $2) WHERE revid = $3 and userid = $4', array(2, $updatedate, $this->getID(), $userid));
		if ($res) {
			return true;
		} else {
			$this->setError(db_error());
			return false;
		}
	}
}
