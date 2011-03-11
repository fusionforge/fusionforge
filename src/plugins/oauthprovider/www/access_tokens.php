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

// Screen which displays a list of access tokens the user has already granted to consumers 

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

require_once 'checks.php';	

$user_id = user_getid();

$t_tokens = OauthAuthzAccessToken::load_all($user_id);

$headers = array(
	$plugin_oauthprovider_consumer_name,
	$plugin_oauthprovider_key,
	$plugin_oauthprovider_secret,
	$plugin_oauthprovider_time_stamp,
	'DELETE'
	);
echo $HTML->boxTop($plugin_oauthprovider_access_tokens);
echo $HTML->boxBottom();
echo $HTML->listTableTop($headers);

$i = 0;
foreach( $t_tokens as $t_token ) {
	$consumer = OauthAuthzConsumer::load($t_token->getConsumerId());
	echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
	echo '<td>'.util_make_link('/plugins/'.$pluginname.'/consumer_manage.php?type='.$type.'&id='.$id. '&consumer_id=' . $t_token->getConsumerId(),$consumer->getName()).'</td>';
	echo '<td>'.$t_token->key.'</td>';
	echo '<td>'.$t_token->secret.'</td>';
	echo '<td>'.date(DATE_RFC822, $t_token->gettime_stamp()) .'</td>';
	echo '<td>'.util_make_link('/plugins/'.$pluginname.'/token_delete.php?type='.$type.'&id='.$id.'&token_id=' . $t_token->getId() . '&token_type=access' . '&plugin_oauthprovider_token_delete_token='.form_generate_key(), $plugin_oauthprovider_delete). '</td>';
	echo '</tr>';
	$i++;
}
	
echo $HTML->listTableBottom();

site_project_footer(array());