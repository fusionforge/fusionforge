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
require_once('common/survey/Survey.class');
require_once('common/survey/SurveyFactory.class');
require_once('common/survey/SurveyQuestion.class');
require_once('common/survey/SurveyQuestionFactory.class');
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

$is_admin_page='y';
$sh = new  SurveyHtml();
$s = new Survey($g, $survey_id);

$sh->header(array('title'=>$Language->getText('survey_add_survey','title')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>". $Language->getText('survey_add_question','permission_denied')."</h1>";
	$sh->footer(array());
	exit;
}

if (getStringFromRequest('post')=="Y") {
    if (!form_key_is_valid(getStringFromRequest('form_key'))) {
		exit_form_double_submit();
	}
	$survey_title = getStringFromRequest('survey_title');
    $to_add = getStringFromRequest('to_add');
    $to_del = getStringFromRequest('to_del');
    $is_active = getStringFromRequest('is_active');
  
    if ($survey_id) { /* Modify */
	$s->update($survey_title, $to_add, $to_del, $is_active);
	$feedback = $Language->getText('survey_edit','update_successful');
    }  else {  /* Add */
	$s->create($survey_title, $to_add, $is_active);
	$feedback = $Language->getText('survey_add_survey', 'survey_inserted');
    }
}

/* Order changes */
if (getStringFromRequest('updown')=="Y") {
    $question_id = getIntFromRequest('question_id');
    $is_up = getStringFromRequest('is_up');

    $s->updateOrder($question_id, $is_up);
    $feedback = $Language->getText('survey_edit','update_successful');
}

/* Error on previous transactions? */
if ($s->isError()) {
    $feedback = $s->getErrorMessage();
    form_release_key(getStringFromRequest("form_key"));
} 

echo ($sh->ShowAddSurveyForm($s));

/* Show list of Servey */
$sf = new SurveyFactory($g);
$ss = & $sf->getSurveys();
if (!$ss) {
    echo ($Language->getText('survey_error', 'no_question_found'));
} else {
    echo($sh->ShowSurveys($ss, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1));
}

$sh->footer(array());
?>
