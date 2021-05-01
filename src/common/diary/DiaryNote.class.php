<?php
/**
 * Copyright 2019, Franck Villaume - TrivialDev
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

require_once $gfcommon.'include/FFObject.class.php';

$DIARYNOTE_OBJ = array();

/**
 * diarynote_get_object() - Get diary note object by diary note ID.
 * You should always use this instead of instantiating the object directly
 *
 * @param	int		$note_id	The ID of the diary note - required
 * @param	int|bool	$res		The result set handle ("SELECT * FROM user_diary WHERE id = $1")
 * @return	DiaryNote	a diary note object or false on failure
 */
function &diarynote_get_object($note_id, $res = false) {
	global $DIARYNOTE_OBJ;
	if (!isset($DIARYNOTE_OBJ["_".$note_id."_"])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			$res = db_query_params('SELECT * FROM user_diary WHERE id = $1',
						array($note_id));
		}
		if (!$res || db_numrows($res) < 1) {
			$DIARYNOTE_OBJ["_".$note_id."_"] = false;
		} else {
			$arr = db_fetch_array($res);
			$DIARYNOTE_OBJ["_".$note_id."_"] = new DiaryNote(user_get_object($arr['user_id']), $note_id, $arr);
		}
	}
	return $DIARYNOTE_OBJ["_".$note_id."_"];
}

class DiaryNote extends FFObject {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array	$data_array.
	 */
	var $data_array;

	/**
	 * The User object.
	 *
	 * @var	object	$User.
	 */
	var $User;

	/**
	 * cached return value of getVotes
	 * @var	int|bool	$votes
	 */
	var $votes = false;

	/**
	 * cached return value of getVoters
	 * @var	int|bool	$voters
	 */
	var $voters = false;

	/**
	 * @param	$User
	 */
	function __construct(&$User, $noteid = false, $arr = false) {
		parent::__construct();
		if (!forge_get_config('use_diary')) {
			$this->setError(_('Diary feature is off.'));
			return;
		}
		if ($User->isError()) {
			$this->setError('DiaryNote: '. $User->getErrorMessage());
			return;
		}
		$this->User =& $User;

		if ($noteid) {
			if (!$arr || !is_array($arr)) {
				if (!$this->fetchData($noteid)) {
					return;
				}
			} else {
				$this->data_array =& $arr;
				if ($this->data_array['user_id'] != $this->User->getID()) {
					$this->setError(_('DiaryNote')._(': ')._('user_id in db result does not match User Object'));
					$this->data_array = null;
					return;
				}
			}
			if (!$this->isPublic()) {
				if (!$this->getUser->getID() != user_getid()) {
					$this->setPermissionDeniedError();
					$this->data_array = null;
				}
			}
		}
	}

	/**
	 * getUser - get the $user object this note is associated with.
	 *
	 * @return	Object	The $user object.
	 */
	function &getUser() {
		return $this->User;
	}

