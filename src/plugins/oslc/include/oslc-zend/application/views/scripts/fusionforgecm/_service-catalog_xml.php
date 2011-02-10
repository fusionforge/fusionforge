<?php

/*
 * This file is (c) Copyright 2009 by Olivier BERGER & Sabri LABBENE, Institut
 * TELECOM
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

/* $Id$ */

// Generate an OSLC Core V2 ServiceProviderCatalog that lists trackers inside a FusionForge project as OSLC-CM Service Providers.
function project_trackers_to_service_catalog($server_url, $base_url, $trackers, $project) {
	$doc = new DOMDocument();
	$doc->formatOutput = true;
	
	$root = $doc->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF");
	$root = $doc->appendChild($root);
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', 'http://purl.org/dc/terms/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:foaf', 'http://xmlns.com/foaf/0.1/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc', 'http://open-services.net/ns/core#');

	$provider = $doc->createElement("oslc:ServiceProvider");
	$provider->setAttribute("rdf:about", $base_url.'/cm/oslc-cm-services/'.$project);
	
	// Title of the ServiceProvider.
	$titlenode = $doc->createElement("dcterms:title", "FusionForge OSLC Core V2 ServiceProvider corresponding to project ".$project);
	$provider->appendChild($titlenode);
	
	// Description of the ServiceProvider.
	$descriptionnode = $doc->createElement("dcterms:description", "Lists all trackers as Service Providers");
	$provider->appendChild($descriptionnode);
	
	// Add rdf:type ressource to the ServiceProvider node.
	$rdftype = $doc->createElement("rdf:type");
	$rdftype->setAttribute("rdf:resource", 'http://open-services.net/ns/core#ServiceProvider');
	$provider->appendChild($rdftype);
	
	// Add oslc:Publisher ressource inside a dcterms:publisher node.
	$publishernode = $doc->createElement("dcterms:publisher");
	$publishernodecontent = $doc->createElement("oslc:Publisher");
	$publishernodecontentid = $doc->createElement("dcterms:identifier", $base_url);
	$publishernodecontenttitle = $doc->createElement("dcterms:title", "FusionForge OSLC V2 plugin");
	$publishernodecontent->appendChild($publishernodecontentid);
	$publishernodecontent->appendChild($publishernodecontenttitle);
	$publishernode->appendChild($publishernodecontent);
	// Add created dcterms:publisher node in the ServiceProvider node.
	$provider->appendChild($publishernode);
	
	// Service Provider details
	$project_trackers_url = $server_url."/tracker/?group_id=".$project;
	$spdetails = $doc->createElement("oslc:details");
	$spdetails->setAttribute("rdf:resource", htmlentities($project_trackers_url));
	$provider->appendChild($spdetails);
	$root->appendChild($provider);

	// We list trackers as Services or ServiceProvider (s) ???????????
	foreach ($trackers as $tracker) {
		// oslc:service node.
		$service = $doc->createElement("oslc:service");
		$service->setAttribute("rdf:about", $base_url.'/cm/oslc-cm-service/'.$tracker['group_id'].'/tracker/'.$tracker['id']);

		// dcterms:title
		$stitle = $doc->createElement("dcterms:title", "OSLC-CM Service for ".$tracker['name']);
		$service->appendChild($stitle);

		// dcterms:description
		$sdesc = $doc->createElement("dcterms:description", $tracker['description']);
		$service->appendChild($sdesc);

		// rdf:type
		$rdftype = $doc->createElement("rdf:type");
		$rdftype->setAttribute("rdf:resource", 'http://open-services.net/ns/core#Service');
		$service->appendChild($rdftype);
		
		// oslc:domain
		$sdomain = $doc->createElement("oslc:domain");
		$sdomain->setAttribute("rdf:resource", "http://open-services.net/ns/cm#");
		$service->appendChild($sdomain);
		
		// oslc:details
		$tracker_url = $server_url."/tracker/index.php?group_id=".$tracker['group_id']."&atid=".$tracker['id'];
		$sdetails = $doc->createElement("oslc:details");
		$sdetails->setAttribute("rdf:resource", htmlentities($tracker_url));
		$service->appendChild($sdetails);
		
		$provider->appendChild($service);
		$root->appendChild($provider);
		
	}
	// A service provider should mention at least one (empty?) service.
	if(count($trackers) == 0){
		$service = $doc->createElement("oslc:service");
		$provider->appendChild($service);
		$root->appendChild($provider);
	}
	return $doc->saveXML();
}


