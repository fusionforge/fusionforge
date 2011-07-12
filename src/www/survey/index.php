<?php
/**
 * Survey Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * http://fusionforge.org/
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'survey/SurveyFactory.class.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');

 /* We need a group_id */
if (!$group_id) {
    exit_no_group();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$user_id = user_getid();

/* Show header */
$title = sprintf(_('Surveys for %1$s'), $g->getPublicName());
$sh = new SurveyHtml();
$sh->header(array('title'=>$title));

plugin_hook ("blocks", "survey index");

/* Show list of Servey */
$sf = new SurveyFactory($g);
$ss = & $sf->getSurveys();
if (!$ss) {
    echo '<p class="warning_msg">' . (_('No Survey is found')) . '</p>';
} else {
	echo '<p>' . _('Select a survey to vote') . '</p>';
    echo($sh->showSurveys($ss, 0, 0, 1, 1, 1, 0));
}

$sh->footer(array());
?>
