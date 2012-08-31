<?php

require_once('../../env.inc.php');
require_once 'checks.php';

oauthconsumer_CheckUser();

$providers = OAuthProvider::get_all_oauthproviders();
if(count($providers)>0)	{
?>
	<p>To get an access token, there are three steps involved: </p> 
    <ol><li>Get an unauthorized request token 
    <li>Authorize the request token 
    <li>Exchange the authorized request token for an access token </ol>
    Select a provider from the list below and get started!</p> 
	<form action="get_access_token.php" method="post">
	
	<select name=providers>
		<?php foreach ($providers as $provider) 	{
			echo '<option value="'.$provider->get_id().'">'.$provider->get_name().'</option>';
		}?>		
	</select>
	<input type="submit" value="<?php echo _('Select') ?>"/>
	</form>
	
	<?php 
	$f_provider_name = "Provider";
	$f_provider_id = getStringFromPost('providers');
	if($f_provider_id)	{
		$f_provider = OAuthProvider::get_provider($f_provider_id);	
		$f_provider_name = $f_provider->get_name();
		$f_consumer_key = $f_provider->get_consumer_key();
		$f_consumer_secret = $f_provider->get_consumer_secret();
		$f_request_token_url = $f_provider->get_request_token_url();
		$f_authorize_url = $f_provider->get_authorize_url();
		$f_access_token_url = $f_provider->get_access_token_url();
	}
	?>
	<br><br>
	<form action="get_access_token.php" method="post">
	<?php echo '<input type="hidden" name="plugin_oauthconsumer_get_request_token" value="'.form_generate_key().'"/>' ?>
	<?php echo '<input type="hidden" name="providers" value="'.$f_provider_id.'"/>' ?>
	<table class="width75" align="center" cellspacing="1">
		
		<tr>
		<td class="form-title" colspan="2"><?php echo _("<b>".$f_provider_name."</b>") ?></td>
		</tr>
		
		<tr>
		<td class="category"><?php echo _('Consumer Key') ?></td>
		<td><input name="consumer_key" maxlength="250" size="80" value="<?php echo $f_consumer_key ?>" readonly/></td>
		</tr>
		
		<tr>
		<td class="category"><?php echo _('Request Token URL') ?></td>
		<td><input name="request_token_url" maxlength="250" size="80" value="<?php echo $f_request_token_url ?>"/></td>
		</tr>
		
		<tr>
		<td class="category"><?php echo _('Authorization URL') ?></td>
		<td><input name="authorize_url" maxlength="250" size="80" value="<?php echo $f_authorize_url ?>"/></td>
		</tr>
		
		<tr>
		<td class="category"><?php echo _('Access Token URL') ?></td>
		<td><input name="access_token_url" maxlength="250" size="80" value="<?php echo $f_access_token_url ?>"/></td>
		</tr>
		
	</table><br>
	<?php
	if((strcasecmp(substr($f_request_token_url, 0, 5),"https")==0) ||
		(strcasecmp(substr($f_authorization_url, 0, 5),"https")==0) ||
		(strcasecmp(substr($f_access_token_url, 0, 5),"https")==0))	{?>
		<input type="checkbox" name="not_verify_ssl">Do not verify SSL Certificate</input>	<br><br>
	<?php
	}
	$url_string = $f_request_token_url?"(from ".$f_request_token_url.")":""; 
	echo _('<b>Step 1: </b>Get Request Token '.$url_string) ?>
	<br>
	<input type="submit" value="<?php echo _('Go') ?>" />
	</form>
	
	<?php
	$form_key = getStringFromPost('plugin_oauthconsumer_get_request_token');
	$f_provider_id = getStringFromPost('providers');
	$f_not_verify_ssl = getStringFromPost('not_verify_ssl');
	if($form_key && $f_provider_id && form_key_is_valid($form_key))	{
		form_release_key($form_key);
		
		$f_provider = OAuthProvider::get_provider($f_provider_id);
		$f_provider_name = $f_provider->get_name();
		$f_consumer_key = $f_provider->get_consumer_key();
		$f_consumer_secret = $f_provider->get_consumer_secret();
		$f_request_token_url = getStringFromPost('request_token_url');
		$f_authorize_url = getStringFromPost('authorize_url');
		$f_access_token_url = getStringFromPost('access_token_url');
		
		$scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") ? 'http' : 'https';
		$http_url = $scheme . '://' . $_SERVER['HTTP_HOST'];
		$callback_url = $http_url."/plugins/".$pluginname."/callback.php";
		$parameters = array("oauth_callback"=>$callback_url);
		
		$ff_consumer = new OAuthConsumer($f_consumer_key, $f_consumer_secret);
		
		$ff_request1 = OAuthRequest::from_consumer_and_token($ff_consumer, false, "GET", $f_request_token_url, $parameters);
		$hmac = new OAuthSignatureMethod_HMAC_SHA1();
		$ff_request1->sign_request($hmac, $ff_consumer, NULL);
		
		//sending request with curl
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $ff_request1->to_url());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		//workaround for untrusted security certificates
		if($f_not_verify_ssl)	{
			curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, 0);
			session_set_cookie('NOT_VERIFY_SSL', 1, 0, '', '', false, true);
		}
		
		$request_token_string = curl_exec ($curl);
				
		if($request_token_string === false)
		{
			trigger_error('Error in curl : '.curl_error($curl), E_USER_WARNING);
		}
		curl_close ($curl);
		//print_r($request_token_string);
		parse_str($request_token_string, $request_token);
		
		if(array_key_exists('oauth_token', $request_token)&&array_key_exists('oauth_token_secret', $request_token))	{
			echo _("New request token received!<br>");
			echo _("Request Token Key : ".$request_token['oauth_token']."<br>");
			echo _("Request Token Secret : ".$request_token['oauth_token_secret']."<br><br>");
			//print_r($request_token);
			setcookie('PROVIDER', $f_provider_id, 0, '', '', false, true);
			setcookie('OAUTH_TOKEN', $request_token['oauth_token'], 0, '', '', false, true);
			setcookie('OAUTH_TOKEN_SECRET', $request_token['oauth_token_secret'], 0, '', '', false, true);
			$oauth_request_token = new OAuthToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
			
			$separator = "?";
			if (strpos($f_authorize_url,"?")!=false) $separator = "&";
			
			$new_user_authorization_url = $f_authorize_url . $separator . "oauth_token=".$request_token['oauth_token']."&oauth_callback=".$callback_url;
			//print_r($new_user_authorization_url);
		
		?>
		
		<form action="get_access_token.php" method="post">
		<?php echo '<input type="hidden" name="authorization_url" value="'.$new_user_authorization_url.'"/>' ?>
		<?php 
		echo _('<b>Step 2: </b>Authorize the Request Token (from '.$f_authorize_url.")") ?>
		<br>
		<input type="submit" value="<?php echo _('Go') ?>" />
		</form>
		<?php 
		//header("Location:".$new_user_authorization_url);
		}else 	{
			echo $HTML->error_msg(htmlspecialchars("Error in retrieving request token"));
				
		}
	}
	
	$f_authorization_url = getStringFromPost('authorization_url');
	if($f_authorization_url)	{
		header("Location:".$f_authorization_url);
	}
}else 	{
	echo '<p>'. _('There are no OAuth Providers registered in the database currently. Please ask your forge administer to create one.').'</p>';
}

echo'<br><br>';

echo util_make_link('/plugins/'.$pluginname.'/providers.php', _('OAuth Providers')). ' <br />';
echo util_make_link('/plugins/'.$pluginname.'/access_tokens.php', _('Access tokens')).'<br /> ';

site_user_footer(array());
