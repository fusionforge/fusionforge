<?php
/**
 * Survey Facility: Question handle program
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2008-2010 (c) FusionForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'survey/SurveyQuestion.class.php';
require_once $gfcommon.'survey/SurveyQuestionFactory.class.php';
require_once $gfwww.'survey/include/SurveyHTML.class.php';

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$question_id = getIntFromRequest('question_id');
$question = getStringFromRequest('question');
$question_type = getStringFromRequest('question_type');

/* We need a group_id */ 
if (!$group_id) {
    exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$is_admin_page='y';
$sh = new  SurveyHtml();
$sh->header(array('title'=>_('Add A Question')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
    echo '<div class="error">'._('Permission denied').'</div>';
    $sh->footer(array());
    exit;
}

/* Create a Survey Question for general purpose */
$sq = new SurveyQuestion($g, $question_id);
if (!$sq || !is_object($sq)) {
    echo '<div class="error">'._('Error'). ' ' . _('Cannot get Survey Question') ."</div>";
} else if ( $sq->isError()) {
    echo '<div class="error">'._('Error'). $sq->getErrorMessage() ."</div>";
}

/* Delete a question */
if (getStringFromRequest('delete')=="Y" && $question_id) {
     $sq->delete();

    /* Error */
    if ( $sq->isError()) {
	$msg = _('Delete failed').' '.$sq->getErrorMessage();
        echo '<div class="error">' .$msg ."</div>";
    } else {
	$msg = _('Delete successful');
        echo '<div class="feedback">' .$msg ."</div>";
    }
} else if (getStringFromRequest('post')=="Y") {
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
    if ( $sq->isError()) {
		$msg = $sq->getErrorMessage();
		form_release_key(getStringFromRequest("form_key"));
        echo '<div class="error">' .$msg ."</div>";
    } else {
        echo '<div class="feedback">' .$msg ."</div>";
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
$sqs = & $sqf->getSurveyQuestions();
if (!$sqs) {
    echo (_('No questions found'));
} else {
    echo($sh->showQuestions($sqs));
}

$sh->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
