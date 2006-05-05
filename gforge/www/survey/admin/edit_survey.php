<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('pre.php');
require_once('www/survey/survey_utils.php');
require_once('www/survey/admin/survey_utils.php');

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
survey_header(array('title'=>$Language->getText('survey_edit','title')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>" .$Language->getText('survey_edit','permission_denied')."</h1>";
	survey_footer(array());
	exit;
}

if (getStringFromRequest('post_changes')) {
	$survey_title = $survey_title;
	$survey_questions = $survey_questions;
	$is_active = $is_active;

	if (!isset($survey_title) || $survey_title == "")
	{
		$feedback .= $Language->getText('survey_edit','survey_title_required');
	}
	elseif (!isset($survey_questions) || $survey_questions == "")
	{
		$feedback .= $Language->getText('survey_edit','survey_question_required');
	}
	if (!isset($survey_id) || !isset($group_id) || $survey_id == "" || $group_id == "")
	{
		$feedback .= $Language->getText('survey_edit','missing_date');
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
			$feedback .= $Language->getText('survey_edit','update_failed');
			echo db_error();
		} else {
			$feedback .= $Language->getText('survey_edit','update_successful');
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

<span class="warning"><?php echo $Language->getText('survey_edit','warning_survey_after_response'); ?></span>

<p><?php echo $Language->getText('survey_edit','change_after_already_response'); ?>.</p>
<p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="post_changes" value="y" />
<strong><?php echo $Language->getText('survey_edit','name_off_survey'); ?>:</strong>
<br />
<input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>" />
<input type="text" name="survey_title" value="<?php echo $survey_title; ?>" length="60" maxlength="150" />
<p>
<strong><?php echo $Language->getText('survey_edit','question'); ?>:</strong>
<br />
<?php echo $Language->getText('survey_edit','list_question_numbers'); ?>
<br /><input type="text" name="survey_questions" value="<?php echo $survey_questions; ?>" length="90" maxlength="1500" /></p>
<p>
<strong><?php echo $Language->getText('survey_edit','is_active'); ?></strong>
<br /><input type="radio" name="is_active" value="1"<?php if ($is_active=='1') { echo ' checked="checked"'; } ?> /> <?php echo $Language->getText('survey_edit','yes'); ?>
<br /><input type="radio" name="is_active" value="0"<?php if ($is_active=='0') { echo ' hecked="checked"'; } ?> /> <?php echo $Language->getText('survey_edit','no'); ?></p>
<p>
<input type="submit" name="submit" value="<?php echo $Language->getText('survey_edit','submit_changes'); ?>"></p>
</form></p>

<?php


/*
	Select all surveys from the database
*/

$sql="SELECT * FROM surveys WHERE group_id='$group_id'";

$result=db_query($sql);

?>
<p>
<form>
<input type="button" name="none" value="<?php echo $Language->getText('survey_edit','show_existing_questions'); ?>" onclick="show_questions()" />
</form></p>
<p>&nbsp;</p>
<h2><?php echo $Language->getText('survey_edit','existing_surveys'); ?></h2>
<?php

ShowResultsEditSurvey($result);

survey_footer(array());
?>
