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

site_admin_header(array('title'=>$Language->getText('admin_responses','title')));

function check_select_value($value, $type)
{
	global $Language;
	if( $value == "100" ) {
		print("<span style=\"color:red\"><strong>".$Language->getText('admin_responses','you_cant',array($GLOBALS['type']))."</strong></span><br />\n");
	}
}
?>

<form method="post" action="<?php echo $PHP_SELF; ?>">
<?php echo $Language->getText('admin_responses','existing_responses'); ?><?php echo get_canned_responses(); ?>
<!-- Reinhard Spisser: commenting localization, since otherwise it will not work -->
<!--
<input name="action" type="submit" value="<?php echo $Language->getText('admin','edit'); ?>"/>
<input name="action" type="submit" value="<?php echo $Language->getText('admin','delete'); ?>" />
--->
<input name="action" type="submit" value="Edit"/>
<input name="action" type="submit" value="Delete" />
<input type="checkbox" name="sure" value="<?php echo $Language->getText('admin','yes'); ?>" />
<?php  echo $Language->getText('admin_responses','yes_im_sure'); ?>
</form>

<br /><br />

<?php

if( $action == "Edit" ) {
	// Edit Response
	check_select_value($response_id, $action);
	if( $action2 ) {
		db_query("UPDATE canned_responses SET response_title='$response_title', response_text='$response_text' WHERE response_id='$response_id'");
		print(" <strong>" .$Language->getText('admin_responses','edit_response')."</strong> ");
	} else {
		$res = db_query("SELECT * FROM canned_responses WHERE response_id='$response_id'");
		$row = db_fetch_array($res);
		$response_title=$row[1];
		$response_text=$row[2];
?>

<?php echo $Language->getText('admin_responses','edit_response_other'); ?><br />
<form method="post" action="<?php echo $PHP_SELF; ?>">
<?php echo $Language->getText('admin_responses','response_title'); ?><input type="text" name="response_title" size="30" maxlength="25" value="<?php echo $response_title; ?>" /><br />
<?php echo $Language->getText('admin_responses','response_text'); ?><br />
<textarea name="response_text" cols="50" rows="10"><?php echo $response_text; ?></textarea>
<input type="hidden" name="response_id" value="<?php echo $response_id; ?>" />
<input type="hidden" name="action2" value="<?php echo $Language->getText('admin_responses','go'); ?>" />
<input type="hidden" name="action" value="Edit">
<input type="submit" name="actionsubmit" value="<?php echo $Language->getText('admin','edit'); ?>" />
</form>

<?php
	}
} else if ( $action == "Delete" ) {
	// Delete Response
	check_select_value($response_id, $action);
	if( $sure == "yes" ) {
		db_query("DELETE FROM canned_responses WHERE response_id='$response_id'");
		print(" <strong>" .$Language->getText('admin_responses','deleted_resposes')."</strong> ");
	} else {
		print( $Language->getText('admin_responses','not_sure_dont_click')."<br />");
		print("<em>" .$Language->getText('admin_responses','by_the_way')."</em><br />\n");
	}
} else if ( $action == "Create" ) {
	// New Response
	add_canned_response($response_title, $response_text);
	print(" <strong>" .$Language->getText('admin_responses','add_response')."</strong> ");
} else {
?>

<?php echo $Language->getText('admin_responses','create_new_response'); ?><br />
<form method="post" action="<?php echo $PHP_SELF; ?>">
<?php echo $Language->getText('admin_responses','response_title'); ?><input type="text" name="response_title" size="30" maxlength="25" /><br />
<?php echo $Language->getText('admin_responses','response_text'); ?><br />
<textarea name="response_text" cols="50" rows="10"></textarea>
<br />
<input type="hidden" name="action" value="Create">
<input type="submit" name="actions" value="<?php echo $Language->getText('admin_responses','create'); ?>" />
</form>

<?php
}

site_admin_footer(array());

?>
