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
survey_header(array('title'=>'Survey Administration','pagename'=>'survey_admin'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<h1>Permission Denied</h1>';
	survey_footer(array());
	exit;
}

?>

<p>
<a href="/survey/admin/add_survey.php?group_id=<?php echo $group_id; ?>">Add Surveys</a><br />
<a href="/survey/admin/edit_survey.php?group_id=<?php echo $group_id; ?>">Edit Existing Surveys</a><br />
<a href="/survey/admin/add_question.php?group_id=<?php echo $group_id; ?>">Add Questions</a><br />
<a href="/survey/admin/show_questions.php?group_id=<?php echo $group_id; ?>">Edit Existing Questions</a><br />
<a href="/survey/admin/show_results.php?group_id=<?php echo $group_id; ?>">Show Survey Results</a><br />
</p>

<p>It's simple to create a survey.
<ol>
<li>Create questions and comments using the forms above.</li>
<li>Create a survey, listing the questions in order (choose from <strong>your</strong> list of questions).</li>
<li>Link to the survey using this format:
	<p><strong>/survey/survey.php?group_id=<?php echo $group_id; ?>&survey_id=XX</strong>, where XX is the survey number</p></li>
</ol>
</p>

<p>You can now activate/deactivate surveys on the
<a href="/survey/admin/edit_survey.php?group_id=<?php echo $group_id; ?>">Edit Existing Surveys</a> page.
</p>
<p>
<?php

survey_footer(array());

?>
</p>
