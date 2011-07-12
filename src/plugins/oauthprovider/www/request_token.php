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

// Request token endpoint

// This is not exactly using FusionForge, as is not meant for humans, and just an endpoint of the OAuth protocol.

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
//require_once 'checks.php';

/*if (!session_loggedin()) {
		exit_not_logged_in();
	}*/

try {
  $oauthprovider_server = new OAuthServer(FFDbOAuthDataStore::singleton());

  $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
  $oauthprovider_server->add_signature_method($hmac_method);

  // Retrieves a new request token for the calling consumer
  $req = OAuthRequest::from_request();//print_r($req->get_parameters());exit;
  $token = $oauthprovider_server->fetch_request_token($req);

  // the default print method is exactly what must be returned
  print $token;

} catch (OAuthException $e) {
  print("Exception on trying to get a request token: ".$e->getMessage() . "\n<hr />\n");
  print("Exception on trying to get a request token: ".$e->getTraceAsString() . "\n<hr />\n");
  print_r($req);
  die();
}

?>
