<?php
require_once '../../env.inc.php';
require_once 'checks.php';
global $feedback;

if(!form_key_is_valid(getStringFromRequest('plugin_oauthconsumer_provider_create_token')))	{
	exit_form_double_submit('admin');
}

session_require_global_perm('forge_admin');

$f_provider_name = getStringFromPost( 'provider_name' );
$f_provider_desc = getStringFromPost( 'provider_desc' );
$f_consumer_key = getStringFromPost( 'consumer_key' );
$f_consumer_secret = getStringFromPost( 'consumer_secret' );
$f_request_token_url = getStringFromPost( 'request_token_url' );
$f_authorize_url = getStringFromPost( 'authorize_url' );
$f_access_token_url = getStringFromPost( 'access_token_url' );

if (($msg=OAuthProvider::check_provider_values(TRUE, $f_provider_name, $f_provider_desc, $f_consumer_key, $f_consumer_secret, $f_request_token_url, $f_authorize_url, $f_access_token_url))!=null) {
	$feedback .= $msg;
	form_release_key(getStringFromRequest('plugin_oauthconsumer_provider_create_token'));
	include 'providers.php';
}
else {
	$f_provider_desc = (htmlspecialchars($f_provider_desc));
	$f_request_token_url = (htmlspecialchars($f_request_token_url));
	$f_authorize_url = (htmlspecialchars($f_authorize_url));
	$f_access_token_url = (htmlspecialchars($f_access_token_url));
	$new_provider = new OAuthProvider($f_provider_name, $f_provider_desc, $f_consumer_key, $f_consumer_secret, $f_request_token_url, $f_authorize_url, $f_access_token_url);
	$new_provider->write_to_db();

	form_release_key(getStringFromRequest('plugin_oauthconsumer_provider_create_token'));

	session_redirect( '/plugins/'.$pluginname.'/providers.php');
}
