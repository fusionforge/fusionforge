<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require("pre.php");
require("vars.php");
require("account.php");
require("proj_email.php");
require("canned_responses.php");
require($DOCUMENT_ROOT.'/admin/admin_utils.php');
require($DOCUMENT_ROOT.'/project/admin/project_admin_utils.php');
global $feedback;

session_require(array('group'=>'1','admin_flags'=>'A'));
site_admin_header(array('title'=>'Approving Pending Projects'));

function check_select_value($value, $type)
{
	if( $value == "100" ) {
		print("<span style='color: Red'><b>You can't $type \"None\", bozo!</b></span><br>\n");
	}
}
?>

<form method="post" action="<?php echo $PHP_SELF; ?>">
Existing Responses: <?php echo get_canned_responses(); ?>
<input name="action" type="submit" value="Edit">
<input name="action" type="submit" value="Delete">
<input type="checkbox" name="sure" value="yes">Yes, I'm sure
</form>

<br><br>

<?php

if( $action == "Edit" ) {
	// Edit Response
	check_select_value($response_id, $action);
	if( $action2 ) {
		db_query("UPDATE canned_responses SET response_title='$response_title', response_text='$response_text' WHERE response_id='$response_id'");
		print(" <b>Edited Response</b> ");
	} else {
		$res = db_query("SELECT * FROM canned_responses WHERE response_id='$response_id'");
		$row = db_fetch_array($res);
		$response_title=$row[1];
		$response_text=$row[2];
?>

Edit Response:<br>
<form method="post" action="<?php echo $PHP_SELF; ?>">
Response Title: <input type="text" name="response_title" size="30" maxlength="25" value="<?php echo $response_title; ?>"><br>
Response Text:<br>
<textarea name="response_text" cols="50" rows="10"><?php echo $response_text; ?></textarea>
<input type="hidden" name="response_id" value="<?php echo $response_id; ?>">
<input type="hidden" name="action2" value="go">
<input type="submit" name="action" value="Edit">
</form>

<?php
	}
} elseif( $action == "Delete" ) {
	// Delete Response
	check_select_value($response_id, $action);
	if( $sure == "yes" ) {
		db_query("DELETE FROM canned_responses WHERE response_id='$response_id'");
		print(" <b>Deleted Response</b> ");
	} else {
		print("If you're aren't sure then why did you click 'Delete'?<br>");
		print("<i>By the way, I didn't delete... just in case...</i><br>\n");
	}
} elseif( $action == "Create" ) {
	// New Response
	add_canned_response($response_title, $response_text);
	print(" <b>Added Response</b> ");
} else {
?>

Create New Response:<br>
<form method="post" action="<?php echo $PHP_SELF; ?>">
Response Title: <input type="text" name="response_title" size="30" maxlength="25"><br>
Response Text:<br>
<textarea name="response_text" cols="50" rows="10"></textarea>
<br>
<input type="submit" name="action" value="Create">
</form>

<?
}

site_admin_footer(array());
?>
