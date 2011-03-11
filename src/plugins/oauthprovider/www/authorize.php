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

// This displays the request token authorization dialog to the user

//should be changed as session_require_login returns with error if not logged in
//to be tested
//session_require_login ();

require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';
require $gfconfig.'/plugins/oauthprovider/config.php';
require_once 'checks.php';	

//non-admin users shud be able to do authorisations
//session_require_global_perm('project_admin');


?>

<h2><?php echo $plugin_oauthprovider_pending_authorizations ?></h2>

<?php

try {
	
	$req = OAuthRequest::from_request();
	//  print_r($req);

	$p_token = $req->get_parameter('oauth_token');
	//  echo "token : $p_token";

	$t_request_token = OauthAuthzRequestToken::load_by_key($p_token);
	
	if($type=="group") $groupname = $name;
	else $groupname = null;
	$group = group_get_object_by_name($groupname);
	$user_id = user_getid();
	//echo "user: ".$user_id;
	//echo "group: ".$groupid;
	$user = user_get_object($user_id);
	$roles = array () ;
	foreach (RBACEngine::getInstance()->getAvailableRolesForUser ($user) as $role) {
		if ($role->getHomeProject() && $role->getHomeProject()->getID() == $group->getID()) {
			$roles[] = $role ;
		}
	}
	
	if($t_request_token) {
		$consumer =  OauthAuthzConsumer::load($t_request_token->getConsumerId());
		// don't allow to authorize tokens older than 24 hours
		$time_stamp = $t_request_token->gettime_stamp();
		$now = time();
		if ($time_stamp < ($now - (int)(24 * 3600))) {
			$time_stamp = null;
			$date = "more than 24 hours ago";
		}
		else {
			$date = "on ".date(DATE_RFC822, $time_stamp);
		}

		$callback_url = $req->get_parameter('oauth_callback');

		// check if there are already access_tokens already authorized for that same consumer
		$t_access_tokens = OauthAuthzAccessToken::load_by_consumer($consumer->getId(), $user_id);
		$already_authorized = count($t_access_tokens);

		if ($already_authorized > 0) {
			echo "<p><b>ATTENTION: You have already $already_authorized authorized access for this consumer on your behalf. You are advised to delete previous access tokens first.</b></p>";
			
		}
	
		// Now we can display the pending request token and point to the authorization confirmation dialog
	echo sprintf( $plugin_oauthprovider_pending_authorization, $consumer->getName(), $date ) . ' ';
	echo "<table><tr><td>";
	if( isset($time_stamp) ) {
		// the time_stamp is recent enough so we can allow authorization
		//echo "<br />";
		echo '<form action="token_authorize.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'" method="post">';
		echo '<input type="hidden" name="plugin_oauthprovider_token_authorize_token" value="'.form_generate_key().'"/>';
		echo '<input type="hidden" name="token_id" value="'.$t_request_token->getId().'"/>';
		echo '<input type="hidden" name="callback_url" value="'.urlencode($callback_url).'"/>';
			
		echo "<table><tr><td>Role:</td><td><select name=\"rolelist\">";
		foreach($roles as $role)	{
			echo '<option value="'.$role->getID().'">'.$role->getName().'</option>';
		}
		echo "</select></td>";
		
		echo '<td><input type="submit" value="'. $plugin_oauthprovider_authorize .'"/></td></tr></table>';
		echo '</form>';
		
	}
	else {
		// just display an inactive authorization link
		print "<a href=\"\">". $plugin_oauthprovider_authorize ."</a>" ;
	}
	echo '</td><td>';
	// Denying it is always an option
	echo '<form action="token_deny.php?type='.$type.'&id='.$id.'&pluginname='.$pluginname.'" method="post">';
	echo '<input type="hidden" name="plugin_oauthprovider_token_deny_token" value="'.form_generate_key().'"/>';
	echo '<input type="hidden" name="token_id" value="'.$t_request_token->getId().'"/>';
	echo "<table><tr><td><b>OR</b></td>";
	echo '<td><input type="submit" value="'. $plugin_oauthprovider_deny .'"/></td></tr></table>';
	echo '</form>';
	echo '</td></tr></table>'
	?>


<?php // TODO needs translation ?>
<p><b>Security-related notices :</b></p>
<ul>
	<li>Fusionforge cannot assert in a fully trusted way if this request was
	actually made by the right OAuth Consumer. You should be able to tell,
	since you have been redirected here from that Consumer application.</li>
	<li>Currently, this feature implements only a one-time access to a dummy page</li>
</ul>

	<?php
	}
	else {

		?>
<p>Could not find token <?php echo "$p_token" ?>!</p>

		<?php

	}


} catch (OAuthException $e) {

	error_parameters($e->getMessage(), "OauthAuthz");
	exit_error( "Oauth authorisation error!", 'oauthprovider' );
	
}
site_project_footer(array());

?>
