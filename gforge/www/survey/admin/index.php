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
require_once('www/survey/include/SurveyHTML.class');

$is_admin_page='y';
$sh = new  SurveyHtml();
$sh->header(array('title'=>$Language->getText('survey_admin_index','title'),'pagename'=>'survey_admin'));

if (!session_loggedin() || !user_ismember($group_id,'A')) {
	echo '<h1>'.$Language->getText('survey_admin_index','permission_denied').'</h1>';
	$sh->footer(array());
	exit;
}

?>

<p>
<UL>
<LI><a href="/survey/admin/question.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','add_question'); ?></a><br />
<LI><a href="/survey/admin/survey.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','add_survey'); ?></a><br />
<LI><a href="/survey/admin/show_results.php?group_id=<?php echo $group_id; ?>"><?php echo $Language->getText('survey_admin_index','show_results'); ?></a><br />
</UL>
</p>
<?php echo $Language->getText('survey_admin_index','its_simple_to_create', array('<p><strong>/survey/survey.php?group_id='.$group_id.'&survey_id=XX</strong>')); ?>
</li>
</ol>
</p>

<p><?php echo $Language->getText('survey_admin_index','you_can_now_activate',array('<a href="/survey/admin/survey.php?group_id='.$group_id.'">','</a>')); ?>

</p>
<p>
<?php

$sh->footer(array());

?>
</p>
