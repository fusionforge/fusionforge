<?php 
/**
 * This file is (c) Copyright 2009 by Sabri LABBENE, Institut TELECOM
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * This program has been developed in the frame of the HELIOS
 * project with financial support of its funders.
 *
 */

class CompactRessourceView {
	
	public function __construct() {
		
	}
	
	public function CompactUserView($params){
		$doc = "<?xml version=\"1.0\" encoding=\"UTF-8\"?> \n" .
			"<rdf:RDF \n" .
			"   xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" \n" .
			"   xmlns:dcterms=\"http://purl.org/dc/terms/\" \n" .
			"   xmlns:oslc=\"http://open-services.net/ns/core#\"> \n" .
			" <oslc:Compact \n" .
			"   rdf:about=\"" . $params['userUri'] . "\"> \n" .
			"   <dcterms:title>" . $params['title'] . "</dcterms:title> \n" .
			"   <oslc:shortTitle>" . $params['shortTitle'] . "</oslc:shortTitle> \n" .
			"   <oslc:icon rdf:resource=\"" . $params['iconUrl'] . "\" /> \n" .
			"   <oslc:smallPreview> \n" .
			"      <oslc:Preview> \n" .
			"         <oslc:document rdf:resource=\"" . $params['userCompactUri'] . "\" /> \n" .
			"         <oslc:hintWidth>500px</oslc:hintWidth> \n" .
			"         <oslc:hintHeight>120px</oslc:hintHeight> \n" .
			"      </oslc:Preview> \n" .
			"   </oslc:smallPreview> \n" .
			"   <oslc:largePreview> \n" .
			"      <oslc:Preview> \n" .
			"         <oslc:document rdf:resource=\"" . $params['lgUrl'] . "\" /> \n" .
			"         <oslc:hintWidth>500px</oslc:hintWidth> \n" .
			"         <oslc:hintHeight>500px</oslc:hintHeight> \n" .
			"      </oslc:Preview> \n" .
			"   </oslc:largePreview> \n" .
			" </oslc:Compact> \n" .
			"</rdf:RDF>";
		return $doc;
	}
	
	public function CompactChangeRequestView($params){
				$doc = "<?xml version=\"1.0\" encoding=\"UTF-8\"?> \n" .
			"<rdf:RDF \n" .
			"   xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" \n" .
			"   xmlns:dcterms=\"http://purl.org/dc/terms/\" \n" .
			"   xmlns:oslc=\"http://open-services.net/ns/core#\"> \n" .
			" <oslc:Compact \n" .
			"   rdf:about=\"" . $params['resourceUri'] . "\"> \n" .
			"   <dcterms:title>" . $params['title'] . "</dcterms:title> \n" .
			"   <oslc:shortTitle>" . $params['shortTitle'] . "</oslc:shortTitle> \n" .
			"   <oslc:icon rdf:resource=\"" . $params['iconUrl'] . "\" /> \n" .
			"   <oslc:smallPreview> \n" .
			"      <oslc:Preview> \n" .
			"         <oslc:document rdf:resource=\"" . $params['smUrl'] . "\" /> \n" .
			"         <oslc:hintWidth>500px</oslc:hintWidth> \n" .
			"         <oslc:hintHeight>120px</oslc:hintHeight> \n" .
			"      </oslc:Preview> \n" .
			"   </oslc:smallPreview> \n" .
			"   <oslc:largePreview> \n" .
			"      <oslc:Preview> \n" .
			"         <oslc:document rdf:resource=\"" . $params['lgUrl'] . "\" /> \n" .
			"         <oslc:hintWidth>500px</oslc:hintWidth> \n" .
			"         <oslc:hintHeight>500px</oslc:hintHeight> \n" .
			"      </oslc:Preview> \n" .
			"   </oslc:largePreview> \n" .
			" </oslc:Compact> \n" .
			"</rdf:RDF>";
		return $doc;
	}
	
	
}

?>