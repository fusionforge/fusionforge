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

form_key_is_valid(getStringFromRequest( 'plugin_oauthprovider_consumer_update_token' ));

//access_ensure_global_level( plugin_config_get( 'manage_threshold' ) ); // equivalent function to be added later for ff
session_require_global_perm('forge_admin');

$f_consumer_id = getIntFromPost( 'consumer_id' );
$f_consumer_name = getStringFromPost( 'consumer_name' );
$f_consumer_url = getStringFromPost( 'consumer_url' );
$f_consumer_desc = getStringFromPost( 'consumer_desc' );
$f_consumer_email = getStringFromPost( 'consumer_email' );
if(array_key_exists('keys_update', $_POST))	{
	$key_secret = OauthAuthzConsumer::new_consumer_keys_generate();
	$f_consumer_key = $key_secret[0];
	$f_consumer_secret = $key_secret[1];
}else {
	$f_consumer_key = getStringFromPost( 'consumer_key' );
	$f_consumer_secret = getStringFromPost( 'consumer_secret' );
}

$t_consumer = OauthAuthzConsumer::load( $f_consumer_id );

$t_consumer->setName($f_consumer_name);
$t_consumer->setURL($f_consumer_url);
$t_consumer->setDesc($f_consumer_desc);
$t_consumer->setEmail($f_consumer_email);
$t_consumer->key = $f_consumer_key;
$t_consumer->secret = $f_consumer_secret;

$t_consumer->save();

form_release_key(getStringFromRequest( 'plugin_oauthprovider_consumer_update_token' ));

session_redirect( '/plugins/'.$pluginname.'/consumer_manage.php?consumer_id=' . $t_consumer->getId()  );
