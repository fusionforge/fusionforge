<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    
require "account.php";

// ###### function register_valid()
// ###### checks for valid register from form post

$res_lostuser = db_query("SELECT * FROM users WHERE confirm_hash='$confirm_hash'");
if (db_numrows($res_lostuser) > 1) {
	exit_error("Error","This confirm hash exists more than once.");
}
if (db_numrows($res_lostuser) < 1) {
	exit_error("Error","Invalid confirmation hash.");
}
$row_lostuser = db_fetch_array($res_lostuser);

if ($Update && $form_pw && !strcmp($form_pw,$form_pw2)) {
        $user=user_get_object($row_lostuser['user_id']);
        if ($user->setPasswd($form_pw)) {
		session_redirect("/");
        }

	$feedback=$user->getErrorMessage();
}

$HTML->header(array('title'=>"Lost Password Login"));
?>
<p><b>Lost Password Login</b>
<P>Welcome, <?php print $row_lostuser['user_name']; ?>. You may now
change your password.

<FORM action="lostlogin.php">
<p>New Password:
<br><input type="password" name="form_pw">
<p>New Password (repeat):
<br><input type="password" name="form_pw2">
<input type="hidden" name="confirm_hash" value="<?php print $confirm_hash; ?>">
<p><input type="submit" name="Update" value="Update">
</form>

<?php
$HTML->footer(array());

?>
