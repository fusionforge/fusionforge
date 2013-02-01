<?php
/**
 * FusionForge ADMS.SW plugin - RDF serializable extension of FRSPackage  
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

// This class overloads FRSPackage to add RDF representation, based on ADMS.SW, using ARC2 and Graphite

include_once('RDFedFRSRelease.class.php');

/**
 *	  Factory method which creates a RDFedFRSPackage from an project/group
 *
 *	  @param Group	  The project object
 *	  @return object  RDFedFRSPackage object
 */
function get_rdfed_frs_packages($Group) {
	$ps = array();
	$res = db_query_params ('SELECT * FROM frs_package WHERE group_id=$1',
			array ($Group->getID())) ;
	if (db_numrows($res) > 0) {
		while($arr = db_fetch_array($res)) {
			$ps[]=new RDFedFRSPackage($Group,$arr['package_id'],$arr);
		}
	}
	return $ps;
}


class RDFedFRSPackage extends FRSPackage {
	
	/**
	 *	newFRSRelease - generates a RDFedFRSRelease, overloading base class' generator
	 *
	 *  @param  string	FRS release identifier 
	 *  @param  array	fetched data from the DB
	 *	@return	RDFedFRSRelease	new RDFedFRSRelease object.
	 */
	protected function newFRSRelease($release_id, $data) {
		return new RDFedFRSRelease($this,$release_id, $data);
	}
	
	/**
	 *	getUri - constructs the resource URI
	 *
	 *	@return	string	resource URI
	 */
	static function getUri($projectname, $package_name) {
		return util_make_url ('/frs/'.$projectname.'/'.$package_name);
	}
	
	/**
	 *	saveToGraph - updates a Graphite graph to add the resource's triples
	 *
	 *  @param  Graphite	a Graphite graph to be updated
	 */
	public function saveToGraph(&$graph) {
		
		$ns = $graph->ns;
			
		$conf = array(
				'ns' => $ns
		);
		
		$group = $this->getGroup();
		
		$projectname = $group->getUnixName();
			
		$package_name = $this->getFileName();
			
		$res = ARC2::getResource($conf);
					
		$res->setURI(RDFedFRSPackage::getUri($projectname, $package_name));
		
		// We don't type the object, which is only there for information, and does not correspond to any ADMS.SW entity
		// $res->setRel('rdf:type', 'fusionforge:ReleaseSeries');
		//rdfutils_setPropToUri($res, 'rdf:type', 'fusionforge:ReleaseSeries');
			
		$res->setProp('rdfs:label', sprintf( _('%1$s release series of project %2$s'), $package_name, $projectname) );
		
		$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );
			
		$frs_releases = $this->getReleases();
		foreach($frs_releases as $frs_release) {
			if( $frs_release->getStatus() == 1 ) {
				$frs_release->saveToGraph($graph);
			}
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
