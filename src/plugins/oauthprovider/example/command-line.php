<?php

/**
 * This file is (c) Copyright 2011 by Olivier BERGER, Institut TELECOM
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

// This program helps test the OAuth provider plugin of fusionforge.
// See the README for more details.

require_once("OAuth.php");

// customize this value which is the address of the FusionForge server

$forge = 'https://192.168.122.90/';

/**
 * Provides invocation details to the user
 * @param integer $code return code on exit
 */
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
	echo "\n";
	echo " call_echo [consumer_key] [consumer_secret] [access_token] [access_token_secret] [message]\n";

	exit($code);
}

/**
 * Makes a requests with the CURL library
 *
 * @param integer $code HTTP return code (writable)
 * @param string $url to be called
 * @param string $params passed to curl_init
 * @return boolean|mixed response (HTTP return code is in $code)
 */
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

/**
 * Displays a message about the need for the user to go authorize the request token in the forge

 * @param string $authorize_endpoint
 * @param string $oauth_token
 */
function authorize_request_token_message($authorize_endpoint, $oauth_token) {
	echo "Go to the following URL in your FusionForge session to authorize the request token:\n";
	echo ' '. $authorize_endpoint. '?oauth_token='. $oauth_token . "\n";
	echo "\n";
	echo "Upon completion, you will be able request access tokens with the authorized token.";
	echo "\n";
}

/**
 * Provides authorize endpoint's URL
 * @return string
 */
function fusionforge_authorize_endpoint() {
	return $forge . 'plugins/oauthprovider/authorize.php';
}

/**
 * Retrieves a request token for the consumer
 * @param string $request_token_endpoint endpoint called on the oauthprovider plugin
 * @param string $consumer_key
 * @param string $consumer_secret
 */
function retrieve_request_token($request_token_endpoint, $consumer_key, $consumer_secret) {

	$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();

	$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret);

	//print_r($test_consumer);

	$params = array();

	$req_req = OAuthRequest::from_consumer_and_token($test_consumer, NULL, "GET", $request_token_endpoint, $params);

	$sig_method = $hmac_method;

	$req_req->sign_request($sig_method, $test_consumer, NULL);

	//print "request url: " . $req_req->to_url(). "\n";
	//print_r($req_req);

	$code = -1;
	$response = request_curl($code, $req_req->to_url());

	// TODO: should be testing HTTP return code in $code
	//print_r($response);
	$params = array();
	parse_str($response, $params);
	//print_r($params);

	echo "received request token :\n";
	echo ' $oauth_token : '. $params['oauth_token'] ."\n";
	echo ' $oauth_token_secret : '. $params['oauth_token_secret'] ."\n";
	echo "\n";

	authorize_request_token_message(fusionforge_authorize_endpoint(), $params['oauth_token']);
}

/**
 * Call remote endpoints through OAuth
 *
 * @param string $endpoint URL
 * @param string $consumer_key
 * @param string $consumer_secret
 * @param string $access_token
 * @param string $token_secret
 * @param array $params passed to the endpoint
 * @return Ambigous <boolean, mixed>
 */
function call_remote_endpoint($endpoint, $consumer_key, $consumer_secret, $access_token, $token_secret, $params) {
	$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();

	$test_consumer = new OAuthConsumer($consumer_key, $consumer_secret);

  	$test_token = new OAuthConsumer($access_token, $token_secret);


	//print_r($test_consumer);

	$acc_req = OAuthRequest::from_consumer_and_token($test_consumer, $test_token, "GET", $endpoint, $params);

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

	if ($code != 200) {
		echo 'received error code : ' . $code . "\n";
		echo ' '. $response;
		exit(1);
	}
	return $response;
}

/**
 * Retrieves an access token in exchange for the request token (that should have been authorized by now)
 *
 * @param string $access_token_endpoint
 * @param string $consumer_key
 * @param string $consumer_secret
 * @param string $request_token
 * @param string $token_secret
 */
function retrieve_access_token($access_token_endpoint, $consumer_key, $consumer_secret, $request_token, $token_secret) {

	$params = array();
	$response = call_remote_endpoint($access_token_endpoint, $consumer_key, $consumer_secret, $request_token, $token_secret, $params);

	$params = array();
	parse_str($response, $params);
	print_r($params);

	$oauth_token = $params['oauth_token'];
	$oauth_token_secret = $params['oauth_token_secret'];
	echo "received access token :\n";
	echo ' $oauth_token : '. $oauth_token ."\n";
	echo ' $oauth_token_secret : '. $oauth_token_secret ."\n";
	echo "\n";
	echo "You may now access endpoints with this token, for instance as in :\n";
	echo " php command-line.php call_echo $consumer_key $consumer_secret $oauth_token $oauth_token_secret\n";

}

// MAIN program

if ($argc < 2) {
	usage();
}

switch ($argv[1]) {
	case 'request_token':
		if($argc < 4) usage(1);
		$consumer_key = $argv[2];
		$consumer_secret = $argv[3];
		$request_token_endpoint = $forge . 'plugins/oauthprovider/request_token.php';
		retrieve_request_token($request_token_endpoint, $consumer_key, $consumer_secret);
		break;
	case 'authorize':
		if($argc < 3) usage(1);
		$oauth_token = $argv[2];
		$authorize_endpoint = fusionforge_authorize_endpoint();
		authorize_request_token_message($authorize_endpoint, $oauth_token);
		break;
	case 'access_token':
		if($argc < 6) usage(1);
		$consumer_key = $argv[2];
		$consumer_secret = $argv[3];
		$request_token = $argv[4];
		$token_secret = $argv[5];
		$access_token_endpoint = $forge . 'plugins/oauthprovider/access_token.php';
		retrieve_access_token($access_token_endpoint, $consumer_key, $consumer_secret, $request_token, $token_secret);
		break;
	case 'call_echo':
		if($argc < 7) usage(1);
		$consumer_key = $argv[2];
		$consumer_secret = $argv[3];
		$access_token = $argv[4];
		$token_secret = $argv[5];
		$echo_endpoint = $forge . 'plugins/oauthprovider/echo.php';
		$params = array('message' => $argv[6]);
		$response = call_remote_endpoint($echo_endpoint, $consumer_key, $consumer_secret, $access_token, $token_secret, $params);
		print_r($response);
		echo "\n";
		break;
	default:
		usage(1);
}
