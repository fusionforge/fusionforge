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

require_once('../../../env.inc.php');
require_once $gfwww.'include/pre.php';

$account_id = getIntFromPost('account_id');
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
	session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&error_msg='. urlencode(_('You can edit only YOUR remote accounts !!!')));
}

$t_account_table = "plugin_globaldashboard_user_forge_account";

$t_acc_query = 	 "UPDATE $t_account_table ".
			" SET forge_account_login_name=$1,".
			"	  forge_account_password=$2,".
			"	  forge_software=$3,".
			"	  forge_account_domain=$4,".
			"	  forge_account_uri=$5,".
			"	  forge_account_is_foaf=$6,".
			"	  forge_oslc_discovery_uri=$7,".
			"	  forge_account_rss_uri=$8,".
			"	  forge_account_soap_wsdl_uri=$9".
			" WHERE account_id=$10".
			" AND   user_id=$11";

$result = db_query_params( $t_acc_query, array( $login_name, $account_password, $forge_software, $account_domain, $account_uri, $account_is_foaf, $oslc_uri, 
								  $rss_uri, $soap_wsdl, $account_id, $user_id 
						   ) 
				);
if ($result) {
	// Now try to update discovery table
	$t_discovery_table = "plugin_globaldashboard_account_discovery";
	$t_disc_query = "UPDATE $t_discovery_table ".
					" SET projects_discovery_method=$1,".
					"	  artifacts_discovery_method=$2".
					" WHERE account_id=$3";
	$disc_result = db_query_params($t_disc_query, array($projects_discovery, $artifacts_discovery, $account_id));
	if ($disc_result){
		session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&feedback='. _('Remote Account successfully updated'));
	} else {
		session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&feedback='.printf('Unable to update remote account: '.db_error()));
	}
} else {
	session_redirect( '/plugins/globaldashboard/admin/manage_accounts.php?type=user&id='.$user_id.'&pluginname=globaldashboard&feedback='.printf('Unable to update remote account: '.db_error()));
}
?>
