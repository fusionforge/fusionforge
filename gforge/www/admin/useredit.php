<?php
/**
 * Site Admin user properties editing page
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

require_once('pre.php');
require_once('common/include/account.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));
$unix_status2str = array(
	'N'=>$Language->getText('admin_useredit','no_unix_account'),
	'A'=>$Language->getText('admin_useredit','active'),
	'S'=>$Language->getText('admin_useredit','suspended'),
	'D'=>$Language->getText('admin_useredit','deleted')
);

$u =& user_get_object($user_id);
if (!$u || !is_object($u)) {
	exit_error('Error','Could Not Get User');
} elseif ($u->isError()) {
	exit_error('Error',$u->getErrorMessage());
}

if ($delete_user != '' && $confirm_delete == '1') {
	// delete user
	if (!$u->delete(true)) {
		exit_error(
			$Language->getText('admin_useredit','could_not_complete_operation'),
			$u->getErrorMessage()
		);
	} else {
		$feedback .= $Language->getText('admin_useredit','deleted').'<br />';
	}

} elseif ($action == "update_user" && $delete_user == '') {

	if (!$u->setEmail($email)
		|| !$u->setShell($shell)
		|| !$u->setStatus($status)) {
		exit_error(
			$Language->getText('admin_useredit','could_not_complete_operation'),
			$u->getErrorMessage()
		);
	}

	if ($u->getUnixStatus() != 'N') {
		$u->setUnixStatus($status);
	} else {
		// make sure that user doesn't have LDAP entry
		$u->setUnixStatus('N');
	}
	
	if ($u->isError()) {
		$feedback .= $u->getErrorMessage();
	} else {
		$feedback .= $Language->getText('admin_useredit','updated').'<br />';
	}

}


site_admin_header(array('title'=>$Language->getText('admin_useredit','title')));

?>
<h3><?php echo $Language->getText('admin_useredit','account_info'); ?><sup>1</sup></h3>

<form method="post" action="<?php echo $PHP_SELF; ?>">
<input type="hidden" name="action" value="update_user" />
<input type="hidden" name="user_id" value="<?php print $user_id; ?>" />

<table>
<tr>
<td>
<?php echo $Language->getText('admin_useredit','user_id'); ?>
</td>
<td>
<?php echo $u->getID(); ?>
</td>
</tr>

<td>
<?php echo $Language->getText('admin_useredit','user_name'); ?>
</td>
<td>
<?php echo $u->getUnixName(); ?>
</td>
</tr>

<td>
<?php echo $Language->getText('admin_useredit','real_name'); ?>
</td>
<td>
<?php echo $u->getRealName(); ?>
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin_useredit','web_account_status'); ?>
</td>
<td>
<?php
if ($u->getStatus() == 'D') {
	$status_letter = array('P','A','S','D');
	$status_text   = array($Language->getText('admin_useredit','pending'),
		$Language->getText('admin_useredit','active'),
		$Language->getText('admin_useredit','suspended'),
		$Language->getText('admin_useredit','deleted'));
} else {
	$status_letter = array('P','A','S');
	$status_text   = array($Language->getText('admin_useredit','pending'),
		$Language->getText('admin_useredit','active'),
		$Language->getText('admin_useredit','suspended'));
}
?>
<?php echo html_build_select_box_from_arrays(
	$status_letter,	$status_text,'status', $u->getStatus(), false);
?>
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin_useredit','unix_account_status'); ?><sup>2</sup>:
</td>
<td>
<?php echo $unix_status2str[$u->getUnixStatus()]; ?>
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin_useredit','unix_shell'); ?>
</td>
<td>
<select name="shell">
<?php account_shellselects($u->getShell()); ?>
</select>
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin_useredit','email'); ?>
</td>
<td>
<input type="text" name="email" value="<?php echo $u->getEmail(); ?>" size="25" maxlength="55" />
</td>
</tr>

<tr>
<td>
<?php echo $Language->getText('admin_useredit','current_confirm_bash'); ?>
</td>
<td>
<?php echo $u->getConfirmHash(); ?>
</td>
</tr>
<?php if ($u->getStatus() != 'D') {	?>
<tr>
<td colspan="2"><input type="checkbox" name="confirm_delete" value="1"><?php echo $Language->getText('admin_useredit','delete_user_confirm'); ?>
&nbsp;<input type="submit" name="delete_user" value="<?php echo $Language->getText('admin_useredit','delete'); ?>" /><br>&nbsp;
</td>
</tr>
<?php } ?>
</table>
<input type="submit" name="submit" value="<?php echo $Language->getText('admin_useredit','update'); ?>" />
<p>
<sup>1</sup><?php echo $Language->getText('admin_useredit','this_page_allows'); ?>
</p>
<p>
<sup>2</sup><?php echo $Language->getText('admin_useredit','unix_status_updated_mirroring'); ?>
</p>

</form>

<hr />

<p>
<h3><?php echo $Language->getText('admin_useredit','group_memerbership'); ?></h3>

<?php
/*
	Iterate and show groups this user is in
*/
$res_cat = db_query("
	SELECT groups.unix_group_name, groups.group_name AS group_name, 
		groups.group_id AS group_id, 
		user_group.admin_flags AS admin_flags
	FROM groups,user_group
	WHERE user_group.user_id=$user_id
	AND groups.group_id=user_group.group_id
");

$title=array();
$title[]=$Language->getText('admin_useredit','name');
$title[]=$Language->getText('admin_useredit','unix_name');
$title[]=$Language->getText('admin_useredit','operations');
echo $GLOBALS['HTML']->listTableTop($title);

while ($row_cat = db_fetch_array($res_cat)) {

	$row_cat['group_name'] = htmlspecialchars($row_cat['group_name']);
	print '
		<tr '.$GLOBALS['HTML']->boxGetAltRowStyle($i++).'>
		<td>'.$row_cat['group_name'].'</td>
		<td>'.$row_cat['unix_group_name'].'</td>
		<td width="40%"><a href="/project/admin/?group_id='.$row_cat['group_id'].'">['.$Language->getText('admin_useredit','project_admin').']</a></td>
		</tr>
	';

}

echo $GLOBALS['HTML']->listTableBottom();

html_feedback_bottom($feedback);

site_admin_footer(array());

?>
