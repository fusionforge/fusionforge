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
require_once $gfplugins.'globaldashboard/include/globalDashboard_utils.php';
require_once $gfplugins.'globaldashboard/include/globalDashboardConstants.php';
require_once $gfplugins.'globaldashboard/common/manage_accounts_db_utils.php';

$user = session_get_user(); // get the user session 

if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "Cannot Process your request for this user.");
}

$type = getStringFromRequest('type');
$id = getStringFromRequest('id');
$pluginname = getStringFromRequest('pluginname');
$action = getStringFromRequest('action');

if (!$type) {
	exit_error("Cannot Process your request","No TYPE specified"); // you can create items in Base.tab and customize this messages
} elseif (!$id) {
	exit_error("Cannot Process your request","No ID specified");
} else {
	if ($type == 'user' && $action == 'edit') {
		$realuser = user_get_object($id);//
		if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
			exit_error("Error", "First activate the User's $pluginname plugin through Account Manteinance Page");
		}
		if ( (!$user) || ($user->getID() != $id)) {
			// if someone else tried to access the private GlobalDashboard part of this user
			exit_error("Access Denied", "You cannot access other user's personal $pluginname");
		}
		// show the header 
		globaldashboard_header(array('title'=> _('Remote Account Management')));
		globaldashboard_toolbar();
		editRemoteAccount(getStringFromRequest('account_id'));
	}
}
site_project_footer(array());

/**
 * 
 * UI to edit a stored remote account.
 * @param integer $account_id
 */
