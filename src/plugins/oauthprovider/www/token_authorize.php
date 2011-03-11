<?php

/**
 * This file is (c) Copyright 2010 by Olivier BERGER, Madhumita DHAR, Institut TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the COCLICO
 * project with financial support of its funders.
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once 'checks.php';	


// invoked when the user decides to authorize a request token
form_key_is_valid(getStringFromRequest('plugin_oauthprovider_token_authorize_token'));

try {

	$f_token_id = getStringFromPost( 'token_id' );
	$f_callback_url = urldecode(getStringFromPost( 'callback_url' ));
	$f_role_id = getStringFromPost( 'rolelist' );

	//  echo "token_id : $f_token_id \n";
	//  echo "callback_url: $f_callback_url \n";
	$t_token = OauthAuthzRequestToken::load( $f_token_id );

	if($t_token) {
		$p_token = $t_token->key;
		$consumer =  OauthAuthzConsumer::load($t_token->getConsumerId());

		// ask for confirmation
		//helper_ensure_confirmed( sprintf( $plugin_oauthprovider_ensure_authorize, $consumer->getName() ), $plugin_oauthprovider_authorize_token );
		//equivalent for fusionforge not found yet

		$user_id = user_getid();
		//    echo "user : $user_id";

		// mark as authorized by the user in the DB
		$t_token->authorize($user_id, $f_role_id);

		form_release_key(getStringFromRequest('plugin_oauthprovider_token_authorize_token'));
		
		//echo "Redirect : $callback_url?oauth_token=$p_token \n";exit;
		Header("Location: $f_callback_url?oauth_token=$p_token");
		//session_redirect( $f_callback_url . "?oauthprovider_token=$p_token" );
	}

} catch (OAuthException $e) {

	error_parameters($e->getMessage(), "OauthAuthz");
	exit_error( "Error trying to authorise token!", 'oauthprovider' );
	
}
