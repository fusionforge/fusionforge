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
require_once('www/survey/admin/survey_utils.php');
$is_admin_page='y';
$group_id = getIntFromRequest('group_id');
$survey_id = getIntFromRequest('survey_id');
survey_header(array('title'=>_('Add A Survey')));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>". _('Permission denied')."</h1>";
	survey_footer(array());
	exit;
}

if (getStringFromRequest('post_changes')) {
	$survey_title = getStringFromRequest('survey_title');
	$survey_questions = getStringFromRequest('survey_questions');

	if (!$survey_title) {
		$feedback .= _('Title required');
	} else {
		$sql="insert into surveys (survey_title,group_id,survey_questions) values ('".htmlspecialchars($survey_title)."','$group_id','$survey_questions')";
		$result=db_query($sql);
		if ($result) {
			$feedback .= _('Question inserted');
		} else {
			$feedback .= _('Question insert failed');
		}
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


<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<strong><?php echo _('Name Of Survey:') ?></strong><?php echo utils_requiredField(); ?>
<br />
<input type="text" name="survey_title" value="" length="60" maxlength="150" /><p>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="post_changes" value="y" />
<?php echo _('List question numbers, in desired order, separated by commas. <strong>Refer to your list of questions</strong> so you can view the question id\'s. Do <strong>not</strong> include spaces or end your list with a comma. <br />Ex: 1,2,3,4,5,6,7'); ?>
<br /><input type="text" name="survey_questions" value="" length="90" maxlength="1500" /></p>
<p><strong><?php echo _('Is Active?') ?></strong>
<br /><input type="radio" name="is_active" value="1" checked="checked" /> <?php echo _('Yes'); ?>
<br /><input type="radio" name="is_active" value="0" /> <?php echo _('No'); ?></p>
<p>
<input type="submit" name="SUBMIT" value="<?php echo _('Add This Survey'); ?>" /></p>
</form></p>

<?php
/*
	Select this survey from the database
*/

$sql="SELECT * FROM surveys WHERE group_id='$group_id'";

$result=db_query($sql);
$numrows=db_numrows($result);
?>
<form>
<input type="button" name="none" value="<?php echo _('Show Existing Questions'); ?>" onclick="show_questions()" />
</form>

<p>&nbsp;</p>
<h2><?php echo ngettext('Existing Survey', 'Existing Surveys', $numrows) ?></h2>
<p>&nbsp;</p>
<?php
ShowResultsEditSurvey($result);

survey_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
