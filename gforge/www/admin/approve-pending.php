<?php
/**
  *
  * Site Admin page for approving/rejecting new projects
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


// Show no more pending projects per page than specified here
$LIMIT = 50;

require_once('pre.php');
require_once('common/include/vars.php');
require_once('common/include/account.php');
require_once('www/include/proj_email.php');
require_once('www/include/canned_responses.php');
require_once('www/admin/admin_utils.php');
require_once('www/project/admin/project_admin_utils.php');
require_once('common/tracker/ArtifactTypes.class');
require_once('common/forum/Forum.class');

session_require(array('group'=>'1','admin_flags'=>'A'));

function activate_group($group_id) {
	global $feedback;
	global $Language;

	$group =& group_get_object($group_id);

	if (!$group || !is_object($group)) {
		$feedback .= $Language->getText('admin_approve_pending','error_creating_group').'<br /> ';
		return false;
	} else if ($group->isError()) {
		$feedback .= $group->getErrorMessage();
		return false;
	}

	$feedback .= '<br />'. $Language->getText('admin_approve_pending','approving_group'). $group->getUnixName().' ';

	if (!$group->approve(session_get_user())) {
		$feedback .= $group->getErrorMessage();
		return false;
	}

	return true;
}

if ($action=='activate') {

	$groups=explode(',', $list_of_groups);
	array_walk($groups, 'activate_group');

} else if ($action=='delete') {

	$group =& group_get_object($group_id);
	exit_assert_object($group, 'Group');

	if (!$group->setStatus(session_get_user(), 'D')) {
		exit_error(
			$Language->getText('admin_apprive_pending','error_group_rejection'),
			$this->getErrorMessage()
		);
	}

	$group->addHistory('rejected', 'x');

	// Determine whether to send a canned or custom rejection letter and send it
	if( $response_id == 100 ) {

                $group->sendRejectionEmail(0, $response_text);

		if( $add_to_can ) {
			add_canned_response($response_title, $response_text);
		}
	} else {
		$group->sendRejectionEmail($response_id);
	}
}


site_admin_header(array('title'=>$Language->getText('admin_approve_pending','approving_pending_projects')));

// get current information
$res_grp = db_query("SELECT * FROM groups WHERE status='P'", $LIMIT);

$rows = db_numrows($res_grp);

if ($rows < 1) {
	print "<h1>".$Language->getText('admin_approve_pending','none_found'). "</h1>";
	print "<p>".$Language->getText('admin_approve_pending','no_pending_projects_to_approve')."</p>";
	site_admin_footer(array());
	exit;
}

if ($rows > $LIMIT) {
	print "<p>".$Language->getText('admin_approve_pending','pending_projects'). "$LIMIT+ ($LIMIT shown)</p>";
} else {
	print "<p>".$Language->getText('admin_approve_pending','pending_projects'). "$rows</p>";
}

while ($row_grp = db_fetch_array($res_grp)) {

	?>
	<h2><?php echo $row_grp['group_name']; ?></h2>

	<p />
	<h3><a href="/admin/groupedit.php?group_id=<?php echo $row_grp['group_id']; ?>"><?php echo $Language->getText('admin_approve_pending','edit_project_details'); ?></a></h3>

	<p />
	<h3><a href="/project/admin/?group_id=<?php echo $row_grp['group_id']; ?>"><?php echo $Language->getText('admin_approve_pending','project_admin'); ?></a></h3>

	<p />
	<h3><a href="userlist.php?group_id=<?php print $row_grp['group_id']; ?>"><?php echo $Language->getText('admin_approve_pending','view_edit_project_members'); ?></a></h3>

	<p />
	<table><tr><td>
	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="activate" />
	<input type="hidden" name="list_of_groups" value="<?php print $row_grp['group_id']; ?>" />
	<input type="submit" name="submit" value="<?php echo $Language->getText('admin_approve_pending','approve'); ?>" />
	</form>
	</td></tr>
	<tr><td>
	<form action="<?php echo $PHP_SELF; ?>" method="post">
	<input type="hidden" name="action" value="delete" />
	<input type="hidden" name="group_id" value="<?php print $row_grp['group_id']; ?>" />
	Canned responses<br />
	<?php print get_canned_responses(); ?> <a href="responses_admin.php"><?php echo $Language->getText('admin_approve_pending','manage_responses'); ?></a>
	<br /><br />
	<?php echo $Language->getText('admin_approve_pending','custom_response_title'); ?><br />
	<input type="text" name="response_title" size="30" maxlength="25" /><br />
	<textarea name="response_text" rows="10" cols="50"></textarea>
	<input type="checkbox" name="add_to_can" value="<?php echo $Language->getText('admin','yes'); ?>" /><?php echo $Language->getText('admin_approve_pending','add_this_custom_response') ;?>
	<br />
	<input type="submit" name="submit" value="<?php echo $Language->getText('admin','reject'); ?>" />
	</form>
	</td></tr>
	</table>

	<p>
	<strong><?php echo $Language->getText('admin','license'); ?><?php echo $row_grp['license']; ?></strong>

	<br /><strong><?php echo $Language->getText('admin_approve_pending','home_box'); ?><?php print $row_grp['unix_box']; ?></strong>
	<br /><strong><?php echo $Language->getText('admin','http_domain'); ?><?php print $row_grp['http_domain']; ?></strong>

	<br />
	&nbsp;</p>
	<?php

	// ########################## OTHER INFO

	print "<p><strong>" .$Language->getText('admin_approve_pending','other_information')."</strong></p>";
	print "<p>" .$Language->getText('admin_approve_pending','unix_group_name'). "$row_grp[unix_group_name]</p>";

	print "<p>" .$Language->getText('admin_approve_pending','submitted_description'). "</p><blockquote>$row_grp[register_purpose]</blockquote>";

	if ($row_grp[license]=="other") {
		print "<p>" .$Language->getText('admin','license_other'). "</p><blockquote>$row_grp[license_other]</blockquote>";
	}

	if ($row_grp[status_comment]) {
		print "<p>" .$Language->getText('admin_approve_pending','pending_reason'). "</p><span style=\"color:red\">$row_grp[status_comment]</span>";
	}

	echo "<p>&nbsp;</p><hr /><p>&nbsp;</p>";

}

//list of group_id's of pending projects
$arr=util_result_column_to_array($res_grp,0);
$group_list=implode($arr,',');

echo '
	<div align="center">
	<form action="'.$PHP_SELF.'" method="post">
	<input type="hidden" name="action" value="'.$Language->getText('admin_approve_pending','activate').'" />
	<input type="hidden" name="list_of_groups" value="'.$group_list.'" />
	<input type="submit" name="submit" value="'.$Language->getText('admin_approve_pending','approve_all_on_this_page').'" />
	</form></div>
	';

site_admin_footer(array());

?>
