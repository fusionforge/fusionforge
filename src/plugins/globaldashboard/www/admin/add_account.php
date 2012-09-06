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

$user_id = getIntFromPost('user_id');
$login_name = getStringFromPost('login_name');
$account_password = getStringFromPost('account_password');
$forge_software = getIntFromPost('forge_software');
$account_domain = getStringFromPost('account_domain');
$account_uri = getStringFromPost('account_uri');
$account_is_foaf = getIntFromPost('account_is_foaf');
$oslc_uri = getStringFromPost('oslc_uri');
$rss_uri = getStringFromPost('rss_uri');
$soap_wsdl = getStringFromPost('soap_wsdl');
$projects_discovery = getIntFromPost('projects_discovery_method');
$artifacts_discovery = getIntFromPost('artifacts_discovery_method');


$user = session_get_user();
if($user->getID() != $user_id) {
	session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&error_msg='. urlencode(_('You can add remote accounts ONLY for yourself !!!')));
}

$t_account_table = "plugin_globaldashboard_user_forge_account";

$t_query = "INSERT INTO $t_account_table "
		." ( user_id, " 
		."forge_account_login_name, "
		."forge_account_password, " 
		."forge_software, "
		."forge_account_domain, "
		."forge_account_uri, "
		."forge_account_is_foaf, "
		."forge_oslc_discovery_uri, " 
		."forge_account_rss_uri, "
		."forge_account_soap_wsdl_uri ) "
		."VALUES ( $1, $2, $3, $4, $5, $6, $7, $8, $9, $10 )";

$result = db_query_params($t_query, array($user_id, $login_name, $account_password, $forge_software, $account_domain, $account_uri, $account_is_foaf, $oslc_uri, $rss_uri, $soap_wsdl));

if($result) {
	$t_discovery_table = "plugin_globaldashboard_account_discovery";
	$t_disc_query = "INSERT INTO $t_discovery_table "
				." ( account_id, "
				."projects_discovery_method, "
				."artifacts_discovery_method ) "
				."VALUES ( $1, $2, $3)";
	$disc_result = db_query_params($t_disc_query, array(db_insertid($result, $t_account_table, 'account_id'), $projects_discovery, $artifacts_discovery));
	if($disc_result) {	
		session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&feedback='. urlencode(_('Remote Account successfully created')));
	} else {
		session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&error_msg='. urlencode(printf('Remote account created but unable to create remote associated discovery parameters: '.db_error())));
	}
} else {
	session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&error_msg='. urlencode(printf('Unable to create remote account: '.db_error())));
}
