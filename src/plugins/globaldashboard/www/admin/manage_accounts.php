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

if (!$type) {
	exit_error("Cannot Process your request","No TYPE specified"); // you can create items in Base.tab and customize this messages
} elseif (!$id) {
	exit_error("Cannot Process your request","No ID specified");
} else {
	if ($type == 'user') {
		$realuser = user_get_object($id);//
		if (!($realuser) || !($realuser->usesPlugin($pluginname))) {
			exit_error("Error", "First activate the User's $pluginname plugin through Account Manteinance Page");
		}
		if ( (!$user) || ($user->getID() != $id)) {
			// if someone else tried to access the private GlobalDashboard part of this user
			exit_error("Access Denied", "You cannot access other user's personal $pluginname");
		}
		// show the header 
		globaldashboard_header(array('title'=> _('Global Dashboard Configuration')));
		globaldashboard_toolbar();
		
		listStoredRemoteAccounts($user->getID());
		createNewAccountForm($user->getID());
	}
}
site_project_footer(array());

/**
*
* Displays the list of all stored remote accounts of the user.
* 
* @param integer $user_id user id.
*/
function listStoredRemoteAccounts($user_id) {
	global $HTML, $feedback;
	
	//echo 'Here are stored accounts list';

	
	$accounts = getDBStoredRemoteAccountsByUserId($user_id);
	if (count($accounts) > 0) { 
		echo '<p>';
		echo '<fieldset>';
		echo '<legend> ' . _('Stored remote accounts') . ' </legend>';
		$html = '';
		$tablearr = array(_("User Name"), _("Remote site"), _("User account URL"), _("Action") );
		$html .= $HTML->listTableTop($tablearr);
		$i = 0;
		foreach ($accounts as $account) {
			$html = $html . '
				<tr ' . $HTML->boxGetAltRowStyle($i++) . '>
				<td><a href="'.$account['forge_account_uri'].'">'.$account['forge_account_login_name'].'</a>
				</td>
				<td><a href="'.$account['forge_account_domain'].'">'.$account['forge_account_domain'].'</a>
				</td>
				<td><a href="'.$account['forge_account_uri'].'">'.$account['forge_account_uri'].'</a>
				</td>
				<td><a href="/plugins/globaldashboard/admin/edit_account_page.php?type=user&id='.$user_id.'&pluginname=globaldashboard&action=edit&account_id='.$account['account_id'].'">   '. _("Edit") . '    </a>
					<a href="/plugins/globaldashboard/admin/delete_account.php?account_id='.$account['account_id'].'&user_id='.$account['user_id'].'">   '. _("Delete") . ' </a> 
				</td>
				</tr>';
		}
		$html .= $HTML->listTableBottom();
		echo $html;
		echo '</fieldset>';
		echo '</p>';
	}
}

/**
*
* Form to add new remote accounts
*/
function createNewAccountForm($user_id) {
	echo '<p>
			<form action="add_account.php" method="POST">';
	echo '
			<fieldset>	
			<legend> '. _('Create a new remote account') . ' </legend>	
			<table>
				<tr>
					<td>' . _('User Name') . ': <span class="requiredfield">*</span> </td>
					<td> 
						<input type="hidden" value="'. $user_id .'" name="user_id">
						<input type="text" name="login_name"> 
					</td>
				</tr>
				<tr>
					<td>' . _('Account password') . ': <span class="requiredfield">*</span> </td>
					<td>  
						<input type="password" name="account_password">
					</td>
				</tr>
				<tr>
					<td>' . _('Remote Forge Software') . ': <span class="requiredfield">*</span> </td>
					<td>  
						<select name="forge_software">
							<option value="'. REMOTE_FORGE_SOFTWARE_FUSIONFORGE .'" selected="selected"> FusionForge </option>
							<option value="'. REMOTE_FORGE_SOFTWARE_CODENDI .'"> Codendi </option>
							<option value="'. REMOTE_FORGE_SOFTWARE_TULEAP .'"> Tuleap </option>
							<option value="'. REMOTE_FORGE_SOFTWARE_REDMINE .'"> Redmine </option>
							<option value="'. REMOTE_FORGE_SOFTWARE_TRACK .'"> Track </option>
						</select>
					</td>
				</tr>
				<tr>
					<td>' . _('Account domain') . ': <span class="requiredfield">*</span> </td>
					<td>  
						<input type="text" size="60" name="account_domain">
					</td>
				</tr>
				<tr>
					<td>' . _('Account URI') . ': <span class="requiredfield">*</span> </td>
					<td>
						<input type="text" size="60" name="account_uri">
						<input type="checkbox" name="account_is_foaf"> '. _("Is account foaf enabled ?").'
					</td>
				</tr>
				<tr>
					<td>' . _('OSLC Discovery URI') . ': </td>
					<td> 
						<input type="text" size="60" name="oslc_uri">
					</td>
				</tr>
				<tr>
					<td>' . _('RSS Stream URI') . ': </td>
					<td>  
						<input type="text" size="60" name="rss_uri">
					</td>
				</tr>
				<tr>
					<td>' . _('SOAP WSDL URI') . ': </td>
					<td>  
						<input type="text" size="60" name="soap_wsdl">
					</td>
				</tr>
			</table>	
			</fieldset>';
	echo '
			<fieldset>
			<legend>' . _('Ressources Discovery Parameters') . '</legend>
			<table>
				<tr>
					<td>' . _('Projects discovery method') . ': </td>
					<td>
						<select name="projects_discovery_method">
							<option value="'. USER_PROJECTS_FETCH_METHOD_NONE .'" selected="selected"> None </option>
							<option value="'. USER_PROJECTS_FETCH_METHOD_SOAP .'"> SOAP </option>
							<option value="'. USER_PROJECTS_FETCH_METHOD_OSLC .'"> OSLC-CM </option>
							<option value="'. USER_PROJECTS_FETCH_METHOD_FOAF .'"> FOAF </option>
						</select>
					</td>
				</tr>
				<tr>
					<td>' . _('Artifacts discovery method') . ': </td>
					<td>
						<select name="artifacts_discovery_method">
							<option value="'. USER_ARTIFACTS_FETCH_METHOD_NONE .'" selected="selected"> None </option>
							<option value="'. USER_ARTIFACTS_FETCH_METHOD_SOAP .'"> SOAP </option>
							<option value="'. USER_ARTIFACTS_FETCH_METHOD_OSLC .'"> OSLC-CM </option>
						</select>
					</td>
				</tr>
			</table>
			</fieldset>';
	
	echo '	<p style="text-align: center;">
				<input type="submit" value="submit new account"></input>
			</p>
			</form>
		</p>';
}
?>
