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

survey_header(array('title'=>'Survey Complete','pagename'=>'survey_survey_resp'));

if (!$survey_id || !$group_id) {
	/*
		Quit if params are not provided
	*/
	echo "<H1>Error - For some reason group_id and/or survey_id did not make it here</H1>";
	survey_footer(array());
	exit;
}

if (!session_loggedin()) {
	/*
		Tell them they need to be logged in
	*/
	echo "<H1>You need to be logged in</H1>";
	echo "Unfortunately, you have to be logged in to participate in surveys.";
	survey_footer(array());
	exit;
}

?>

Thank you for taking time to complete this survey.
<P>
Regards,
<P>
<B>The <?php echo $GLOBALS['sys_name']; ?> Crew</B>
<P>
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
		"VALUES ('".user_getid()."','" . addslashes($group_id) . "','" . addslashes($survey_id) . "','" . addslashes($quest_array[$i]) . "','". addslashes($$val) . "','$now')";
	$result=db_query($sql);
	if (!$result) {
		echo "<h1>Error</h1>";
	}
}

survey_footer(array());

?>
