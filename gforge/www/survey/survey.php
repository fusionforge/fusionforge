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
require_once('www/survey/include/SurveyHTML.class');


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

$sh->header(array('title'=>$Language->getText('survey','title'),'pagename'=>'survey_survey'));

if (!$survey_id) {
    echo "<h1>".$Language->getText('survey','for_some_reason')."</h1>";
} else {
    echo($sh->ShowSurveyForm($s));
}

$sh->footer(array());

?>
