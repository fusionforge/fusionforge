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
survey_header(array('title'=>$Language->getText('survey_add_survey','title'),'pagename'=>'survey_admin_add_survey'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo "<h1>". $Language->getText('survey_add_question','permission_denied')."</h1>";
	survey_footer(array());
	exit;
}

if ($post_changes) {
	if (!$survey_title) {
		$feedback .= $Language->getText('survey_add_question','title_is_required');
	} else {
		$sql="insert into surveys (survey_title,group_id,survey_questions) values ('".htmlspecialchars($survey_title)."','$group_id','$survey_questions')";
		$result=db_query($sql);
		if ($result) {
			$feedback .= $Language->getText('survey_add_question','survey_inserted');
		} else {
			$feedback .= $Language->getText('survey_add_question','error_in_insert');
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


<form action="<?php echo $PHP_SELF; ?>" method="post">

<strong><?php echo $Language->getText('survey_add_survey','name_of_survey') ?></strong><?php echo utils_requiredField(); ?>
<br />
<input type="text" name="survey_title" value="" length="60" maxlength="150" /><p>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>" />
<input type="hidden" name="post_changes" value="y" />
<?php echo $Language->getText('survey_add_survey','list_questions_numbers'); ?>
<br /><input type="text" name="survey_questions" value="" length="90" maxlength="1500" /></p>
<p><strong><?php echo $Language->getText('survey_add_survey','is_active') ?></strong>
<br /><input type="radio" name="is_active" value="1" checked="checked" /> <?php echo $Language->getText('survey_add_survey','yes'); ?>
<br /><input type="radio" name="is_active" value="0" /> <?php echo $Language->getText('survey_add_survey','no'); ?></p>
<p>
<input type="submit" name="SUBMIT" value="<?php echo $Language->getText('survey_add_survey','add_this_survey'); ?>" /></p>
</form></p>

<?php
/*
	Select this survey from the database
*/

$sql="SELECT * FROM surveys WHERE group_id='$group_id'";

$result=db_query($sql);

?>
<form>
<input type="button" name="none" value="<?php echo $Language->getText('survey_add_survey','show_existing_question'); ?>" onclick="show_questions()" />
</form>

<p>&nbsp;</p>
<h2><?php echo $Language->getText('survey_add_survey','existing_surveys') ?></h2>
<p>&nbsp;</p>
<?php
ShowResultsEditSurvey($result);

survey_footer(array());
?>
