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
require_once('../../env.inc.php');
require_once('pre.php');
require_once('common/survey/SurveyQuestion.class.php');
require_once('common/survey/SurveyQuestionFactory.class.php');
require_once('www/survey/include/SurveyHTML.class.php');

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
    echo "<h1>"._('You don\'t have a permission to access this page')."</h1>";
    $sh->footer(array());
    exit;
}

/* Create a Survey Question for general purpose */
$sq = new SurveyQuestion($g, $question_id);
if (!$sq || !is_object($sq)) {
    echo "<h3>"._('Error'). ' Can not get Survey Question' ."</H3>";
} else if ( $sq->isError()) {
    echo "<h3>"._('Error'). $sq->getErrorMessage() ."</H3>";
}

/* Delete a question */
if (getStringFromRequest('delete')=="Y" && $question_id) {
     $sq->delete();

    /* Error */
    if ( $sq->isError()) {
	$msg = _('Delete failed').' '.$sq->getErrorMessage();
    } else {
	$msg = _('Delete successful');
    }
    echo "<H3>".$msg ."</H3>";
} else if (getStringFromRequest('post')=="Y") {
    /* Modification */
    if ($question_id) {
	$sq->update($question, $question_type);
	$msg = _('UPDATE SUCCESSFUL');
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
