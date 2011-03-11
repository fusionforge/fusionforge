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

require_once('OAuth.php');

/**
 * OAuth Consumer class stored in DB
 * 
 * @author Olivier Berger
 */
class OauthAuthzConsumer extends OAuthConsumer {
	protected $id; // ID in the DB
	protected $name; // admin provided user-friendly name for the consumer
	
	protected $url;
	protected $desc;
	protected $email;

	function __construct( $p_name, $p_key, $p_secret, $p_url, $p_desc, $p_email) {
	  parent::__construct($p_key, $p_secret);
	  $this->id = 0;
	  $this->name = $p_name;
	  $this->url = $p_url;
	  $this->desc = $p_desc;
	  $this->email = $p_email;
	}
	
	public function setId($p_id) {
  		$this->id = $p_id;
  	}
  	
	public function setName($p_name) {
  		$this->name = $p_name;
  	}
  	
	public function setURL($p_url) {
  		$this->url = $p_url;
  	}
  	
	public function setDesc($p_desc) {
  		$this->desc = $p_desc;
  	}
  	
	public function setEmail($p_email) {
  		$this->email = $p_email;
  	}
  
	public function getId() {
  		return $this->id;
  	}
  	
	public function getName() {
  		return $this->name;
  	}
  	
	public function getUrl() {
  		return $this->url;
  	}
  	
	public function getDesc() {
  		return $this->desc;
  	}
  	
	public function getEmail() {
  		return $this->email;
  	}
  	
  	static function check_consumer_values($p_consumer_name, $p_consumer_url, $p_consumer_desc, $p_consumer_email)	{
	  	if ((!trim($p_consumer_name))) {
			//$missing_params[] = _('"Consumer Name"');
			return "The field 'Consumer Name' is empty! "; 
			//exit_missing_param('', $missing_params,'oauthprovider');
		}
		elseif ((!trim($p_consumer_url))) {
			return "The field 'Consumer URL' is empty! "; 
		}
		elseif ((!trim($p_consumer_desc))) {
			return "The field 'Consumer Description' is empty! "; 
		}
		elseif ((!trim($p_consumer_email))) {
			return "The field 'Consumer Email' is empty! "; 
		}
		elseif(strlen($p_consumer_name)<5)	{
			return "The Consumer Name cannot be less than 5 characters!";
		}
		elseif(strlen($p_consumer_name)>15)	{
			return "The Consumer Name cannot be more than 15 characters!";
		}
		elseif(is_numeric(substr($p_consumer_name, 0, 1)))	{
			return "The Consumer Name cannot begin with a numeral!";
		}
		elseif((substr($p_consumer_name, 0, 1))=="_")	{
			return "The Consumer Name cannot begin with an underscore!";
		}
		elseif(preg_match('/^[A-z][A-z_0-9]{4,}/', $p_consumer_name)==0)	{
			return "The Consumer Name can only contain alphabets (a-z,A-Z), numbers (0-9) and underscores (_). Please choose a Consumer Name accordingly!";
		}
		elseif(OauthAuthzConsumer::consumer_exists($p_consumer_name))	{
			return "The name '".$p_consumer_name."' has already been taken. Please choose another!";
				
		}
		elseif(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $p_consumer_url))	{
			return "The Consumer URL is not valid.";
		}
		else {
			return null;
		}
  	}
	
  /**
   * Converts a row returned by select * into an object
   * 
   * @param array $t_row
   * @return OauthAuthzRequestToken
   */
  	static function row_to_new_consumer ($t_row) {
  		if($t_row!=null)	{
  			$t_consumer = new OauthAuthzConsumer( $t_row['name'], $t_row['consumer_key'], $t_row['consumer_secret'], $t_row['consumer_url'], $t_row['consumer_desc'], $t_row['consumer_email'] );
	    	$t_consumer->setId($t_row['id']);
	    	return $t_consumer;
  		}else {
  			return null;
  		}
  		
  	}
  	
	/**
	   * returns a newly generated consumer key and secret
	   * 
	   * @return array 
	   */
	  	static function new_consumer_keys_generate () {
	  		$DBSTORE = FFDbOAuthDataStore::singleton();
	  		return $DBSTORE->new_consumer_keys();
	  	}
  	 
	/**
	 * Loads an OauthAuthzConsumer from its ID in the DB
	 * 
	 * @param int $p_id ID in the DB
	 * @return OauthAuthzConsumer
	 */
	static function load( $p_id ) {
		$DBSTORE = FFDbOAuthDataStore::singleton();
		$t_row = $DBSTORE->find_consumer_from_id($p_id); 
		$t_consumer = OauthAuthzConsumer::row_to_new_consumer($t_row);
		$t_consumer->setId($t_row['id']);
		return $t_consumer;
	}

	/**
	 * Loads all OauthAuthzConsumer from the DB
	 * 
	 * @return array of OauthAuthzConsumer
	 */
	static function load_all() {
		$DBSTORE = FFDbOAuthDataStore::singleton();
		$t_rows = $DBSTORE->find_all_consumers();

		$t_consumers = array();

		foreach ($t_rows as $t_row) {
			$t_consumer = OauthAuthzConsumer::row_to_new_consumer($t_row);

			$t_consumers[] = $t_consumer;
		}

		return $t_consumers;
	}

	/**
	 * Loads an OauthAuthzConsumer from the DB provided its key
	 * 
	 * @param string $p_consumer_key
	 * @return OauthAuthzConsumer
	 */
	static function load_by_key( $p_consumer_key ) {
		$DBSTORE = FFDbOAuthDataStore::singleton();
		$t_row = $DBSTORE->find_consumer_from_key($p_consumer_key);
		if($t_row==null)	{
			return null;
		}
		else {
			$t_consumer = OauthAuthzConsumer::row_to_new_consumer($t_row);
			return $t_consumer;
		}
	}
	
	/**
	 * Loads an OauthAuthzConsumer from the DB provided its name
	 * 
	 * @param string $p_consumer_name
	 * @return bool
	 */
	static function consumer_exists( $p_consumer_name ) {
		$DBSTORE = FFDbOAuthDataStore::singleton();
		$t_row = $DBSTORE->find_consumer_from_name($p_consumer_name);
		if($t_row==null)	{
			return false;
		}
		else {
			return true;
		}
		
	}

	/**
	 * Saves an OauthAuthzConsumer to the DB
	 */
	function save() {
		if ( strlen(trim( $this->name ))==0 || strlen(trim( $this->key ))==0 || strlen(trim( $this->secret ))==0 ) {
			exit_error( "Error trying to save consumer. Please try again.", 'oauthprovider' );
		}
		$DBSTORE = FFDbOAuthDataStore::singleton();
		$id=$DBSTORE->save_consumer($this);
		if(!$id)	{
			exit_error("Error trying to create new consumer in DB", 'oauthprovider');
		}else {
			$this->setId($id);
		}
		
		
	}
	
	/**
	 * Deletes an OauthAuthzConsumer from the DB
	 */
	function delete() {
		$DBSTORE = FFDbOAuthDataStore::singleton();
		if(!($DBSTORE->delete_consumer($this->id)))	{
			exit_error("Error trying to delete consumer from DB", 'oauthprovider');
		}
	}
	

  };
