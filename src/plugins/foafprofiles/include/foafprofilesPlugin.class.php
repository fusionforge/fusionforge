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
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class foafprofilesPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "foafprofiles";
		$this->text = "User FOAF Profiles"; // To show in the tabs, use...
		$this->_addHook("script_accepted_types");
		$this->_addHook("content_negociated_user_home");

	}

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

				$projects = $user_obj->getGroups() ;
				sortProjectList($projects) ;
				$roles = RBACEngine::getInstance()->getAvailableRolesForUser($user_obj) ;
				sortRoleList($roles) ;
				
				$member_of_xml='';

				$groups_xml='';
				
				$projects_xml ='';
				
				// see if there were any groups
				if (count($projects) >= 1) {
					foreach ($projects as $p) {
						// TODO : report also private projects if authenticated, for instance through OAuth
						if($p->isPublic()) {
							$project_link = util_make_link_g ($p->getUnixName(),$p->getID(),$p->getPublicName());
							$project_uri = util_make_url_g ($p->getUnixName(),$p->getID());
							// sioc:UserGroups for all members of a project are named after /projects/A_PROJECT/members/
							$usergroup_uri = $project_uri .'members/';
	
							$group_roles_xml = '';
							
							$role_names = array () ;
							foreach ($roles as $r) {
								if ($r instanceof RoleExplicit
								&& $r->getHomeProject() != NULL
								&& $r->getHomeProject()->getID() == $p->getID()) {
									$role_names[$r->getID()] = $r->getName() ;
									$role_uri = $project_uri .'roles/'.$r->getID();
									$group_roles_xml .= '<planetforge:group_has_function rdf:resource="'. $role_uri .'" />';
								}
							}
							
							$member_of_xml .= '<sioc:member_of rdf:resource="'. $usergroup_uri .'" />';
							$groups_xml .= '<sioc:UserGroup rdf:about="'. $usergroup_uri .'">
			      					<sioc:usergroup_of rdf:resource="'. $project_uri .'"/>';
							
							$groups_xml .= $group_roles_xml;
							
	      					$groups_xml .= '</sioc:UserGroup>';
							$projects_xml .= '<planetforge:ForgeProject rdf:about="'. $project_uri .'">
	      						<doap:name>'. $p->getUnixName() .'</doap:name>
	      						</planetforge:ForgeProject>';
							
							foreach ($role_names as $id => $name) {
								$projects_xml .= '<sioc:Role rdf:about="'. $project_uri .'roles/'.$id .'">
									<sioc:name>'. $name .'</sioc:name>
								</sioc:Role>';
							}
						}	
					}
				} // end if groups
								
				$xml_content = '<?xml version="1.0"?>
				<rdf:RDF
      				xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      				xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
      				xmlns:foaf="http://xmlns.com/foaf/0.1/"
      				xmlns:sioc="http://rdfs.org/sioc/ns#"
      				xmlns:doap="http://usefulinc.com/ns/doap#"
      				xmlns:planetforge="http://coclico-project.org/ontology/planetforge#">

      			<foaf:OnlineAccount rdf:about="">
      				<foaf:accountServiceHomepage rdf:resource="/"/>
      				<foaf:accountName>'. $username .'</foaf:accountName>
      				<sioc:account_of rdf:resource="#person" />
      				<foaf:accountProfilePage rdf:resource="" />';
				
      			$xml_content .= $member_of_xml;

      			$xml_content .= '</foaf:OnlineAccount>
				
      			<foaf:Person rdf:ID="person">
      				<foaf:name>'. $user_real_name .'</foaf:name>
					<foaf:holdsAccount rdf:resource="" />
					<foaf:mbox_sha1sum>'. $mbox_sha1sum .'</foaf:mbox_sha1sum>
    			</foaf:Person>';
      			
      			$xml_content .= $groups_xml;
      			
      			$xml_content .= $projects_xml;
      			 
      			$xml_content .= '</rdf:RDF>';
      			
      			$doc = new DOMDocument();
      			$doc->preserveWhiteSpace = false;
      			$doc->formatOutput   = true;
      			$doc->loadXML($xml_content);
      			
    			$params['content'] = $doc->saveXML();
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
