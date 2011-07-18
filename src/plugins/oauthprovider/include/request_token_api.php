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

require_once('token_api.php');

/**
 * Request Token concrete class
 *
 * Extends the OauthAuthzToken to add the boolean if the request token is authorized and pending
 * consumption by the consumer.
 *
 * @author Olivier Berger
 *
 */
class OauthAuthzRequestToken extends OauthAuthzToken {

  protected $authorized; // if a user has authorized the token
  protected $role_id; //access level granted

  const TOKEN_TYPE = 'request';

  /**
   * @param int $p_consumer_id
   * @param string $p_key
   * @param string $p_secret
   * @param bool $p_authorized
   * @param int $p_user_id
   * @param int $p_time_stamp
   */
  function __construct( $p_consumer_id, $p_key, $p_secret, $p_authorized=false, $p_user_id=null, $p_role_id=null, $p_time_stamp=null) {
    parent::__construct( $p_consumer_id, $p_key, $p_secret, $p_user_id, $p_time_stamp);

    $this->authorized = $p_authorized;
    $this->role_id = $p_role_id;
  }

  public function getAuthorized() {
  	return $this->authorized;
  }

  public function getRoleId() {
  	return $this->role_id;
  }

  /**
   * Converts a row returned by select * into an object
   * @param array $t_row
   * @return OauthAuthzRequestToken
   */
  static function row_to_new_token ($t_row) {
    $t_token = new OauthAuthzRequestToken( $t_row['consumer_id'], $t_row['token_key'], $t_row['token_secret'], $t_row['authorized'], $t_row['user_id'], $t_row['role_id'], $t_row['time_stamp'] );

    $t_token->id = $t_row['id'];

    return $t_token;
  }

  static function load( $p_id ) {
  	$row = parent::load($p_id, self::TOKEN_TYPE);
  	return self::row_to_new_token($row);
  }

  static function load_all($user_id=null)	{
  	$rows = parent::load_all($user_id=null, self::TOKEN_TYPE);
  	$tokens = array();

    foreach ($rows as $row) {
      $token = self::row_to_new_token($row);

      $tokens[] = $token;
    }

    return $tokens;
  }

  static function load_by_key( $p_token_key )	{
  	$row = parent::load_by_key($p_token_key, self::TOKEN_TYPE);
  	return self::row_to_new_token($row);
  }

  function delete()	{
  	parent::delete(self::TOKEN_TYPE);
  }

  /**
   * Saves the token properly to the DB (insert or update with proper columns)
   */
  function save() {

    $this->check_mandatory();

    if ( $this->authorized && strlen(trim( $this->user_id ))==0 ) {
      exit_error( "Error trying to save request token!", 'oauthprovider' );
    }

  	$DBSTORE = FFDbOAuthDataStore::singleton();
	$this->id = $DBSTORE->save_request_token($this);
  }

  public function authorize($user_id, $role_id) {
  	$this->authorized = 1;
  	$this->user_id = $user_id;
  	$this->role_id = $role_id;
  	$this->save();
  }


};
