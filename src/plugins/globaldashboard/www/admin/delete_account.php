<?php
/**
* Copyright 2011, Sabri LABBENE - Institut Télécom
*
*
* This file is part of FusionForge.
*
* FusionForge is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* FusionForge is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

require_once '../../../env.inc.php';
require_once $gfwww.'include/pre.php';

$account_id = getStringFromRequest('account_id');
$user_id = getStringFromRequest('user_id');

$user = session_get_user();
if($user->getID() != $user_id) {
	session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&error_msg='. urlencode(_('You can remove only YOUR remote accounts !!!')));
}

$t_account_table = "plugin_globaldashboard_user_forge_account";
$t_query = "DELETE FROM $t_account_table WHERE account_id=$1 AND user_id=$2";

$result = db_query_params($t_query, array($account_id, $user_id));
if ($result) {
	session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&feedback='. urlencode(_('Remote Account successfully deleted')));
} else {
	session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&error_msg='. urlencode(_('Unable to delete remote account: ').db_error()));
}
?>
