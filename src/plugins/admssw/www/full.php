<?php
/**
 * admssw plugin script which produces a full list of public projects with all their details
*
* This file is (c) Copyright 2012 by Olivier BERGER, Institut Mines-Telecom
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
	
require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';

$pluginname = 'admssw';

$script = 'admssw_full';
$default_content_type = 'text/html';

// check for alternate representations (RDF content-types)
$content_type = util_negociate_alternate_content_types($script, $default_content_type);

$plugin = plugin_get_object($pluginname);

$documenturi = util_make_url('/plugins/'.$pluginname.'/full.php');

// page length
$pl = $plugin->getPagingLimit();

$projectsnum = $plugin->getProjectListSize();

$p = $plugin->process_paging_params_or_redirect($projectsnum, $pl);

if ($content_type != 'text/html') {
	header('Content-type: '. $content_type);
}
else {
	$HTML->header(array('title'=>_('Full ADMS.SW export'),'pagename'=>'admssw_full'));
	$HTML->printSoftwareMapLinks();
}
print $plugin->getProjectsListDisplay($documenturi, $content_type, $p, $pl, true);

if ($content_type == 'text/html') {
	$HTML->footer(array());
}
// $pageuri = '';
// $chunksize = null;
// $chunk = null;
// // if paging is requested
// if ($p > 0) {
// 	$chunksize = $pl;
// 	$chunk = $p;
// 	$pageuri = $documenturi . '?page='. (string)$p;
// }

// // process as in content_negociated_projects_list but with full details
// $graph = $plugin->getProjectListResourcesGraph($documenturi, true, $chunk, $chunksize);

// // if not HTML
// if($content_type != $default_content_type) {

// 	if ($p > 0) {
// 		$ns = $plugin->admsswNameSpaces();
// 		$conf = array(
// 				'ns' => $ns
// 		);
		
// 		$res = ARC2::getResource($conf);
// 		$res->setURI( $pageuri );
// 		rdfutils_setPropToUri($res, 'rdf:type', 'ldp:Page');
		
// 		if($p < ( (int) ($projectsnum / $pl) ) ) {
// 			$nextpageuri = $documenturi . '?page=' . (string) ($p + 1);
// 			rdfutils_setPropToUri($res, 'ldp:nextPage', $nextpageuri);
// 		}
// 		else {
// 			rdfutils_setPropToUri($res, 'ldp:nextPage', 'rdf:nil');
// 		}
// 		rdfutils_setPropToUri($res, 'ldp:pageOf', $documenturi);
		
// 		$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );
// 	}

// 	// We can support only RDF as RDF+XML or Turtle
// 	if ($content_type == 'text/turtle' || $content_type == 'application/rdf+xml') {
// 		header('Content-type: '. $content_type);
// 		if ($content_type == 'text/turtle') {
// 			print $graph->serialize($serializer="Turtle")."\n";
// 		}
// 		if ($content_type == 'application/rdf+xml') {
// 			print $graph->serialize()."\n";
// 		}
// 	}
// 	else {
// 		header('HTTP/1.1 406 Not Acceptable',true,406);
// 		print $graph->dumpText();
// 		exit(0);
// 	}
// } else {
// 	$HTML->header(array('title'=>_('Full ADMS.SW export'),'pagename'=>'admssw_full'));
// 	$HTML->printSoftwareMapLinks();
	
// 	echo '<p>'. sprintf( _('This script is meant to produce machine-readable RDF meta-data, in Turtle or RDF/XML formats, which can be obtained at <tt>%1$s</tt> as Turtle'), $documenturi).'<br />';
	
// 	$html_limit = '<span style="text-align:center;font-size:smaller">';
// 	$html_limit .= sprintf(_('<strong>%1$s</strong> projects in result set.'), $projectsnum);
// 	// only display pages stuff if there is more to display
// 	if ($projectsnum > $pl) {
// 		$html_limit .= trove_html_limit_navigation_box($documenturi, $projectsnum, $pl, $p);
// 	}
// 	$html_limit .= '</span>';
	
// 	print $html_limit;
	
// 	print $graph->dump();
	
// 	echo _('To access this RDF document, you may use, for instance :<br />');
// 	echo '<tt>$ curl -L -H "Accept: text/turtle" '. $documenturi .'</tt><br />';
	
// 	echo _('This may redirect to several pages documents in case of too big number of results (observing the LDP paging specifications).<br /><br />');
	
// 	echo _('Alternatively, if you are sure you want the full dump in one single document, use :<br />');
// 	echo '<tt>$ curl -H "Accept: text/turtle" "'. $documenturi .'?allatonce"</tt>';
	
// 	$HTML->footer(array());
// }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>