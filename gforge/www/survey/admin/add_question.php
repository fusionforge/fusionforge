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
survey_header(array('title'=>'Add A Question','pagename'=>'survey_admin_add_question'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>Permission Denied</h1>";
	survey_footer(array());
	exit;
}

if ($post_changes) {
	$sql="INSERT INTO survey_questions (group_id,question,question_type) VALUES ('$group_id','".htmlspecialchars($question)."','$question_type')";
	$result=db_query($sql);
	if ($result) {
		$feedback .= "Question Added";
	} else {
		$feedback .= "Error inserting question";
	}
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

<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="post_changes" value="Y" />
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
Question:<br />
<input type="text" name="question" value="" size="60" maxlength="150" />
<p>

Question Type:<br />
<?php

$sql="SELECT * from survey_question_types";
$result=db_query($sql);
echo html_build_select_box($result,'question_type','xzxz',false);

?>
</p>

<p><input type="submit" name="submit" value="Add This Question"></p>
</form></p>

<p>
<form>
<input type="button" name="none" value="Show Existing Questions" onclick="show_questions()" />
</form></p>

<?php

survey_footer(array());

?>
