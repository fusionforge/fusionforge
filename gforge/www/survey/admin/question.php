<?php
/**
  *
  * GForge Survey Facility: Question handle program
  *
  * Copyright 2004 (c) GForge
  * http://gforge.org
  *
  *
  */
require_once('pre.php');
require_once('common/survey/SurveyQuestion.class');
require_once('common/survey/SurveyQuestionFactory.class');
require_once('www/survey/include/SurveyHTML.class');

$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
$question_id = getIntFromRequest('question_id');
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
$sh->header(array('title'=>$Language->getText('survey_add_question','title'),'pagename'=>'survey_admin_add_question'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
    echo "<h1>".$Language->getText('survey_error','permission_denied')."</h1>";
    $sh->footer(array());
    exit;
}

/* Create a Survey Question for general purpose */
$sq = new SurveyQuestion($g, $question_id);
if (!$sq || !is_object($sq)) {
    echo "<h3>".$Language->getText('general','error'). ' Can not get Survey Question' ."</H3>";
} else if ( $sq->isError()) {
    echo "<h3>".$Language->getText('general','error'). $sq->getErrorMessage() ."</H3>";
}

/* Delete a question */
if (getStringFromRequest('delete')=="Y" && $question_id) {
     $sq->delete();

    /* Error */
    if ( $sq->isError()) {
	$msg = $Language->getText('survey_edit','delete_failed').' '.$sq->getErrorMessage();
    } else {
	$msg = $Language->getText('survey_edit','delete_successful');
    }
    echo "<H3>".$msg ."</H3>";
} else if (getStringFromRequest('post')=="Y") {
    /* Modification */
    if ($question_id) {
	$sq->update($question, $question_type);
	$msg = $Language->getText('survey_edit_question','update_successful');
    } else { /* adding new question */
	$sq->create($question, $question_type);
	$msg = $Language->getText('survey_add_question', 'question_added'); 
    }
    
    /* Error */
    if ( $sq->isError()) {
	$msg = $sq->getErrorMessage();
    }
    
    echo "<H3>".$msg ."</H3>";

    /* Add now Question */
    $sq = false;
}

/* Show Add/Modify form 
 * If $question is null it is add form, otherwise modify 
 */
echo($sh->showAddQuestionForm($sq));

/* Show existing questions 
 */
$sqf = new SurveyQuestionFactory($g);
$sqs = & $sqf->getSurveyQuestions();
if (!$sqs) {
    echo ($Language->getText('survey_error', 'no_questions_found'));
} else {
    echo($sh->showQuestions($sqs));
}

$sh->footer(array());
?>
