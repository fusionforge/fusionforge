<?php
/**
  *
  * Site Admin user properties editing page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('common/include/account.php');
require_once('www/admin/admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));
$unix_status2str = array(
	'N'=>'No Unix account (N)',
	'A'=>'Active (A)',
	'S'=>'Suspended (S)',
	'D'=>'Deleted (D)'
);

$u =& user_get_object($user_id);
exit_assert_object($u, 'User');

if ($action == "update_user") {

	if (!$u->setEmail($email)
	    || !$u->setShell($shell)
	    || !$u->setStatus($status)) {
		exit_error(
			'Could Not Complete Operation',
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
		$feedback .= 'Updated<br>';
	}

}


site_admin_header(array('title'=>'Site Admin: User Info'));

?>
<h3>Account Information <sup>1</sup></h3>

<FORM method="post" action="<?php echo $PHP_SELF; ?>">
<INPUT type="hidden" name="action" value="update_user">
<INPUT type="hidden" name="user_id" value="<?php print $user_id; ?>">

<table>
<tr>
<td>
User ID:
</td>
<td>
<?php echo $u->getID(); ?>
</td>
</tr>

<td>
User Name:
</td>
<td>
<?php echo $u->getUnixName(); ?>
</td>
</tr>

<td>
Real Name:
</td>
<td>
<?php echo $u->getRealName(); ?>
</td>
</tr>

<tr>
<td>
Web Account Status:
</td>
<td>
<?php echo html_build_select_box_from_arrays(
	array('P','A','S','D'),
	array(
		'Pending (P)',
		'Active (A)',
		'Suspended (S)',
		'Deleted (D)'
	),
	'status', $u->getStatus(),false
); ?>
</td>
</tr>

<tr>
<td>
Unix Account Status<sup>2</sup>:
</td>
<td>
<?php echo $unix_status2str[$u->getUnixStatus()]; ?>
</td>
</tr>

<tr>
<td>
Unix Shell:
</td>
<td>
<select name="shell">
<?php account_shellselects($u->getShell()); ?>
</select>
</td>
</tr>

<tr>
<td>
Email:
</td>
<td>
<input type="TEXT" name="email" value="<?php echo $u->getEmail(); ?>" size="25" maxlength="55">
</td>
</tr>

<tr>
<td>
Current confirm hash:
</td>
<td>
<?php echo $u->getConfirmHash(); ?>
</td>
</tr>


</table>

<input type="submit" name="submit" value="Update">

<p>
<sup>1</sup> This pages allows to change only direct properties of user object. To edit
properties pertinent to user within specific group, visit admin page of that
group (below).
</p>
<p>
<sup>2</sup> Unix status updated mirroring web status, unless it has
value 'No unix account (N)'.
</p>

</FORM>

<HR>

<p>
<h3>Groups Membership</h3>

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
$title[]='Name';
$title[]='Unix Name';
$title[]='Operations';
echo html_build_list_table_top($title);

while ($row_cat = db_fetch_array($res_cat)) {

	$row_cat[group_name] = htmlspecialchars($row_cat[group_name]);
	print '
		<tr bgcolor="'.html_get_alt_row_color($i++).'">
		<td>'.$row_cat['group_name'].'</td>
		<td>'.$row_cat['unix_group_name'].'</td>
		<td><a href="/project/admin/?group_id='.$row_cat[group_id].'">[Project Admin]</a>
		<a href="/project/admin/userperms.php?group_id='.$row_cat['group_id'].'">[Member Permissions]</a></td>
		</tr>
	';

}

print "</table>";

html_feedback_bottom($feedback);

site_admin_footer(array());

?>
