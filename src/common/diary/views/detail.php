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
global $HTML;

$diary_id = getIntFromRequest('diary_id');

echo html_ao('div', array('id' => 'diary'));
echo html_ao('div', array('id' => 'diary_left'));
include ($gfcommon.'diary/views/detailnote.php');
echo html_ac(html_ap() - 1);
echo html_ao('div', array('id' => 'diary_right'));
include ($gfcommon.'diary/views/archive.php');
echo html_ac(html_ap() - 2);
