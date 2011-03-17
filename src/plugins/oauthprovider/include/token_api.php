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

// OAuth PHP library (http://code.google.com/p/oauth/source/browse/#svn%2Fcode%2Fphp) as of rev. 622

require_once('OAuth.php');

/**
 * Tokens stored in DB
 * 
 * This is only the base class that will be subclassed by Request Tokens or Access Tokens
 * All tokens have an ID in the DB, and are issued for a consumer, at a certain time (stamp)
 * When a token has been authorized by a user, the user id is recorded
 * 
 * @author Olivier Berger
 *
 */
class OauthAuthzToken extends OAuthToken {
	
  protected $id; // in the table
  
  protected $consumer_id;  // consumer for which the token was issued
  protected $user_id; // user for which the token is (or will be) authorized
  protected $time_stamp; // time of creation of the token

  const TOKEN_TYPE = 'null';

  /**
   * @param int $p_consumer_id
   * @param string $p_key
   * @param string $p_secret
   * @param int $p_user_id
   * @param int $p_time_stamp
   */
  function __construct( $p_consumer_id, $p_key, $p_secret, $p_user_id=null, $p_time_stamp=null) {
  	// parent only stores key and secret
    parent::__construct($p_key, $p_secret);
    
    // will be set once inserted in the DB
    $this->id = 0;
    
    $this->consumer_id = $p_consumer_id;
    $this->user_id = $p_user_id;
    $this->time_stamp = $p_time_stamp;
  }

  public function getId() {
  	return $this->id;
  }
  
  public function getConsumerId() {
  	return $this->consumer_id;
  }
  
  public function getUserId() {
  	return $this->user_id;
  }
  
  public function gettime_stamp() {
  	return $this->time_stamp;
  }
  
  /* TO BE SUBCLASSED
  static function row_to_new_token ($t_row) {
    $t_token = new OauthAuthzToken( $t_row['consumer_id'], $t_row['token_key'], $t_row['token_secret'] );
    $t_token->id = $t_row['id'];
    return $t_token;
  }
  */

  /**
   * Loads a particular token from the DB knowing its ID
   * 
   * @param int $p_id
   */
  static function load( $p_id, $token_type ) {
  	
  	$DBSTORE = FFDbOAuthDataStore::singleton();
  	
  	$t_row = $DBSTORE->find_token_from_id($token_type, $p_id);
    
    if(!$t_row) {
    	exit_error( "Error trying to load token!", 'oauthprovider' );
    }
    return $t_row;
  }

  /**
   * @param int $user_id
   * @return Ambigous <multitype:, unknown>
   */
  static function load_all($user_id=null, $token_type) {
  	
  	$DBSTORE = FFDbOAuthDataStore::singleton();
  	
    $t_rows = $DBSTORE->find_all_tokens($token_type, $user_id);
    return $t_rows;    
  }

  /**
   * Loads a token by its token key
   * 
   * @param string $p_token_key
   * @return OauthAuthzToken subclass
   */
  static function load_by_key( $p_token_key, $token_type ) {

  	$DBSTORE = FFDbOAuthDataStore::singleton();
  	    
	$t_row = $DBSTORE->find_token_from_key($token_type, $p_token_key);
    
    if(!$t_row) {
    	exit_error( "Error trying to load ".$token_type." token!", 'oauthprovider' );
    }
    return $t_row;
  }

  /**
   * Check that mandatory values are OK
   */
  function check_mandatory() {
    if ( strlen(trim( $this->consumer_id ))==0 || strlen(trim( $this->key ))==0 || strlen(trim( $this->secret ))==0 ) {
    	throw new OAuthException('Mandatory "consumer_id", "key" or "secret" Token attribute(s) not set.');
    }
  }
  
  /**
   * @param int $p_id
   */
  function delete($token_type) {
  	
  	$DBSTORE = FFDbOAuthDataStore::singleton();
  	    
	$DBSTORE->delete_token( $token_type, $this->id);
  }

};
