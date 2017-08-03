<?php
/**
 * FusionForge
 *
 * Copyright 2005, GForge, LLC
 * Copyright 2009-2010, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
require_once $gfcommon.'include/Validator.class.php';

function &get_group_join_requests($Group) {
	$reqs = array();
	if ($Group && is_object($Group) && !$Group->isError()) {
		$res = db_query_params('SELECT * FROM group_join_request WHERE group_id=$1',
			array($Group->getID()));
		if (db_numrows($res)) {
			while ($arr = db_fetch_array($res)) {
				$reqs[] = new GroupJoinRequest($Group, $arr['user_id'], $arr);
			}
		}
	}
	return $reqs;
}

class GroupJoinRequest extends FFError {

	/**
	 * Associative array of data from db.
	 *
	 * @var  array   $data_array.
	 */
	var $data_array;

	var $Group;

	/**
	 * @param	bool|Group	$Group		The Group object.
	 * @param	bool|int	$user_id	The user_id.
	 * @param	array|bool	$arr		The associative array of data.
	 */
	function __construct($Group = false, $user_id = false, $arr = false) {
		parent::__construct();

		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('GroupJoinRequest: '.$Group->getErrorMessage());
			return;
		}
		$this->Group =& $Group;
		if ($user_id) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($Group->getID(), $user_id)) {
					return;
				}
			} else {
				$this->data_array =& $arr;
				//
				//      Verify this message truly belongs to this Group
				//
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('group_id in db result does not match Group Object');
					return;
				}
			}
		}
	}

	/**
	 * create - create a new GroupJoinRequest in the database.
	 *
	 * @param	int	$user_id	user_id.
	 * @param	string	$comments	comments.
	 * @param	bool	$send_email	whether to send an email to the admin(s)
	 * @return	bool	Success.
	 */
	function create($user_id, $comments, $send_email = true) {
		$v = new Validator();
		$v->check($user_id, "user_id");
		$v->check(trim($comments), "comments");
		if (!$v->isClean()) {
			$this->setError($v->formErrorMsg(_("Must include ")));
			return false;
		}

		// Check if user is already a member of the project
		$user = user_get_object($user_id);
		foreach ($user->getGroups(true) as $p) {
			if ($p->getID() == $this->Group->getID()) {
				$this->setError(_('You are already a member of this project.'));
				return false;
			}
		}

		// Check if user has already submitted a request
		$result = db_query_params('SELECT * FROM group_join_request WHERE group_id=$1 AND user_id=$2',
			array($this->Group->getID(),
				$user_id));
		if (db_numrows($result)) {
			$this->setError(_('You have already sent a request to the project administrators. Please wait for their reply.'));
			return false;
		}

		db_begin();

		$result = db_query_params('INSERT INTO group_join_request (group_id,user_id,comments,request_date)
			VALUES ($1, $2, $3, $4)',
			array($this->Group->getID(),
				$user_id,
				htmlspecialchars($comments),
				time()));
		if (!$result || db_affected_rows($result) < 1) {
			$this->setError('GroupJoinRequest::create() Posting Failed '.db_error());
			db_rollback();
			return false;
		}

		if (!$this->fetchData($this->Group->getID(), $user_id)) {
			db_rollback();
			return false;
		}
		if ($send_email) {
			$this->sendJoinNotice();
		}
		db_commit();
		return true;
	}

	/**
	 * fetchData - re-fetch the data for this GroupJoinRequest from the database.
	 *
	 * @param	int	$group_id	The group_id.
	 * @param	int	$user_id	The user_id.
	 * @return	bool	success.
	 */
	function fetchData($group_id, $user_id) {
		$res = db_query_params('SELECT * FROM group_join_request WHERE user_id=$1 AND group_id=$2',
			array($user_id,
				$this->Group->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError('GroupJoinRequest::fetchData() Invalid ID '.db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getID - get this GroupJoinRequest ID
	 *
	 * @return	int	The group_id.
	 */
	function getID() {
		return $this->data_array['group_id'];
	}

	/**
	 * getGroup - get the group object.
	 *
	 * @return	Group	The Group.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getUserId - get the field user_id.
	 *
	 * @return	int	The field.
	 */
	function getUserId() {
		return $this->data_array['user_id'];
	}

	/**
	 * getComments - get the field comments.
	 *
	 * @return	string	The field.
	 */
	function getComments() {
		return $this->data_array['comments'];
	}

	/**
	 * getRequestDate - get the field request_date.
	 *
	 * @return	int	The field.
	 */
	function getRequestDate() {
		return $this->data_array['request_date'];
	}

	/**
	 * sendJoinNotice() - send mail notification to project admin when user requests to join the project
	 *
	 * @return	bool	true/false.
	 */
	function sendJoinNotice() {
		$user =& session_get_user();
		$admins =& $this->Group->getAdmins();
		for ($i = 0; $i < count($admins); $i++) {
			setup_gettext_for_user($admins[$i]);

			$email = $admins[$i]->getEmail();
			$subject = sprintf(_('Request to Join Project %1$s from %2$s (%3$s)'),
				$this->Group->getPublicName(),
				$user->getRealName(),
				$user->getUnixName()
			);
			$comments = util_unconvert_htmlspecialchars($this->data_array["comments"]);
			$body = sprintf(_('%1$s (%2$s) has requested to join your project.'),
							 $user->getRealName(), $user->getUnixName());
			$body .= "\n";
			$body .= sprintf(_('You can approve this request here: %s'),
							 util_make_url('/project/admin/users.php?group_id='.$this->Group->getID()));
			$body .= "\n\n";
			$body .= _('Comments by the user:');
			$body .= "\n";
			$body .= $comments;
			$body = str_replace("\\n", "\n", $body);

			util_send_message($email, $subject, $body);
		}
		setup_gettext_from_context();
		return true;
	}

	/**
	 * reject() - reject the join and send a notification to the user
	 *
	 * @return	bool	success.
	 */
	function reject() {
		$user = user_get_object($this->getUserId());
		setup_gettext_for_user($user);
		$subject = sprintf(_('Request to Join Project %s'), $this->Group->getPublicName());
		$body = sprintf(_('Your request to join the %s project was denied by an administrator.'), $this->Group->getPublicName());
		util_send_message($user->getEmail(), $subject, $body);
		setup_gettext_from_context();
		return $this->delete(1);
	}

	/**
	 * send_accept_mail() - send a notification to the user on accept
	 *
	 */
	function send_accept_mail() {
		$user = user_get_object($this->getUserId());
		setup_gettext_for_user($user);
		$subject = sprintf(_('Request to Join Project %s'), $this->Group->getPublicName());
		$body = sprintf(_('Your request to join the %s project was granted by an administrator.'), $this->Group->getPublicName());
		util_send_message($user->getEmail(), $subject, $body);
		setup_gettext_from_context();
	}

	/**
	 * delete() - delete this row from the database.
	 *
	 * @param	bool	$sure	I'm Sure.
	 * @return	bool	true/false.
	 */
	function delete($sure) {
		if (!$sure) {
			$this->setError(_('Must be sure before deleting'));
			return false;
		}
		if (!forge_check_perm('project_admin', $this->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		} else {
			$res = db_query_params('DELETE FROM group_join_request WHERE group_id=$1 AND user_id=$2',
				array($this->Group->getID(),
					$this->getUserId()));
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError(_('Delete failed')._(': ').db_error());
                return false;
			} else {
				return true;
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
