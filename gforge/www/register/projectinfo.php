<?php
/**
  *
  * Project Registration: Project Information.
  *
  * This page is used to request data required for project registration:
  *	 o Project Public Name
  *	 o Project Registartion Purpose
  *	 o Project License
  *	 o Project Public Description
  *	 o Project Unix Name
  * All these data are more or less strictly validated.
  *
  * This is last page in registartion sequence. Its successful subsmission
  * leads to creation of new group with Pending status, suitable for approval.
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('common/include/license.php');

//
//	Test if restricted project registration
//
if ($sys_project_reg_restricted) {
	session_require(array('group'=>'1','admin_flags'=>'A'));
}

session_require(array('isloggedin'=>'1'));

if ($submit) {
	$full_name = trim($full_name);
	$purpose = trim($purpose);
	$license_other = trim($license_other);
	$description = trim($description);
	$unix_name = strtolower($unix_name);

	/*
		Fierce validation
	*/

	if (strlen($full_name)<3) {
		$feedback .= $Language->getText('register','invalid_full_name');
	} else if (!account_groupnamevalid($unix_name)) {
		$feedback .= $Language->getText('register','invalid_unix_name');
	} else if (db_numrows(db_query("SELECT group_id FROM groups WHERE unix_group_name='$unix_name'")) > 0) {
		$feedback .= $Language->getText('register','unix_group_name_already_taken');
	} else if (strlen($purpose)<20) {
		$feedback .= $Language->getText('register','describe_registration');
	} else if (strlen($description)<10) {
		$feedback .= $Language->getText('register','comprehensive_description');
	} else if (strlen($description)>255) {
		$feedback .= $Language->getText('register','maximum_description');
	} else if (!$license) {
		$feedback .= $Language->getText('register','no_license_chosen');
	} else if ($license!=GROUP_LICENSE_OTHER && $license_other) {
		$feedback .= $Language->getText('register','conflicting_licenses_choice');
	} else if ($license==GROUP_LICENSE_OTHER && strlen($license_other)<50) {
		$feedback .= $Language->getText('register','more_license_description');
	} else {
		$group = new Group();
		$u =& session_get_user();
		$res = $group->create(
			$u,
			$full_name,
			$unix_name,
			$description,
			$license,
			$license_other,
			$purpose
		);

		if (!$res) {
			$feedback .= $group->getErrorMessage();
		} else {
			$HTML->header(array('title'=>$Language->getText('register','registration_complete'),'pagename'=>'register_complete'));

			?>

			<p><?php echo $Language->getText('register','project_submitted',array($GLOBALS['sys_name']))?>
			</p>

			<?php

			$HTML->footer(array());
			exit();
		}

	}
} else if ($i_disagree) {
	session_redirect("/");
}

site_header(array('title'=>$Language->getText('register','project_information'),'pagename'=>'register_projectinfo'));
?>

<p><?php echo $Language->getText('register','apply_for_registration') ?>
</p>

<form action="<?php echo $PHP_SELF; ?>" method="post">

<?php echo $Language->getText('register','project_full_name') ?>

<input size="40" maxlength="40" type=text name="full_name" value="<?php echo stripslashes($full_name); ?>">

<?php echo $Language->getText('register','purpose_and_summarization', array($GLOBALS['sys_name']))?>
<p>
<font size="-1">
<textarea name="purpose" wrap="virtual" cols="70" rows="10">
<?php echo stripslashes($purpose); ?>
</textarea>
</font>

<?php echo $Language->getText('register','project_license', array($GLOBALS['sys_name'])) ?>

<?php
echo license_selectbox('license',$license);
?>
<p>
<?php echo $Language->getText('register','other_license') ?>
<br />
<textarea name="license_other" wrap=virtual cols=60 rows=5>
<?php echo stripslashes($license_other); ?>
</textarea>
<p>

<?php echo $Language->getText('register','project_description')?>
</p>
<font size="-1">
<textarea name="description" wrap="virtual" cols="70" rows="5">
<?php echo stripslashes($description); ?>
</textarea>
</font>

<?php echo $Language->getText('register','project_unix_name',array($GLOBALS['sys_default_domain'])) ?>

<input type=text maxlength="15" SIZE="15" name="unix_name" value="<?php echo $unix_name; ?>">

<div align="center">
<input type=submit name="submit" value="<?php echo $Language->getText('register','i_agree') ?>"> <input type=submit name="i_disagree" value="<?php echo $Language->getText('register','i_disagree') ?>">
</div>

</form>

<?php

site_footer(array());

?>

