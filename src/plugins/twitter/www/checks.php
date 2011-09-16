<?php

/*
 * This file contains the functionality of the different checks 
 * needed to be done before displaying any page of the
 * twitter plugin
 */ 

require_once $gfwww.'include/pre.php';

$pluginname = 'twitter';

$tabs = array(	"Home",
				"Public",
				"My tweets",
				"Post a tweet",
				"Other API requests");

$tablinks = array(	'/plugins/'.$pluginname.'/index.php',
					'/plugins/'.$pluginname.'/index.php?list=public',
					'/plugins/'.$pluginname.'/index.php?list=user',
					'/plugins/'.$pluginname.'/post.php',
					'/plugins/'.$pluginname.'/others.php');

// the header that displays for the user portion of the plugin
function twitter_User_Header($params) {
	global $DOCUMENT_ROOT,$HTML, $user_id, $pluginname;
	$params['toptab']=$pluginname; 
	$params['user']=$user_id;
	site_user_header($params);    
}

/*
 * checks whether the user is logged in and has activated the plugin
 */
function twitter_CheckUser() {
	
	if (!session_loggedin()) { //check if user logged in
		exit_not_logged_in();
	}	
	
	global $pluginname;
	
	$user = session_get_user(); // get the session user

	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
		exit_error("Invalid User, Cannot Process your request for this user.", $pluginname);
	}

	$id = $user->getID();
	
	if (!$id) {
		exit_error("Cannot Process your request: Invalid User", $pluginname);
	}
	
	$realuser = user_get_object($id);
	if (!($realuser) || !($realuser->usesPlugin($pluginname))) { //check if user has activated the plugin
		exit_error("First activate the User's $pluginname plugin through Account Maintenance Page", $pluginname);
	}

	//displays the page header and toolbar
	twitter_User_Header();
	twitter_toolbar();
		
}

function twitter_toolbar()	{
	global $tabs, $tablinks;
	echo '<ul class="widget_toolbar">';
	for($i=0; $i<count($tabs); $i++)	{
		echo "<li>".util_make_link($tablinks[$i], _($tabs[$i]))."</li>";
	}
	echo '</ul>';
}

function twitter_get_access_token()	{
	$userid = session_get_user()->getID();
	
	$providers = OAuthProvider::get_all_oauthproviders();
	foreach ($providers as $provider) {
		if(strcasecmp(trim($provider->get_name()), "twitter")==0)	{
			$twitter_provider = $provider;
		}
	}
	
	if($twitter_provider)	{
		$access_tokens = OAuthAccessToken::get_all_access_tokens_by_provider($twitter_provider->get_id(), $userid);
	
		if($access_tokens)	{
			$twitter_token = $access_tokens[0];
			for ($i=1; $i<count($access_tokens); $i++)	{
				//get the latest access token
				if($access_tokens[$i]->get_time_stamp()>$twitter_token->get_time_stamp())	{
					$twitter_token = $access_tokens[$i];
				}
			}
			return array($twitter_provider, $twitter_token);
		}else 	{
			exit_error(_('You have no Twitter Access Tokens registered in the database currently'));
		}
	}else 	{
		//error
		exit_error(_("Couldn't find a twitter provider registered in the database. If a twitter provider exists, it needs to be named 'Twitter', else it has to be created in the OAuth Consumer plugin"));
	}
}