// Generate an OSLC Core V2 ServiceProviderCatalog that lists projects as OSLC Service Providers.
function projects_to_service_catalog($base_url, $projects) {

	$doc = new DOMDocument();
	$doc->formatOutput = true;

	// Generate namespaces for root rdf:RDF node.
	$root = $doc->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF");
	$root = $doc->appendChild($root);
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', 'http://purl.org/dc/terms/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:foaf', 'http://xmlns.com/foaf/0.1/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc', 'http://open-services.net/ns/core#');

	$catalog = $doc->createElement("oslc:ServiceProviderCatalog");
	$catalog->setAttribute("rdf:about", $base_url.'/cm/oslc-services/');
	
	// Title of the ServiceProviderCatalog.
	$titlenode = $doc->createElement("dcterms:title", "FusionForge OSLC Core V2 Service Provider Catalog");
	$catalog->appendChild($titlenode);
	
	// Description of the ServiceProviderCatalog.
	$descriptionnode = $doc->createElement("dcterms:description", "Lists all projects as Service (trackers) Providers");
	$catalog->appendChild($descriptionnode);
	
	// Add rdf:type ressource to the ServiceProviderCatalog node.
	$rdftype = $doc->createElement("rdf:type");
	$rdftype->setAttribute("rdf:resource", 'http://open-services.net/ns/core#ServiceProviderCatalog');
	$catalog->appendChild($rdftype);
	
	// Add oslc:Publisher ressource inside a dcterms:publisher node.
	$publishernode = $doc->createElement("dcterms:publisher");
	$publishernodecontent = $doc->createElement("oslc:Publisher");
	$publishernodecontentid = $doc->createElement("dcterms:identifier", $base_url);
	$publishernodecontenttitle = $doc->createElement("dcterms:title", "FusionForge OSLC V2 plugin");
	$publishernodecontent->appendChild($publishernodecontentid);
	$publishernodecontent->appendChild($publishernodecontenttitle);
	$publishernode->appendChild($publishernodecontent);
	// Add created dcterms:publisher node in the ServiceProviderCatalog node.
	$catalog->appendChild($publishernode);
	 
	$root->appendChild($catalog);
	
	foreach ($projects as $proj) {
		$sp = $doc->createElement("oslc:ServiceProvider");
		$sp->setAttribute("rdf:about", $base_url.'/cm/oslc-cm-services/'.$proj['id']);
		
		// dcterms:title
		$sptitle = $doc->createElement("dcterms:title", "Project: ".$proj["name"]);
		$sp->appendChild($sptitle);
		
		// dcterms:description
		$spdescription = $doc->createElement("dcterms:description", "FusionForge project ".$proj['name']." as an OSLC-CM ServiceProvider");
		$sp->appendChild($spdescription);
		
		// rdf:type
		$rdftype = $doc->createElement("rdf:type");
		$rdftype->setAttribute("rdf:resource", 'http://open-services.net/ns/core#ServiceProvider');
		$sp->appendChild($rdftype);
		
		// dcterms:publisher
		$sppublisher = $doc->createElement("dcterms:publisher");
		$sppublishercontent = $doc->createElement("oslc:Publisher");
		$sppublishercontentid = $doc->createElement("dcterms:identifier", $base_url);
		$sppublishercontenttitle = $doc->createElement("dcterms:title", "FusionForge OSLC V2 plugin");
		$sppublishercontent->appendChild($sppublishercontentid);
		$sppublishercontent->appendChild($sppublishercontenttitle);
		$sppublisher->appendChild($sppublishercontent);
		$sp->appendChild($sppublisher);

		// ServiceProvider should lis at least one oslc:service. 
		// Telling about the oslc:domain of the service is mandatory. 
		$service = $doc->createElement("oslc:Service");
		$servicedomain = $doc->createElement("oslc:domain", "http://open-services.net/ns/core#Service");
		$service->appendChild($servicedomain);
		$sp->appendChild($service);

		$catalog->appendChild($sp);
		$root->appendChild($catalog);
	}
	return $doc->saveXML();
}
