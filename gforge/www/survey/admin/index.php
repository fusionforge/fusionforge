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
survey_header(array('title'=>$Language->getText('survey_admin_index','title'),'pagename'=>'survey_admin'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<h1>'.$Language->getText('survey_admin_index','permission_denied').'</h1>';
	survey_footer(array());
	exit;
}

?>

<p>
<a href="/survey/admin/add_survey.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','add_survey'); ?></a><br />
<a href="/survey/admin/edit_survey.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','edir_existing_survey'); ?></a><br />
<a href="/survey/admin/add_question.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','add_question'); ?></a><br />
<a href="/survey/admin/show_questions.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','edit_existing_question'); ?></a><br />
<a href="/survey/admin/show_results.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','show_results'); ?></a><br />
</p>
<?php echo $Language->getText('survey_admin_index','its_simple_to_create', array('<p><strong>/survey/survey.php?group_id='.$group_id.'&survey_id=XX</strong>')); ?>
</li>
</ol>
</p>

<p><?php echo $Language->getText('survey_admin_index','you_can_now_activate',array('<a href="/survey/admin/edit_survey.php?group_id='.$group_id.'">','</a>')); ?>

</p>
<p>
<?php

survey_footer(array());

?>
</p>
