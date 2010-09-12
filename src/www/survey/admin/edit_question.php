<?php
/**
 * FusionForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Copyright 2008-2010 (c) FusionForge Team
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'survey/survey_utils.php';

$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');

survey_header(array('title'=>_('Edit A Question')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<div class="error">' ._('Permission denied'). '</div>';
	survey_footer(array());
	exit;
}

if (getStringFromRequest('post_changes')) {
	$question = getStringFromRequest('question');
	$question_type = getStringFromRequest('question_type');
	$question_id = getIntFromRequest('question_id');

	$result = db_query_params ('UPDATE survey_questions SET question=$1, question_type=$2 where question_id=$3 AND group_id=$4',
				   array (htmlspecialchars($question),
					  $question_type,
					  $question_id,
					  $group_id));
        if (db_affected_rows($result) < 1) {
                $feedback .= _('Update Failed');
        } else {
                $feedback .= _('Update Successful');
        }
}

$result = db_query_params ('SELECT * FROM survey_questions WHERE question_id=$1 AND group_id=$2',
			   array ($question_id,
				  $group_id));

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

<div class="warning"><?php echo _('WARNING! It is a bad idea to change a question after responses to it have been submitted'); ?></div>

<p><?php echo _('If you change a question after responses have been posted, your results pages may be misleading'); ?>.</p>

<p>
<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
<input type="hidden" name="post_changes" value="Y" />
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>"/>
<input type="hidden" name="question_id" value="<?php echo $question_id; ?>" />

<?php echo _('Question'); ?>:
<br />
<input type="text" name="question" value="<?php echo $question; ?>" size="60" maxlength="150" />

<p><?php echo _('Question type'); ?>:
<br />
<?php

$result = db_query_params ('SELECT * FROM survey_question_types',
			   array ());
echo html_build_select_box($result,'question_type',$question_type,false);

?>
</p>

<p><input type="submit" name="submit" value="<?php echo _('Submit Changes'); ?>" /></p>
</form>

<form>
<input type="button" name="none" value="<?php echo _('Show Existing Questions'); ?>" onclick="show_questions()" />
</form>

<?php

survey_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
