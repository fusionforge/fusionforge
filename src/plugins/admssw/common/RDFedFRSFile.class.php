<?php
/**
 * FusionForge ADMS.SW plugin - RDF serializable extension of FRSFile  
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

// This class overloads FRSFile to add RDF representation, based on ADMS.SW, using ARC2 and Graphite

require_once $gfcommon.'frs/FRSFile.class.php';
require_once 'RDFedFRSRelease.class.php';

/**
 *	  Factory method which creates a RDFedFRSFile from an file id
 *
 *	  @param int	  The file id
 *	  @param array	The result array, if it's passed in
 *	  @return object  RDFedFRSFile object
 */
function &rdfed_frsfile_get_object($file_id, $data=false) {
	global $RDFED_FRSFILE_OBJ;
	if (!isset($RDFED_FRSFILE_OBJ['_'.$file_id.'_'])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res = db_query_params ('SELECT * FROM frs_file WHERE file_id=$1',
					array ($file_id)) ;
			if (db_numrows($res)<1 ) {
				$RDFED_FRSFILE_OBJ['_'.$file_id.'_']=false;
				return false;
			}
			$data = db_fetch_array($res);
		}
		$FRSRelease =& rdfed_frsrelease_get_object($data['release_id']);
		$RDFED_FRSFILE_OBJ['_'.$file_id.'_']= new RDFedFRSFile($FRSRelease,$data['file_id'],$data);
	}
	return $RDFED_FRSFILE_OBJ['_'.$file_id.'_'];
}


class RDFedFRSFile extends FRSFile {

	/**
	 *	getDownloadUrl - constructs the download URL
	 *
	 *	@return	string	URL
	 */
	public function getDownloadUrl() {
		return util_make_url('/frs/download.php/file/'.$this->getID().'/'.rawurlencode($this->getName()));
	}
	
	/**
	 *	getUri - constructs the resource URI
	 *
	 *	@return	string	resource URI
	 */
	public function getUri() {
		return $this->getDownloadUrl().'#package';
	}
	
	/**
	 *	saveToGraph - updates a Graphite graph to add the resource's triples
	 *
	 *  @param  Graphite	a Graphite graph to be updated
	 */
	public function saveToGraph(&$graph) {
		
		// Construct an ARC2_Resource containing the RDFedFRSFile RDF description
		$ns = $graph->ns;
			
		$conf = array(
				'ns' => $ns
		);
			
			
		$res = ARC2::getResource($conf);
			
		$frs_release = $this->getFRSRelease();
		$frs_package = $frs_release->getFRSPackage();
		$group = $frs_package->getGroup();
		
		$frs_file_name = $this->getName();
		
		$file_uri = $this->getUri();
		$res->setURI($file_uri);
		
		// $res->setRel('rdf:type', 'admssw:SoftwarePackage');
		rdfutils_setPropToUri($res, 'rdf:type', 'admssw:SoftwarePackage');
		
		$res->setProp('rdfs:label', $frs_file_name);
		$description = $frs_file_name. _(', part of ') .$frs_package->getName(). ' ' .$frs_release->getName();
		$res->setProp('dcterms:description', $description);
		
		rdfutils_setPropToUri($res, 'schema:downloadUrl', $this->getDownloadUrl());
		rdfutils_setPropToXSDdateTime($res, 'dcterms:created', date('c', $this->getReleaseTime()));
		$res->setProp('schema:fileSize', $this->getSize());
		
		$frs_filetype_id = $this->getTypeID();
		$frs_filetype = new FRSFileType($frs_filetype_id);
		
		// This is hackish... ultimately, FusionForge should support proper mime-types
		$mime_type = '';
		$frs_filetype_name = $frs_filetype->getName();
		switch ($frs_filetype_name) {
			case '.deb':
				$mime_type = 'application/x-deb';
				break;
			case '.rpm':
				$mime_type = 'application/x-rpm';
				break;
			case '.zip':
				$mime_type = 'application/zip';
				break;
			case '.bz2':
				$mime_type = 'application/x-bzip2';
				break;
			case '.gz':
				$mime_type = 'application/x-gzip';
				break;
			case '.jpg':
				$mime_type = 'image/jpeg';
				break;
			case 'text':
				$mime_type = 'text/plain';
				break;
			case 'html':
				$mime_type = 'text/html';
				break;
			case 'pdf':
				$mime_type = 'application/pdf';
				break;
				/* we won't treat these
				 Source .zip
				Source .bz2
				Source .gz
				Source .rpm
				Other Source File
				Other
				*/
			default:
				$mime_type = 'application/binary';
				break;
		}
		rdfutils_setPropToUri($res, 'dcterms:format', 'http://purl.org/NET/mediatypes/'. $mime_type);
		
		rdfutils_setPropToUri($res, 'admssw:release', $frs_release->getUri());
		
		rdfutils_setPropToUri($res, 'dcterms:license', $this->getDownloadUrl().'#unspecified_license');
		
		$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );
		
		// Add a resource for the license, that is an explicit "unspecified license", rather than not setting it... the ADMS.SW seem to require a mandatory license
		$res = ARC2::getResource($conf);
		$res->setURI($this->getDownloadUrl().'#unspecified_license');
		$res->setProp('rdfs:label', 'Unspecified license (unavailable meta-data in the FusionForge File Release System)');
		
		$count = $graph->addTriples( ARC2::getTriplesFromIndex($res->index) );
		
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
