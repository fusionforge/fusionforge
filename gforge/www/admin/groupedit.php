<?php
/**
  *
  * Site Admin group properties editing page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/vars.php');
require_once('www/admin/admin_utils.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

$group =& group_get_object($group_id);

if (!$group || !is_object($group)) {
        exit_error(
        	'Error',
                'Error creating group object'
        );
}

if ($group->isError()) {
	// If group object was created, but has error condition,
	// don't treat this as fatal - this page is supposed to be
	// "repair" page for such circumstances.
	$feedback .= $group->getErrorMessage().'<br /> ';
}

// This function performs very update
function do_update(&$group, $is_public, $status, $license,
		   $group_type, $unix_box, $http_domain) {
	global $feedback;

	db_begin();

        if (!$group->setStatus(session_get_user(), $status)) {
		$feedback .= $group->getErrorMessage();
		db_rollback();
		return false;
	}

        if (!$group->updateAdmin(session_get_user(), $is_public, $license, $group_type, $unix_box, $http_domain)) {
		$feedback .= $group->getErrorMessage();
		db_rollback();
		return false;
        }

	db_commit();

	$feedback .= 'Updated<br /> ';

	return true;
}


if ($submit) {

	do_update($group, $form_public, $form_status, $form_license,
		  1, $form_box, $form_domain);

} else if ($resend) {

	$group->sendApprovalEmail();
	$feedback .= 'Instruction email sent<br /> ';

}

site_admin_header(array('title'=>'Site Admin: Group Info'));

echo '<h2>'.$group->getPublicName().'</h2>' ;?>

<p>
<?php print "<a href=\"/project/admin/?group_id=$group_id\"><h3>[Project Admin]</h3></a>"; ?></p>

<p>
<form action="<?php echo $PHP_SELF; ?>" method="post">


<table>

<tr>
<td colspan="3">

Status:
<?php echo html_build_select_box_from_arrays(
	array('I','A','P','H','D'),
	array(
		'Incomplete (I)',
		'Active (A)',
		'Pending (P)',
		'Holding (H)',
		'Deleted (D)'
	),
	'form_status', $group->getStatus(), false
); ?>

Public?:
<?php echo html_build_select_box_from_arrays(
	array(0,1),
	array('No','Yes'),
	'form_public', $group->isPublic(), false
); ?>

</td>
</tr>

<tr>
<td>
Unix Group Name:
</td>
<td>
<?php echo $group->getUnixName(); ?>
</td>
</tr>

<tr>
<td>
License:
</td>
<td>
<select name="form_license">
<option value="none">N/A</option>
<option value="other">Other</option>
<?php
	while (list($k,$v) = each($LICENSE)) {
		print "<option value=\"$k\"";
		if ($k == $group->getLicense()) print " selected=\"selected\"";
		print ">$v</option>\n";
	}
?>
</select>
</td>
</tr>

<tr>
<td>
Home Box:
</td>
<td>
<input type="text" name="form_box" value="<?php echo $group->getUnixBox(); ?>" />
</td>
</tr>

<tr>
<td>
HTTP Domain:
</td>
<td>
<input size="40" type="text" name="form_domain" value="<?php echo $group->getDomain(); ?>" />
</td>
</tr>

<tr>
<td>
Registration Application:
</td>
<td>
<?php echo $group->getRegistrationPurpose(); ?>
</td>
</tr>

<?php
if ($group->getLicense() == 'other') {
?>
	<tr>
	<td>License Other:
	</td>
	<td>
	<?php echo $group->getLicenseOther(); ?>
	</td>
	</tr>
<?php
}
?>

</table>

<input type="hidden" name="group_id" value="<?php print $group_id; ?>" />

<br /><input type="submit" name="submit" value="Update" />
&nbsp;&nbsp;&nbsp; <input type="submit" name="resend" value="Resend New Project Instruction Email" />
</form></p>

<?php

echo show_grouphistory($group->getID());

site_admin_footer(array());

?>
