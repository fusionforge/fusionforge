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

if (!user_isloggedin() || !user_ismember($group_id,'A')) {
	echo '<H1>Permission Denied</H1>';
	survey_footer(array());
	exit;
}

?>

<P>
<A HREF="/survey/admin/add_survey.php?group_id=<?php echo $group_id; ?>">Add Surveys</A><BR>
<A HREF="/survey/admin/edit_survey.php?group_id=<?php echo $group_id; ?>">Edit Existing Surveys</A><BR>
<A HREF="/survey/admin/add_question.php?group_id=<?php echo $group_id; ?>">Add Questions</A><BR>
<A HREF="/survey/admin/show_questions.php?group_id=<?php echo $group_id; ?>">Edit Existing Questions</A><BR>
<A HREF="/survey/admin/show_results.php?group_id=<?php echo $group_id; ?>">Show Survey Results</A><BR>
<P>
It's simple to create a survey.
<OL>
<LI>Create questions and comments using the forms above.
<LI>Create a survey, listing the questions in order (choose from <B>your</B> list of questions).
<LI>Link to the survey using this format: <P>
	<B>/survey/survey.php?group_id=<?php echo $group_id; ?>&survey_id=XX</B>, where XX is the survey number
</OL>
<P>
You can now activate/deactivate surveys on the 
<A HREF="/survey/admin/edit_survey.php?group_id=<?php echo $group_id; ?>">Edit Existing Surveys</A> page.
<P>
<?php

survey_footer(array());

?>
