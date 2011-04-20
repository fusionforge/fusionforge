<?php

/**
 * oslcPlugin Class
 *
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

class oslcPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "oslc";
		$this->text = "oslc"; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user's personal part of the plugin
		$this->_addHook("usermenu");
		$this->_addHook("groupmenu");	// To put into the project tabs
		$this->_addHook("groupisactivecheckbox"); // The "use ..." checkbox in editgroupinfo
		$this->_addHook("groupisactivecheckboxpost"); //
		$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost"); //
		$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook("user_link_with_tooltip"); 
		$this->_addHook("project_link_with_tooltip");
		$this->_addHook("javascript_file"); // Add js files for oslc plugin	
		$this->_addHook("javascript"); // Add js initialization code
		$this->_addHook("cssfile");
		$this->_addHook("script_accepted_types");
		$this->_addHook("content_negociated_user_home");
		$this->_addHook("content_negociated_project_home");		
	}

	function CallHook ($hookname, &$params) {
		global $use_oslcplugin,$G_SESSION,$HTML;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("oslc")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/oslc/index.php' . $param ));				
			}
		} elseif ($hookname == "groupmenu") {
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
				$params['DIRS'][]=util_make_uri('/plugins/oslc/');
				$params['ADMIN'][]='';
			} else {
				$params['TITLES'][]=$this->text." is [Off]";
				$params['DIRS'][]='';
				$params['ADMIN'][]='';
			}	
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_oslcplugin" value="1" ';
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
		} elseif ($hookname == "groupisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the project edit public info page
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			$use_oslcplugin = getStringFromRequest('use_oslcplugin');
			if ( $use_oslcplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "userisactivecheckbox") {
			//check if user is active
			// this code creates the checkbox in the user account manteinance page to activate/deactivate the plugin
			$user = $params['user'];
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_oslcplugin" value="1" ';
			// checked or unchecked?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "checked";
 			}
			echo " />    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "userisactivecheckboxpost") {
			// this code actually activates/deactivates the plugin after the form was submitted in the user account manteinance page
			$user = $params['user'];
			$use_oslcplugin = getStringFromRequest('use_oslcplugin');
			if ( $use_oslcplugin == 1 ) {
				$user->setPluginUse ( $this->name );
			} else {
				$user->setPluginUse ( $this->name, false );
			}
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_oslcplugin" value="1" ';
			// checked or unchecked?
			if ( $user->usesPlugin ( $this->name ) ) {
				echo "checked";
			}
			echo " />    Use ".$this->text." Plugin";
			echo "</td>";
			echo "</tr>";
		} elseif ($hookname == "user_personal_links") {
			// this displays the link in the user's profile page to it's personal oslc (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$text = $params['text'];
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>' ;
				echo util_make_link ("/plugins/oslc/index.php?id=$userid&type=user&pluginname=".$this->name,
						     _('View Personal oslc')
					);
				echo '</p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  oslc administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo '<p>'.util_make_link ("/plugins/oslc/admin/index.php?id=".$group->getID().'&type=admin&pluginname='.$this->name,
						     _('oslc Admin')).'</p>' ;
			}
		}
		elseif ($hookname == "user_link_with_tooltip"){
			require_once dirname( __FILE__ ) . '/compactResource.class.php';
			$cR = compactResource::createCompactResource($params);
			$params['user_link'] = $cR->getResourceLink();
		}
		elseif ($hookname == "project_link_with_tooltip") {
			require_once dirname( __FILE__ ) . '/compactResource.class.php';
			$cR = compactResource::createCompactResource($params);
			$params['group_link'] = $cR->getResourceLink();			
			/*
			// replace behaviour of util_display_user()
		
			// invoke user_logo hook
			$logo_params = array('user_id' => $params['user_id'], 'size' => $params['size'], 'content' => '');
        	plugin_hook_by_reference('user_logo', $logo_params);
        
        	// construct a link that is the base for a hover popup.
        	$url = '<a class="personPopupTrigger" href="'. util_make_url_u ($params['username'], $params['user_id']) .
				'" rel="' . $params['username'] . '">'. $params['username'] . '</a>';
        	if ($logo_params['content']) {
                $params['user_link'] = $logo_params['content'] . $url .'<div class="new_line"></div>';
        	}
			else {
				$params['user_link'] = $url;
			}
			*/
		}
		elseif ($hookname == "javascript_file") {
			// The userTooltip.js script is used by the compact preview feature (see content_negociated_user_home)
			use_javascript('/scripts/jquery/jquery.js');
			use_javascript('/plugins/oslc/scripts/oslcTooltip.js');
		}
		elseif ($hookname == "javascript") {
			// make sure jquery won't conflict with prototype
		       echo 'jQuery.noConflict();';
		}
		elseif ($hookname == "cssfile") {
			use_stylesheet('/plugins/oslc/css/oslcTooltipStyle.css');
		}
		elseif($hookname == "script_accepted_types"){
			$script = $params['script']; 
			if ($script == 'user_home' || $script == 'project_home') { 
				$params['accepted_types'][] = 'application/x-oslc-compact+xml'; 
			} 
		}
		elseif($hookname == "content_negociated_user_home") {
			$username = $params['username']; 
			$accept = $params['accept']; 
			if($accept == 'application/x-oslc-compact+xml') {
				$params['return'] = '/plugins/oslc/compact/user/'.$username;
			}
		}
		elseif($hookname == "content_negociated_project_home") {
			$projectname = $params['groupname'];
			$accept = $params['accept'];
			if($accept == 'application/x-oslc-compact+xml') {
				$params['return'] = '/plugins/oslc/compact/project/'.$projectname;
			}
		}
		elseif ($hookname == "blahblahblah") {
			// ...
		} 
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
