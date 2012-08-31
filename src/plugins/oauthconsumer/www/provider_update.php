<?php

require_once('../../env.inc.php');
require_once 'checks.php';

if(!form_key_is_valid(getStringFromRequest( 'plugin_oauthconsumer_provider_update_token' )))	    {
	exit_form_double_submit('admin');
}

session_require_global_perm('forge_admin');

$f_provider_id = getStringFromPost( 'provider_id' );
$f_provider_name = getStringFromPost( 'provider_name' );
$f_provider_desc = getStringFromPost( 'provider_desc' );
$f_consumer_key = getStringFromPost( 'consumer_key' );
$f_consumer_secret = getStringFromPost( 'consumer_secret' );
$f_request_token_url = getStringFromPost( 'request_token_url' );
$f_authorize_url = getStringFromPost( 'authorize_url' );
$f_access_token_url = getStringFromPost( 'access_token_url' );

if (($msg=OAuthProvider::check_provider_values(FALSE, $f_provider_name, $f_provider_desc, $f_consumer_key, $f_consumer_secret, $f_request_token_url, $f_authorize_url, $f_access_token_url))!=null) {
	$feedback .= $msg;
	form_release_key(getStringFromRequest('plugin_oauthconsumer_provider_update_token'));
	include 'provider_edit.php';
}
else {

	$provider = OAuthProvider::get_provider($f_provider_id);
	
	$provider->set_name($f_provider_name);
	$provider->set_description($f_provider_desc);
	$provider->set_consumer_key($f_consumer_key);
	$provider->set_consumer_secret($f_consumer_secret);
	$provider->set_request_token_url($f_request_token_url);
	$provider->set_authorize_url($f_authorize_url);
	$provider->set_access_token_url($f_access_token_url);
	
	$provider->write_to_db();
	
	form_release_key(getStringFromRequest( 'plugin_oauthconsumer_provider_update_token' ));
	
	session_redirect( '/plugins/'.$pluginname.'/providers.php' );
}
