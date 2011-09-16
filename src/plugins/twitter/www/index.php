<?php

require_once('../../env.inc.php');
require_once 'checks.php';

twitter_CheckUser();
list($twitter_provider, $twitter_token) = twitter_get_access_token();

if($_GET['list']=="public")	{
	$resource_url = "http://api.twitter.com/1/statuses/public_timeline.json";
}if($_GET['list']=="user")	{
	$resource_url = "http://api.twitter.com/1/statuses/user_timeline.json";
}else 	{
	$resource_url = "http://api.twitter.com/1/statuses/home_timeline.json";
}

$http_method = "get";
		
$resource = new OAuthResource($resource_url, $twitter_provider->get_id(), $http_method);
$transaction = new OAuthTransaction($twitter_provider, $twitter_token, $resource);
$response = $transaction->send_request();
$response_array = json_decode($response);
//print_r($response_array[0]->user);
	
echo '<table cellpadding="10">';
foreach ($response_array as $tweet)	{
	echo '<tr><th rowspan="2"><img src="'.$tweet->user->profile_image_url.'" alt="twitter"></th>';
	echo '<th valign="bottom">'.$tweet->user->name.'</th></tr>';
	echo '<tr><td valign="top">'.$tweet->text.'</th></tr>';
}
echo '</table>';
	
site_user_footer();