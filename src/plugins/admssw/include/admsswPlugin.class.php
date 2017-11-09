<?php

/**
 * admsswPlugin Class
 *
 * Copyright 2012-2013, Olivier Berger & Institut Mines-Telecom
 * Copyright 2013, Roland Mas
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

require_once 'common/include/TroveCat.class.php';
require_once 'common/frs/FRSFileType.class.php';
require_once $gfplugins.'admssw/common/RDFedFRSPackage.class.php' ;
require_once 'common/include/rdfutils.php';
include_once 'Graphite.php';

class admsswPlugin extends Plugin {

	public static $PAGING_LIMIT = 50;

	//var $trovecat_id_index; // cat_id to TroveCat instances
	var $trovecat_id_to_shortname;	// cat_id to shortname
	var $trovecat_id_to_path; // cat_id to path

	/**
	 * @param number $id plugin identifier
	 */
	function __construct($id=0) {
		parent::__construct($id);
		$this->name = "admssw";
		$this->text = _("ADMS.SW"); // To show in the tabs, use...
		$this->pkg_desc =
_("This plugin provides ADMS.SW additions to the DOAP RDF documents for
projects on /projects URLs with content-negotiation
(application/rdf+xml).");

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
				'rad' => 'http://www.w3.org/ns/rad#',
				'ldp' => 'http://www.w3.org/ns/ldp#'
		);

		//$this->trovecat_id_index = array();
		$this->trovecat_id_to_shortname = array();
		$this->trovecat_id_to_path = array();

		// Add the doaprdf plugin's namespaces
		$doaprdfplugin = plugin_get_object ("doaprdf");
		if ($doaprdfplugin == NULL)
		{
			// FIXME: constructor use of plugin_get_object
			// requires 'doaprdf' to be listed before
			// 'admssw' _in the plugins DB table_.
			return;
		}
		$ns = $doaprdfplugin->doapNameSpaces();
		foreach($ns as $s => $u)
		{
			if (! in_array($u, $this->ns)) {
				$this->ns[$u] = $s;
			}
		}

		$this->_addHook("project_rdf_metadata"); // will provide some RDF metadata for the project's DOAP profile to 'doaprdf' plugin
		$this->_addHook("alt_representations"); // declares a link to itself in the link+meta HTML headers of /projects and /softwaremap
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
		if ($php_self == '/softwaremap/trove_list.php') {
			$params['return'][] = '<link rel="alternate" type="application/rdf+xml" title="ADMS.SW RDF Data" href="'. util_make_url ("/plugins/admssw/trove.php") .'"/>';
			$params['return'][] = '<link rel="alternate" type="text/turtle" title="ADMS.SW RDF Data" href="'. util_make_url ("/plugins/admssw/trove.php") .'"/>';
		}
		elseif ($script_name == '/softwaremap') {
			$params['return'][] = '<link rel="alternate" type="application/rdf+xml" title="ADMS.SW RDF Data" href="'. util_make_url ("/projects") .'"/>';
			$params['return'][] = '<link rel="alternate" type="text/turtle" title="ADMS.SW RDF Data" href="'. util_make_url ("/projects") .'"/>';
		}
		elseif($script_name == '/projects') {
			$params['return'][] = '<link rel="alternate" type="application/rdf+xml" title="ADMS.SW RDF Data" href="'. util_make_url ($php_self) .'"/>';
			$params['return'][] = '<link rel="alternate" type="text/turtle" title="ADMS.SW RDF Data" href="'. util_make_url ($php_self) .'"/>';
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
		$params['ATTRS'][] = array('title' => _('ADMS.SW RDF meta-data about forge projects.'));
	}

	/**
	 * Add a line in the project infos widget in the project home
	 *
	 * @param array $params
	 */
	public function project_after_description (&$params) {
		$group_id = $params['group_id'];
		print '<br />'. sprintf( _('Preview <a href="%s">ADMS.SW meta-data</a> about the project'), util_make_url ('/plugins/'. $this->name .'/projectturtle.php?group_id='.$group_id));
	}

	//
	// Utility functions
	//

	/**
	 * Returns namespaces used in ADMS.SW
	 *
	 * @return array the namespaces associative array
	 */
	public function admsswNameSpaces() {
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
	public function htmlPreviewProjectAsTurtle($group_id) {

		$detailed = true;

		$graph = new Graphite();

		$this->graphSetAdmsswNameSpaces($graph);

		$resourceindex = $this->addProjectResourceToGraph($graph, $group_id, $detailed);

		return $graph->dump();
	}

	/**
	 * Provides an ARC2 resource index of a project's ADMS.SW meta-data
	 *
	 * @param int $group_id
	 */
	private function addProjectResourceToGraph(&$graph, $group_id, $detailed = false) {

		// part of the work is done by the doaprdf plugin, which will in turn call us back (see project_rdf_metadata)
		$doaprdfplugin = plugin_get_object ("doaprdf");

		$ns = $this->admsswNameSpaces();

		$resourceindex = $doaprdfplugin->getProjectResourceIndex($group_id, $ns, $detailed);

		// update the namespaces if they happen to get updated in between
		foreach($ns as $s => $u)
		{
			if (! in_array($u, $this->ns)) {
				$this->ns[$u] = $s;
			}
		}

		$count = $graph->addTriples( ARC2::getTriplesFromIndex($resourceindex) );

		return $graph;
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

		$detailed = false;
		if ($params['details'] == 'full') {
			$detailed = true;
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
		$trovecats = TroveCat::getProjectCats($group_id);
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

		if ($detailed) {
			$graph = new Graphite();
			$this->graphSetAdmsswNameSpaces($graph);

			$this->addProjectFrsResourcesToGraph($graph, $group_id);

			$subjects = $graph->allSubjects();
			foreach ($subjects as $subject) {
				$resource = $graph->resource( $subject );
				$triples = $resource->toArcTriples();

				$index = ARC2::getSimpleIndex($triples, false);

				$res = ARC2::getResource();
				$res->setIndex($index);

				$params['out_Resources'][] = $res;
			}
		}


	}


	//
	// Project list / SoftwareRepository related methods
	//

	/**
	 * Returns the number of projects in a project index
	 */
	public function getProjectListSize() {
		// same as for trove's full list
 		$projects = group_get_public_active_projects_asc();
		return count($projects);
	}

	/**
	 * Provides a Graphite graph for resource(s) representing the ADMS.SW SoftwareRepository
	 *
	 * @param string URI of the document to use
	 * @param bool are projects to be fully described or just a URI of their resource
	 * @param $chunk number of the chunk to be returned in case of paging
	 * @param $chunksize size of chunks in case of paging
	 */
    public function getProjectListResourcesGraph($documenturi, $detailed=false, $chunk=null, $chunksize=null) {

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
		$projects = group_get_public_active_projects_asc();

		if ( isset($chunk) && isset($chunksize) ) {
			// TODO : do some checks on $chunk $chunksize values
			// 			if ( ($chunk < 1) && ($chunksize >= 1) ) {
			// 				// error
			// 			}
			$projects_chunks = array_chunk($projects, $chunksize);
			$projects = $projects_chunks[$chunk-1];
		}

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
		foreach ($projects as $row_grp) {
				$group_id = $row_grp['group_id'];
				//$proj_uri = util_make_url_g(strtolower($row_grp['unix_group_name']),$row_grp['group_id']);
				$count = $this->addProjectResourceToGraph($graph, $row_grp['group_id'], $detailed);
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

	/*
	 * Returns the size of the pages for paged documents (project indexes or full projects dump)
	 */
	public function getPagingLimit() {
		return self::$PAGING_LIMIT;
	}

	/*
	 * Process the paging parameters and eventually redirect, ala LDP
	 *
	 * When there are too many projects to be displayed, it will redirect to the first page : ?page=1
	 * This can be overriden with the ?allatonce parameter
	 */
	public function process_paging_params_or_redirect($projectsnum, $pl) {

		$p = getIntFromRequest('page', 0);

		if ( null !== getStringFromRequest('allatonce', null)) {
			$pl = $projectsnum + 1;
			$p = 0;
		}

		// force paging if too many projects
		if ( ($projectsnum > $pl) && ! ($p > 0) ) {
			header("Location: ?page=1");
			header($_SERVER["SERVER_PROTOCOL"]." 303 See Other",true,303);
			exit;
		}

		// if paging is requested
		if ($p > 0) {
			$maxpage = ceil($projectsnum / $pl);
			if ($p > $maxpage) {
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found",true,404);
				printf("Page %d requested is beyond the maximum %d !", $p, $maxpage);
				exit;
			}
		}

		return $p;
	}

	/**
	 * Provides either an HTML preview looking like turtle, or plain RDF of the ADMS.SW SoftwareRepository meta-data
	 *
	 * @param	string	URL of the RDF document
	 * @param	string	expected content type for the document
	 * @param	int		page number. If null, means no paging but full document
	 * @param	int		page length : how many projects per page
	 * @param	bool	if has to provide full details about projects
	 * @param	string	URL of the HTML script if different than the RDF document
	 */
	public function getProjectsListDisplay($documenturi, $content_type, $p, $pl, $detailed=false, $scripturl=false) {

		$doc = '';

		if(! $scripturl) {
			$scripturl = $documenturi;
		}

		$pageuri = '';
		$chunksize = null;
		$chunk = null;
		// if paging is requested
		if ($p > 0) {
			$chunksize = $pl;
			$chunk = $p;
			$pageuri = $documenturi . '?page='. (string)$p;
		}

		$projectsnum = $this->getProjectListSize();

		// process as in content_negociated_projects_list but with full details
		$graph = $this->getProjectListResourcesGraph($documenturi, $detailed, $chunk, $chunksize);

		// if not HTML
		if($content_type != 'text/html') {

			if ($p > 0) {
				$ns = $this->admsswNameSpaces();
				$conf = array(
						'ns' => $ns
				);

				$res = ARC2::getResource($conf);
				$res->setURI( $pageuri );
				rdfutils_setPropToUri($res, 'rdf:type', 'ldp:Page');

				if( $p < ceil($projectsnum / $pl) ) {
					$nextpageuri = $documenturi . '?page=' . (string) ($p + 1);
					rdfutils_setPropToUri($res, 'ldp:nextPage', $nextpageuri);
				}
				else {
					rdfutils_setPropToUri($res, 'ldp:nextPage', 'rdf:nil');
				}
				rdfutils_setPropToUri($res, 'ldp:pageOf', $documenturi);

				$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );
			}

			// We can support only RDF as RDF+XML or Turtle
			if ($content_type == 'text/turtle' || $content_type == 'application/rdf+xml') {
				if ($content_type == 'text/turtle') {
					$doc = $graph->serialize($serializer="Turtle")."\n";
				}
				if ($content_type == 'application/rdf+xml') {
					$doc = $graph->serialize()."\n";
				}
			}
			else {
				header('HTTP/1.1 406 Not Acceptable',true,406);
				print $graph->dumpText();
				exit(0);
			}
		} else {
			// HTML

			$doc = '<p>'. _('The following is a preview of (machine-readable) RDF meta-data, in Turtle format (see at the bottom for more details)') .'<br />';

			$html_limit = '<span style="text-align:center;font-size:smaller">';
			$html_limit .= sprintf(_('<strong>%d</strong> projects in result set.'), $projectsnum);
			// only display pages stuff if there is more to display
			if ($projectsnum > $pl) {
				$html_limit .= html_trove_limit_navigation_box($scripturl, $projectsnum, $pl, $p);
			}
			$html_limit .= '</span>';

			$doc .= $html_limit;

			$doc .= $graph->dump();

			$doc .= _('To access this RDF document, you may use, for instance:');
			$doc .= '<br />';
			$doc .= '<kbd>$ curl -L -H "Accept: text/turtle" '. $documenturi .'</kbd><br />';

			$doc .= _('This may redirect to several pages documents in case of too big number of results (observing the LDP paging specifications).');
			$doc .= '<br /><br />';

			$doc .= _('Alternatively, if you are sure you want the full dump in one single document, use:');
			$doc .= '<br />';
			$doc .= '<kbd>$ curl -H "Accept: text/turtle" "'. $documenturi .'?allatonce"</kbd>';

		}
		return $doc;
	}

	/**
	 * Outputs the public projects list as ADMS.SW for /projects
	 *
	 * @param unknown_type $params
	 *
	 * This has a counterpart in /plugins/admssw/projectsturtle.php which previews the Turtle as HTML
	 */
	public function content_negociated_projects_list (&$params) {

		$accept = $params['accept'];

		// we are asked for RDF either as RDF+XML or Turtle
		if($accept == 'application/rdf+xml' || $accept == 'text/turtle') {

 			// We will return RDF
 			$params['content_type'] = $accept;

 			$documenturi = util_make_url ("/projects");

 			// page length
 			$pl = admsswPlugin::$PAGING_LIMIT;

 			$projectsnum = $this->getProjectListSize();

 			$p = $this->process_paging_params_or_redirect($projectsnum, $pl);

 			$doc = $this->getProjectsListDisplay($documenturi, $accept, $p, $pl);

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

		$rootcats = TroveCat::getAllRoots();

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
	public function htmlPreviewTroveCatsAsTurtle() {
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

		$group = group_get_object($group_id);

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
