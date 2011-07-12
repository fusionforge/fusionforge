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

global $feedback;

if(!form_key_is_valid(getStringFromRequest('plugin_oauthprovider_consumer_create_token')))	{
	exit_form_double_submit('admin');
}

//access_ensure_global_level( plugin_config_get( 'manage_threshold' ) ); // equivalent function to be added later for ff
session_require_global_perm('forge_admin');

$f_consumer_name = getStringFromPost( 'consumer_name' );
$f_consumer_url = getStringFromPost( 'consumer_url' );
$f_consumer_desc = getStringFromPost( 'consumer_desc' );
$f_consumer_email = getStringFromPost( 'consumer_email' );

	if (($msg=OauthAuthzConsumer::check_consumer_values($f_consumer_name, $f_consumer_url, $f_consumer_desc, $f_consumer_email))!=null) {
		//$missing_params[] = _('"Consumer Name"');
		$feedback .= $msg;
		//exit_missing_param('', $missing_params,'oauthprovider');
		form_release_key(getStringFromRequest('plugin_oauthprovider_consumer_create_token'));

		//site_admin_header(array('title'=>_('Create OAuth consumer')));

		include 'consumer.php';
	}
	else {
		$key_secret = OauthAuthzConsumer::new_consumer_keys_generate();
		$f_consumer_key = $key_secret[0];
		$f_consumer_secret = $key_secret[1];
		$f_consumer_url = (htmlspecialchars($f_consumer_url));
		$f_consumer_desc = (htmlspecialchars($f_consumer_desc));
		$f_consumer_email = (htmlspecialchars($f_consumer_email));
		$t_consumer = new OauthAuthzConsumer( $f_consumer_name, $f_consumer_key, $f_consumer_secret, $f_consumer_url, $f_consumer_desc, $f_consumer_email );
		$t_consumer->save();

		form_release_key(getStringFromRequest('plugin_oauthprovider_consumer_create_token'));

		session_redirect( '/plugins/'.$pluginname.'/consumer.php');
	}
