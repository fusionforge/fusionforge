<?php

require_once('../../env.inc.php');
require_once 'checks.php';

if (!form_key_is_valid(getStringFromRequest('plugin_oauthprovider_consumer_delete_token'))) 	{
	exit_form_double_submit('admin');
}

oauthconsumer_CheckForgeAdminExit();

$provider_id = getStringFromGet( 'provider_id' );
$provider = OAuthProvider::get_provider($provider_id);
$provider->delete();

form_release_key(getStringFromRequest('plugin_oauthconsumer_provider_delete_token'));
session_redirect( '/plugins/'.$pluginname.'/providers.php');