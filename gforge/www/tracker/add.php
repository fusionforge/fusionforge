<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


$ath->header(array ('title'=>$Language->getText('tracker_add','submit'),'pagename'=>'tracker_add','sectionvals'=>array($ath->getName())));

	echo '
	<p>&nbsp;</p>';
	/*
	    Show the free-form text submitted by the project admin
	*/
	echo $ath->getSubmitInstructions();

	echo '<p>
	<form action="'.$PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID().'" method="post" enctype="multipart/form-data">
	<input type="hidden" name="func" value="postadd" />
	<table>
	<tr><td <valign="top" colspan="2"><strong>'.$Language->getText('tracker_add','for_project').':</strong><br />'.$group->getPublicName().'</td></tr>
	<tr><td <valign="top"><strong>'.$Language->getText('tracker','category').': <a href="javascript:help_window(\'/help/tracker.php?helpname=category\')"><strong>(?)</strong></a></strong><br />';

		echo $ath->categoryBox('category_id');
		echo '&nbsp;<a href="/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;add_cat=1">('.$Language->getText('tracker','admin').')</a>';
	?>
	</td><td><strong><?php echo $Language->getText('tracker','item_group') ?>: <a href="javascript:help_window('/help/tracker.php?helpname=group')"><strong>(?)</strong></a></strong><br />
	<?php
		echo $ath->artifactGroupBox('artifact_group_id');
		echo '&nbsp;<a href="/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;add_group=1">('.$Language->getText('tracker','admin').')</a>';
	?>
	</td></tr>
	<?php 
	if ($ath->userIsAdmin()) {
		echo '<tr><td><strong>'.$Language->getText('tracker','assigned_to').': <a href="javascript:help_window(\'/help/tracker.php?helpname=assignee\')"><strong>(?)</strong></a></strong><br />';
		echo $ath->technicianBox ('assigned_to');
		echo '&nbsp;<a href="/tracker/admin/?group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;update_users=1">('.$Language->getText('tracker','admin').')</a>';

		echo '</td><td><strong>'.$Language->getText('tracker','priority').': <a href="javascript:help_window(\'/help/tracker.php?helpname=priority\')"><strong>(?)</strong></a></strong><br />';
		echo build_priority_select_box('priority');
		echo '</td></tr>';
	}
	?>
	<tr><td colspan="2"><strong><?php echo $Language->getText('tracker','summary') ?>: <a href="javascript:help_window('/help/tracker.php?helpname=summary')"></strong><?php echo utils_requiredField(); ?><strong>(?)</strong></a><br />
		<input type="text" name="summary" size="35" maxlength="40" />
	</td></tr>

	<tr><td colspan="2">
		<strong><?php echo $Language->getText('tracker','detailed_description') ?>:</strong><?php echo utils_requiredField(); ?>
		<p>
		<textarea name="details" rows="30" cols="55" wrap="hard"></textarea></p>
	</td></tr>

	<tr><td colspan="2">
	<?php 
	if (!session_loggedin()) {
		echo '
		<h3><span style="color:red">'.$Language->getText('tracker','please_login',array('<a href="/account/login.php?return_to='.urlencode($REQUEST_URI).'">','</a>')).'</span></h3><br />
		'.$Language->getText('tracker','insert_email').':<p>
		<input type="text" name="user_email" size="30" maxlength="35" /></p>
		';

	} 
	?>
		<p>&nbsp;</p>
		<h3><span style="color:red"><?php echo $Language->getText('tracker','security_note') ?></span></h3>
		<p>&nbsp;</p>
	</td></tr>

	<tr><td colspan="2">
		<strong><?php echo $Language->getText('tracker','check_upload') ?>:</strong> <input type="checkbox" name="add_file" value="1" />
		<a href="javascript:help_window('/help/tracker.php?helpname=comment')"><strong>(?)</strong></a><br />
		<p>
		<input type="file" name="input_file" size="30" /></p>
		<p>
		<strong><?php echo $Language->getText('tracker','file_description') ?>:</strong><br />
		<input type="text" name="file_description" size="40" maxlength="255" /></p>
		<p>&nbsp;</p>
	</td><tr>

	<tr><td colspan="2">
		<input type="submit" name="submit" value="<?php echo $Language->getText('general','submit')?>" />
		<p>&nbsp;</p>
	</td></tr>

	</table></form></p>

	<?php

	$ath->footer(array());

?>
