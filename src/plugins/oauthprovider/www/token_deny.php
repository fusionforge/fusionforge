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

$pluginname = 'oauthprovider';

// deletes a request token if the users refuses to authorize it for a consumer
form_key_is_valid(getStringFromRequest('plugin_oauthprovider_token_deny_token'));

try {

  $f_token_id = getStringFromPost( 'token_id' );

  //  echo "token_id : $f_token_id \n";

  $t_token = OauthAuthzRequestToken::load( $f_token_id );
  
  if($t_token) {
    $consumer =  OauthAuthzConsumer::load($t_token->getConsumerId());

    // ask for confirmation
    //    helper_ensure_confirmed( sprintf( $plugin_oauthprovider_ensure_authorize, $consumer->getName() ), $plugin_oauthprovider_authorize_token );

    $t_token->delete();
    
    oauthprovider_CheckUser();
    
    ?>

<h2><?php echo _('Authorization Denied') ?></h2>

<p><?php echo sprintf( _('You have denied Consumer "%s" access to Fusionforge on your behalf. The pending OAuth token request has been deleted.'), $consumer->getName() )?></p>

<?php

	form_release_key(getStringFromRequest('plugin_oauthprovider_token_deny_token'));

  }

} catch (OAuthException $e) {

	error_parameters($e->getMessage(), "OauthAuthz");
	exit_error( "Error trying to deny/delete token!", 'oauthprovider' );
	
}

site_project_footer(array());

?>
