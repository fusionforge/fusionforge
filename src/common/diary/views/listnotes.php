<?php
/**
 * FusionForge Diary aka blog
 *
 * Copyright 2019, Franck Villaume - TrivialDev
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

/* please do not add require here : use www/diary/index.php to add require */
global $HTML;
global $diaryNoteFactoryObject;

if ($diaryNoteFactoryObject->hasNotes(1)) {
	$year = getIntFromRequest('year', 0);
	$month = getIntFromRequest('month', 0);
	if ($year && $month) {
		echo html_e('h2', array(), _('Existing Diary and Notes Entries for').' '.$year.'/'.$month);
	} else {
		echo html_e('h2', array(), _('Existing Last 10 Diary and Notes Entries'));
	}
	echo $HTML->listTableTop();
	$cells = array();
	$cells[][] = _('Subject');
	$cells[][] = _('Date');
	echo $HTML->multiTableRow(array(), $cells, true);
	if ($year && $month) {
		foreach ($diaryNoteFactoryObject->getDIaryNoteIDsByYearAndMonth($year, $month, 1) as $diarynoteid) {
			$cells = array();
			$cells[][] = util_make_link($diaryNoteFactoryObject->getDiaryNote($diarynoteid)->getLink(), $diaryNoteFactoryObject->getDiaryNote($diarynoteid)->getSummary());
			$cells[][] = $diaryNoteFactoryObject->getDiaryNote($diarynoteid)->getDatePostedOn();
			echo $HTML->multiTableRow(array(), $cells);
		}
	} else {
		foreach ($diaryNoteFactoryObject->getDiaryNoteIDs(1, 10) as $diarynoteid) {
			$cells = array();
			$cells[][] = util_make_link($diaryNoteFactoryObject->getDiaryNote($diarynoteid)->getLink(), $diaryNoteFactoryObject->getDiaryNote($diarynoteid)->getSummary());
			$cells[][] = $diaryNoteFactoryObject->getDiaryNote($diarynoteid)->getDatePostedOn();
			echo $HTML->multiTableRow(array(), $cells);
		}
	}
	echo $HTML->listTableBottom();
} else {
	echo $HTML->information(_('This User Has No Diary Entries'));
}
