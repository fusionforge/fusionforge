<?php
/**
 * Site Admin page for approving/rejecting new projects
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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


// Show no more pending projects per page than specified here
$LIMIT = 50;

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/include/account.php');
require_once('common/include/license.php');
require_once('www/include/canned_responses.php');
require_once('www/admin/admin_utils.php');
require_once('www/project/admin/project_admin_utils.php');
require_once('common/tracker/ArtifactTypes.class.php');
require_once('common/forum/Forum.class.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

function activate_group($group_id) {
	global $feedback;
	global $Language;

	$group =& group_get_object($group_id);

	if (!$group || !is_object($group)) {
		$feedback .= _('Error creating group object').'<br />';
		return false;
	} else if ($group->isError()) {
		$feedback .= $group->getErrorMessage().'<br />';
		return false;
	}

	$feedback .= _('Approving Group:'). $group->getUnixName().'<br />';

	if (!$group->approve(session_get_user())) {
		$feedback .= $group->getErrorMessage().'<br />';
		return false;
	}

	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	plugin_hook ("group_approved", $hook_params) ;
	
	//plugin webcalendar
	//create webcal group
	plugin_hook('add_cal_group',$group_id);

	return true;
}

$action = getStringFromRequest('action');
if ($action=='activate') {
	$group_id = getIntFromRequest('group_id');
	$list_of_groups = getStringFromRequest('list_of_groups');

	$groups=explode(',', $list_of_groups);
	array_walk($groups, 'activate_group');
	//plugin webcalendar
	//create webcal group
	plugin_hook('add_cal_group',$group_id);

} else if ($action=='delete') {
	$group_id = getIntFromRequest('group_id');
	$response_id = getIntFromRequest('response_id');
	$add_to_can = getStringFromRequest('add_to_can');
	$response_text = getStringFromRequest('response_text');
	$response_title = getStringFromRequest('response_title');
	//plugin webcalendar
	//create webcal group
	plugin_hook('del_cal_group',$group_id);
	
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_error('Error','Could Not Get Group');
	} elseif ($group->isError()) {
		exit_error('Error',$group->getErrorMessage());
	}

	if (!$group->setStatus(session_get_user(), 'D')) {
		exit_error(
			_('Error during group rejection'),
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


site_admin_header(array('title'=>_('Approving Pending Projects')));

// get current information
$res_grp = db_query("SELECT * FROM groups WHERE status='P'", $LIMIT);

$rows = db_numrows($res_grp);

if ($rows < 1) {
	print "<h1>"._('None Found'). "</h1>";
	print "<p>"._('No Pending Projects to Approve')."</p>";
	site_admin_footer(array());
	exit;
}

if ($rows > $LIMIT) {
	print "<p>"._('Pending projects:'). "$LIMIT+ ($LIMIT shown)</p>";
} else {
	print "<p>"._('Pending projects:'). "$rows</p>";
}

while ($row_grp = db_fetch_array($res_grp)) {

	?>
	<h2><?php echo $row_grp['group_name']; ?></h2>

	<p />
	<h3><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/admin/groupedit.php?group_id=<?php echo $row_grp['group_id']; ?>"><?php echo _('[Edit Project Details]'); ?></a></h3>

	<p />
	<h3><a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/project/admin/?group_id=<?php echo $row_grp['group_id']; ?>"><?php echo _('Project Admin'); ?></a></h3>

	<p />
	<h3><a href="userlist.php?group_id=<?php print $row_grp['group_id']; ?>"><?php echo _('[View/Edit Project Members]'); ?></a></h3>

	<p />
	<table><tr><td>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<input type="hidden" name="action" value="activate" />
	<input type="hidden" name="list_of_groups" value="<?php print $row_grp['group_id']; ?>" />
	<input type="submit" name="submit" value="<?php echo _('Approve'); ?>" />
	</form>
	</td></tr>
	<tr><td>
	<form action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">
	<input type="hidden" name="action" value="delete" />
	<input type="hidden" name="group_id" value="<?php print $row_grp['group_id']; ?>" />
	<?php echo _('Canned responses'); ?><br />
	<?php print get_canned_responses(); ?> <a href="responses_admin.php"><?php echo _('(manage responses)'); ?></a>
	<br /><br />
	<?php echo _('Custom response title and text'); ?><br />
	<input type="text" name="response_title" size="30" maxlength="25" /><br />
	<textarea name="response_text" rows="10" cols="50"></textarea>
	<input type="checkbox" name="add_to_can" value="<?php echo _('yes'); ?>" /><?php echo _('Add this custom response to canned responses') ;?>
	<br />
	<input type="submit" name="submit" value="<?php echo _('Reject'); ?>" />
	</form>
	</td></tr>
	</table>

	<p>
	<strong><?php echo _('License:')." "; print license_getname($row_grp['license']); ?></strong>

	<?php
		global $sys_use_shell;
		if ($sys_use_shell) {
	?>  
	<br /><strong><?php echo _('Home Box:')." "; print $row_grp['unix_box']; ?></strong>
	<?php
		} //end of sys_use_shell
	?> 
	<br /><strong><?php echo _('HTTP Domain:')." "; print $row_grp['http_domain']; ?></strong>

	<br />
	&nbsp;</p>
	<?php

	// ########################## OTHER INFO

	print "<p><strong>" ._('Other Information')."</strong></p>";
	print "<p>" ._('Unix Group Name:'). " ".$row_grp['unix_group_name']."</p>";

	print "<p>" ._('Submitted Description:'). "</p><blockquote>".$row_grp['register_purpose']."</blockquote>";

	if ($row_grp['license']=="other") {
		print "<p>" ._('License Other:'). "</p><blockquote>".$row_grp['license_other']."</blockquote>";
	}

	if (isset($row_grp['status_comment'])) {
		print "<p>" ._('Pending reason:'). "</p><span class=\"important\">".$row_grp['status_comment']."</span>";
	}

	echo "<p>&nbsp;</p><hr /><p>&nbsp;</p>";

}

//list of group_id's of pending projects
$arr=util_result_column_to_array($res_grp,0);
$group_list=implode($arr,',');

echo '
	<div align="center">
	<form action="'.getStringFromServer('PHP_SELF').'" method="post">
	<input type="hidden" name="action" value="activate" />
	<input type="hidden" name="list_of_groups" value="'.$group_list.'" />
	<input type="submit" name="submit" value="'._('Approve All On This Page').'" />
	</form></div>
	';

site_admin_footer(array());

?>
