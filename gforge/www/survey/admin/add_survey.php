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
survey_header(array('title'=>'Add A Survey','pagename'=>'survey_admin_add_survey'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>Permission Denied</h1>";
	survey_footer(array());
	exit;
}

if ($post_changes) {
	//$survey_questions=trim(ltrim($survey_questions));
	$sql="insert into surveys (survey_title,group_id,survey_questions) values ('".htmlspecialchars($survey_title)."','$group_id','$survey_questions')";
	$result=db_query($sql);
	if ($result) {
		$feedback .= " Survey Inserted ";
	} else {
		$feedback .= " Error in Survey Insert ";
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


<form action="<?php echo $PHP_SELF; ?>" method="post">

<strong>Name of Survey:</strong>
<br />
<input type="text" name="survey_title" value="" length="60" maxlength="150" /><p>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="post_changes" value="y" />
List question numbers, in desired order, separated by commas. <strong>Refer to your list of questions</strong> so you can view
the question id's. Do <strong>not</strong> include spaces or end your list with a comma.
<br />
Ex: 1,2,3,4,5,6,7
<br /><input type="text" name="survey_questions" value="" length="90" maxlength="1500" /></p>
<p><strong>Is Active</strong>
<br /><input type="radio" name="is_active" value="1" checked="checked" /> Yes
<br /><input type="radio" name="is_active" value="0" /> No</p>
<p>
<input type="submit" name="SUBMIT" value="Add This Survey" /></p>
</form></p>

<?php

Function  ShowResultsEditSurvey($result) {
	global $group_id;
	$rows  =  db_NumRows($result);
	$cols  =  db_NumFields($result);
	echo "<h3>$rows Found</h3>";

	if ($rows > 0) {
		echo /*"<table bgcolor=\"NAVY\"><tr><td bgcolor=\"NAVY\">*/ "<table border=\"0\">\n";

		/*  Create  the  headers  */
		echo "<tr style=\"background-color:$GLOBALS[COLOR_MENUBARBACK]\">\n";
		for ($i  =  0;  $i  <  $cols;  $i++)  {
			printf( "<th><span><strong>%s</strong></span></th>\n",  db_fieldname($result,$i));
		}
		echo "</tr>";
		for($j  =  0;  $j  <  $rows;  $j++)  {

			if ($j%2==0) {
				$row_bg="white";
			} else {
				$row_bg="$GLOBALS[COLOR_LTBACK1]";
			}

			echo "<tr style=\"background-color:$row_bg\">\n";
			echo "<td><a href=\"edit_survey.php?group_id=$group_id&amp;survey_id=".db_result($result,$j,0)."\">".db_result($result,$j,0)."</a></td>";
			for ($i = 1; $i < $cols; $i++)  {
				printf("<td>%s</td>\n",db_result($result,$j,$i));
			}

			echo "</tr>";
		}
		echo "</table>"; //</td></tr></TABLE>";
	}
}

/*
	Select this survey from the database
*/

$sql="SELECT * FROM surveys WHERE group_id='$group_id'";

$result=db_query($sql);

?>
<form>
<input type="button" name="none" value="Show Existing Questions" onclick="show_questions()" />
</form>

<p>&nbsp;</p>
<h2>Existing Surveys</h2>
<p>&nbsp;</p>
<?php
ShowResultsEditSurvey($result);

survey_footer(array());
?>
