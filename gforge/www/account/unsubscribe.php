<?php
/**
  *
  * Disable optional site mailings for account
  *
  * This page is accessed via URL present in site mailings
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

if (!$confirm_hash) {
	$confirm_hash = $ch;
}

$confirm_hash = html_clean_hash_string($confirm_hash);

$res_user = db_query("SELECT * FROM users WHERE confirm_hash='$confirm_hash'");
if (db_numrows($res_user) > 1) {
	exit_error("Error","This confirm hash exists more than once.");
}
if (db_numrows($res_user) < 1) {
	exit_error("Error","Invalid confirmation hash.");
}

$row_user = db_fetch_array($res_user);
$user =& user_get_object($row_user['user_id'], $res_user);
exit_assert_object($user, 'User');

$user->unsubscribeFromMailings($all);

site_header(array('title'=>"Unsubscription Complete"));

if ($all) {
	$what = $Language->getText('account_unsubscribe', 'all_mailings');
} else {
	$what = $Language->getText('account_unsubscribe', 'site_mailings');
}
?>

<h2>Unsubscription Complete</h2>
<p>
You have been unsubscribed from <?php print $what; ?>. In case you
will want to re-activate your subscriptions in the future, login
and visit your Account Maintenance page.
</p>

<p>
<A href="/"><?php echo $Language->getText('general', 'return'); ?></A>
</p>

<?php
site_footer(array());

?>
