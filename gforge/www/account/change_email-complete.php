<?php
/**
 * Commit user's email change
 *
 * This page should be accessed with confirmation URL sent to user in email
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

require_once('pre.php');    
require_once('common/include/account.php');

$confirm_hash = getStringFromRequest('confirm_hash');

if (!$confirm_hash) {
	// XXX ogi: What's $ch?
	$confirm_hash = $ch;
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
$u =& user_get_object(db_result($res_user, 0, 'user_id'), $res_user);
if (!$u || !is_object($u)) {
    exit_error('Error','Could Not Get User');
} elseif ($u->isError()) {
    exit_error('Error',$u->getErrorMessage());
}

if (!$u->setEmail($u->getNewEmail())) {
	exit_error(
		'Could Not Complete Operation',
		$u->getErrorMessage()
	);
}

site_user_header(array('title'=>$Language->getText('account_change_email-complete','title')));
?>

<p>
<?php echo $Language->getText('account_change_email-complete','confirm', array($u->getUnixName(),$u->getEmail(),'&lt;'.$u->getUnixName().'@'.$GLOBALS['sys_users_host'].'&gt')) ?>
</p>

<p><a href="/"><?php echo $Language->getText('account_change_email', 'return'); ?></a></p>

<?php

site_user_footer(array());

?>
