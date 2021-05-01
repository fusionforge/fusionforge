<?php
/**
 * Survey Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';

require_once $gfcommon.'survey/Survey.class.php';
require_once $gfcommon.'survey/SurveyFactory.class.php';
require_once $gfcommon.'survey/SurveyQuestion.class.php';
require_once $gfcommon.'survey/SurveyQuestionFactory.class.php';
require_once $gfcommon.'survey/SurveyResponse.class.php';
require_once $gfcommon.'survey/SurveyResponseFactory.class.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$graph = getStringFromRequest('graph');
$show_comment = getStringFromRequest('show_comment');

/* We need a group_id */
if (!$group_id) {
	exit_no_group();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$sh = new  SurveyHtml();

html_use_jqueryjqplotpluginCanvas();
html_use_jqueryjqplotpluginPie();
html_use_jqueryjqplotpluginhighlighter();
html_use_jqueryjqplotplugindateAxisRenderer();
html_use_jqueryjqplotpluginBar();

$title = _('Survey Results');
$sh->header(array('title' => $title, 'modal' => 1));

if (!session_loggedin() || !forge_check_perm('project_admin', $group_id)) {
	echo $HTML->error_msg(_('Permission denied.'));
	$sh->footer();
	exit;
}

/* Show detailed results of a survey */
if ($survey_id) {
	$s = new Survey($g, $survey_id);

	if (!$s || !is_object($s)) {
		echo $HTML->error_msg(_('Error')._(': ')._('Cannot get Survey'));
		$sh->footer();
		exit;
	} elseif ( $s->isError()) {
		echo $HTML->error_msg(_('Error')._(': ').$s->getErrorMessage());
		$sh->footer();
		exit;
	}

	/* A specific question */
	$question_id = getIntFromRequest('question_id');
	if ($question_id) {
		/* Create a Survey Question for general purpose */
		$sq = new SurveyQuestion($g, $question_id);
		if (!$sq || !is_object($sq)) {
			echo $HTML->error_msg(_('Error')._(': ')._('Cannot get Survey Question'));
		} elseif ($sq->isError()) {
			echo $HTML->error_msg(_('Error')._(': ').$sq->getErrorMessage());
		} else {
			showResult($sh, $s, $sq, 1, 0, $graph);
		}

	} else {
		echo html_e('h2', array(),$s->getTitle().' ('. $s->getNumberOfVotes() .' ' . _("Votes") . ')');

		/* Get questions of this survey */
		$questions = $s->getQuestionInstances();

		$question_number = 1;
		for ($i=0; $i<count($questions); $i++) {
			if ($questions[$i]->isError()) {
				echo $questions[$i]->getErrorMessage();
			} else {
				if ($questions[$i]->getQuestionType()!='4') {
		    		showResult($sh, $s, $questions[$i], $show_comment, $question_number++, $graph);
				}
			}
		}
	}
}

/* Show list of Surveys with result link */
/* Show list of Surveys */
$sf = new SurveyFactory($g);
$ss = & $sf->getSurveys();
if (!$ss) {
	echo $HTML->information(_('No Survey Question is found'));
} else {
	echo($sh->showSurveys($ss, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1));
}

$sh->footer();

/**
 * showResult - Get Result from Survey and Question. Pass the result to Show Result HTML class
 *
 *  @param object $SurveyHTML	a survey object
 *  @param object $Survey
 *  @param object $Question		a question object
 *  @param int    $show_comment	whether print out export(csv) format
 *  @param string $q_num
 *  @param int    $graph
 */
function showResult(&$SurveyHTML, &$Survey, &$Question, $show_comment=0, $q_num="", $graph=0) {
	global $HTML;
	/* Get results */
	$srf = new SurveyResponseFactory($Survey, $Question);
	if (!$srf || !is_object($srf)) {
		echo $HTML->error_msg(_('Error'). ' ' . _('Cannot get Survey Response Factory'));
	} elseif ($srf->isError()) {
		echo $HTML->error_msg(_('Error'). $srf->getErrorMessage());
	} else {
		/* Show result in HTML*/
		echo ($SurveyHTML->showResult($srf, $show_comment, $q_num, $graph));
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
