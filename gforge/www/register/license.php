<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: license.php,v 1.31 2000/12/07 21:08:41 tperdue Exp $

require "pre.php";    // Initial db and session library, opens session
require "vars.php";
require('account.php');
session_require(array('isloggedin'=>'1'));

if ($insert_group_name && $group_id && $rand_hash && $form_full_name && $form_unix_name) {
	/*
		check for valid group name
	*/
	$form_unix_name=strtolower($form_unix_name);

	if (!account_groupnamevalid($form_unix_name)) {
		exit_error("Invalid Group Name",$register_error);
	}
	/*
		See if it's taken already
	*/
	if (db_numrows(db_query("SELECT group_id FROM groups WHERE unix_group_name='$form_unix_name'")) > 0) {
		exit_error("Group Name Taken","That group name already exists.");
	}
	/*
		Hash prevents them from updating a live, existing group account
	*/
	$sql="UPDATE groups SET unix_group_name='$form_unix_name', group_name='$form_full_name', ".
		"http_domain='$form_unix_name.$GLOBALS[sys_default_domain]', homepage='$form_unix_name.$GLOBALS[sys_default_domain]' ".
		"WHERE group_id='$group_id' AND rand_hash='__$rand_hash'";
	$result=db_query($sql);

} else {
	exit_error('Error','Missing Info Or Invalid State. Some form variables were missing. 
		If you are certain you entered everything, <B>PLEASE</B> report to admin@'. $GLOBALS['sys_default_domain'].' and
		include info on your browser and platform configuration');
}

$HTML->header(array('title'=>'License'));
echo "<H2>".$Language->REGISTER_step5_title."</H2>";
echo $Language->REGISTER_step5;
?>

<FONT size=-1>
<FORM action="category.php" method="post">
<INPUT TYPE="HIDDEN" NAME="insert_license" VALUE="y">
<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="<?php echo $group_id; ?>">
<INPUT TYPE="HIDDEN" NAME="rand_hash" VALUE="<?php echo $rand_hash; ?>">
<B>Your License:</B><BR>
<?php
	echo '<SELECT NAME="form_license">';
	while (list($k,$v) = each($LICENSE)) {
		print "<OPTION value=\"$k\"";
		print ">$v\n";
	}
	echo '</SELECT>';

?>
<P>
If you selected "other", please provide an explanation along
with a description of your license. Realize that other licenses may
not be approved. 
<BR><TEXTAREA name="form_license_other" wrap=virtual cols=60 rows=10></TEXTAREA>
<P>
<H2><FONT COLOR="RED"><?php echo $Language->REGISTER_step4_warn; ?></FONT></H2> 
<P>
<INPUT type=submit name="Submit" value="<?php echo $Language->REGISTER_step6_title; ?>">
</FORM>
</FONT>

<?php
$HTML->footer(array());

?>

