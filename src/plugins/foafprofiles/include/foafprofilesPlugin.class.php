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
