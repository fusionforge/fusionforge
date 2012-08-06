<?php
/**
 * Disable optional site mailings for account
 *
 * This page is accessed via URL present in site mailings
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/account.php';

$confirm_hash = getStringFromRequest('confirm_hash');

if (!$confirm_hash) {
	$confirm_hash = getStringFromRequest('ch');
}
if (!$confirm_hash) {
	exit_missing_param('',array(_('Confirm Hash')),'my');
}

$confirm_hash = html_clean_hash_string($confirm_hash);

$res_user = db_query_params ('SELECT * FROM users WHERE confirm_hash=$1',
			array($confirm_hash)) ;

if (db_numrows($res_user) > 1) {
	exit_error(_('This confirm hash exists more than once.'),'my');
}
if (db_numrows($res_user) < 1) {
	exit_error(_('Invalid confirmation hash.'),'my');
}

$row_user = db_fetch_array($res_user);
$user =& user_get_object($row_user['user_id'], $res_user);
if (!$user || !is_object($user)) {
    exit_error(_('Could Not Get User'),'home');
} elseif ($user->isError()) {
    exit_error($user->getErrorMessage(),'my');
}

$all=getStringFromRequest('all');
$user->unsubscribeFromMailings($all);

site_header(array('title'=>_("Unsubscription Complete")));


if ($all) {
	$what = sprintf(_('You have been unsubscribed from all %1$s mailings and notifications. In case you will want to re-activate your subscriptions in the future, login and visit your Account Maintenance page.'), forge_get_config ('forge_name'));
} else {
	$what = sprintf(_('You have been unsubscribed from %1$s site mailings. In case you will want to re-activate your subscriptions in the future, login and visit your Account Maintenance page.'), forge_get_config ('forge_name'));
}
?>

<h2>Unsubscription Complete</h2>
<p>
<?php print $what; ?>
</p>

<p><?php echo util_make_link ("/", _('Return')); ?></p>

<?php

site_footer(array());

?>
