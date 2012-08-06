<?php
/**
 * twitter plugin main script
 *
 * This file is (c) Copyright 2010, 2011 by Olivier BERGER, Madhumita DHAR, Institut TELECOM
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

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
	echo '<tr><th><img src="'.$tweet->user->profile_image_url.'" alt="twitter"></th>';
	echo '<td valign="top"><b>'.$tweet->user->name.'</b><br>'.$tweet->text.'</th></tr>';
}
echo '</table>';
	
site_user_footer();