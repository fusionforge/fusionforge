<?php
/**
 * Survey Facility: Question handle program
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2008-2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014, Franck Villaume - TrivialDev
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
require_once $gfcommon.'survey/SurveyQuestion.class.php';
require_once $gfcommon.'survey/SurveyQuestionFactory.class.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

global $HTML;

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$question_id = getIntFromRequest('question_id');
$question = getStringFromRequest('question');
$question_type = getStringFromRequest('question_type');

/* We need a group_id */
if (!$group_id) {
	exit_no_group();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$is_admin_page='y';
$sh = new SurveyHtml();

$title = $question_id ? _('Edit a Question') : _('Add a Question');
$sh->header(array('title'=>$title, 'modal'=>1));

if (!session_loggedin() || !forge_check_perm('project_admin', $group_id)) {
	echo $HTML->error_msg(_('Permission denied.'));
	$sh->footer();
	exit;
}

/* Create a Survey Question for general purpose */
$sq = new SurveyQuestion($g, $question_id);
if (!$sq || !is_object($sq)) {
	echo $HTML->error_msg(_('Error'). ' ' . _('Cannot get Survey Question'));
} elseif ($sq->isError()) {
	echo $HTML->error_msg(_('Error'). $sq->getErrorMessage());
}

/* Delete a question */
if (getStringFromRequest('delete')=="Y" && $question_id) {
	$sq->delete();

	/* Error */
	if ($sq->isError()) {
		echo $HTML->error_msg(_('Delete failed').' '.$sq->getErrorMessage());
	} else {
		echo $HTML->feedback(_('Successfully Deleted.'));
	}
} elseif (getStringFromRequest('post')=="Y") {
	/* Modification */
	if ($question_id) {
		$sq->update($question, $question_type);
		$msg = _('Update Successful');
	} else { /* adding new question */
		$question = getStringFromRequest('question');
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit();
		}
		$sq->create($question, $question_type);
		$msg = _('Question Added');
	}

	/* Error */
	if ($sq->isError()) {
		echo $HTML->error_msg($sq->getErrorMessage());
		form_release_key(getStringFromRequest("form_key"));
	} else {
		echo $HTML->feedback($msg);
	}

	/* Add now Question */
	$sq = false;
}

/* Show Add/Modify form
 * If $question is null it is add form, otherwise modify
 */
echo($sh->showAddQuestionForm($sq));

/* Show existing questions (if any)
 */
$sqf = new SurveyQuestionFactory($g);
$sqs = $sqf->getSurveyQuestions();
if (!$sqs) {
	echo $HTML->information(_('No questions found'));
} else {
	echo($sh->showQuestions($sqs));
}

$sh->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
