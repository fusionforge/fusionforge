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


form_key_is_valid(getStringFromRequest('plugin_oauthprovider_token_delete_token'));

//access_ensure_global_level( plugin_config_get( 'manage_threshold' ) ); // equivalent function to be added later for ff
//session_require_global_perm('project_admin');

$f_token_id = getStringFromGet( 'token_id' );
$f_type = getStringFromGet( 'token_type' );

if($f_type == 'access') {
	$t_token = OauthAuthzAccessToken::load( $f_token_id );
}
else if ($f_type == 'request'){
	$t_token = OauthAuthzRequestToken::load( $f_token_id );
}

//helper_ensure_confirmed( sprintf( $plugin_oauthprovider_ensure_token_delete, $t_token->key ), $plugin_oauthprovider_delete_token );
//equivalent for fusionforge not found yet

$t_token->delete();

form_release_key(getStringFromRequest('plugin_oauthprovider_token_delete_token'));
session_redirect( '/plugins/'.$pluginname.'/'. $f_type.'_tokens.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname);
