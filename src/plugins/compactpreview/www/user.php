<?php

/**
 * This file is (c) Copyright 2010 by Sabri LABBENE, Institut
 * TELECOM
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
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
//require_once $gfconfig.'plugins/compactpreview/config.php';


// 	$user = session_get_user(); // get the session user

// 	if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
// 		exit_error("Invalid User", "Cannot Process your request for this user.");
// 	}

// 	$type = getStringFromRequest('type');
// 	$id = getStringFromRequest('id');
// 	$pluginname = getStringFromRequest('pluginname');

// 	if (!$type) {
// 		exit_error("Cannot Process your request","No TYPE specified"); // you can create items in Base.tab and customize this messages
// 	} elseif (!$id) {
// 		exit_error("Cannot Process your request","No ID specified");
// 	} else {

$user = getStringFromRequest('user');

$user_obj = user_get_object_by_name($user);


$user_real_name = $user_obj->getRealName();
$user_name = $user_obj->getUnixName();
$user_id = $user_obj->getID();
$user_uri = util_make_url ("/users/$user_name/");
$user_title = $user_obj->getTitle();
$title = ($user_title ? $user_title .' ' :''). $user_real_name;

// invoke user_logo hook
$logo_params = array('user_id' => $user_id, 'size' => 'm', 'content' => '');
plugin_hook_by_reference('user_logo', $logo_params);

if ($logo_params['content']) {
	$logo = $logo_params['content'];
}
else {
	$logo = '';
}

?>
<html>
<head>
<title>User: <?php echo $user_real_name;?> (Identifier: <?php echo $user_id;?>)</title>
</head>
<body>
	<table>
		<tr>
			<td>
			<?php echo $logo;?>
			</td>
			<td>
				<table>
					<tr>
						<td colspan="2"><i>Compact User Preview</i></td>
					</tr>
					<tr>
						<!-- TODO : use  user_logo hook here -->
						<td rowspan="5"><img
							src="/plugins/compactpreview/images/userTooltip/oslc.png" />
						</td>
						<td><b>User Name:</b>
						
						
						
						 <?php echo $title;?></td>
					</tr>
					<tr>
						<td><b>Login Name:</b>
						
						
						
						 <?php echo $user_name;?></td>
					</tr>
					<tr>
						<td><b>Identifier:</b>
						
						
						
						  <?php echo $user_id;?></td>
					</tr>
					<tr>
						<td><b>Member since:</b>
						
						
						
						 <?php print date(_('Y-m-d H:i'), $user_obj->getAddDate()); ?></td>
					</tr>
					<tr>
						<td><small><b>URI:</b> <a href="<?php echo $user_uri;?>"><?php echo $user_uri;?>
							</a> </small></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<!-- 
	<b>User Name:</b> <?php echo $title;?><br/>
	<b>Login Name:</b> <?php echo $user_name;?> <br/>
	<b>Identifier:</b>  <?php echo $user_id;?> <br/>
	<b>Member since:</b> <?php print date(_('Y-m-d H:i'), $user_obj->getAddDate()); ?><br/>
	<small><b>URI:</b> <?php echo $user_uri;?></small><br/>
	</table>
	-->
</body>
</html>
<?php

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
