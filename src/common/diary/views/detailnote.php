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

global $diaryNoteFactoryObject;
global $diary_user;
global $HTML;

$diary_id = getIntFromRequest('diary_id');

if (!$diary_id) {
	$diary_id = $diaryNoteFactoryObject->getLastDiaryID(1);
}

if ($diary_id) {
	if ($diaryNoteFactoryObject->getDiaryNote($diary_id)->isPublic()) {
		echo $HTML->boxTop($diaryNoteFactoryObject->getDiaryNote($diary_id)->getSummary());
		echo html_e('p', array(), _('Posted on ') . $diaryNoteFactoryObject->getDiaryNote($diary_id)->getDatePostedOn());

		$votes = $diaryNoteFactoryObject->getDiaryNote($diary_id)->getVotes();
		if ($votes[1]) {
			$content = html_e('span', array('id' => 'diary-votes'), html_e('strong', array(), _('Votes') . _(': ')).sprintf('%1$d/%2$d (%3$d%%)', $votes[0], $votes[1], $votes[2]));
			if ($diaryNoteFactoryObject->getDiaryNote($diary_id)->canVote()) {
				if ($diaryNoteFactoryObject->getDiaryNote($diary_id)->hasVote()) {
					$key = 'pointer_down';
					$txt = _('Retract Vote');
				} else {
					$key = 'pointer_up';
					$txt = _('Cast Vote');
				}
				$content .= util_make_link('/developer/?diary_id='.$diary_id.'&diary_user='.$diary_user.'&action='.$key, html_image('ic/'.$key.'.png', 16, 16), array('id' => 'group-vote', 'alt' => $txt));
			}
			echo html_e('p', array(), $content);
		}
		echo $diaryNoteFactoryObject->getDiaryNote($diary_id)->getDetails();
		echo $HTML->boxBottom();
	} else {
		echo $HTML->error_msg(_('Entry Not Found For This User'));
	}
}
