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
require_once('www/survey/survey_utils.php');

survey_header(array('title'=>$Language->getText('survey_resp','title'),'pagename'=>'survey_survey_resp'));

if (!$survey_id || !$group_id) {
	/*
		Quit if params are not provided
	*/
	echo "<h1>".$Language->getText('survey_resp','error_some_reason')."</h1>";
	survey_footer(array());
	exit;
}

if (!session_loggedin()) {
	/*
		Tell them they need to be logged in
	*/
	echo "<h1>".$Language->getText('survey_resp','you_nedd_to_be_logged_in')."</h1>";
	echo "<p>".$Language->getText('survey_resp','unfortunately_you_have_to_be')."</p>";
	survey_footer(array());
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

$sql="select * from surveys where survey_id='$survey_id'";

$result=db_query($sql);

/*
	Select the questions for this survey
*/
$questions = db_result($result, 0, "survey_questions");
$questions = str_replace(" ", "", $questions);
$quest_array=explode(',', $questions);

$count=count($quest_array);
$now=time();

for ($i=0; $i<$count; $i++) {

	/*
		Insert each form value into the responses table
	*/

	$val="_" . $quest_array[$i];

	$sql="INSERT INTO survey_responses (user_id,group_id,survey_id,question_id,response,date) ".
		"VALUES ('".user_getid()."','" . addslashes($group_id) . "','" . addslashes($survey_id) . "','" . addslashes($quest_array[$i]) . "','". htmlspecialchars(addslashes($$val)) . "','$now')";
	$result=db_query($sql);
	if (!$result) {
		echo $Language->getText('survey_resp','error');
	}
}

survey_footer(array());

?>
