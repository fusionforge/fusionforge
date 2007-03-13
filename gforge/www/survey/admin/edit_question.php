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

require_once('../../env.inc.php');
require_once('pre.php');
require_once('www/survey/survey_utils.php');

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');

survey_header(array('title'=>_('Edit A Question')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>" ._('Permission Denied')."</h1>";
	survey_footer(array());
	exit;
}

if (getStringFromRequest('post_changes')) {
	$question = getStringFromRequest('question');
	$question_type = getStringFromRequest('question_type');
	$question_id = getIntFromRequest('question_id');

	$sql="UPDATE survey_questions SET question='".htmlspecialchars($question)."', question_type='$question_type' where question_id='$question_id' AND group_id='$group_id'";
	$result=db_query($sql);
        if (db_affected_rows($result) < 1) {
                $feedback .= _('UPDATE FAILED');
        } else {
                $feedback .= _('UPDATE SUCCESSFUL');
        }
}

$sql="SELECT * FROM survey_questions WHERE question_id='$question_id' AND group_id='$group_id'";
$result=db_query($sql);

if ($result) {
	$question=db_result($result, 0, "question");
	$question_type=db_result($result, 0, "question_type");
} else {
	$feedback .= _('Error finding question');
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

<h2><?php echo _('Editing Question'); ?> #<?php echo $question_id; ?></h2>

<span class="warning"><?php echo _('WARNING! It is a bad idea to change a question after responses to it have been submitted'); ?></span>

<p><?php echo _('If you change a question after responses have been posted, your results pages may be misleading'); ?>.</p>

<p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="post_changes" value="Y" />
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>"/>
<input type="hidden" name="question_id" value="<?php echo $question_id; ?>" />

<?php echo _('Question'); ?>:
<br />
<input type="text" name="question" value="<?php echo $question; ?>" size="60" maxlength="150" />

<p><?php echo _('Question Type'); ?>:
<br />
<?php

$sql="SELECT * FROM survey_question_types";
$result=db_query($sql);
echo html_build_select_box($result,'question_type',$question_type,false);

?>
</p>

<p><input type="submit" name="submit" value="<?php echo _('Submit Changes'); ?>"></p>
</form></p>

<p>
<form>
<input type="button" name="none" value="<?php echo _('Show Existing Questions'); ?>" onclick="show_questions()" />
</form></p>

<?php

survey_footer(array());

?>
