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

$is_admin_page='y';
survey_header(array('title'=>'Edit A Question','pagename'=>'survey_admin_edit_question'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>Permission Denied</h1>";
	survey_footer(array());
	exit;
}

if ($post_changes) {
	$sql="UPDATE survey_questions SET question='".htmlspecialchars($question)."', question_type='$question_type' where question_id='$question_id' AND group_id='$group_id'";
	$result=db_query($sql);
        if (db_affected_rows($result) < 1) {
                $feedback .= ' UPDATE FAILED ';
        } else {
                $feedback .= ' UPDATE SUCCESSFUL ';
        }
}

$sql="SELECT * FROM survey_questions WHERE question_id='$question_id' AND group_id='$group_id'";
$result=db_query($sql);

if ($result) {
	$question=db_result($result, 0, "question");
	$question_type=db_result($result, 0, "question_type");
} else {
	$feedback .= " Error finding question ";
}

?>
<script type="text/javascript">
<!--
var timerID2 = null;

function show_questions() {
	newWindow = open("","occursDialog","height=600,width=500,scrollbars=yes,resizable=yes");
	newWindow.location=('show_questions.php?group_id=<?php echo $group_id; ?>');
}

// -->
</script>

<h2>Editing Question #<?php echo $question_id; ?></h2>

<h3><span style="color:red">WARNING! It is a bad idea to change a question after responses to it have been submitted</span></h3>

<p>If you change a question after responses have been posted, your results pages may be misleading.</p>

<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="post_changes" value="Y" />
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>"/>
<input type="hidden" name="question_id" value="<?php echo $question_id; ?>" />

Question:
<br />
<input type="text" name="question" value="<?php echo $question; ?>" size="60" maxlength="150" />

<p>Question Type:
<br />
<?php

$sql="SELECT * FROM survey_question_types";
$result=db_query($sql);
echo html_build_select_box($result,'question_type',$question_type,false);

?>
</p>

<p><input type="submit" name="submit" value="Submit Changes"></p>
</form></p>

<p>
<form>
<input type="button" name="none" value="Show Existing Questions" onclick="show_questions()" />
</form></p>

<?php

survey_footer(array());

?>
