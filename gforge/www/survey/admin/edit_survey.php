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
survey_header(array('title'=>'Edit A Survey','pagename'=>'survey_admin_edit_survey'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>Permission Denied</h1>";
	survey_footer(array());
	exit;
}

if ($post_changes) {
	if (!isset($survey_title) || $survey_title == "")
	{
		$feedback .= ' UPDATE FAILED: Survey Title Required';
	}
	elseif (!isset($survey_questions) || $survey_questions == "")
	{
		$feedback .= ' UPDATE FAILED: Survey Questions Required';
	}
	if (!isset($survey_id) || !isset($group_id) || $survey_id == "" || $group_id == "")
	{
		$feedback .= ' UPDATE FAILED: Missing Data';
	}
	else
	{
		if ($is_active) {
			$is_active = 1;
		} else {
			$is_active = 0;
		}
		$sql="UPDATE surveys SET survey_title='".htmlspecialchars($survey_title)."', survey_questions='$survey_questions', is_active='$is_active' ".
			 "WHERE survey_id='$survey_id' AND group_id='$group_id'";
		$result=db_query($sql);
		if (db_affected_rows($result) < 1) {
			$feedback .= ' UPDATE FAILED ';
			echo db_error();
		} else {
			$feedback .= ' UPDATE SUCCESSFUL ';
		}
	}
}

/*
	Get this survey out of the DB
*/
if ($survey_id) {
	$sql="SELECT * FROM surveys WHERE survey_id='$survey_id' AND group_id='$group_id'";
	$result=db_query($sql);
	$survey_title=db_result($result, 0, "survey_title");
	$survey_questions=db_result($result, 0, "survey_questions");
	$is_active=db_result($result, 0, "is_active");
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

<h3><span style="color:red">WARNING! It is a bad idea to edit a survey after responses have been posted</span></h3>

<p>If you change a survey after you already have responses, your results pages could be misleading or messed up.</p>
<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="post_changes" value="y" />
<strong>Name of Survey:</strong>
<br />
<input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>" />
<input type="text" name="survey_title" value="<?php echo $survey_title; ?>" length="60" maxlength="150" />
<p>
<strong>Questions:</strong>
<br />
List question numbers, in desired order, separated by commas. <strong>Refer to your list of questions</strong> so you can view 
the question id's. Do <strong>not</strong> include spaces or end your list with a comma.
<br />
Ex: 1,2,3,4,5,6,7
<br /><input type="text" name="survey_questions" value="<?php echo $survey_questions; ?>" length="90" maxlength="1500" /></p>
<p>
<strong>Is Active</strong>
<br /><input type="radio" name="is_active" value="1"<?php if ($is_active=='1') { echo ' checked="checked"'; } ?> /> Yes
<br /><input type="radio" name="is_active" value="0"<?php if ($is_active=='0') { echo ' hecked="checked"'; } ?> /> No</p>
<p>
<input type="submit" name="submit" value="Submit Changes"></p>
</form></p>

<?php

Function  ShowResultsEditSurvey($result) {
	global $group_id,$PHP_SELF;
	$rows  =  db_NumRows($result);
	$cols  =  db_NumFields($result);
	echo "<h3>$rows Found</h3>";

	if ($rows > 0) {
		echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";
		/*  Create  the  headers  */
		echo "<tr style=\"background-color:$GLOBALS[COLOR_MENUBARBACK]\">\n";
		for ($i = 0; $i < $cols; $i++)  {
			printf( "<th><span><strong>%s</strong></span></th>\n",  db_fieldname($result,$i));
		}
		echo "</tr>";
		for ($j=0; $j<$rows; $j++)  {

			if ($j%2==0) {
				$row_bg="white";
			} else {
				$row_bg="$GLOBALS[COLOR_LTBACK1]";
			}

			echo "<tr style=\"background-color:$row_bg\">\n";

			echo "<td><a href=\"$PHP_SELF?group_id=$group_id&amp;survey_id=".
				db_result($result,$j,0)."\">".db_result($result,$j,0)."</a></td>";
			for ($i = 1; $i < $cols; $i++)  {
				printf("<td>%s</td>\n",db_result($result,$j,$i));
			}

			echo "</tr>";
		}
		echo "</table>"; //</td></tr></TABLE>";
	}
}

/*
	Select all surveys from the database
*/

$sql="SELECT * FROM surveys WHERE group_id='$group_id'";

$result=db_query($sql);

?>
<p>
<form>
<input type="button" name="none" value="Show Existing Questions" onclick="show_questions()" />
</form></p>
<p>&nbsp;</p>
<h2>Existing Surveys</h2>
<?php

ShowResultsEditSurvey($result);

survey_footer(array());
?>
