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

require_once('OAuth.php');

/**
 * OAuthDataStore singleton class to manage tokens, consumers and nonce in FusionForge DB
 *
 * Everything specific to the DB model is handled in this class : no other SQL request should exist outside it
 * It should be reimplemented for other apps, the rest of the classes being untouched
 *
 * It will assume that OauthAuthzConsumer, OauthAuthzToken and its sub-classes are used
 *
 * @author Olivier Berger
 *
 */

class FFDbOAuthDataStore extends OAuthDataStore {

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
		exit_error('Clone is not allowed.', 'oauthprovider');
	}

	/**
	 * Converts request or access token types to table names for FusionForge
	 *
	 * @param string $token_type
	 * @return string
	 */
	protected function token_table_name($token_type) {
		$t_token_table = null;
		if( ($token_type == 'request') || ($token_type == 'access') ) {
			$t_token_table = "plugin_oauthprovider_".$token_type."_token";
		}
		return $t_token_table;
	}

	/**
	 * Retrieve values of columns for a consumer in the DB provided its id
	 *
	 * @param int $p_id ID in the DB
	 * @return array of column values
	 */
	function find_consumer_from_id( $p_id ) {
		$t_consumer_table = "plugin_oauthprovider_consumer";

		$t_result = db_query_params ("SELECT * FROM $t_consumer_table WHERE id=$1",
					   array ( (int) $p_id )) ;
		if (!$t_result || ( db_numrows( $t_result ) < 1 )) {
			exit_error( "Consumer not found!", 'oauthprovider' );
		}

		$t_row = db_fetch_array( $t_result );

		return $t_row;
	}

	/**
	 * Retrieve a table of columns values for all consumers
	 *
	 * @return array of arrays of column values
	 */
	function find_all_consumers() {
		$t_consumer_table = "plugin_oauthprovider_consumer";
		$t_result = db_query_params("SELECT * FROM $t_consumer_table ORDER BY name ASC", array());

		$t_rows = array();

		while ( $t_row = db_fetch_array( $t_result ) ) {
			$t_rows[] = $t_row;
		}

		return $t_rows;
	}

	/**
	 * Retrieve values of columns for a consumer in the DB provided its key
	 *
	 * @param string $p_consumer_key consumer's key
	 * @return array of column values
	 */
	function find_consumer_from_key( $p_consumer_key ) {
		$t_consumer_table = "plugin_oauthprovider_consumer";

		$t_query = "SELECT * FROM $t_consumer_table WHERE consumer_key = $1";
		$t_result = db_query_params( $t_query, array( $p_consumer_key ) );

		if ( db_numrows( $t_result ) < 1 ) {
		  exit_error( "Consumer not found!", 'oauthprovider' );
		  return null;
		}
		$t_row = db_fetch_array( $t_result );

		return $t_row;
	}

	/**
	 * Retrieve values of columns for a consumer in the DB provided its key
	 *
	 * @param string $p_consumer_key consumer's key
	 * @return array of column values
	 */
	public function lookup_consumer( $p_consumer_key ) {
		$t_consumer_table = "plugin_oauthprovider_consumer";

		$t_query = "SELECT * FROM $t_consumer_table WHERE consumer_key = $1";
		$t_result = db_query_params( $t_query, array( $p_consumer_key ) );

		if ( db_numrows( $t_result ) < 1 ) {
		  trigger_error("Consumer not found!");
		  //return null;
		}
		$t_row = db_fetch_array( $t_result );
		$t_consumer = OauthAuthzConsumer::row_to_new_consumer($t_row);
		return $t_consumer;
	}

	/**
	 * Retrieve values of columns for a consumer in the DB provided its name
	 *
	 * @param string $p_consumer_name
	 * @return array of column values
	 */
	function find_consumer_from_name( $p_consumer_name ) {
		$t_consumer_table = "plugin_oauthprovider_consumer";

		$t_query = "SELECT * FROM $t_consumer_table WHERE name = $1";
		$t_result = db_query_params( $t_query, array( $p_consumer_name ) );

		if ( db_numrows( $t_result ) < 1 ) {
		  return null;
		}

		$t_row = db_fetch_array( $t_result );

		return $t_row;
	}

	/**
	 * Saves an OauthAuthzConsumer to the DB
	 *
	 * @param OauthAuthzConsumer $consumer
	 * @return int the consumer ID in the DB
	 */
	public function save_consumer($consumer) {
		$t_consumer_table = "plugin_oauthprovider_consumer";

		$consumer_id = $consumer->getId();
		if ( 0 == $consumer_id ) { # create

			db_begin();
			$result = db_query_params ("INSERT INTO $t_consumer_table".' ( name, consumer_key, consumer_secret, consumer_url, consumer_desc, consumer_email ) VALUES ($1,$2,$3,$4,$5,$6)',
						   array ($consumer->getName(), $consumer->key, $consumer->secret, $consumer->getUrl(), $consumer->getDesc(), $consumer->getEmail())) ;
			if (!$result) {
				//$this->setError('Error Adding Consumer: '.db_error());
				db_rollback();
				return false;
			}
			$consumer_id = db_insertid($result, $t_consumer_table, 'id' );

			db_commit();

		} else { # update
			$t_query = "UPDATE $t_consumer_table SET name=$1, consumer_key=$2, consumer_secret=$3, consumer_url=$4, consumer_desc=$5, consumer_email=$6 WHERE id=$7";
			db_query_params( $t_query, array( $consumer->getName(), $consumer->key, $consumer->secret, $consumer->getUrl(), $consumer->getDesc(), $consumer->getEmail(), $consumer->getId() ) );
		}
		return $consumer_id;
	}

	/**
	 * Creates a new consumer key-secret
	 */
	function new_consumer_keys()
	{
		$key = md5(util_randbytes(20));
		$secret = md5(util_randbytes(20));
		return array($key, $secret);
	}

  /**
   * Deletes a consumer from the DB
   *
   * @param int $consumer_id
   */
	public function delete_consumer( $consumer_id ) {

		$t_consumer_table = "plugin_oauthprovider_consumer";

		$t_query = "DELETE FROM $t_consumer_table WHERE id=$1";
		$t_result = db_query_params( $t_query, array( (int) $consumer_id ) );

		if (!$t_result) {
			db_rollback();
			return false;
		}

		db_commit();
		return true;
	}

	/**
	 * Retrieve values of columns for a token in the DB provided its key
	 *
	 * @param string $token_type
	 * @param string $token_string
	 * @return array of column values
	 */
	public function find_token_from_key($token_type, $token_string) {
		$t_token_table = $this->token_table_name($token_type);

		$t_query = "SELECT * FROM $t_token_table WHERE token_key = $1";
		$t_result = db_query_params( $t_query, array( $token_string ) );

		if ( db_numrows( $t_result ) < 1 ) {
			return null;
		}

		$t_row = db_fetch_array( $t_result );

		return $t_row;
	}

	/**
	 * Retrieve values of columns for a token in the DB provided its id
	 *
	 * @param string $token_type
	 * @param int $token_id
	 * @return array of column values
	 */
	public function find_token_from_id($token_type, $token_id) {
		$t_token_table = $this->token_table_name($token_type);

		$t_query = "SELECT * FROM $t_token_table WHERE id = $1";
		$t_result = db_query_params( $t_query, array( (int) $token_id ) );

		if ( db_numrows( $t_result ) < 1 ) {
			return null;
		}

		$t_row = db_fetch_array( $t_result );

		return $t_row;
	}

	/**
	 * Retrieve a table of columns values for all tokens (of a user)
	 *
	 * @param string $token_type
	 * @param optional int $user_id
	 * @return array of arrays of column values
	 */
	public function find_all_tokens($token_type, $user_id=null) {
		$t_token_table = $this->token_table_name($token_type);

		if(isset($user_id)) {
			$t_query = "SELECT * FROM $t_token_table WHERE user_id = $1";
			$t_result = db_query_params( $t_query, array( (int) $user_id ) );
		}
		else {
			$t_query = "SELECT * FROM $t_token_table";
			$t_result = db_query_params( $t_query, array() );
		}

		$t_rows = array();

		while ( $t_row = db_fetch_array( $t_result ) ) {
			$t_rows[] = $t_row;
		}

		return $t_rows;
	}

	/**
	 * Retrieve a table of columns values for all tokens issued for a consumer (and a user)
	 *
	 * @param string $token_type
	 * @param int $consumer_id
	 * @param optional int $user_id
	 * @return array of arrays of column values
	 */
	public function find_tokens_by_consumer($token_type, $consumer_id, $user_id=null) {
		$t_token_table = $this->token_table_name($token_type);

		if(isset($user_id)) {
			$t_query = "SELECT * FROM $t_token_table WHERE consumer_id = $1 AND user_id = $2";
			$t_result = db_query_params( $t_query, array( (int) $consumer_id, (int) $user_id ) );
		}
		else {
			$t_query = "SELECT * FROM $t_token_table WHERE consumer_id = $1";
			$t_result = db_query_params( $t_query, array( (int) $consumer_id ) );
		}

		$t_rows = array();

		while ( $t_row = db_fetch_array( $t_result ) ) {
			$t_rows[] = $t_row;
		}

		return $t_rows;
	}

	/**
	 * Retrieve an OAuthToken from its key
	 *
	 * Concrete class implementation required for OAuthDataStore
	 *
	 * @param string $token_type
	 * @param string $token_string
	 * @return OauthAuthzToken
	 */
	/* public */ function lookup_token($consumer, $token_type, $token_string) {

		$token=null;

		$t_row=$this->find_token_from_key($token_type, $token_string);

		if(!isset($t_row)) {
			return null;
		}

		// will refuse request tokens too old (older than 24 hours)
		if( $token_type == 'request' ) {
			$now = time();
			$time_stamp = $t_row['time_stamp'];

			if ( $time_stamp < ($now - (int)(24 * 3600) ) ) {
				throw new OAuthException("Invalid (too old) $token_type token: $token_string");
			}
		}

		if( $t_row['consumer_id'] == $consumer->getId() ) {
			$token = new OAuthToken($t_row['token_key'], $t_row['token_secret'] );
		}

		return $token;

	}

	/**
	 * Check a nonce already existed in the DB
	 *
	 * It will auto-purge nonce older than 10 minutes (cleanup made every 100 nonce creation) to avoid the table to fillup
	 *
	 * Concrete class implementation required for OAuthDataStore
	 *
	 * @param OAuthConsumer $consumer
	 * @param OAuthToken $token
	 * @params string $nonce
	 * @params int $time_stamp
	 * @return bool
	 */
	/* public */ function lookup_nonce($consumer, $token, $nonce, $time_stamp) {
		$t_nonce_table = "plugin_oauthprovider_consumer_nonce";

		$token_key = ($token) ? $token->key : 'two-legged';

		$t_query = "SELECT * FROM $t_nonce_table WHERE consumer_id = $1 AND token_key = $2 AND nonce = $3 AND time_stamp = $4";
		$t_result = db_query_params( $t_query, array( $consumer->getId(), $token_key, $nonce, (int) $time_stamp) );

		//      if( ! $consumer->check_nonce ) return false;

		if ( db_numrows( $t_result ) < 1 ) {

			$t_query = "INSERT INTO $t_nonce_table ( consumer_id, token_key, nonce, time_stamp ) VALUES ( $1, $2, $3, $4 )";
			$t_insert_result = db_query_params( $t_query, array( $consumer->getId(), $token_key, $nonce, (int) $time_stamp) );

			$nonce_id = db_insertid($t_insert_result, $t_nonce_table, 'id' );

			// every 100 nonce, try and remove obsolete nonces
			if (($nonce_id % 100) == 0) {
				// will remove nonces older than 10 minutes (2* OAuthServer's time_stamp_threshold)
				$now = time();
				$t_query = "DELETE FROM $t_nonce_table WHERE  time_stamp < $1";
				db_query_params( $t_query, array( (int) ($now - 600) ) );
			}

			return false;
		}
		else {
			return true;
		}

	}

	// make sure this fails... as it seems not implemented / used in parent class
	function fetch_request_token($consumer) {
		exit_error('fetch_request_token() not yet implemented.', 'oauthprovider');
	}

	// make sure this fails... as it seems not implemented / used in parent class
	function fetch_access_token($token, $consumer) {
		exit_error('fetch_access_token() not yet implemented.', 'oauthprovider');
	}

	/**
	 * Generates an new token in the DB
	 *
 	 * It will auto-purge request tokens older than 24 hours that haven't been converted to access tokens in time (cleanup made every 100 request token creation)
	 *
	 * @param OAuthConsumer $consumer
	 * @param string $token_type
	 * @return OAuthToken
	 */
	protected function new_token($consumer, $token_type, $role_id=0) {
		$t_token_table = $this->token_table_name($token_type);

		$random = util_randbytes(32);
		$hash = sha1($random);
		$key = substr($hash, 0, 20);
		$secret = substr($hash, 20, 40);

		$time_stamp = time();

		$token = new OAuthToken($key, $secret);

		$t_query = "INSERT INTO $t_token_table ( consumer_id, token_key, token_secret, role_id, time_stamp ) VALUES ( $1, $2, $3, $4, $5 )";
		$t_result = db_query_params( $t_query, array( $consumer->getId(), $token->key, $token->secret, $role_id,  $time_stamp) );

		$token_id = db_insertid($t_result, $t_token_table, 'id');

		if( $token_type == 'request' ) {
			// every 100 request token, try and remove obsolete ones
			if (($token_id % 100) == 0) {
				// will remove request tokens older than 24 hours
				$now = time();
				$t_query = "DELETE FROM $t_token_table WHERE time_stamp < $1";
				db_query_params( $t_query, array( (int) ($now - (24 * 3600) ) ) );
			}
		}
		return $token;
	}

	/**
	 * Generates a new request token in the DB
	 *
	 * Concrete class implboundementation
	 * called by the OAuthServer
	 *
	 * @param OAuthConsumer $consumer
	 * @return OAuthToken
	 */
	public function new_request_token($consumer) {
		$token = $this->new_token($consumer, 'request');

		// TODO : return an OauthAuthzRequestToken
		return $token;
	}

	/**
	 * Generates a new access token in the DB
	 *
	 * Concrete class implementation
	 * called by the OAuthServer
	 *
	 * @param OAuthToken $request_token
	 * @param OAuthConsumer $consumer
	 * @return OAuthToken
	 */
	public function new_access_token($request_token, $consumer) {

		//    $t_row=$this->find_token_from_key('access', $request_token->key);
		$t_row=$this->find_token_from_key('request', $request_token->key);

		$token_id = $t_row['id'];
		$consumer_id = $t_row['consumer_id'];
		$authorized = $t_row['authorized'];
		$user_id = $t_row['user_id'];
		$role_id = $t_row['role_id'];

		// delete in any case to avoid replaying and such
		$this->delete_token('request', $token_id);

		if( $consumer->getId() === $consumer_id ) {
			if( $authorized && isset($user_id) ) {

				$access_token = $this->new_token($consumer, 'access', $role_id);

				$t_token_table = "plugin_oauthprovider_access_token";

				$t_query = "UPDATE $t_token_table SET user_id=$1 WHERE token_key = $2";
				db_query_params( $t_query, array( $user_id, $access_token->key ) );

				// TODO : return an OauthAuthzAccessToken
				return $access_token;
			} else {
				// Token wasn't authorized
				throw new OAuthException('You can\'t swap a unauthorized request token for a access token. Your Access Token was still deleted though. Nice try..');
			}
		} else {
			// Token was fubar
			throw new OAuthException('This Request Token doesn\'t belong to your Consumer Key. Your Access Token was still deleted though. Nice Try.');
		}
	}

	/**
	 * Saves an OauthAuthzAccessToken to the DB
	 *
	 * @param OauthAuthzAccessToken $token
	 * @return int the token ID in the DB
	 */
	public function save_access_token($token) {

		$t_token_table = $this->token_table_name('access');

		$token_id = $token->getId();
		if ( 0 == $token_id ) { # create
			$t_query = "INSERT INTO $t_token_table ( consumer_id, token_key, token_secret, user_id, role_id, time_stamp ) VALUES ($1, $2, $3 $4, $5, $6)";
			$t_result = db_query_params( $t_query, array( $token->getConsumerId(), $token->key, $token->secret, $token->getUserId(), $token->getRoleId(), $token->gettime_stamp() ) );

			$token_id = db_insertid($t_result, $t_token_table, 'id');
			return $token_id;
		}
		else { # TODO feature to be added later, with lifetime/limited access feature support
			//$t_query = "UPDATE $t_token_table SET consumer_id=$1, token_key=$2, token_secret=$3, user_id=$4, time_stamp=$4 WHERE id=$5";
			//db_query_params( $t_query, array( $token->getConsumerId(), $token->key, $token->secret, $token->getUserId(), $token->gettime_stamp(), $token->getId() ) );
			exit_error("The access token already exists and cannot be modified.", 'oauthprovider');
		}

	}

	/**
	 * Saves an OauthAuthzRequestToken to the DB
	 *
	 * @param OauthAuthzRequestToken $token
	 * @return int the token ID in the DB
	 */
	public function save_request_token($token) {

		$t_token_table = $this->token_table_name('request');

		$token_id = $token->getId();
		if ( 0 == $token_id ) { # create
			$t_query = "INSERT INTO $t_token_table ( consumer_id, token_key, token_secret, authorized, user_id, role_id, time_stamp ) VALUES ($1, $2, $3, $4, $5, $6, $7)";
			$t_result = db_query_params( $t_query, array( $token->getConsumerId(), $token->key, $token->secret, $token->getAuthorized(), $token->getUserId(), $token->getRole(), $token->gettime_stamp() ) );

			$token_id = db_insertid($t_result, $t_token_table, 'id');
		} else { # update
			$t_query = "UPDATE $t_token_table SET consumer_id=$1, token_key=$2, token_secret=$3, authorized=$4, user_id=$5, role_id=$6, time_stamp=$7 WHERE id=$8";
			db_query_params( $t_query, array( $token->getConsumerId(), $token->key, $token->secret, $token->getAuthorized(), $token->getUserId(), $token->getRole(), $token->gettime_stamp(), $token->getId() ) );
		}
		return $token_id;
	}


  /**
   * Deletes a token from the DB
   *
   * @param string $token_type
   * @param int $token_id
   */
  function delete_token( $token_type, $token_id) {
	$t_token_table = $this->token_table_name($token_type);

    $t_query = "DELETE FROM $t_token_table WHERE id=$1";
    $t_result = db_query_params( $t_query, array( (int) $token_id ) );
  }


}
