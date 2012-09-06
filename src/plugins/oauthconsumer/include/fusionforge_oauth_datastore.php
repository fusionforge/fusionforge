<?php

/**
 * This file is (c) Copyright 2010 by Olivier BERGER, Madhumita DHAR, Institut TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the COCLICO
 * project with financial support of its funders.
 *
 */


// Inspired from examples described in "Creating a OAuth Service
// Provider in PHP" by Morten Fangel
// (http://sevengoslings.net/~fangel/oauthprovider-sp-guide.html)

require_once 'OAuth.php';

/**
 * OAuthDataStore singleton class to manage tokens, consumers and providers in FusionForge DB
 *
 * Everything specific to the DB model is handled in this class : no other SQL request should exist outside it
 * It should be reimplemented for other apps, the rest of the classes being untouched
 *
 * It will assume that OAuthProvider, OauthAuthzConsumer, OauthAuthzToken and its sub-classes are used
 *
 * @author Olivier Berger
 *
 */

class FFOAuthDataStore extends OAuthDataStore {

	// Hold an instance of the class
	private static $instance;

	/**
	 * Singleton pattern's method to retrieve the instance
	 */
	public static function singleton()
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	/**
	 * Prevent users to clone the instance
	 */
	public function __clone()
	{
		exit_error('Clone is not allowed.', 'oauthconsumer');
	}

	/**
	 * Retrieve values of columns for a provider in the DB provided its id
	 *
	 * @param int $p_id ID in the DB
	 * @return array of column values
	 */
	function find_provider_from_id( $p_id ) {
		$t_provider_table = "plugin_oauthconsumer_provider";

		$t_result = db_query_params ("SELECT * FROM $t_provider_table WHERE id=$1",
					   array ( (int) $p_id )) ;
		if (!$t_result || ( db_numrows( $t_result ) < 1 )) {
			exit_error( "provider not found!", 'oauthconsumer' );
		}

		$t_row = db_fetch_array( $t_result );

		return $t_row;
	}

	/**
	 * Retrieve a table of columns values for all providers
	 *
	 * @return array of arrays of column values
	 */
	function find_all_providers() {
		$t_provider_table = "plugin_oauthconsumer_provider";
		$t_result = db_query_params("SELECT * FROM $t_provider_table ORDER BY name ASC", array());

		$t_rows = array();

		while ( $t_row = db_fetch_array( $t_result ) ) {
			$t_rows[] = $t_row;
		}

		return $t_rows;
	}

	/**
	 * Retrieve values of columns for a provider in the DB provided its name
	 *
	 * @param string $p_provider_name
	 * @return array of column values
	 */
	function find_provider_from_name( $p_provider_name ) {
		$t_provider_table = "plugin_oauthconsumer_provider";

		$t_query = "SELECT * FROM $t_provider_table WHERE name = $1";
		$t_result = db_query_params( $t_query, array( $p_provider_name ) );

		if ( db_numrows( $t_result ) < 1 ) {
		  return null;
		}

		$t_row = db_fetch_array( $t_result );

		return $t_row;
	}

	/**
	 * Saves an OauthAuthzprovider to the DB
	 *
	 * @param OauthAuthzprovider $provider
	 * @return int the provider ID in the DB
	 */
	public function save_provider($provider) {
		$t_provider_table = "plugin_oauthconsumer_provider";

		$provider_id = $provider->get_id();
		if ( 0 == $provider_id ) { # create

			db_begin();
			$result = db_query_params ("INSERT INTO $t_provider_table".' ( name, description, consumer_key, consumer_secret, request_token_url, authorize_url, access_token_url) VALUES ($1,$2,$3,$4,$5,$6,$7)',
						   array ( $provider->get_name(), $provider->get_description(), $provider->get_consumer_key(), $provider->get_consumer_secret(), $provider->get_request_token_url(), $provider->get_authorize_url(), $provider->get_access_token_url())) ;
			if (!$result) {
				//$this->setError('Error Adding provider: '.db_error());
				db_rollback();
				return false;
			}
			$provider_id = db_insertid($result, $t_provider_table, 'id' );

			db_commit();

		} else { # update
			$t_query = "UPDATE $t_provider_table SET name=$1, description=$2, consumer_key=$3, consumer_secret=$4, request_token_url=$5, authorize_url=$6, access_token_url=$7 WHERE id=$8";
			db_query_params( $t_query, array ( $provider->get_name(), $provider->get_description(), $provider->get_consumer_key(), $provider->get_consumer_secret(), $provider->get_request_token_url(), $provider->get_authorize_url(), $provider->get_access_token_url(), $provider->get_id()) );
		}
		return $provider_id;
	}

	
  /**
   * Deletes a provider from the DB
   *
   * @param int $provider_id
   */
	public function delete_provider( $provider_id ) {

		$t_provider_table = "plugin_oauthconsumer_provider";

		$t_query = "DELETE FROM $t_provider_table WHERE id=$1";
		$t_result = db_query_params( $t_query, array( (int) $provider_id ) );

		if (!$t_result) {
			db_rollback();
			return false;
		}

		db_commit();
		return true;
	}
	
