<?php
/**
 * Developer's Diary Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/vote_function.php';
require_once $gfcommon.'diary/DiaryNoteFactory.class.php';

global $HTML;

if (!forge_get_config('use_diary')) {
	exit_disabled('home');
}

$diary_user = getIntFromRequest('diary_user');
if ($diary_user) {
	$diary_id = getIntFromRequest('diary_id');
	$diaryNoteFactoryObject = new diaryNoteFactory(user_get_object($diary_user));

	if (!$diaryNoteFactoryObject) {
		exit_error( _('Entry Not Found'), 'home');
	} elseif ($diaryNoteFactoryObject->isError()) {
		exit_error($diaryNoteFactoryObject->getErrorMessage(),'home');
	}

	$title = _('Diary and Notes for') . ' ' . $diaryNoteFactoryObject->getUser()->getRealName();
	$HTML->header(array('title' => $title));

	if ($diary_id) {
		if ($diaryNoteFactoryObject->getDiaryNote($diary_id)->isPublic()) {
			echo $HTML->boxTop($diaryNoteFactoryObject->getDiaryNote($diary_id)->getSummary());
			echo '<p>' . _('Posted on ') . $diaryNoteFactoryObject->getDiaryNote($diary_id)->getDatePostedOn().'</p>';
			echo $diaryNoteFactoryObject->getDiaryNote($diary_id)->getDetails();
			echo $HTML->boxBottom();
		} else {
			echo $HTML->error_msg(_('Entry Not Found For This User'));
		}
	}

	echo html_e('h2', array(), _('Existing Diary and Notes Entries'));
	echo $HTML->listTableTop();
	/*
		List all diary entries
	*/
	if ($diaryNoteFactoryObject->hasNotes(1)) {
		$cells = array();
		$cells[][] = _('Subject');
		$cells[][] = _('Date');
		echo $HTML->multiTableRow(array(), $cells, true);
		foreach ($diaryNoteFactoryObject->getDiaryNoteIDs(1) as $diarynoteid) {
			$cells = array();
			$cells[][] = util_make_link('/developer/diary.php?diary_id='.$diarynoteid.'&diary_user='. $diary_user, $diaryNoteFactoryObject->getDiaryNote($diarynoteid)->getSummary());
			$cells[][] = $diaryNoteFactoryObject->getDiaryNote($diary_id)->getDatePostedOn();
			echo $HTML->multiTableRow(array(), $cells);
		}
	} else {
		echo '<tr><td><strong>'._('This User Has No Diary Entries').'</strong></td></tr>';
	}
	echo $HTML->listTableBottom();

	$HTML->footer();

} else {
	exit_error(_('No User Selected'),'home');
}
