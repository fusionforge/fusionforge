<?php

/**
 * This file is (c) Copyright 2010 by Madhumita DHAR, Institut TELECOM
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

global $gfplugins;
require_once $gfplugins.'oauthprovider/include/oauthprovider_plugin.php' ;

require_once $gfplugins.'oauthprovider/include/consumer_api.php';
require_once $gfplugins.'oauthprovider/include/request_token_api.php';
require_once $gfplugins.'oauthprovider/include/access_token_api.php';
require_once $gfplugins.'oauthprovider/include/fusionforge_oauth_datastore.php';


$oauthproviderPluginObject = new oauthproviderPlugin ;

register_plugin ($oauthproviderPluginObject) ;

?>