	/**
	* Saves an OAuthAccessToken to the DB
	*
	* @param OAuthAccessToken $token
	* @return int the token ID in the DB
	*/
	public function save_access_token($token) {
	
		$t_token_table = "plugin_oauthconsumer_access_token";
		$time_stamp = time();
		$token_id = $token->get_id();
		if ( 0 == $token_id ) {
			# create
			$t_query = "INSERT INTO $t_token_table ( provider_id, token_key, token_secret, user_id, time_stamp ) VALUES ($1, $2, $3, $4, $5)";
			$t_result = db_query_params( $t_query, array( $token->get_provider_id(), $token->get_token_key(), $token->get_token_secret(), $token->get_user_id(), $time_stamp ) );
		
			$token_id = db_insertid($t_result, $t_token_table, 'id');
			return $token_id;
		}
		else { # TODO feature to be added later, with lifetime/limited access feature support
			//$t_query = "UPDATE $t_token_table SET provider_id=$1, token_key=$2, token_secret=$3, user_id=$4, time_stamp=$4 WHERE id=$5";
			//db_query_params( $t_query, array( $token->getproviderId(), $token->key, $token->secret, $token->getUserId(), $token->gettime_stamp(), $token->getId() ) );
			exit_error("The access token already exists and cannot be modified.", 'oauthconsumer');
		}
	
	}

	/**
	* Retrieve a table of columns values for all access tokens (of a user)
	*
	* @param int $user_id
	* @return array of arrays of column values
	*/
	public function find_all_access_tokens($user_id) {
		$t_token_table = "plugin_oauthconsumer_access_token";
		if(isset($user_id)||($user_id)) {
			$t_query = "SELECT * FROM $t_token_table WHERE user_id = $1";
			$t_result = db_query_params( $t_query, array( (int) $user_id ) );
				
		}
		$t_rows = array();
	
		while ( $t_row = db_fetch_array( $t_result ) ) {
			$t_rows[] = $t_row;
		}
	
		return $t_rows;
	}
	
	/**
	* Retrieve values of columns for a token in the DB provided its key
	*
	* @param string $token_key
	* @return array of column values
	*/
	public function find_access_token_from_key($token_key) {
		$t_token_table = "plugin_oauthconsumer_access_token";
	
		$t_query = "SELECT * FROM $t_token_table WHERE token_key = $1";
		$t_result = db_query_params( $t_query, array( $token_key ) );
	
		if ( db_numrows( $t_result ) < 1 ) {
			return null;
		}
	
		$t_row = db_fetch_array( $t_result );	
		return $t_row;
	}
	
	/**
	 * Retrieve values of columns for a token in the DB provided its id
	 *
	 * @param int $token_id
	 * @return array of column values
	 */
	public function find_token_from_id($token_id) {
		$t_token_table = "plugin_oauthconsumer_access_token";
	
		$t_query = "SELECT * FROM $t_token_table WHERE id = $1";
		$t_result = db_query_params( $t_query, array( (int) $token_id ) );
	
		if ( db_numrows( $t_result ) < 1 ) {
			return null;
		}
	
		$t_row = db_fetch_array( $t_result );	
		return $t_row;
	}
	
	/**
	 * Retrieve a table of columns values for all tokens issued for a provider (and a user)
	 *
	 * @param int $provider_id
	 * @param int $user_id
	 * @return array of arrays of column values
	 */
	public function find_access_tokens_by_provider($provider_id, $user_id) {
		$t_token_table = "plugin_oauthconsumer_access_token";
	
		if(isset($user_id)) {
			$t_query = "SELECT * FROM $t_token_table WHERE provider_id = $1 AND user_id = $2";
			$t_result = db_query_params( $t_query, array( (int) $provider_id, (int) $user_id ) );
		}
	
		$t_rows = array();
	
		while ( $t_row = db_fetch_array( $t_result ) ) {
			$t_rows[] = $t_row;
		}
	
		return $t_rows;
	}
	
	/**
	* Deletes an access token from the DB
	*
	* @param string $token_type
	* @param int $token_id
	*/
	function delete_access_token($token_id) {
		$t_token_table = "plugin_oauthconsumer_access_token";
	
		$t_query = "DELETE FROM $t_token_table WHERE id=$1";
		$t_result = db_query_params( $t_query, array( (int) $token_id ) );
		
		if (!$t_result) {
			db_rollback();
			return false;
		}
		
		db_commit();
		return true;
	}
	
	/**
	* Saves an OAuthResource to the DB
	*
	* @param OAuthResource $resource
	* @return int the resource ID in the DB
	*/
	public function save_oauth_resource($resource) {
	
		$t_resource_table = "plugin_oauthconsumer_resource";
		$id = $resource->get_id();
		if ( 0 == $id ) { # create

			db_begin();
			$result = db_query_params ("INSERT INTO $t_resource_table".' ( url, provider_id, http_method) VALUES ($1,$2,$3)',
						   array ( $resource->get_url(), $resource->get_provider_id(), $resource->get_http_method())) ;
			if (!$result) {
				db_rollback();
				return false;
			}
			$resource_id = db_insertid($result, $t_resource_table, 'id' );

			db_commit();

		} else { # update
			$t_query = "UPDATE $t_resource_table SET url=$1, provider_id=$2, http_method=$3 WHERE id=$4";
			db_query_params( $t_query, array ($resource->get_url(), $resource->get_provider_id(), $resource->get_http_method(), $resource->get_id()) );
		}
		return $provider_id;
	
	}

}
