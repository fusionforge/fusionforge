<?php

class OAuthProvider	{
	
	protected $id;
	protected $name;
	protected $description;
	protected $consumer_key;
	protected $consumer_secret;
	protected $request_token_url;
	protected $authorize_url;
	protected $access_token_url;
	
	function __construct($name, $description, $consumer_key, $consumer_secret, $request_token_url, $authorize_url, $access_token_url, $id = 0)	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->request_token_url = $request_token_url;
		$this->authorize_url = $authorize_url;
		$this->access_token_url = $access_token_url;		
	}
	
	public function get_id()	{
		return $this->id;
	} 
	
	protected function set_id($id)	{
		$this->id = $id;
	}
	
	public function get_name() {
		return $this->name;
	}
	
	public function get_description()	{
		return $this->description;
	}
	
	public function get_consumer_key()	{
		return $this->consumer_key;
	}
	
	public function get_consumer_secret()	{
		return $this->consumer_secret;
	}
	
	public function get_request_token_url()	{
		return $this->request_token_url;
	}
	
	public function get_authorize_url()	{
		return $this->authorize_url;
	}
	
	public function get_access_token_url()	{
		return $this->access_token_url;
	}
	
	public function set_name($name) {
		$this->name = $name;
	}
	
	public function set_description($description)	{
		$this->description = $description;
	}
	
	public function set_consumer_key($consumer_key)	{
		$this->consumer_key = $consumer_key;
	}
	
	public function set_consumer_secret($consumer_secret)	{
		$this->consumer_secret = $consumer_secret;
	}
	
	public function set_request_token_url($request_token_url)	{
		$this->request_token_url = $request_token_url;
	}
	
	public function set_authorize_url($authorize_url)	{
		$this->authorize_url = $authorize_url;
	}
	
	public function set_access_token_url($access_token_url)	{
		$this->access_token_url = $access_token_url;
	}
	
	static function convert_row_to_object($row)	{
		if($row!=null)	{
			$provider = new OAuthProvider($row['name'], $row['description'], $row['consumer_key'], $row['consumer_secret'], $row['request_token_url'], $row['authorize_url'], $row['access_token_url'], $row['id']);
			return $provider;
		}else {
			return null;
		}
	}
	
	static function get_provider($id) {
		$conn = FFOAuthDataStore::singleton();
		$row = $conn->find_provider_from_id($id);
		$provider = self::convert_row_to_object($row);
		return $provider;
	}
	
	static function get_provider_by_name($name) {
		$conn = FFOAuthDataStore::singleton();
		$row = $conn->find_provider_from_name($name);
		$provider = self::convert_row_to_object($row);
		return $provider;
	}
	
	static function get_all_oauthproviders()	{
		$conn = FFOAuthDataStore::singleton();
		$rows = $conn->find_all_providers();
		$providers = array();
		foreach ($rows as $row)	{
			$provider = OAuthProvider::convert_row_to_object($row);
			$providers[] = $provider;
		}
		return $providers;
	}
	
	static function check_provider_values($new=TRUE, $name, $description, $consumer_key, $consumer_secret, $request_token_url, $authorize_url, $access_token_url)	{
		if ((!trim($name))) {
			return "The field 'Name' is empty! ";
		}
		elseif ((!trim($description))) {
			return "The field 'Description' is empty! ";
		}
		elseif ((!trim($consumer_key))) {
			return "The field 'Consumer Key' is empty! ";
		}
		elseif ((!trim($consumer_secret))) {
			return "The field 'Consumer Secret' is empty! ";
		}
		elseif(strlen($name)<5)	{
			return "The field 'Name' cannot be less than 5 characters!";
		}
		elseif(strlen($name)>15)	{
			return "The field 'Name' cannot be more than 15 characters!";
		}
		elseif(is_numeric(substr($name, 0, 1)))	{
			return "The field 'Name' cannot begin with a numeral!";
		}
		elseif((substr($name, 0, 1))=="_")	{
			return "The field 'Name' cannot begin with an underscore!";
		}
		elseif(preg_match('/^[A-z][A-z_0-9]{4,}/', $name)==0)	{
			return "The field 'Name' can only contain alphabets (a-z,A-Z), numbers (0-9) and underscores (_). Please choose a Name accordingly!";
		}
		elseif($new && self::provider_exists($name))	{
			return "The name '".$name."' has already been taken. Please choose another!";	
		}
		elseif((trim($request_token_url))&&(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $request_token_url)))	{
			return "The Request Token URL is not valid.";
		}
		elseif((trim($authorize_url))&&(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $authorize_url)))	{
			return "The Authorization URL is not valid.";
		}
		elseif((trim($access_token_url))&&(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $access_token_url)))	{
			return "The Access Token URL is not valid.";
		}
		else {
			return null;
		}
	}
	
	static function provider_exists($name)	{
		$conn = FFOAuthDataStore::singleton();
		$row = $conn->find_provider_from_name($name);
		if($row==null)	{
			return false;
		}
		else {
			return true;
		}
	}
	
	function write_to_db() {
		if ( strlen(trim( $this->name ))==0 || strlen(trim( $this->description ))==0 || strlen(trim( $this->consumer_key ))==0 || strlen(trim( $this->consumer_secret ))==0 ) {
			exit_error( "Error trying to add the oauth provider. Please try again.", 'oauthconsumer' );
		}
		$conn = FFOAuthDataStore::singleton();
		$id = $conn->save_provider($this);
		if(!$id)	{
			exit_error("Error trying to add new oauth provider to DB", 'oauthconsumer');
		}else {
			$this->set_id($id);
		}	
	}
	
	function delete()	{
		$conn = FFOAuthDataStore::singleton();
		$id = $this->get_id();
		if($id!=0)	{
			if(!($conn->delete_provider($id)))	{
				exit_error("Error trying to delete provider from DB", 'oauthconsumer');
			}
		}else 	{
			exit_error("Trying to delete non-existent provider from DB", 'oauthconsumer');
		}
	}
	
}
