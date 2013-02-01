<?php

/**
 * admsswPlugin Class
 *
 * Copyright 2012-2013, Olivier Berger & Institut Mines-Telecom
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

// This Plugin publishes meta-data about the public projects hosted on the forge 
// as RDF conforming to the ADMS.SW 1.0 specifications :  
// https://joinup.ec.europa.eu/asset/adms_foss/release/release100

require_once('common/include/TroveCat.class.php');
require_once('common/frs/FRSFileType.class.php');
require_once $gfplugins.'admssw/common/RDFedFRSPackage.class.php' ;
require_once('common/include/rdfutils.php');
include_once("Graphite.php");

class admsswPlugin extends Plugin {
	
	//var $trovecat_id_index; // cat_id to TroveCat instances
	var $trovecat_id_to_shortname;	// cat_id to shortname
	var $trovecat_id_to_path; // cat_id to path
		
	/**
	 * Constructor
	 * 
	 * @param number $id plugin identifier
	 */
	public function __construct($id=0) {
		$this->Plugin($id) ;
		$this->name = "admssw";
		$this->text = "ADMS.SW"; // To show in the tabs, use...
		
		// The standard RDF namespaces that will be used in the plugin
		$this->ns = array(
				'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
				'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
				'doap' => 'http://usefulinc.com/ns/doap#',
				'dcterms' => 'http://purl.org/dc/terms/',
				'admssw' => 'http://purl.org/adms/sw/',
				'adms' => 'http://www.w3.org/ns/adms#',
				'foaf' => 'http://xmlns.com/foaf/0.1/',
				'schema' => 'http://schema.org/',
				'rad' => 'http://www.w3.org/ns/rad#'
		);
		
		//$this->trovecat_id_index = array();
		$this->trovecat_id_to_shortname = array();
		$this->trovecat_id_to_path = array();
		
		// Add the doaprdf plugin's namespaces
		$doaprdfplugin = plugin_get_object ("doaprdf");
		$ns = $doaprdfplugin->doapNameSpaces();
		foreach($ns as $s => $u)
		{
			if (! in_array($u, $this->ns)) {
				$this->ns[$u] = $s;
			}
		}

		$this->_addHook("project_rdf_metadata"); // will provide some RDF metadata for the project's DOAP profile to 'doaprdf' plugin		
		$this->_addHook("alt_representations"); // supports conneg (content-negociation) for alternate representations of some scripts
		$this->_addHook("script_accepted_types"); // supported alternate content-types (Accept HTTP header values)
		$this->_addHook("content_negociated_projects_list"); // registers to be able to return content-negociated content for /projects/...
		$this->_addHook("softwaremap_links"); // display additional submenu in the softwaremap tool
		$this->_addHook("project_after_description"); // displays additional info in the project info widget
		$this->_addHook("content_negociated_trove_list"); // generates the RDF document containing the trove categories as SKOS
		$this->_addHook("content_negociated_frs_index"); // generates the public projects list/index as RDF
		$this->_addHook("content_negociated_frs_download_file"); // generates the RDF meta-data about a FRS downloadable file
	}

	//
	// General conneg related or other hook callbacks
	//
	
	/**
	 * Declares itself as accepting RDF XML or Turtle on /projects and other scripts ...
	 * 
	 * @param unknown_type $params
	 */
	public function script_accepted_types (&$params) {
		$script = $params['script'];
		if ($script == 'projects_list' || $script == 'admssw_full' || $script == 'trove_list' || $script == 'frs_index' || $script == 'frs_download_file') {
			$params['accepted_types'][] = 'application/rdf+xml';
			$params['accepted_types'][] = 'text/turtle';
		}
	}

	/**
	 * Declares a link to itself in the link+meta HTML headers of /projects and /softwaremap
	 * 
	 * @param unknown_type $params
	 */
	public function alt_representations (&$params) {
		$script_name = $params['script_name'];
		$php_self = $params['php_self'];
		$php_self = substr($php_self,0,strpos($php_self,'/',1));
		if ($php_self == '/projects' || $php_self == '/softwaremap') {
			$params['return'][] = '<link rel="meta" type="application/rdf+xml" title="ADMS.SW RDF Data" href="'. util_make_url ("/projects") .'"/>';
			$params['return'][] = '<link rel="meta" type="text/turtle" title="ADMS.SW RDF Data" href="'. util_make_url ("/projects") .'"/>';
		}
	}
	
	/**
	 * Add a new link to the softwaremap submenu
	 * 
	 * @param array $params
	 */
	public function softwaremap_links (&$params) {
	
		$params['TITLES'][] = _('ADMS.SW meta-data');
		$params['URLS'][] = '/plugins/'. $this->name .'/index.php';
		$params['ATTRS'][] = array('title' => _('ADMS.SW RDF meta-data about forge projects.'), 'class' => 'tabtitle');
	}
	
	/**
	 * Add a line in the project infos widget in the project home
	 * 
	 * @param array $params
	 */
	public function project_after_description (&$params) {
		$group_id = $params['group_id'];
		print '<br />'. sprintf( _('View <a href="%1$s">ADMS.SW meta-data</a> about the project'), util_make_url ('/plugins/'. $this->name .'/projectturtle.php?group_id='.$group_id));
	}
	
	
	//
	// Utility functions
	//
	
	/**
	 * Returns namespaces used in ADMS.SW
	 * 
	 * @return array the namespaces associative array
	 */
	private function admsswNameSpaces() {
		return $this->ns;
	}
	
	/**
	 * Initialize Graphite graph's namespaces
	 */
	private function graphSetAdmsswNameSpaces(&$graph) {
		$ns = $this->admsswNameSpaces();
		foreach($ns as $s => $u)
		{
			$graph->ns( $s, $u );
		}
	}
	
	static function repositoryUri() {
		return util_make_url ('/projects#repo');
	}
	
	//
	// Project related methods and callbacks
	//
	
	/**
	 * Provides an HTML preview of a project's ADMS.SW meta-data looking like turtle
	 * 
	 * @param int $group_id
	 */
	private function htmlPreviewProjectAsTurtle($group_id) {
	
		$resourceindex = $this->getProjectResourceIndex($group_id);

		$graph = new Graphite();
	
		$this->graphSetAdmsswNameSpaces($graph);
		
		$count = $graph->addTriples( ARC2::getTriplesFromIndex($resourceindex) );
	
		return $graph->dump();
	}
	
	/**
	 * Provides an ARC2 resource index of a project's ADMS.SW meta-data
	 * 
	 * @param int $group_id
	 */
	private function getProjectResourceIndex($group_id) {

		// part of the work is done by the doaprdf plugin, which will in turn call us back (see project_rdf_metadata)	
		$doaprdfplugin = plugin_get_object ("doaprdf");
		
		$ns = $this->admsswNameSpaces();
		
		$resourceindex = $doaprdfplugin->getProjectResourceIndex($group_id, $ns);
		
		// update the namespaces if they happen to get updated in between
		foreach($ns as $s => $u)
		{
			if (! in_array($u, $this->ns)) {
				$this->ns[$u] = $s;
			}
		}
		
		return $resourceindex;
	}
	
	/**
	 * Improves the DOAP description made by doaprdf (ARC2 resource) to add ADMS.SW meta-data for a project
	 * 
	 * @param unknown_type $params
	 * 
	 * This is a hook callback so part of the resource construction is made in the doaprdf plugin
	 */
	public function project_rdf_metadata (&$params) {
	
		# TODO : check that the passed in_Resource is indeed a doap:Project
		$group_id = $params['group'];
	
		// Update the prefixes by ADMS.SW ones
		$new_prefixes = $this->admsswNameSpaces();
		foreach($new_prefixes as $s => $u)
		{
			if (! isset($params['prefixes'][$u])) {
				$params['prefixes'][$u] = $s;
			}
		}
		
		// The ARC2 RDF_Resource already initialized by doaprdf plugin
		$res = $params['in_Resource'];
	
		// we could save the type doap:Project in such case, as there's an equivalence, but not sure all consumers do reasoning
		$types = array('doap:Project', 'admssw:SoftwareProject');
		rdfutils_setPropToUri($res, 'rdf:type', $types);
		
		
		// Handle project tags
		$tags_list = NULL;
		if (forge_get_config('use_project_tags')) {
			$group = group_get_object($group_id);
			$tags_list = $group->getTags();
		}
		// connect to FusionForge internals
		$pm = ProjectManager::instance();
		$project = $pm->getProject($group_id);
		$tags = array();
		if($tags_list) {
			$tags = split(', ',$tags_list);
			
			// reuse the same as dcterms:subject until further specialization of adms.sw keywords
			$res->setProp('rad:keyword', $tags);
		}
			
		$project_description = $project->getDescription();
		if($project_description) {
				// it seems that doap:description is not equivalent to dcterms:description, so repeat
				$res->setProp('dcterms:description', $project_description);
		}
		
		// Handle trove categories
		$trovecaturis=array('admssw:intendedAudience' => array(),
				'admssw:locale' => array(),
				'admssw:userInterfaceType' => array(),
				'admssw:programmingLanguage' => array(),
				'schema:operatingSystem' => array(),
				'admssw:status' => array(),
				'rad:theme' => array());
		$trovecats = TroveCat::getprojectcats($group_id);
		foreach($trovecats as $trovecat) {
			$cat_id = $trovecat->getId();
			if(!isset($this->trovecat_id_to_shortname[$cat_id])) {
				$this->trovecat_id_to_shortname[$cat_id] = $trovecat->getShortName();
			}
			$idsfullpath = $trovecat->getIdsFullPath();
			$folders_ids = explode(" :: ", $idsfullpath);
			$paths = array();
			foreach ($folders_ids as $id) {
				if (! isset($this->trovecat_id_to_shortname[$id])) {
					$supercat = new TroveCat($id);
					$this->trovecat_id_to_shortname[$id] = $supercat->getShortName();
				}
				$paths[] = $this->trovecat_id_to_shortname[$id];
			}
			$path=implode('/', $paths);
			$this->trovecat_id_to_path[$cat_id] = $path;
			$trovecaturi = util_make_url ('/softwaremap/trove/'.$path);
			$rootcatid = $trovecat->getRootCatId();
			$rootcatshortname = $this->trovecat_id_to_shortname[$rootcatid];
			// This is a bit hackish
			switch ($rootcatshortname) {
				case 'developmentstatus':
					$trovecaturis['admssw:status'][] = $trovecaturi;
					break;
				case 'audience':
					$trovecaturis['admssw:intendedAudience'][] = $trovecaturi;
					break;
				case 'license':
					$trovecaturis['dcterms:license'][] = $trovecaturi;
					break;
				case 'natlanguage':
					$trovecaturis['admssw:locale'][] = $trovecaturi;
					break;
				case 'os':
					$trovecaturis['schema:operatingSystem'][] = $trovecaturi;
					break;
				case 'language':
					$trovecaturis['admssw:programmingLanguage'][] = $trovecaturi;
					break;
				default:
					$trovecaturis['rad:theme'][] = $trovecaturi;
					break;
			}			
		}
		foreach ($trovecaturis as $prop => $uris) {
			if (count($uris)) {
				rdfutils_setPropToUri($res, $prop, $uris);
			}
		}
		
		$res->setProp('rdfs:comment', "Generated with the doaprdf and admssw plugins of fusionforge");

		rdfutils_setPropToUri($res, 'dcterms:isPartOf', admsswPlugin::repositoryUri());
		
		// Handle project members
		$admins = $project->getAdmins() ;
		$members = $project->getUsers() ;
		$contributors_uris = array();
		foreach ($admins as $u) {
			$contributor_uri = util_make_url_u ($u->getUnixName(),$u->getID());
			$contributor_uri = rtrim($contributor_uri, '/');
			$contributor_uri = $contributor_uri . '#person';
			if (! in_array($contributor_uri, $contributors_uris) ) {
				$contributors_uris[] = $contributor_uri;
			}
		}
		foreach ($members as $u) {
			$contributor_uri = util_make_url_u ($u->getUnixName(),$u->getID());
			$contributor_uri = rtrim($contributor_uri, '/');
			$contributor_uri = $contributor_uri . '#person';
			if (! in_array($contributor_uri, $contributors_uris) ) {
				$contributors_uris[] = $contributor_uri;
			}
		}
		rdfutils_setPropToUri($res, 'schema:contributor', $contributors_uris);
		
		// Handle FRS releases
		$release_uris = array();
		$frs_packages = get_rdfed_frs_packages($project);
		foreach ($frs_packages as $frs_package) {
			//print_r($frs_package);
			
			if($frs_package->isPublic() && $frs_package->getStatus() == 1) {
				$package_name = $frs_package->getFileName();
				
				$frs_releases = $frs_package->getReleases();
				foreach ($frs_releases as $frs_release) {
					if( $frs_release->getStatus() == 1 ) {
						//print_r($frs_release);
						$release_uris[] = $frs_release->getUri();
					}
				}
			}
		}
		if(count($release_uris)) {
			rdfutils_setPropToUri($res, 'doap:release', $release_uris);
		}
		// The releases aren't discoverable by follow your nose yet, so add the proper document/script URL to use
		rdfutils_setPropToUri($res, 'rdfs:seeAlso', util_make_url ('/frs/?group_id='.$group_id));
		
		$params['out_Resources'][] = $res;

	}
	
	
	//
	// Project list / SoftwareRepository related methods
	//
	
	/**
	 * Provides a Graphite graph for resource(s) representing the ADMS.SW SoftwareRepository
	 * 
	 * @param string URI of the document to use
	 * @param bool are projects to be fully described or just a URI of their resource
	 */
    public function getProjectListResourcesGraph($documenturi, $detailed=false) {
		
    	// Construct an ARC2_Resource containing the project's RDF (DOAP) description
		$ns = $this->admsswNameSpaces();
		
		$conf = array(
				'ns' => $ns
		);
		
		$res = ARC2::getResource($conf);
		$res->setURI( admsswPlugin::repositoryUri() );
		
		// $res->setRel('rdf:type', 'admssw:SoftwareRepository');
		rdfutils_setPropToUri($res, 'rdf:type', 'admssw:SoftwareRepository');
		
		//$res->setProp('doap:name', $projectname);
		rdfutils_setPropToUri($res, 'adms:accessURL', util_make_url ("/softwaremap/") );
		$forge_name = forge_get_config ('forge_name');
		$ff = new FusionForge();
		$res->setProp('dcterms:description', 'Public projects in the '. $ff->software_name .' Software Map on '. $forge_name );
		$res->setProp('rdfs:label', $forge_name .' public projects');
		$res->setProp('adms:supportedSchema', 'ADMS.SW v1.0');
			
		// same as for trove's full list
		$projects = get_public_active_projects_asc();
		$proj_uris = array();
		foreach ($projects as $row_grp) {
			$proj_uri = util_make_url_g(strtolower($row_grp['unix_group_name']),$row_grp['group_id']).'#project';
			$proj_uris[] = $proj_uri;
		}
		if(count($proj_uris)) {
			rdfutils_setPropToUri($res, 'dcterms:hasPart', $proj_uris);
		}
		
		$graph = new Graphite();
		$this->graphSetAdmsswNameSpaces($graph);
		
		$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );

		// if needed, provide also full details about the projects
		if($detailed) {
			foreach ($projects as $row_grp) {
				$group_id = $row_grp['group_id'];
				//$proj_uri = util_make_url_g(strtolower($row_grp['unix_group_name']),$row_grp['group_id']);
				$resindex = $this->getProjectResourceIndex($row_grp['group_id']);
				$count = $graph->addTriples( ARC2::getTriplesFromIndex($resindex) );
				
				$this->addProjectFrsResourcesToGraph($graph, $group_id);
				
			}
		}
		
		$this->graphSetAdmsswNameSpaces($graph);
		
		// The document the document itself
		$res = ARC2::getResource($conf);
		$res->setURI( $documenturi );
		rdfutils_setPropToUri($res, 'rdf:type', 'foaf:Document');
		rdfutils_setPropToUri($res, 'foaf:primaryTopic', admsswPlugin::repositoryUri() );
		rdfutils_setPropToXSDdateTime($res, 'dcterms:created', date('c'));
		$res->setProp('dcterms:title', "ADMS.SW full dump" );
		
		$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );
				
		return $graph;
	}
	
	/**
	 * Provides an HTML preview of the ADMS.SW SoftwareRepository meta-data looking like turtle
	 * 
	 * @param int $group_id
	 */
	public function htmlPreviewProjectsAsTurtle() {
		$graph = $this->getProjectListResourcesGraph(util_make_url ("/projects"));
		
		return $graph->dump();
	}
	
	/**
	 * Outputs the public projects list as ADMS.SW for /projects
	 * 
	 * @param unknown_type $params
	 */
	public function content_negociated_projects_list (&$params) {
		
		$accept = $params['accept'];
		
		// we are asked for RDF either as RDF+XML or Turtle
		if($accept == 'application/rdf+xml' || $accept == 'text/turtle') {
				
				
			// We will return RDF
			$params['content_type'] = $accept;
	
			$graph = $this->getProjectListResourcesGraph(util_make_url ("/projects"));
						
			if ($accept == 'text/turtle') {
				$doc = $graph->serialize($serializer="Turtle");
			}
			if ($accept == 'application/rdf+xml') {
				$doc = $graph->serialize();
			}
			
			$params['content'] = $doc . "\n";
		
		}
	}
	
	
	//
	// Trove related methods
	//

	/**
	 * Provides a Graphite graph for resource(s) representing the trove categories as SKOS concepts
	 * 
	 */
	private function getTroveListResourcesGraph() {
	
		// Construct an ARC2_Resource containing the project's RDF (DOAP) description
		$ns = array(
			'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
			'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
			'skos' => 'http://www.w3.org/2004/02/skos/core#'
		);
	
		$conf = array(
				'ns' => $ns
		);


		$graph = new Graphite();
		$this->graphSetAdmsswNameSpaces($graph);
		
		$rootcats = TroveCat::getallroots();
		
		foreach($rootcats as $rootcat) {
			
			// First the ConceptScheme itself
			
			$conceptschemeres = ARC2::getResource($conf);
			
			$scheme_shortname = $rootcat->getShortName();
			$conceptscheme_uri = util_make_url ('/softwaremap/trove/'.$scheme_shortname);
			$conceptschemeres->setURI( $conceptscheme_uri );

			// $res->setRel('rdf:type', 'doap:Project');
			rdfutils_setPropToUri($conceptschemeres, 'rdf:type', 'skos:ConceptScheme');
			
			$conceptschemeres->setProp('skos:prefLabel', $rootcat->getFullName());
			$conceptschemeres->setProp('skos:definition', $rootcat->getDescription());
			
			$count = $graph->addTriples( ARC2::getTriplesFromIndex($conceptschemeres->index) );
		
			
			$subcats = $rootcat->listSubTree();
			
			// memorize a few indexes
						
			foreach($subcats as $subcat) {
				$cat_id = $subcat->getId();
				//$this->trovecat_id_index[$cat_id] = &$subcat;
				
				$this->trovecat_id_to_shortname[$cat_id] = $subcat->getShortName(); 
			}
				
			foreach($subcats as $subcat) {
				$cat_id = $subcat->getId();
				
				$idsfullpath = $subcat->getIdsFullPath();
				
				$folders_ids = explode(" :: ", $idsfullpath);
				
				$paths = array();
				foreach ($folders_ids as $id) {
					if (isset($this->trovecat_id_to_shortname[$id])) {
						$paths[] = $this->trovecat_id_to_shortname[$id];
					}
				}
				$path=implode('/', $paths);
				
				$this->trovecat_id_to_path[$cat_id] = $path;
			}
			
			// Now, for all Concepts in this ConceptScheme
			
			foreach($subcats as $subcat) {
				$conceptres = ARC2::getResource($conf);
				
				$path = $this->trovecat_id_to_path[$subcat->getId()];
				$conceptres->setURI( util_make_url ('/softwaremap/trove/'.$scheme_shortname.'/'.$path) );
				
				// $res->setRel('rdf:type', 'doap:Project');
				rdfutils_setPropToUri($conceptres, 'rdf:type', 'skos:Concept');
				
				rdfutils_setPropToUri($conceptres, 'skos:inScheme', $conceptscheme_uri);
				
				$conceptres->setProp('skos:prefLabel', $subcat->getFullName());
				$conceptres->setProp('skos:definition', $subcat->getDescription());
				
				$parentid = $subcat->getParentId();
				$rootparentid = $subcat->getRootCatId();
				
				if ($parentid != $rootparentid) {
					$parentpath = $this->trovecat_id_to_path[$parentid];
					rdfutils_setPropToUri($conceptres, 'skos:broader', util_make_url ('/softwaremap/trove/'.$scheme_shortname.'/'.$parentpath) );
				}
				
				$count = $graph->addTriples( ARC2::getTriplesFromIndex($conceptres->index) );
			}
		
		}
		
		return $graph;
	}
	
	/**
	 * Provides an HTML preview of the trove categories as SKOS looking like turtle
	 * 
	 */
	private function htmlPreviewTroveCatsAsTurtle() {
		$graph = $this->getTroveListResourcesGraph();
	
		return $graph->dump();
	}
	
	/**
	 * Outputs the trove categories as SKOS
	 * 
	 * @param unknown_type $params
	 */
	public function content_negociated_trove_list (&$params) {
	
		$accept = $params['accept'];
	
		// we are asked for RDF either as RDF+XML or Turtle
		if($accept == 'application/rdf+xml' || $accept == 'text/turtle') {
	
	
			// We will return RDF
			$params['content_type'] = $accept;
	
			$graph = $this->getTroveListResourcesGraph();
	
			if ($accept == 'text/turtle') {
				$doc = $graph->serialize($serializer="Turtle");
			}
			if ($accept == 'application/rdf+xml') {
				$doc = $graph->serialize();
			}
				
			$params['content'] = $doc . "\n";
	
		}
	}
	
	
	//
	// FRS related methods
	//
	
	/**
	 * Add RDF resources to a Graphite graph for the File Release system
	 * 
	 * @param Graphite	graph to be updated
	 * @param int	group id
	 * @param int	optional release id
	 */
	private function addProjectFrsResourcesToGraph(&$graph, $group_id, $release_id = false) {
		
		$group = &group_get_object($group_id);
		
		// if we are passed a release ID, then only process that particular release
		if($release_id) {
			
			$frs_release = rdfed_frsrelease_get_object($release_id);
			
			// Don't produce RDF for hidden releases
			if( $frs_release->getStatus() == 1 ) {
				$frs_package = $frs_release->getFRSPackage();
				
				// Only produce RDF for releases of public and not hidden packages
				if ($frs_package->isPublic() && $frs_package->getStatus() == 1) {

					$frs_release->saveToGraph($graph);
										
				}
			}
		}
		else {
			// then produce RDF for all the project's packages
			$frs_packages = get_rdfed_frs_packages($group);
			
			foreach($frs_packages as $frs_package) {
				// well actually, only for public and not hidden packages
				if ($frs_package->isPublic() && $frs_package->getStatus() == 1) {
					
					$frs_package->saveToGraph($graph);
				}
			}
		}
		return $graph;
		
	}

	/**
	 * Outputs the public projects releases as ADMS.SW for /frs
	 * 
	 * @param unknown_type $params
	 */
	public function content_negociated_frs_index (&$params) {
	
		$accept = $params['accept'];
	
		// we are asked for RDF either as RDF+XML or Turtle
		if($accept == 'application/rdf+xml' || $accept == 'text/turtle') {
	
	
			// We will return RDF
			$params['content_type'] = $accept;
	
			$group_id = $params['group_id'];
			$release_id = $params['release_id'];

			$graph = new Graphite();
			$this->graphSetAdmsswNameSpaces($graph);
			
			$this->addProjectFrsResourcesToGraph($graph, $group_id, $release_id);
	
			if ($accept == 'text/turtle') {
				$doc = $graph->serialize($serializer="Turtle");
			}
			if ($accept == 'application/rdf+xml') {
				$doc = $graph->serialize();
			}
	
			$params['content'] = $doc . "\n";
	
		}
	}
	
	/**
	 * Outputs the public downloadable files as ADMS.SW SoftwarePackages for /frs/download.php...
	 * 
	 * @param unknown_type $params
	 */
	public function content_negociated_frs_download_file (&$params) {
	
		$accept = $params['accept'];
	
		// we are asked for RDF either as RDF+XML or Turtle
		if($accept == 'application/rdf+xml' || $accept == 'text/turtle') {
	
			// We will return RDF
			$params['content_type'] = $accept;
	
			$file_id = $params['file_id'];
				
			$frs_file = rdfed_frsfile_get_object($file_id);
			if (!$frs_file) {
				session_redirect404();
			}
			
			$frs_release = $frs_file->getFRSRelease();
			$frs_package = $frs_release->getFRSPackage();
			$group = $frs_package->getGroup();
			
			$graph = new Graphite();
			$this->graphSetAdmsswNameSpaces($graph);

			// We only accept files in public and non-hidden project's packages
			if ($frs_package->isPublic() && $frs_package->getStatus() == 1) {
				$frs_file->saveToGraph($graph);
			}
			
			if ($accept == 'text/turtle') {
				$doc = $graph->serialize($serializer="Turtle");
			}
			if ($accept == 'application/rdf+xml') {
				$doc = $graph->serialize();
			}
	
			$params['content'] = $doc . "\n";
	
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
