<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
require_once('pre.php');
require_once('common/survey/Survey.class');
require_once('www/survey/include/SurveyHTML.class');

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');

/* We need a group_id */ 
if (!$group_id) {
    exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

// Check to make sure they're logged in.
if (!session_loggedin()) {
	exit_not_logged_in();
}

$sh = new  SurveyHtml();
$s = new Survey($g, $survey_id);

$sh->header(array('title'=>$Language->getText('survey','title')));

if (!$survey_id) {
    echo "<h1>".$Language->getText('survey','for_some_reason')."</h1>";
} else {
    echo($sh->ShowSurveyForm($s));
}

$sh->footer(array());

?>
