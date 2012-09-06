<?php
require_once 'OAuth.php';

class OAuthAccessToken extends OAuthToken {
	
	protected $id;
	protected $provider_id;
	protected $user_id;
	protected $time_stamp;
	
	function __construct( $provider_id, $key, $secret, $user_id, $time_stamp=null, $id=0) {
		parent::__construct($key, $secret);
		$this->id = $id;
		$this->provider_id = $provider_id;
		$this->user_id = $user_id;
		$this->time_stamp = $time_stamp;
	}
	
	public function get_id()	{
		return $this->id;
	}
	
	public function set_id($id) 	{
		$this->id = $id;
	}
	
	public function get_provider_id()	{
		return $this->provider_id;
	}
	
	public function get_token_key() {
		return $this->key;
	}
	
	public function get_token_secret() {
		return $this->secret;
	}
	
	public function get_user_id() {
		return $this->user_id;
	}
	
	public function get_time_stamp() {
		return $this->time_stamp;
	}
	
	static function convert_row_to_object($row)	{
		if($row!=null)	{
			$access_token = new OAuthAccessToken($row['provider_id'], $row['token_key'], $row['token_secret'], $row['user_id'], $row['time_stamp'], $row['id']);
			return $access_token;
		}else {
			return null;
		}
	}
	
	static function get_access_token($id) {
		$conn = FFOAuthDataStore::singleton();
		$row = $conn->find_token_from_id($id);
		$access_token = self::convert_row_to_object($row);
		return $access_token;
	}
	
	static function get_all_access_tokens($user_id) {
		$conn = FFOAuthDataStore::singleton();
		$rows = $conn->find_all_access_tokens($user_id);
		$access_tokens = array();
		foreach ($rows as $row)	{
			$access_token = OAuthAccessToken::convert_row_to_object($row);
			$access_tokens[] = $access_token;
		}
		return $access_tokens;
	}
	
	static function get_all_access_tokens_by_provider($provider_id, $user_id) {
		$conn = FFOAuthDataStore::singleton();
		$rows = $conn->find_access_tokens_by_provider($provider_id, $user_id);
		$access_tokens = array();
		foreach ($rows as $row)	{
			$access_token = OAuthAccessToken::convert_row_to_object($row);
			$access_tokens[] = $access_token;
		}
		return $access_tokens;
	}
	
	function write_to_db() {
		if ( strlen(trim( $this->get_provider_id() ))==0 || strlen(trim( $this->get_user_id() ))==0 || strlen(trim( $this->get_token_key() ))==0 || strlen(trim( $this->get_token_secret() ))==0 ) {
			exit_error( "Error trying to add the access token. Some required parameters are not set.", 'oauthconsumer' );
		}
		$conn = FFOAuthDataStore::singleton();
		$id = $conn->save_access_token($this);
		if(!$id)	{
			exit_error("Error trying to add access token to DB", 'oauthconsumer');
		}else {
			$this->set_id($id);
		}
	}
	
	function delete()	{
		$conn = FFOAuthDataStore::singleton();
		$id = $this->get_id();
		if($id!=0)	{
			if(!($conn->delete_access_token($id)))	{
				exit_error("Error trying to delete access token from DB", 'oauthconsumer');
			}
		}else 	{
			exit_error("Trying to delete non-existent access token from DB", 'oauthconsumer');
		}
	}
	
}
