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

# This script demonstrates the way to protect access to a resource using OAuth (see README for example of its use).

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

session_set_for_authplugin('oauthprovider');

// Here the session should be constructed with the OAuthprovider plugin set as sufficient (and no other required).
$user = session_get_user(); // get the session user

if($user) {
	$user = $user->getRealName().' ('.$user->getUnixName().')';
	echo "Acting on behalf of user : $user\n";
	echo "\n";
	
	echo "Received message : \n";
	$message = $_GET['message'];
	print_r($message);
}
else {
	echo "Sorry, you didn't authenticate successfully!";
}
