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
require_once('common/survey/SurveyResponse.class');
require_once('www/survey/include/SurveyHTML.class');

$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
    exit_no_group();
}

$sh = new  SurveyHtml();
$sh->header(array('title'=>$Language->getText('survey_resp','title'),'pagename'=>'survey_survey_resp'));

if (!$survey_id) {
	/*
		Quit if params are not provided
	*/
	echo "<h1>".$Language->getText('survey_resp','error_some_reason')."</h1>";
	$sh->footer(array());
	exit;
}

if (!session_loggedin()) {
	/*
		Tell them they need to be logged in
	*/
	echo "<h1>".$Language->getText('survey_resp','you_nedd_to_be_logged_in')."</h1>";
	echo "<p>".$Language->getText('survey_resp','unfortunately_you_have_to_be')."</p>";
	$sh->footer(array());
	exit;
}

?>

<p><?php echo $Language->getText('survey_resp','thank_you'); ?></p>
<p>&nbsp;</p>
<?php echo $Language->getText('survey_resp','regards'); ?>,
<p>&nbsp;</p>
<strong><?php echo $Language->getText('survey_resp','the_crew',array($GLOBALS['sys_name'])); ?></strong>
<p>&nbsp;</p>
<?php
/*
	Delete this customer's responses in case they had back-arrowed
*/
$result=db_query("DELETE FROM survey_responses WHERE survey_id='" . addslashes($survey_id) . "' AND group_id='" . addslashes($group_id) . "' AND user_id='".user_getid()."'");

/*
	Select this survey from the database
*/
$s = new Survey($g, $survey_id);
$quest_array= & $s->getQuestionArray();

$count=count($quest_array);
$now=time();

/* Make a dummy SurveyResponses for creating */
$sr = new SurveyResponse($g);

for ($i=0; $i<$count; $i++) {
    /*	Insert each form value into the responses table */
    
    $val="_" . $quest_array[$i];
    $response = $$val;

    $sr->create(user_getid(), $survey_id, $quest_array[$i], $response);
    if ($sr->isError()) {
	echo $sr->getErrorMessage();
    }
}

$sh->footer(array());

?>
