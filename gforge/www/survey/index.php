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
require_once('common/survey/SurveyFactory.class');
require_once('www/survey/include/SurveyHTML.class');

/* We need a group_id */ 
if (!$group_id) {
    exit_no_group();
}

$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$user_id = user_getid();

/* Show header */
$sh = new  SurveyHtml();
$sh->header(array('title'=>$Language->getText('survey_index','title'),'pagename'=>'survey','titlevals'=>array(group_getname($group_id))));

/* Show list of Servey */
$sf = new SurveyFactory($g);
$ss = & $sf->getSurveys();
if (!$ss) {
    echo ($Language->getText('survey_error', 'no_survey_found'));
} else {
    echo($sh->showSurveys($ss, 0, 0, 1, 1, 1, 0));
}

$sh->footer(array());
?>
