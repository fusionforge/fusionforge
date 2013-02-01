<?php
/**
 * FusionForge ADMS.SW plugin - RDF serializable extension of FRSRelease  
 *
 * Copyright 2013, Olivier Berger and Institut Mines-Telecom
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

// This class overloads FRSRelease to add RDF representation, based on ADMS.SW, using ARC2 and Graphite

include_once('RDFedFRSFile.class.php');

/**
 *	  Factory method which creates a RDFedFRSRelease from an release id
 *
 *	  @param int	  The release id
 *	  @param array	The result array, if it's passed in
 *	  @return object  RDFedFRSRelease object
 */
function rdfed_frsrelease_get_object($release_id, $data = false) {
	global $RDFED_FRSRELEASE_OBJ;
	if (!isset($RDFED_FRSRELEASE_OBJ['_'.$release_id.'_'])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res = db_query_params ('SELECT * FROM frs_release WHERE release_id=$1',
					array ($release_id)) ;
			if (db_numrows($res)<1 ) {
				$RDFED_FRSRELEASE_OBJ['_'.$release_id.'_']=false;
				return false;
			}
			$data = db_fetch_array($res);
		}
		$FRSPackage = rdfed_frspackage_get_object($data['package_id']);
		$RDFED_FRSRELEASE_OBJ['_'.$release_id.'_']= new RDFedFRSRelease($FRSPackage,$data['release_id'],$data);
	}
	return $RDFED_FRSRELEASE_OBJ['_'.$release_id.'_'];
}

class RDFedFRSRelease extends FRSRelease {
	
	var $uri;

	/**
	 *	newFRSFile - generates a RDFedFRSFile, overloading base class' generator
	 *
	 *  @param  string	FRS file identifier 
	 *  @param  array	fetched data from the DB
	 *	@return	RDFedFRSFile	new RDFedFRSFile object.
	 */
	protected function newFRSFile($file_id, $data) {
		return new RDFedFRSFile($this, $file_id, $data);
	}
	
	/**
	 *	getUri - constructs the resource URI
	 *
	 *	@return	string	resource URI
	 */
	function getUri() {
		if(!$this->uri) {
			$frs_package = $this->getFRSPackage();
			$group = $frs_package->getGroup();
			
			$projectname = $group->getUnixName();
			$package_name = $frs_package->getFileName();
			$release_name = $this->getFileName();
			
			$this->uri = util_make_url ('/frs/'.$projectname.'/'.$package_name.'/'.$release_name);
		}
		return $this->uri;
	}
	
	/**
	 *	saveToGraph - updates a Graphite graph to add the resource's triples
	 *
	 *  @param  Graphite	a Graphite graph to be updated
	 */
	public function saveToGraph(&$graph) {
	
		// Construct an ARC2_Resource containing the FRSRelease RDF description
		$ns = $graph->ns;
		
		$conf = array(
				'ns' => $ns
		);
	
		$frs_package = $this->getFRSPackage();
		$group = $frs_package->getGroup();
		
		$projectname = $group->getUnixName();
	
		$release_name = $this->getFileName();
	
		$package_name = $frs_package->getFileName();
	
		$res = ARC2::getResource($conf);
		
		$res->setURI($this->getUri());
			
		// $res->setRel('rdf:type', 'admssw:SoftwareRelease');
		rdfutils_setPropToUri($res, 'rdf:type', 'admssw:SoftwareRelease');
	
		$res->setProp('rdfs:label', $package_name.' '.$release_name );
		$res->setProp('doap:revision', $release_name );
	
		rdfutils_setPropToXSDdateTime($res, 'dcterms:created', date('c', $this->getReleaseDate()));
	
		$res->setProp('dcterms:description', $this->getNotes());
	
		$res->setProp('schema:releaseNotes', $this->getChanges());
	
		$projecturi = util_make_url ('/projects/'. $projectname .'/#project');
	
		rdfutils_setPropToUri($res, 'admssw:project', $projecturi);
	
		rdfutils_setPropToUri($res, 'dcterms:publisher', $projecturi);
	
		rdfutils_setPropToUri($res, 'adms:relatedWebPage', util_make_url ('/frs/shownotes.php?release_id='.$this->getID()) );
	
		rdfutils_setPropToUri($res, 'dcterms:isPartOf', util_make_url ('/projects#repo'));
	
		$file_uris = array();
		$frs_files = $this->getFiles();
		foreach($frs_files as $frs_file) {
			$file_uris[] = $frs_file->getUri();
		}
		rdfutils_setPropToUri($res, 'admssw:package', $file_uris);
	
		$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );
	
		foreach($frs_files as $frs_file) {
			$frs_file->saveToGraph($graph);
		}
	
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
