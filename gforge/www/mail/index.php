<?php
/**
  *
  * SourceForge Mailing Lists Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('../mail/mail_utils.php');

if ($group_id) {

	mail_header(array('title'=>'Mailing Lists for '.group_getname($group_id),'pagename'=>'mail','sectionvals'=>array(group_getname($group_id))));

	if (session_loggedin() && user_ismember($group_id)) {
		$public_flag='0,1';
	} else {
		$public_flag='1';
	}

	$sql="SELECT * FROM mail_group_list WHERE group_id='$group_id' AND is_public IN ($public_flag)";

	$result = db_query ($sql);

	$rows = db_numrows($result);

	if (!$result || $rows < 1) {
		echo '
			<h1>No Lists found for '.group_getname($group_id).'</h1>';
		echo '
			<p>Project administrators use the admin link to request mailing lists.</p>';
		$HTML->footer(array());
		exit;
	}

	echo $Language->getText('mail', 'provided_by');
	echo "<p>Choose a list to browse, search, and post messages.</p>\n";

	/*
		Put the result set (list of mailing lists for this group) into a column with folders
	*/

	echo "<table width=\"100%\" border=\"0\">\n".
		"<tr><td valign=\"top\">\n";

	for ($j = 0; $j < $rows; $j++) {
		echo '<a href="http://'.$GLOBALS['sys_lists_host'].'/pipermail/'.
			db_result($result, $j, 'list_name').'/">' .
			html_image("ic/cfolder15.png","15","13",array("border"=>"0")) . ' &nbsp; '.
			db_result($result, $j, 'list_name').' Archives</a>';
		echo ' (go to <a href="http://'.$GLOBALS['sys_lists_host'].'/mailman/listinfo/'.
			db_result($result, $j, 'list_name').'">Subscribe/Unsubscribe/Preferences</a>)<br />';
		echo '&nbsp;'.  db_result($result, $j, 'description') . '<p />';
	}
	echo '</td></tr></table>';

} else {
	mail_header(array('title'=>'Choose a Group First','pagename'=>'mail'));
	require_once('../mail/mail_nav.php');
	echo '
		<h1>Error - choose a group first</h1>';
}
mail_footer(array());

?>
