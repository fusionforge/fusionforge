<?php

require_once("OAuth.php");

function usage($code=0) {
	echo "php command-line.php [command] [args]\n";
	echo "\n";
	echo "where command in : request_token, ...\n";
	echo "\n";
	echo " request_token [consumer_key] [consumer_secret]\n";
	echo "\n";
	echo " authorize [request_token]\n";
	echo "\n";
	echo " access_token [consumer_key] [consumer_secret] [request_token] [request_token_secret]\n";
	
	exit($code);
}

if ($argc < 2) {
	usage();
}

	
function request_curl(&$code, $url, $params=array()) {
	$method='GET';
        $params = http_build_query($params, '', '&');
        $curl = curl_init($url . ($method == 'GET' && $params ? '?' . $params : ''));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/xrds+xml, */*'));

        curl_setopt($curl, CURLOPT_HTTPGET, true);
        
        $response = curl_exec($curl);
        if(curl_errno($curl))
		{
    		echo 'Curl error: ' . curl_error($curl);
    		$code = -1;
    		return False;
		}
		else {
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			return $response;
		}
}

function retrieve_request_token($request_token_endpoint, $consumer_key, $consumer_secret) {
	$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
	
	$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret);
	
	//print_r($test_consumer);

	//$parsed = parse_url($endpoint);
	$params = array();
	//parse_str($parsed['query'], $params);
	
	$req_req = OAuthRequest::from_consumer_and_token($test_consumer, NULL, "GET", $request_token_endpoint, $params);
	
	//$rsa_method = new TestOAuthSignatureMethod_RSA_SHA1();
	//$sig_method = $rsa_method;
	$sig_method = $hmac_method;
	
	$req_req->sign_request($sig_method, $test_consumer, NULL);
	
	//print "request url: " . $req_req->to_url(). "\n";
	//print_r($req_req);
	
	$code = -1;
	$response = request_curl($code, $req_req->to_url());
	
	//print_r($response);
	$params = array();
	parse_str($response, $params);
	//print_r($params);
	
	echo "received request token :\n";
	echo ' $oauth_token : '. $params['oauth_token'] ."\n";
	echo ' $oauth_token_secret : '. $params['oauth_token_secret'] ."\n";
	echo "\n";
	authorize_request_token(fusionforge_authorize_endpoint(), $params['oauth_token']);
}

function fusionforge_authorize_endpoint() {
	return 'https://192.168.122.90/plugins/oauthprovider/authorize.php';
}

function authorize_request_token($authorize_endpoint, $oauth_token) {
	echo "Go to the following URL in your FusionForge session to authorize the request token:\n";
	echo ' '. $authorize_endpoint. '?oauth_token='. $oauth_token . "\n";
	echo "\n";
	echo "Upon completion, you will be able request access tokens with the authorized token.";
	echo "\n";
}


function retrieve_access_token($access_token_endpoint, $consumer_key, $consumer_secret, $request_token, $token_secret) {

	$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
	
	$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret);
	
  	$test_token = new OAuthConsumer($request_token, $token_secret);

	
	//print_r($test_consumer);

	//$parsed = parse_url($endpoint);
	$params = array();
	//parse_str($parsed['query'], $params);
	
	$acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $test_token, "GET", $access_token_endpoint, $params);
	
	//$rsa_method = new TestOAuthSignatureMethod_RSA_SHA1();
	//$sig_method = $rsa_method;
	$sig_method = $hmac_method;
	
	$acc_req->sign_request($sig_method, $test_consumer, $test_token);
	
	//print "request url: " . $req_req->to_url(). "\n";
	//print_r($req_req);
	
	$code = -1;
	$response = request_curl($code, $acc_req->to_url());
	
	//print_r($response);
	
	//echo "code : ";
	//echo $code;
	//echo "\n";
	
	if ($code == 200) {
		$params = array();
		parse_str($response, $params);
		print_r($params);
		
		echo "received access token :\n";
		echo ' $oauth_token : '. $params['oauth_token'] ."\n";
		echo ' $oauth_token_secret : '. $params['oauth_token_secret'] ."\n";
		echo "\n";
	}
	else {
		echo 'received error code : ' . $code . "\n";
		echo ' '. $response;
		exit(1);
	}
	
}


switch ($argv[1]) {
	case 'request_token':
		if($argc < 4) usage(1);
		$consumer_key = $argv[2];
		$consumer_secret = $argv[3];
		$request_token_endpoint = 'https://192.168.122.90/plugins/oauthprovider/request_token.php';
		retrieve_request_token($request_token_endpoint, $consumer_key, $consumer_secret);
		break;
	case 'authorize':
		if($argc < 3) usage(1);
		$oauth_token = $argv[2];
		$authorize_endpoint = fusionforge_authorize_endpoint();
		authorize_request_token($authorize_endpoint, $oauth_token);
		break;
	case 'access_token':
		if($argc < 6) usage(1);
		$consumer_key = $argv[2];
		$consumer_secret = $argv[3];
		$request_token = $argv[4];
		$token_secret = $argv[5];
		$access_token_endpoint = 'https://192.168.122.90/plugins/oauthprovider/access_token.php';
		retrieve_access_token($access_token_endpoint, $consumer_key, $consumer_secret, $request_token, $token_secret);
		break;
	default:
		usage(1);
}



//