	/**
	 * fetchData() - re-fetch the data for this diarynote from the database.
	 *
	 * @param	int	$noteid	The diary note id.
	 * @return	bool	success
	 */
	function fetchData($noteid) {
		$res = db_query_params('SELECT * FROM user_diary WHERE id=$1',
					array($noteid));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('DiaryNote: Invalid noteid'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * isPublic - whether this diary note is available to the general public.
	 *
	 * @return	bool	is_public.
	 */
	function isPublic() {
		return (($this->data_array['is_public'] == 1) ? true : false);
	}

	function getDatePostedOn() {
		return date(_('Y-m-d H:i'), $this->data_array['date_posted']);
	}

	function getSummary() {
		return $this->data_array['summary'];
	}

	function getDetails() {
		global $gfcommon;
		$result_html = util_gen_cross_ref($this->data_array['details']);
		$parsertype = forge_get_config('diary_parser_type');
		switch ($parsertype) {
			case 'markdown':
				require_once $gfcommon.'include/Markdown.include.php';
				$result_html = FF_Markdown($result_html);
				break;
			default:
				$result_html = nl2br($result_html);
		}
		return $result_html;
	}

	function getID() {
		return $this->data_array['id'];
	}

	function getLink() {
		return '/developer/?view=detail&diary_id='.$this->getID().'&diary_user='.$this->getUser()->getID();
	}

	function getAbstract() {
		global $gfcommon;
		//get the first paragraph of the diary note.
		if (strstr($this->data_array['details'], '<br/>')) {
			$arr = explode('<br/>', $this->data_array['details']);
		} else {
			$arr = explode("\n", $this->data_array['details']);
		}
		$abstract = util_gen_cross_ref($arr[0]);
		$parsertype = forge_get_config('diary_parser_type');
		switch ($parsertype) {
			case 'markdown':
				require_once $gfcommon.'include/Markdown.include.php';
				$abstract = FF_Markdown($abstract);
				break;
			default:
				$abstract = nl2br($abstract);
		}
		$arr_v = $this->getVotes();
		$content = html_e('div', array('class' => 'widget-sticker-header box'), html_e('div', array(), util_make_link($this->getLink(), $this->getSummary()).' - '.$arr_v[0].' '._('Vote(s)').'&nbsp;'._('by').'&nbsp;').util_display_user($this->getUser()->getUnixname(), $this->getUser()->getID(), $this->getUser()->GetRealname()));
		$content .= html_e('div', array('class' => 'widget-sticker-body'), $abstract.html_e('br').util_make_link($this->getLink(),_('... Read more')));
		$content .= html_e('div', array('class' => 'widget-sticker-footer'), _('Posted')._(': ').$this->getDatePostedOn());
		return html_e('div', array('class' => 'widget-sticker-container'), $content);
	}

	/**
	 * castVote - Vote on this tracker item or retract the vote
	 * @param	bool	$value	true to cast, false to retract
	 * @return	bool	success (false sets error message)
	 */
	function castVote($value = true) {
		if (!($uid = user_getid()) || $uid == 100) {
			$this->setMissingParamsError(_('User ID not passed'));
			return false;
		}
		if (!$this->canVote()) {
			$this->setPermissionDeniedError();
			return false;
		}
		$has_vote = $this->hasVote($uid);
		if ($has_vote == $value) {
			/* nothing changed */
			return true;
		}
		if ($value) {
			$res = db_query_params('INSERT INTO diary_votes (diary_id, user_id) VALUES ($1, $2)',
						array($this->getID(), $uid));
		} else {
			$res = db_query_params('DELETE FROM diary_votes WHERE diary_id = $1 AND user_id = $2',
						array($this->getID(), $uid));
		}
		if (!$res) {
			$this->setError(db_error());
			return false;
		}
		return true;
	}

	/**
	 * hasVote - Check if a user has voted on this group item
	 *
	 * @param	int|bool	$uid	user ID (default: current user)
	 * @return	bool	true if a vote exists
	 */
	function hasVote($uid = false) {
		if (!$uid) {
			$uid = user_getid();
		}
		if (!$uid || $uid == 100) {
			return false;
		}
		$res = db_query_params('SELECT * FROM diary_votes WHERE diary_id = $1 AND user_id = $2',
					array($this->getID(), $uid));
		return (db_numrows($res) == 1);
	}

	/**
	 * getVotes - get number of valid cast and potential votes
	 *
	 * @return	array|bool	(votes, voters, percent)
	 */
	function getVotes() {
		if ($this->votes !== false) {
			return $this->votes;
		}

		$lvoters = $this->getVoters();
		if (($numvoters = count($lvoters)) < 1) {
			$this->votes = array(0, 0, 0);
			return $this->votes;
		}

		$res = db_query_params('SELECT COUNT(*) AS count FROM diary_votes WHERE diary_id = $1 AND user_id = ANY($2)',
					array($this->getID(), db_int_array_to_any_clause($lvoters)));
		$db_count = db_fetch_array($res);
		$numvotes = $db_count['count'];

		/* check for invalid values */
		if ($numvotes < 0 || $numvoters < $numvotes) {
			$this->votes = array(-1, -1, 0);
		} else {
			$this->votes = array($numvotes, $numvoters,
				(int)($numvotes * 100 / $numvoters + 0.5));
		}
		return $this->votes;
	}

	/**
	 * canVote - check whether the current user can vote on
	 *		items in this tracker
	 *
	 * @return	bool	true if they can
	 */
	function canVote() {
		if ((user_getid() != $this->getUser()->getID()) && in_array(user_getid(), $this->getVoters())) {
			return true;
		}
		return false;
	}

	/**
	 * getVoters - get IDs of users that may vote on
	 *		items in this tracker
	 *
	 * @return	array	list of user IDs
	 */
	function getVoters() {
		if ($this->voters !== false) {
			return $this->voters;
		}

		$this->voters = array();
		$res = db_query_params('SELECT user_id FROM users WHERE status = $1 AND user_id != $2', array('A', $this->getUser()->getID()));
		$this->voters = util_result_column_to_array($res);
		return $this->voters;
	}
}
