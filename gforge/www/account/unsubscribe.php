<?php
/**
 * Disable optional site mailings for account
 *
 * This page is accessed via URL present in site mailings
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

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/include/account.php');

$confirm_hash = getStringFromRequest('confirm_hash');

if (!$confirm_hash) {
	// XXX ogi: What's $ch?
	$confirm_hash = getStringFromRequest('ch');
}
if (!$confirm_hash) {
	exit_missing_param();
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
if (!$u || !is_object($u)) {
    exit_error('Error','Could Not Get User');
} elseif ($u->isError()) {
    exit_error('Error',$u->getErrorMessage());
}

$all=getStringFromRequest('all');
$user->unsubscribeFromMailings($all);

site_header(array('title'=>"Unsubscription Complete"));

if ($all) {
	$what = $Language->getText('account_unsubscribe', 'all_mailings', array($GLOBALS['sys_name']));
} else {
	$what = $Language->getText('account_unsubscribe', 'site_mailings', array($GLOBALS['sys_name']));
}
?>

<h2>Unsubscription Complete</h2>
<p>
You have been unsubscribed from <?php print $what; ?>. In case you
will want to re-activate your subscriptions in the future, login
and visit your Account Maintenance page.
</p>

<p>
<a href="<?php echo $GLOBALS['sys_urlprefix']; ?>/"><?php echo $Language->getText('general', 'return', $GLOBALS['sys_name']); ?></a>
</p>

<?php

site_footer(array());

?>
