<?php
/**
  *
  * Group Admin page to confirm removal of user from group
  *
  * This page is called from Project/Foundry Admins when admin requests
  * removal of a developer. This page checks whether it is possible
  * to remove one, if no, shows decription why not, else presents
  * admin with the confirmation form. Results of this form are submitted
  * back to calling Project/Foundry Admin page (i.e. very removal is
  * performed there). Since Project/Foundry Admins use slightly different
  * parameter passing interface, there's a bit of dirty magic here.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>$group_id));

$group =& group_get_object($group_id);
exit_assert_object($group, 'Group');

// Do some text substitutions below
if ($group->getType()==2) {
	$type = 'foundry';
	// foundries hate explicit group_id
	$passed_group_id = '';
} else {
	$type = 'project';
	$passed_group_id = '<input type="hidden" name="group_id" value="'.$group_id.'">';
}

// Need to check if user being removed is admin
$rm_user =& user_get_object($rm_id);
exit_assert_object($rm_user, 'User');
$perm = $group->getPermission($rm_user);

if ($perm->isAdmin()) {
	exit_error(
		'Operation Not Permitted',
		'You cannot remove '.$type.' admin.'
	);
}


project_admin_header(array('title'=>"Project Admin: ".group_getname($group_id),'group'=>$group_id));

?>

<h3>Removing Developer from <?php echo ucfirst($type); ?></h3>
<p>
You are about to remove developer from the <?php echo $type; ?>. Please
confirm your action:
</p>

<table>
<tr><td>

<form action="<?php echo $return_to; ?>" method="POST">
<input type="hidden" name="func" value="rmuser">
<?php echo $passed_group_id; ?>
<input type="hidden" name="rm_id" value="<?php echo $rm_id; ?>">
<input type="submit" value="Remove">
</form>

</td><td>

<form action="<?php echo $return_to; ?>" method="GET">
<?php echo $passed_group_id; ?>
<input type="submit" value="Cancel">
</form>

</td></tr>
</table>

<?php

project_admin_footer(array());

?>
