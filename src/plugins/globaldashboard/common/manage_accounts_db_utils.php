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

/**
*
* Gets remote accounts hold by a user from the database.
* @param array $params contain user_id and account fetching method
*
* @return array array of remote accounts hold by the user.
*/
function getDBStoredRemoteAccountsByUserId($user_id) {
	$t_accounts_table = "plugin_globaldashboard_user_forge_account";
	$t_result = db_query_params(
		"SELECT * FROM $t_accounts_table ".
		"WHERE user_id=$1".
		"ORDER BY forge_account_login_name ASC", 
	array( (int) $user_id));

	$t_rows = array();

	while ( $t_row = db_fetch_array( $t_result ) ) {
		$t_rows[] = $t_row;
	}

	return $t_rows;
}

/**
 * Fetches a remote account from FF DB knowing its id.
 * 
 * @param integer $account_id account id.
 * @return array $t_row array of account data.
 */
function getDBStoredRemoteAccountById($account_id) {
	$t_accounts_table = "plugin_globaldashboard_user_forge_account";
	$t_result = db_query_params(
		"SELECT * FROM $t_accounts_table ".
		"WHERE account_id=$1", array( (int) $account_id)
	);
	
	if (!$t_result || ( db_numrows( $t_result ) < 1 )) {
		exit_error( "Remote account not found", 'Global Dashboard' );
	}
	$t_row = array();
	$t_row = db_fetch_array( $t_result );
	return $t_row;
}

/**
*
* Fetches Forge Software for a remote account identified by its id.
*
* @param integer $account_id
*/
function getDBForgeSoftwareByAccountId($account_id) {
	$t_account_table = "plugin_globaldashboard_user_forge_account";
	$t_result = db_query_params(
			"SELECT forge_software FROM $t_account_table ".
			"WHERE account_id=$1", array((int) $account_id)
	);
	if (db_numrows($t_result)) {
		$forge_software = db_result($t_result, 0, 'forge_software');
	}
	return $forge_software;
}

/**
 * 
 * Fetches account discovery params from DB knowing account id
 * 
 * @param integer $account_id
 * 
 * @return array $t_row array of discovery data.
 */
function getDBAccountDiscoveryByAccountId($account_id) {
	$t_discovery_table = "plugin_globaldashboard_account_discovery";
	$t_result = db_query_params(
			"SELECT * FROM $t_discovery_table ".
			"WHERE account_id=$1", array( (int) $account_id)
	);
	
	if (!$t_result || ( db_numrows( $t_result ) < 1 )) {
		exit_error( "Remote account not found", 'Global Dashboard' );
	}
	$t_row = array();
	$t_row = db_fetch_array( $t_result );
	return $t_row;
}

/**
 * 
 * returns the fetch method for a ressource (projects, artifacts, etc.)
 * @param integer $account_id
 * @param string $ressource_type (projects | artifacts | ...)
 */
function getDBFetchMethod($account_id, $ressource_type) {
	$t_account_discovery_table = "plugin_globaldashboard_account_discovery";
	$t_result = db_query_params(
		"SELECT * FROM $t_account_discovery_table ".
		"WHERE account_id=$1",
		array( (int) $account_id)
	);
	
	if(!$t_result || (db_numrows( $t_result ) < 1)) {
		return 0;
	}
	$t_row = db_fetch_array($t_result);
	
	switch ($ressource_type) {
		case 'projects':
			return $t_row['projects_discovery_method'];
		case 'artifacts':
			return $t_row['artifacts_discovery_method'];
		default:
			return 0;
	}
	
}

/**
*
* Gets the remote accounts of a user from its foaf profile
* stored somewhere
* @param string $user_profile_url Url of the user foaf profile.
*
*  @return array array of user accounts
*/
function getUserRdfRemoteAccounts($user_profile_url) {
	// TODO fetch user accounts from its foaf profile using
	// an RDF parser.
}
