<?php
require_once('../../env.inc.php');
require_once 'checks.php';

oauthconsumer_CheckUser();

$verifier = $_GET['oauth_verifier']?$_GET['oauth_verifier']:getStringFromPost('oauth_verifier');
$token = $_GET['oauth_token']?$_GET['oauth_token']:getStringFromPost('oauth_token');

if(!$verifier || !$token)	{
	echo $HTML->error_msg(htmlspecialchars("OAuth parameters not found."));
}
?>
<form action="callback.php" method="post">
	<?php 
	echo '<input type="hidden" name="oauth_verifier" value="'.$verifier.'"/>';
	echo '<input type="hidden" name="oauth_token" value="'.$token.'"/>';
	echo '<input type="hidden" name="provider_id" value="'.$_COOKIE['PROVIDER'].'"/>';
	echo _('<b>Step 3: </b>Exchange the authorized request token for an access token');?>
	<br>
	<input type="submit" value="<?php echo _('Go') ?>" />
</form>
<?php 
$f_provider_id = getStringFromPost('provider_id');

if($f_provider_id)	{
	$provider = OAuthProvider::get_provider($f_provider_id);
	$provider_name = $provider->get_name();
	$consumer_key = $provider->get_consumer_key();
	$consumer_secret = $provider->get_consumer_secret();
	$request_token_url = $provider->get_request_token_url();
	$authorize_url = $provider->get_authorize_url();
	$access_token_url = $provider->get_access_token_url();
		
	$parameters = array("oauth_verifier"=>$verifier, "oauth_token"=>$token);
	
	$ff_consumer = new OAuthConsumer($consumer_key, $consumer_secret);
	$oauth_request_token = new OAuthToken($_COOKIE['OAUTH_TOKEN'], $_COOKIE['OAUTH_TOKEN_SECRET']);
	setcookie('OAUTH_TOKEN', '', time()-3600);
	setcookie('OAUTH_TOKEN_SECRET', '', time()-3600);
	
	$ff_request2 = OAuthRequest::from_consumer_and_token($ff_consumer, false, "GET", $access_token_url, $parameters);
	$hmac = new OAuthSignatureMethod_HMAC_SHA1();
	$ff_request2->sign_request($hmac, $ff_consumer, $oauth_request_token);
	
	//sending request with curl
	$curl = curl_init();
	
	curl_setopt($curl, CURLOPT_URL, $ff_request2->to_url());
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	
	//temporary workaround for untrusted security certificates
	$not_verify_ssl = $_COOKIE['NOT_VERIFY_SSL']?$_COOKIE['NOT_VERIFY_SSL']:0;
	if($not_verify_ssl)	{
		curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
	}
	setcookie('NOT_VERIFY_SSL', '', time()-3600);
	
	$access_token_string = curl_exec ($curl);
	curl_close ($curl);
	
	parse_str($access_token_string, $access_token_array);
	$userid = session_get_user()->getID();
	if(!$access_token_array['oauth_token'] || !$access_token_array['oauth_token_secret'])	{
		echo $HTML->error_msg(htmlspecialchars("Access Token not received."));
	}
	$new_access_token = new OAuthAccessToken($f_provider_id, $access_token_array['oauth_token'], $access_token_array['oauth_token_secret'], $userid);
	$new_access_token->write_to_db();
	
	echo _("New access token received and saved!<br>");
	echo _("Access Token Key : ".$access_token_array['oauth_token']."<br>");
	echo _("Access Token Secret : ".$access_token_array['oauth_token_secret']."<br>");
		
}

echo'<br><br>';

echo util_make_link('/plugins/'.$pluginname.'/providers.php', _('OAuth Providers')). ' <br />';
echo util_make_link('/plugins/'.$pluginname.'/get_access_token.php', _('Get Access tokens')).'<br /> ';
echo util_make_link('/plugins/'.$pluginname.'/access_tokens.php', _('Access tokens')).'<br /> ';

site_user_footer(array());