function editRemoteAccount($account_id) {
	$account = array();
	$account = getDBStoredRemoteAccountById($account_id);
	echo '<p>
		<form action="edit_account.php" method="POST">';
	echo '
		<fieldset>	
		<legend> '. _('Main account properties') . ' </legend>	
		<table>
			<tr>
				<td>' . _('User Name') . ': </td>
				<td> 
					<input type="hidden" value="'. $account['account_id'] .'" name="account_id">
					<input type="hidden" value="'. $account['user_id'] .'" name="user_id">
					<input type="text" value="'.$account["forge_account_login_name"].'" name="login_name">
					</input> 
				</td>
			</tr>
			<tr>
				<td>' . _('Account password') . ': </td>
				<td>  
					<input type="password" name="account_password">
				</td>
			</tr>
			<tr>
				<td>' . _('Remote Forge Software') . ': </td>
				<td>  
					<select name="forge_software">
						<option value="'. REMOTE_FORGE_SOFTWARE_FUSIONFORGE .'"';
						if ($account['forge_software'] == REMOTE_FORGE_SOFTWARE_FUSIONFORGE){
							echo ' selected="selected"';
						}
						echo '> FusionForge </option>
						<option value="'. REMOTE_FORGE_SOFTWARE_CODENDI .'"';
						if ($account['forge_software'] == REMOTE_FORGE_SOFTWARE_CODENDI){
							echo 'selected="selected"';
						}
						echo '> Codendi </option>
						<option value="'. REMOTE_FORGE_SOFTWARE_TULEAP .'"';
						if ($account['forge_software'] == REMOTE_FORGE_SOFTWARE_TULEAP) {
							echo 'selected="selected"';
						}
						echo '> Tuleap </option>
						<option value="'. REMOTE_FORGE_SOFTWARE_REDMINE .'"';
						if ($account['forge_software'] == REMOTE_FORGE_SOFTWARE_REDMINE) {
							echo 'selected="selected"';
						}
						echo '> Redmine </option>
						<option value="'. REMOTE_FORGE_SOFTWARE_TRACK .'"';
						if ($account['forge_software'] == REMOTE_FORGE_SOFTWARE_TRACK) {
							echo 'selected="selected"';
						}
						echo '> Track </option>
					</select>
				</td>
			</tr>
			<tr>
				<td>' . _('Account domain') . ': </td>
				<td>  
					<input type="text" size="60" value="'.$account["forge_account_domain"].'" name="account_domain">
					</input>
				</td>
			</tr>
			<tr>
				<td>' . _('Account URI') . ': </td>
				<td>
					<input type="text" size="60" value="'.$account["forge_account_uri"].'" name="account_uri">
					<input type="checkbox" name="account_is_foaf"';
					if($account['forge_account_is_foaf']) {
						echo 'checked="yes"';
					}
					echo '> ';
					echo _('Is account FOAF enabled ?') . ' 
				</td>
			</tr>
		</table>	
		</fieldset>';

	echo '
		<fieldset>	
		<legend> '. _('Account Discovery Capabilities') . ' </legend>	
		<table>
			<tr>
				<td>' . _('OSLC Discovery URI') . ': </td>
				<td> 
					<input type="text" size="60" value="'.$account["forge_oslc_discovery_uri"].'" name="oslc_uri">
					</input> 
				</td>
			</tr>
			<tr>
				<td>' . _('RSS Stream URI') . ': </td>
				<td>  
					<input type="text" size="60" value="'.$account["forge_account_rss_uri"].'" name="rss_uri">
				</td>
			</tr>
			<tr>
				<td>' . _('SOAP WSDL URI') . ': </td>
				<td>  
					<input type="text" size="60" value="'.$account['forge_account_soap_wsdl_uri'].'" name="soap_wsdl">
				</td>
			</tr>
		</table>	
		</fieldset>';
	$discovery = getDBAccountDiscoveryByAccountId($account_id);
	echo '
		<fieldset>
		<legend>' . _('Ressources Discovery Parameters') . '</legend>
		<table>
			<tr>
				<td>' . _('Projects discovery method') . ': </td>
				<td>
					<select name="projects_discovery_method">
						<option value"'. USER_PROJECTS_FETCH_METHOD_NONE .'"';
						if ($discovery['projects_discovery_method'] == USER_PROJECTS_FETCH_METHOD_NONE) {
							echo 'selected="selected"';
						}
						echo '> None </option>
						<option value="'. USER_PROJECTS_FETCH_METHOD_SOAP .'"';
						if ($discovery['projects_discovery_method'] == USER_PROJECTS_FETCH_METHOD_SOAP) {
							echo 'selected="selected"';
						}
						echo '> SOAP </option>
						<option value="'. USER_PROJECTS_FETCH_METHOD_OSLC .'"';
						if ($discovery['projects_discovery_method'] == USER_PROJECTS_FETCH_METHOD_OSLC) {
							echo 'selected="selected"';
						}
						echo '> OSLC-CM </option>
						<option value="' . USER_PROJECTS_FETCH_METHOD_FOAF . '"';
						if ($discovery['projects_discovery_method'] == USER_PROJECTS_FETCH_METHOD_FOAF) {
							echo 'selected="selected"';
						}
						echo '> FOAF </option>
					</select>
				</td>
			</tr>
			<tr>
				<td>' . _('Artifacts discovery method') . ': </td>
				<td>
					<select name="artifacts_discovery_method">
						<option value="'. USER_ARTIFACTS_FETCH_METHOD_NONE .'"';
						if($discovery['artifacts_discovery_method'] == USER_ARTIFACTS_FETCH_METHOD_NONE) {
							echo 'selected="selected"';
						}
						echo '> None </option>
						<option value="'. USER_ARTIFACTS_FETCH_METHOD_SOAP .'"';
						if($discovery['artifacts_discovery_method'] == USER_ARTIFACTS_FETCH_METHOD_SOAP) {
							echo 'selected="selected"';
						}
						
						echo '> SOAP </option>
						<option value="'. USER_ARTIFACTS_FETCH_METHOD_OSLC .'"';
						if($discovery['artifacts_discovery_method'] == USER_PROJECTS_FETCH_METHOD_OSLC) {
							echo 'selected="selected"';
						}
						echo '> OSLC-CM </option>
					</select>
				</td>
			</tr>
		</table>
		</fieldset>';
	
	echo '<p style="text-align: center;">
			<input type="submit" value="Submit account changes">
		</p>
		</form>
	</p>';
	
}
?>
