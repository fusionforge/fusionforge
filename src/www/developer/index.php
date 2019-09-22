<?php
/**
 * FusionForge Diary aka Blog feature
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/vote_function.php';
require_once $gfcommon.'diary/DiaryNoteFactory.class.php';

global $HTML;

if (!forge_get_config('use_diary')) {
	exit_disabled('home');
}

/* get informations from request or $_POST */
$diary_user = getIntFromRequest('diary_user');

/* validate user */
if (!$diary_user)
	exit_no_user();

$user = user_get_object($diary_user);
if (!$user || !is_object($user) || !$user->isActive())
	exit_no_user();

$diaryNoteFactoryObject = new diaryNoteFactory(user_get_object($diary_user));

if (!$diaryNoteFactoryObject) {
	exit_error( _('Entry Not Found'), 'home');
} elseif ($diaryNoteFactoryObject->isError()) {
	exit_error($diaryNoteFactoryObject->getErrorMessage(),'home');
}

/* everything sounds ok, now let's do the job */
$action = getStringFromRequest('action');
if (file_exists(forge_get_config('source_path').'/common/diary/actions/'.$action.'.php')) {
	include(forge_get_config('source_path').'/common/diary/actions/'.$action.'.php');
}

$title = _('Diary and Notes for') . ' ' . $diaryNoteFactoryObject->getUser()->getRealName();
$HTML->header(array('title' => $title));

echo html_ao('div', array('id' => 'views'));
include ($gfcommon.'diary/views/views.php');
echo html_ac(html_ap() - 1);

$HTML->footer();
