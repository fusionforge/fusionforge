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
require_once $gfcommon.'diary/DiaryNote.class.php';

class DiaryNoteFactory extends FFObject {

	var $diarynoteids = false;

	/**
	 * The User object.
	 *
	 * @var	object	$User.
	 */
	var $User;

	/**
	 * @param	$User
	 */
	function __construct(&$User) {
		parent::__construct();
		if (!forge_get_config('use_diary')) {
			$this->setError(_('Diary feature is off.'));
			return;
		}
		if ($User->isError()) {
			$this->setError('DiaryNoteFactory: '. $User->getErrorMessage());
			return;
		} elseif (!$User->isActive()) {
			$this->setError(_('User not available'));
			return;
		}
		$this->User =& $User;
	}

	function getDiaryNoteIDs($public = 0, $limit = 0) {
		if (is_array($this->diarynoteids)) {
			if ($limit) {
				return array_slice($this->diarynoteids, 0, $limit);
			} else {
				return $this->diarynoteids;
			}
		}
		$qpa = false;
		$qpa = db_construct_qpa($qpa, 'SELECT id FROM user_diary WHERE user_id = $1', array($this->User->getID()));
		if ($public) {
			$qpa = db_construct_qpa($qpa, ' AND is_public = $1', array($public));
		}
		$qpa = db_construct_qpa($qpa, ' ORDER BY date_posted DESC', array());
		$res = db_query_qpa($qpa);
		$this->diarynoteids = util_result_column_to_array($res);
		if ($limit) {
			return array_slice($this->diarynoteids, 0, $limit);
		} else {
			return $this->diarynoteids;
		}
	}

	function hasNotes($public = 0) {
		$this->getDiaryNoteIDs($public);
		if (is_array($this->diarynoteids) && count($this->diarynoteids)) {
			return true;
		}
		return false;
	}

	function getDiaryNote($diarynote_id) {
		return diarynote_get_object($diarynote_id);
	}

	function getUser() {
		return $this->User;
	}

	function getArchivesTree($public = 0) {
		global $HTML;
		$qpa = false;
		$qpa = db_construct_qpa($qpa, 'SELECT count(id), year, month FROM user_diary WHERE user_id = $1', array($this->User->getID()));
		if ($public) {
			$qpa = db_construct_qpa($qpa, ' AND is_public = $1', array($public));
		}
		$qpa = db_construct_qpa($qpa, ' GROUP BY year, month ORDER by year DESC, month DESC');
		$res = db_query_qpa($qpa);
		if ($res && db_numrows($res)) {
			$liElements = array();
			while ($arr = db_fetch_array($res)) {
				$liElements[]['content'] = $arr[1].'/'.$arr[2].' ('.$arr[0].')';
			}
			return $HTML->html_list($liElements);
		} else {
			return $HTML->information(_('No archive available'));
		}
	}
}
