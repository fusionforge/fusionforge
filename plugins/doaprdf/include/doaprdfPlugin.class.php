<?php

/**
 * doaprdfPlugin Class
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

require_once('common/include/ProjectManager.class.php');

class doaprdfPlugin extends Plugin {
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "doaprdf";
		$this->text = "DoaPRDF!"; // To show in the tabs, use...
		$this->_addHook("script_accepted_types");
		$this->_addHook("content_negociated_project_home");

	}

	/**
	 * Declares itself as accepting RDF XML on /users
	 * @param unknown_type $params
	 */
	function script_accepted_types (&$params) {
		$script = $params['script'];
		if ($script == 'project_home') {
			$params['accepted_types'][] = 'application/rdf+xml';
		}
	}

	/**
	 * Outputs user's FOAF profile
	 * @param unknown_type $params
	 */
	function content_negociated_project_home (&$params) {
		$projectname = $params['groupname'];
		$accept = $params['accept'];
		$group_id = $params['group_id'];

		if($accept == 'application/rdf+xml') {
			$pm = ProjectManager::instance();
			$project = $pm->getProject($group_id);
			$project_shortdesc = $project->getPublicName();
			$project_description = $project->getDescription();
			$tags_list = NULL;
			if (forge_get_config('use_project_tags')) {
				$group = group_get_object($group_id);
				$tags_list = $group->getTags();
			}
			
			$params['content_type'] = 'application/rdf+xml';

			// invoke the 'project_rdf_metadata' hook so as to complement the RDF description
			// Invoke plugins' hooks 'script_accepted_types' to discover which alternate content types they would accept for /users/...
			$hook_params = array();
			$hook_params['prefixes'] = array(
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#' => 'rdf',
							'http://www.w3.org/2000/01/rdf-schema#' => 'rdfs',
							'http://usefulinc.com/ns/doap#' => 'doap',
							'http://purl.org/dc/terms/' => 'dcterms'
			);
			$hook_params['xml'] = array();
			$hook_params['group'] = $group_id;
			
			plugin_hook_by_reference('project_rdf_metadata', $hook_params);
			
			$xml = '<?xml version="1.0"?>
				<rdf:RDF';
			foreach($hook_params['prefixes'] as $url => $prefix) {
				$xml .= ' xmlns:'. $prefix . '="'. $url .'"';
			}
      		
      		$xml .='>

      			<doap:Project rdf:about="">
      				<doap:name>'. $projectname .'</doap:name>';
			$xml .= '<doap:shortdesc>'. $project_shortdesc . '</doap:shortdesc>';
      		if($project_description) {
				$xml .= '<doap:description>'. $project_description . '</doap:description>';
			}
			if($tags_list) {
				$tags = split(', ',$tags_list);
				foreach($tags as $tag) {
					$xml .= '<dcterms:subject>'.$tag.'</dcterms:subject>';
				}
			}
			
			if (count($hook_params['xml'])) {
				foreach($hook_params['xml'] as $fragment) {
					$xml .= $fragment;
				}
			}
			
			$xml .='</doap:Project>
    			</rdf:RDF>';
			
			$doc = new DOMDocument();
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput   = true;
			$doc->loadXML($xml);
			 
			$params['content'] = $doc->saveXML();
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
