<?php
/**
  *
  * Site Admin page to edit canned responces for project rejection
  *
  * This page is linked from approve-pending.php
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
require_once('common/include/account.php');
require_once('www/include/proj_email.php');
require_once('www/include/canned_responses.php');
require_once('www/admin/admin_utils.php');
require_once('www/project/admin/project_admin_utils.php');

session_require(array('group'=>'1','admin_flags'=>'A'));

site_admin_header(array('title'=>'Site Admin: Edit Rejection Responses'));

function check_select_value($value, $type)
{
	if( $value == "100" ) {
		print("<span style=\"color:red\"><strong>You can't $type \"None\", bozo!</strong></span><br />\n");
	}
}
?>

<form method="post" action="<?php echo $PHP_SELF; ?>">
Existing Responses: <?php echo get_canned_responses(); ?>
<input name="action" type="submit" value="Edit" />
<input name="action" type="submit" value="Delete" />
<input type="checkbox" name="sure" value="yes" />Yes, I'm sure
</form>

<br /><br />

<?php

if( $action == "Edit" ) {
	// Edit Response
	check_select_value($response_id, $action);
	if( $action2 ) {
		db_query("UPDATE canned_responses SET response_title='$response_title', response_text='$response_text' WHERE response_id='$response_id'");
		print(" <strong>Edited Response</strong> ");
	} else {
		$res = db_query("SELECT * FROM canned_responses WHERE response_id='$response_id'");
		$row = db_fetch_array($res);
		$response_title=$row[1];
		$response_text=$row[2];
?>

Edit Response:<br />
<form method="post" action="<?php echo $PHP_SELF; ?>">
Response Title: <input type="text" name="response_title" size="30" maxlength="25" value="<?php echo $response_title; ?>" /><br />
Response Text:<br />
<textarea name="response_text" cols="50" rows="10"><?php echo $response_text; ?></textarea>
<input type="hidden" name="response_id" value="<?php echo $response_id; ?>" />
<input type="hidden" name="action2" value="go" />
<input type="submit" name="action" value="Edit" />
</form>

<?php
	}
} else if ( $action == "Delete" ) {
	// Delete Response
	check_select_value($response_id, $action);
	if( $sure == "yes" ) {
		db_query("DELETE FROM canned_responses WHERE response_id='$response_id'");
		print(" <strong>Deleted Response</strong> ");
	} else {
		print("If you're aren't sure then why did you click 'Delete'?<br />");
		print("<em>By the way, I didn't delete... just in case...</em><br />\n");
	}
} else if ( $action == "Create" ) {
	// New Response
	add_canned_response($response_title, $response_text);
	print(" <strong>Added Response</strong> ");
} else {
?>

Create New Response:<br />
<form method="post" action="<?php echo $PHP_SELF; ?>">
Response Title: <input type="text" name="response_title" size="30" maxlength="25" /><br />
Response Text:<br />
<textarea name="response_text" cols="50" rows="10"></textarea>
<br />
<input type="submit" name="action" value="Create" />
</form>

<?php
}

site_admin_footer(array());

?>
