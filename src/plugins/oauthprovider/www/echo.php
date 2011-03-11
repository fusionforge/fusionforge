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

# This script demonstrates the way to protect access to a resource using OAuth.

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
//require_once 'checks.php';	




try {
  $oauthprovider_server = new OAuthServer(FFDbOAuthDataStore::singleton());

  $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
  $oauthprovider_server->add_signature_method($hmac_method);

  $req = OAuthRequest::from_request();
  list($consumer, $token) = $oauthprovider_server->verify_request( $req);

  // Now, the request is valid.

  // We know which consumer is connected
  echo "Authenticated as consumer : \n";
  //print_r($consumer);
  echo "  name: ". $consumer->getName() ."\n";
  echo "  key: $consumer->key\n";
  echo "\n";

  // And on behalf of which user it connects
  echo "Authenticated with access token whose key is :  $token->key \n";
  echo "\n";
  $t_token = OauthAuthzAccessToken::load_by_key($token->key);
  $user_object =& user_get_object($t_token->getUserId());
  $user = $user_object->getRealName().' ('.$user_object->getUnixName().')';
  echo "Acting on behalf of user : $user\n";
  echo "\n";

  echo "Received message : \n";
  $message = $_GET['message'];
  print_r($message);


} catch (OAuthException $e) {
  print($e->getMessage() . "\n<hr />\n");
  print_r($req);
  die();
}
