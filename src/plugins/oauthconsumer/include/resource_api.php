<?php

// FIXME : missing copyright

require_once('OAuth.php');

class OAuthResource	{
	
	protected $id;
	protected $url;
	protected $provider_id;
	protected $http_method;
	
	function __construct($url, $provider_id, $http_method, $id=0)	{
		$this->url = $url;
		$this->provider_id = $provider_id;
		$this->http_method = $http_method;
		$this->id = $id;
	}
	
	public function get_id()	{
		return $this->id;
	}
	
	function set_id($id)	{
		$this->id = $id;
	}
	
	public function get_url()	{
		return $this->url;
	}
	
	public function get_provider_id()	{
		return $this->provider_id;
	}
	
	public function get_http_method()	{
		return $this->http_method;
	}
	
	function write_to_db()	{
		if ( strlen(trim( $this->url ))==0 || strlen(trim( $this->provider_id ))==0 || strlen(trim( $this->http_method ))==0 ) {
			exit_error( "Error trying to add the oauth resource. Please try again.", 'oauthconsumer' );
		}
		$conn = FFOAuthDataStore::singleton();
		$id = $conn->save_oauth_resource($this);
		if(!$id)	{
			exit_error("Error trying to add new oauth resource to DB", 'oauthconsumer');
		}else {
			$this->set_id($id);
		}
	}
}

class OAuthTransaction {
	
	protected $consumer; //an OAuthConsumer object
	protected $token; //an OAuthToken object
	protected $resource; //an OAuthResource object
	protected $request; //an OAuthRequest object
		
	/**
	* Constructor
	*
	* @param OAuthProvider $provider
	* @param OAuthAccessToken $access_token
	* @param OAuthResource $resource
	* @param array $post_data (should be in the form of an array)
	* @param boolean $json
	* 
	*/
	function __construct($provider, $access_token, $resource, $post_data=NULL)	{
		$this->consumer = new OAuthConsumer($provider->get_consumer_key(), $provider->get_consumer_secret());
		$this->token = new OAuthToken($access_token->get_token_key(), $access_token->get_token_secret());
		$this->resource = $resource;
		$this->request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $resource->get_http_method(), $resource->get_url(), $post_data);				
	}
	
	function send_request($not_verify_ssl=FALSE)	{
		$hmac = new OAuthSignatureMethod_HMAC_SHA1();
		$this->request->sign_request($hmac, $this->consumer, $this->token);
		if(strcasecmp($this->resource->get_http_method(), "get")==0)	{
			return $this->send_http_get($not_verify_ssl);
		}elseif(strcasecmp($this->resource->get_http_method(), "post")==0)	{
			return $this->send_http_post($not_verify_ssl);
		}
	}
	
	function send_http_get($not_verify_ssl)	{
		$separator = "?";
		if (strpos($this->request->get_normalized_http_url(),"?")!=false) $separator = "&";
		
		$curl = curl_init();
		
		$url = $this->request->get_normalized_http_url().$separator.$this->request->to_postdata();
		curl_setopt($curl, CURLOPT_URL, $url);
		
		if($not_verify_ssl)	{
			curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec ($curl);
		curl_close ($curl);
		
		return $response;
	}
	
	function send_http_post($not_verify_ssl)	{
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_POST, TRUE);
		
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->request->to_postdata());		
		curl_setopt($curl, CURLOPT_URL, $this->request->get_normalized_http_url());
		
		if($not_verify_ssl)	{
			curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
		}
				
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec ($curl);
		curl_close ($curl);
		
		return $response;
	}
	
}