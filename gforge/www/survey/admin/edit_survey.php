<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
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

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'survey/survey_utils.php';
require_once $gfwww.'survey/admin/survey_utils.php';

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
survey_header(array('title'=>_('Edit A Survey')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<div class="error">'._('Permission denied').'</div>';
	survey_footer(array());
	exit;
}

if (getStringFromRequest('post_changes')) {
	$survey_title = $survey_title;
	$survey_questions = $survey_questions;
	$is_active = $is_active;

	if (!isset($survey_title) || $survey_title == "")
	{
		$feedback .= _('Update Failed: Survey Title Required');
	}
	elseif (!isset($survey_questions) || $survey_questions == "")
	{
		$feedback .= _('Update Failed: Survey Questions Required');
	}
	if (!isset($survey_id) || !isset($group_id) || $survey_id == "" || $group_id == "")
	{
		$feedback .= _('Update Failed: Missing Data');
	}
	else
	{
		if ($is_active) {
			$is_active = 1;
		} else {
			$is_active = 0;
		}
		$result = db_query_params ('UPDATE surveys SET survey_title=$1, survey_questions=$2, is_active=$3
WHERE survey_id=$4 AND group_id=$5',
					   array (htmlspecialchars($survey_title),
						  $survey_questions,
						  $is_active,
						  $survey_id,
						  $group_id));
		if (db_affected_rows($result) < 1) {
			$feedback .= _('Update Failed');
			echo db_error();
		} else {
			$feedback .= _('Update Successful');
		}
	}
}

/*
	Get this survey out of the DB
*/
if ($survey_id) {
	$result = db_query_params ('SELECT * FROM surveys WHERE survey_id=$1 AND group_id=$2',
				   array ($survey_id,
					  $group_id));
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

<span class="warning"><?php echo _('WARNING! It is a bad idea to edit a survey after responses have been posted'); ?></span>

<p><?php echo _('If you change a survey after you already have responses, your results pages could be misleading or messed up'); ?>.</p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="post_changes" value="y" />
<strong><?php echo _('Name of Survey'); ?>:</strong>
<br />
<input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>" />
<input type="text" name="survey_title" value="<?php echo $survey_title; ?>" size="60" maxlength="150" />
<p>
<strong><?php echo _('Questions'); ?>:</strong>
<br />
<?php echo _('List question numbers, in desired order, separated by commas. <strong>Refer to your list of questions</strong> so you can view	the question id\'s. Do <strong>not</strong> include spaces or end your list with a comma.<br /> Ex: 1,2,3,4,5,6,7'); ?>
<br /><input type="text" name="survey_questions" value="<?php echo $survey_questions; ?>" length="90" maxlength="1500" /></p>
<p>
<strong><?php echo _('Is Active'); ?></strong>
<br /><input type="radio" name="is_active" value="1"<?php if ($is_active=='1') { echo ' checked="checked"'; } ?> /> <?php echo _('Yes'); ?>
<br /><input type="radio" name="is_active" value="0"<?php if ($is_active=='0') { echo ' checked="checked"'; } ?> /> <?php echo _('No'); ?></p>
<p>
<input type="submit" name="submit" value="<?php echo _('Submit Changes'); ?>"></p>
</form>

<?php


/*
	Select all surveys from the database
*/
$result = db_query_params ('SELECT * FROM surveys WHERE group_id=$1',
			   array ($group_id));
$numrows=db_numrows($result);

?>

<form>
<input type="button" name="none" value="<?php echo _('Show Existing Questions'); ?>" onclick="show_questions()" />
</form>
<p>&nbsp;</p>
<h2><?php echo ngettext('Existing Survey', 'Existing Surveys', $numrows) ?></h2>
<?php

ShowResultsEditSurvey($result);

survey_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
