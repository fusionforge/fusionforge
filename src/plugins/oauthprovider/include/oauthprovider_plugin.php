<?php

/**
 * oauthproviderPlugin Class
 *
 * This file is (c) Copyright 2010, 2011 by Olivier BERGER, Madhumita DHAR, Institut TELECOM
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

// TODO : fix missing copyright

class oauthproviderPlugin extends ForgeAuthPlugin {
	public function __construct() {
		
		$this->ForgeAuthPlugin() ;
		
		$this->name = 'oauthprovider';
		$this->text = 'OAuthProvider'; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user's personal part of the plugin
		$this->_addHook("usermenu");
		$this->_addHook("groupmenu");	// To put into the project tabs
		$this->_addHook("groupisactivecheckbox"); // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost"); //
		$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost"); //
		$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook("site_admin_option_hook");
		$this->_addHook("account_menu");
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		
		
		$this->declareConfigVars();
	}

	function usermenu() {
		global $G_SESSION,$HTML;
	$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("oauthprovider")) {
				echo  $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/oauthprovider/index.php'), array(''));				
			}
	}
	function groupmenu() {
		$group_id=$params['group'];
			$project = &group_get_object($group_id);
			if (!$project || !is_object($project)) {
				return;
			}
			if ($project->isError()) {
				return;
			}
			if (!$project->isProject()) {
				return;
			}
			if ( $project->usesPlugin ( $this->name ) ) {
				$params['TITLES'][]=$this->text;
				$params['DIRS'][]=util_make_url ('/plugins/oauthprovider/index.php?type=group&id=' . $group_id) ; // we indicate the part we're calling is the project one
			} else {
				$params['TITLES'][]=$this->text." is [Off]";
				$params['DIRS'][]='';
			}	
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
	}
	function groupisactivecheckbox() {
		//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_oauthproviderplugin" value="1" ';
			// checked or unchecked?
			if ( $group->usesPlugin ( $this->name ) ) {
				echo "checked";
			}
			echo " /><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";
	}
	function groupisactivecheckboxpost() {
				global $use_oauthproviderplugin;
		
	// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_oauthproviderplugin = getStringFromRequest('use_oauthproviderplugin');
			if ( $use_oauthproviderplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
	}
	function userisactivecheckbox () {
		//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_oauthproviderplugin" value="1" ';
			// checked or unchecked?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "checked";
			}
			echo " /><br/>";
			echo "</td>";
			echo "<td>";
			echo "<strong>Use ".$this->text." Plugin</strong>";
			echo "</td>";
			echo "</tr>";
	}
	function userisactivecheckboxpost() {
				global $use_oauthproviderplugin;
		
	// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$use_oauthproviderplugin = getStringFromPost('use_oauthproviderplugin');
			if ( $use_oauthproviderplugin == 1 ) {
				$user->setPluginUse ( $this->name );
			} else {
				$user->setPluginUse ( $this->name, false );
			}
	}
	function user_personal_links() {
	// this displays the link in the user's profile page to it's personal oauthprovider (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$text = $params['text'];
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>' ;
				echo util_make_link ("/plugins/oauthprovider/index.php?type=user",
						     _('View Personal oauthprovider')
					);
				echo '</p>';
			}
	}
	function project_admin_plugins( ) {
					// this displays the link in the project admin options page to it's  oauthprovider administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo '<p>'.util_make_link ("/plugins/oauthprovider/admin/index.php?id=".$group->getID().'&type=admin&pluginname='.$this->name,
						     _('oauthprovider Admin')).'</p>' ;
			}
		
	}
	
	function site_admin_option_hook( ) {
		echo '<li>'. util_make_link ('/plugins/oauthprovider/consumer.php', _('Manage OAuth consumers'). ' [' . _('OAuth provider plugin') . ']'). '</li>';
	  }
	
	function account_menu( ) {
		return array( '<a href="' . $gfplugins.'oauthprovider/www/access_tokens.php' . '">' . $plugin_oauthprovider_menu_account_summary. '</a>', );
	  }
	  
	protected function declareConfigVars() {
		parent::declareConfigVars();
		
		// Change vs default 
		forge_define_config_item ('required', $this->name, 'no');
		forge_set_config_item_bool ('required', $this->name) ;

		// Change vs default
		forge_define_config_item ('sufficient', $this->name, 'yes');
		forge_set_config_item_bool ('sufficient', $this->name) ;
	
	}

	/**
	 * Is there a valid session?
	 * @param unknown_type $params
	 */
	
	function checkAuthSession(&$params) {
		$this->saved_user = NULL;
		$user = NULL;

		try {
			$oauthprovider_server = new OAuthServer(FFDbOAuthDataStore::singleton());
			
			$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
			$oauthprovider_server->add_signature_method($hmac_method);
			
			$req = OAuthRequest::from_request();
			list($consumer, $token) = $oauthprovider_server->verify_request( $req);
			
			// Now, the request is valid.
			
			// We know which consumer is connected
			//echo "Authenticated as consumer : \n";
			//print_r($consumer);
			//echo "  name: ". $consumer->getName() ."\n";
			//echo "  key: $consumer->key\n";
			//echo "\n";
			
			// And on behalf of which user it connects
			//echo "Authenticated with access token whose key is :  $token->key \n";
			//echo "\n";
			$t_token = OauthAuthzAccessToken::load_by_key($token->key);
			$user =& user_get_object($t_token->getUserId());
			//$user_name = $user->getRealName().' ('.$user->getUnixName().')';
			//echo "Acting on behalf of user : $user_name\n";
			//echo "\n";
			
			// TODO: but with which role is the user authenticated ??
			
		} catch (OAuthException $e) {
			$code = $e->getCode();
			if ($code) {
				switch($code) {
					case 401:
						header('HTTP/1.1 401 Unauthorized', 401);
						break;
					case 400:
						header('HTTP/1.1 400 Bad Request', 400);
						break;
					default:
						break;
				}
			}
			
			echo "OAuth problem - code $code: \n";
			print($e->getMessage() . "\n<hr />\n");
			print_r($req);
		}
		
		if ($user) {
			if ($this->isSufficient()) {
				$this->saved_user = $user;
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_ACCEPT;
				
			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		} else {
			if ($this->isRequired()) {
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_REJECT;
			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		}
	}
	
	
}