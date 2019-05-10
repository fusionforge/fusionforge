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
					return;
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
		$abstract = $arr[0];
		$parsertype = forge_get_config('diary_parser_type');
		switch ($parsertype) {
			case 'markdown':
				require_once $gfcommon.'include/Markdown.include.php';
				$abstract = FF_Markdown($abstract);
				break;
			default:
				$abstract = nl2br($abstract);
		}
		$content = html_e('div', array('class' => 'widget-sticker-header box'), html_e('div', array(),util_make_link($this->getLink(), $this->getSummary()).'&nbsp;'._('by').'&nbsp;').util_display_user($this->getUser()->getUnixname(), $this->getUser()->getID(), $this->getUser()->GetRealname()));
		$content .= html_e('div', array('class' => 'widget-sticker-body'), $abstract.html_e('br').util_make_link($this->getLink(),_('... Read more')));
		$content .= html_e('div', array('class' => 'widget-sticker-footer'), _('Posted')._(': ').$this->getDatePostedOn());
		return html_e('div', array('class' => 'widget-sticker-container'), $content);
	}
}
