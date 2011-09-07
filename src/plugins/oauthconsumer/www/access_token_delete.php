<?php

require_once('../../env.inc.php');
require_once 'checks.php';

oauthconsumer_CheckUser();

if(!form_key_is_valid(getStringFromRequest('plugin_oauthconsumer_delete_access_token')))	{
	exit_form_double_submit();
}

$token_id = getStringFromGet('token_id');
$token = OAuthAccessToken::get_access_token($token_id);
$token->delete();

form_release_key(getStringFromRequest('plugin_oauthconsumer_delete_access_token'));
session_redirect( '/plugins/'.$pluginname.'/access_tokens.php');
