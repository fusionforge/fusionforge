<?php
/**
 * Copyright 2019,2022, Franck Villaume - TrivialDev
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

require_once 'Widget.class.php';
require_once $gfcommon.'diary/DiaryFactory.class.php';

class Widget_HomeLatestDiaryNotes extends Widget {
	function __construct() {
		parent::__construct('homelatestdiarynotes');
		if (forge_get_config('use_diary')) {
			$this->title = _('Latest 5 public diary & notes.');
		}
	}

	function getCategory() {
		return _('Diary Notes');
	}

	function getTitle() {
		return $this->title;
	}

	function getDescription() {
		return _('Display last 5 public diary and notes.');
	}

	function isAvailable() {
		return isset($this->title);
	}

	function getContent() {
		global $HTML;
		$diaryFactory = new DiaryFactory();
		if ($diaryFactory->hasNotes(1)) {
			$content = $HTML->listTableTop();
			$cells = array();
			$cells[][] = _('Subject');
			$cells[][] = _('Date');
			$cells[][] = _('Author');
			$content .= $HTML->multiTableRow(array(), $cells, true);
			foreach ($diaryFactory->getDiaryNoteIDs(1) as $key => $diarynoteid) {
				if ($key < 5) {
					$cells = array();
					$cells[][] = util_make_link($diaryFactory->getDiaryNote($diarynoteid)->getLink(), $diaryFactory->getDiaryNote($diarynoteid)->getSummary());
					$cells[][] = $diaryFactory->getDiaryNote($diarynoteid)->getDatePostedOn();
					$cells[][] = util_display_user($diaryFactory->getDiaryNote($diarynoteid)->getUser()->getUnixName(), $diaryFactory->getDiaryNote($diarynoteid)->getUser()->getID(), $diaryFactory->getDiaryNote($diarynoteid)->getUser()->getRealName());
					$content .= $HTML->multiTableRow(array(), $cells);
				}
			}
			$content .= $HTML->listTableBottom();
			return $content;
		} else {
			return $HTML->warning(_('No Diary Notes found'));
		}
	}
}
