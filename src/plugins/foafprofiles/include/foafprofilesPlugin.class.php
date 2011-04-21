<?php

/**
 * foafprofilesPlugin Class
 *
 * Copyright 2011, Olivier Berger & Institut Telecom
 *
 * This program was developped in the frame of the COCLICO project
 * (http://www.coclico-project.org/) with financial support of the Paris
 * Region council.
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

class foafprofilesPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "foafprofiles";
		$this->text = "FOAFProfiles!"; // To show in the tabs, use...
		//$this->_addHook("user_personal_links");//to make a link to the user's personal part of the plugin
		//$this->_addHook("usermenu");
		//$this->_addHook("groupmenu");	// To put into the project tabs
		//$this->_addHook("groupisactivecheckbox"); // The "use ..." checkbox in editgroupinfo
		//$this->_addHook("groupisactivecheckboxpost"); //
		//$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		//$this->_addHook("userisactivecheckboxpost"); //
		//$this->_addHook("project_admin_plugins"); // to show up in the admin page fro group
		$this->_addHook("script_accepted_types");
		$this->_addHook("content_negociated_user_home");
		
	}
/*
	function CallHook ($hookname, &$params) {
		global $use_foafprofilesplugin,$G_SESSION,$HTML;
		if ($hookname == "usermenu") {
			$text = $this->text; // this is what shows in the tab
			if ($G_SESSION->usesPlugin("foafprofiles")) {
				$param = '?type=user&id=' . $G_SESSION->getId() . "&pluginname=" . $this->name; // we indicate the part we're calling is the user one
				echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array ('/plugins/foafprofiles/index.php' . $param ));				
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
				$params['DIRS'][]=util_make_url ('/plugins/foafprofiles/index.php?type=group&id=' . $group_id . "&pluginname=" . $this->name) ; // we indicate the part we're calling is the project one
			} else {
				$params['TITLES'][]=$this->text." is [Off]";
				$params['DIRS'][]='';
			}	
			(($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
		} elseif ($hookname == "groupisactivecheckbox") {
			//Check if the group is active
			// this code creates the checkbox in the project edit public info page to activate/deactivate the plugin
			$group_id=$params['group'];
			$group = &group_get_object($group_id);
			echo "<tr>";
			echo "<td>";
			echo ' <input type="checkbox" name="use_foafprofilesplugin" value="1" ';
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
			$use_foafprofilesplugin = getStringFromRequest('use_foafprofilesplugin');
			if ( $use_foafprofilesplugin == 1 ) {
				$group->setPluginUse ( $this->name );
			} else {
				$group->setPluginUse ( $this->name, false );
			}
		} elseif ($hookname == "user_personal_links") {
			// this displays the link in the user's profile page to it's personal FOAFProfiles (if you want other sto access it, youll have to change the permissions in the index.php
			$userid = $params['user_id'];
			$user = user_get_object($userid);
			$text = $params['text'];
			//check if the user has the plugin activated
			if ($user->usesPlugin($this->name)) {
				echo '	<p>' ;
				echo util_make_link ("/plugins/foafprofiles/index.php?id=$userid&type=user&pluginname=".$this->name,
						     _('View Personal FOAFProfiles')
					);
				echo '</p>';
			}
		} elseif ($hookname == "project_admin_plugins") {
			// this displays the link in the project admin options page to it's  FOAFProfiles administration
			$group_id = $params['group_id'];
			$group = &group_get_object($group_id);
			if ( $group->usesPlugin ( $this->name ) ) {
				echo '<p>'.util_make_link ("/plugins/foafprofiles/admin/index.php?id=".$group->getID().'&type=admin&pluginname='.$this->name,
						     _('FOAFProfiles Admin')).'</p>' ;
			}
		}												    
		elseif ($hookname == "blahblahblah") {
			// ...
		} 
	}
	*/
	
	/**
	 * Declares itself as accepting RDF XML on /users
	 * @param unknown_type $params
	 */
	function script_accepted_types (&$params) {
		$script = $params['script']; 
		if ($script == 'user_home') { 
			$params['accepted_types'][] = 'application/rdf+xml'; 
		} 
	}
	
	/**
	 * Outputs user's FOAF profile
	 * @param unknown_type $params
	 */
	function content_negociated_user_home (&$params) {
		$username = $params['username']; 
		$accept = $params['accept']; 
		
		if($accept == 'application/rdf+xml') {
				$params['content_type'] = 'application/rdf+xml';
			
				$user_obj = user_get_object_by_name($username);
				
				$user_real_name = $user_obj->getRealName();
				$user_email = $user_obj->getEmail();
				$mbox = 'mailto:'.$user_email;
				$mbox_sha1sum = sha1($mbox);
				
				$params['content'] = '<?xml version="1.0"?>
				<rdf:RDF
      				xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      				xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
      				xmlns:foaf="http://xmlns.com/foaf/0.1/"
      				xmlns:sioc="http://rdfs.org/sioc/ns#">
      				
      			<foaf:OnlineAccount rdf:about="">
      				<foaf:accountServiceHomepage rdf:resource="/"/>
      				<foaf:accountName>'. $username .'</foaf:accountName>
      				<sioc:account_of rdf:resource="#person" />
      				<foaf:accountProfilePage rdf:resource="" />
    			</foaf:OnlineAccount>
    			
      			<foaf:Person rdf:ID="person">
      				<foaf:name>'. $username .'</foaf:name>
					<foaf:holdsAccount rdf:resource="" />
					<foaf:mbox_sha1sum>'. $mbox_sha1sum .'</foaf:mbox_sha1sum>
    			</foaf:Person>
    			
    			</rdf:RDF>';
  
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
