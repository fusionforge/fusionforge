<?php

require_once '../../env.inc.php';
require_once 'checks.php';

oauthconsumer_CheckUser();

$f_token_id = getStringFromPost('tokens');
$f_resource_url = getStringFromPost('resource_url');
$f_http_method = getStringFromPost('http');
$f_post_data = getStringFromPost('post_data');

if(!$f_post_data)	$f_post_data = NULL;

$access_token = OAuthAccessToken::get_access_token($f_token_id);
$resource = new OAuthResource($f_resource_url, $access_token->get_provider_id(), $f_http_method);
$provider = OAuthProvider::get_provider($access_token->get_provider_id());

if(substr($f_resource_url, -5, 5)==".json")	$f_post_data = json_decode($f_post_data, TRUE);

$transaction = new OAuthTransaction($provider, $access_token, $resource, $f_post_data);
$response = $transaction->send_request();

//twitter, identi.ca rest api endpoints
$timeline_resources = array("public_timeline",
					"home_timeline",
					"friends_timeline",
					"mentions",
					"replies",
					"user_timeline",
					"retweeted_to_me",
					"retweeted_by_me",
					"retweets_of_me");
$url_prefix = array("http://api.twitter.com/1/statuses/",
					"http://identi.ca/api/statuses/");

if(substr($f_resource_url, -5, 5)==".json")	{
	$response_array = json_decode($response, TRUE);
	foreach ($url_prefix as $prefix) {
		foreach($timeline_resources as $suffix)	{
			if($f_resource_url==$prefix.$suffix.".json") {
				$timeline = true;
				$title = str_replace("_", " ", $suffix);
				echo "<p>".$title."</p><ol>";
				foreach($response_array as $tweet)	{
					if(array_key_exists("text", $tweet))	{
						echo "<li>".$tweet["text"]."</li>";
					}else {
						print_r($response_array);
						break;
					}
				}
				echo "</ol>";
			}			
		}
	}
	if(!$timeline)	{
		if(($f_resource_url=="http://api.twitter.com/1/statuses/update.json")||($f_resource_url=="http://identi.ca/api/statuses/update.json"))	{
			if(array_key_exists("text", $response_array))	{
				echo $HTML->feedback("Tweet: '".$response_array["text"]." posted successfully");
			}else 	{
				print_r($response_array);
			}
		}else 	{
			print_r($response_array);
		}
	}
}else 	{
	var_dump($response);
}

echo'<br><br>';

echo util_make_link('/plugins/'.$pluginname.'/providers.php', _('OAuth Providers')). ' <br />';
echo util_make_link('/plugins/'.$pluginname.'/get_access_token.php', _('Get Access tokens')).'<br /> ';
echo util_make_link('/plugins/'.$pluginname.'/access_tokens.php', _('Access tokens')).'<br /> ';

site_user_footer(array());
