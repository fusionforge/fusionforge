<?php
/**
  *
  * SourceForge Survey Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');

require_once('common/survey/Survey.class');
require_once('common/survey/SurveyFactory.class');
require_once('common/survey/SurveyQuestion.class');
require_once('common/survey/SurveyQuestionFactory.class');
require_once('common/survey/SurveyResponse.class');
require_once('common/survey/SurveyResponseFactory.class');
require_once('www/survey/include/SurveyHTML.class');

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

$is_admin_page='y';
$sh->header(array('title'=>$Language->getText('survey_show_results','title'),'pagename'=>'survey_admin_show_results'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>".$Language->getText('survey_show_results','permission_denied')."</h1>";
	$sh->footer(array());
	exit;
}

/* Show detailed results of a survey */
if ($survey_id) {
    $s = new Survey($g, $survey_id);
		
    if (!$s || !is_object($s)) {
	echo "<h3>".$Language->getText('general','error'). ' Can not get Survey' ."</H3>";
	$sh->footer(array());
	exit;
    } else if ( $s->isError()) {
	echo "<h3>".$Language->getText('general','error'). $s->getErrorMessage() ."</H3>";
	$sh->footer(array());
	exit;
    }

    /* A specific question */
    if ($question_id) {
	/* Create a Survey Question for general purpose */
	$sq = new SurveyQuestion($g, $question_id);
	if (!$sq || !is_object($sq)) {
	    echo "<h3>".$Language->getText('general','error'). ' Can not get Survey Question' ."</H3>";
	} else if ( $sq->isError()) {
	    echo "<h3>".$Language->getText('general','error'). $sq->getErrorMessage() ."</H3>";
	} else {
	    showResult($sh, $s, $sq, 1, 0, $graph);
	}
	
    } else {
	echo '<h2>'.$s->getTitle().' ( '. $s->getNumberOfVotes() .' Votes )</h2><p/>';

	/* Get questions of this survey */
	$questions = & $s->getQuestionInstances();
	
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
/* Show list of Servey */
$sf = new SurveyFactory($g);
$ss = & $sf->getSurveys();
if (!$ss) {
    echo ($Language->getText('survey_error', 'no_question_found'));
} else {
    echo($sh->ShowSurveys($ss, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1));
}

$sh->footer(array());

/**
 * ShowResult - Get Result from Survey and Question. Pass the reuslt to Show Result HTML class
 *
 *  @param object a survey object
 *  @param object a qustsion object
 *  @param int    wheather print out export(csv) format
 */
function ShowResult(&$SurveyHTML, &$Survey, &$Question, $show_comment=0, $q_num="", $graph=0) {
    /* Get results */
    $srf = new SurveyResponseFactory(&$Survey, &$Question);
    if (!$srf || !is_object($srf)) {
	echo "<h3>".$Language->getText('general','error'). ' Can not get Survey Response Factory' ."</H3>";
    } else if ( $srf->isError()) {
	echo "<h3>".$Language->getText('general','error'). $srf->getErrorMessage() ."</H3>";
    } else {
        /* Show result in HTML*/ 
	echo ($SurveyHTML->ShowResult($srf, $show_comment, $q_num, $graph));
    }
}
?>
