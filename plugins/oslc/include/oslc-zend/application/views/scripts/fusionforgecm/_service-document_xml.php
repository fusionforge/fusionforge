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

// Generate an OSLC-CM V2 Service Description document

function project_to_service_description($base_url, $project, $tracker) {

	$doc = new DOMDocument();
	$doc->formatOutput = true;

	$root = $doc->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF");
	$root = $doc->appendChild($root);

	// namespaces
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', 'http://purl.org/dc/terms/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:foaf', 'http://xmlns.com/foaf/0.1/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc', 'http://open-services.net/ns/core#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc_cm', 'http://open-services.net/ns/cm#');

	$provider = $doc->createElement("oslc:ServiceProvider");
	// rdf:about
	$provider->setAttribute("rdf:about", $base_url.'/cm/oslc-cm-service/'.$project.'/tracker/'.$tracker);

	// rdf:type

	// oslc:Publisher ressource inside a dcterms:publisher node.
	$publishernode = $doc->createElement("dcterms:publisher");
	$publishernodecontent = $doc->createElement("oslc:Publisher");
	$publishernodecontentid = $doc->createElement("dcterms:identifier", $base_url);
	$publishernodecontenttitle = $doc->createElement("dcterms:title", "FusionForge OSLC V2 plugin");
	$publishernodecontent->appendChild($publishernodecontentid);
	$publishernodecontent->appendChild($publishernodecontenttitle);
	$publishernode->appendChild($publishernodecontent);
	// Add created dcterms:publisher node in the ServiceProvider node.
	$provider->appendChild($publishernode);

	// dcterms:title
	$title = $doc->createElement("dcterms:title","OSLC-CM V2 service description document");
	$provider->appendChild($title);

	//dcterms:description
	$desc = $doc->createElement("dcterms:description","FusionForge Tracker services");
	$provider->appendChild($desc);

	/**
	 * Services description
	 */

	$servicenode = $doc->createElement("oslc:service");

	$service = $doc->createElement("oslc:Service");

	// oslc:domain
	$sdomain = $doc->createElement("oslc:domain");
	$sdomain->setAttribute("rdf:resource", "http://open-services.net/ns/cm#");
	$service->appendChild($sdomain);

	// Creation Factory.
	$cfactnode = $doc->createElement("oslc:creationFactory");
	$cfact = $doc->createElement("oslc:CreationFactory");
	
	$cfacttitle = $doc->createElement("dcterms:title", "Location for creation of change Requests with a POST HTTP request");
	$cfactlabel = $doc->createElement("oslc:label", "New Tracker items Creation");
	$cfactcreation = $doc->createElement("oslc:creation");
	$cfactcreation->setAttribute("rdf:resource", $base_url.'/cm/project/'.$project.'/tracker/'.$tracker);
	$cfact->appendChild($cfacttitle);
	$cfact->appendChild($cfactlabel);
	$cfact->appendChild($cfactcreation);

	$cfactnode->appendChild($cfact);
	$service->appendChild($cfactnode);

	// Query capabilities.
	$qcnode = $doc->createElement("oslc:queryCapability");
	$qc = $doc->createElement("oslc:QueryCapability");

	$qctitle = $doc->createElement("dcterms:title", "GET-Based Tracker items query");
	$qclabel = $doc->createElement("oslc:label", "Tracker items query");
	$qcqbase = $doc->createElement("oslc:queryBase");
	$qcqbase->setAttribute("rdf:resource",$base_url.'/cm/project/'.$project.'/tracker/'.$tracker);
	$qc->appendChild($qctitle);
	$qc->appendChild($qclabel);
	$qc->appendChild($qcqbase);

	$qcnode->appendChild($qc);
	$service->appendChild($qcnode);

	// Delegated Selection UI.
	$sD = $doc->createElement("oslc:selectionDialog");
	$d = $doc->createElement("oslc:Dialog");
	$dtitle = $doc->createElement("dcterms:title", "Change Requests Selection Dialog");
	$dlabel = $doc->createElement("oslc:label", "Tracker items selection UI");
	$ddialog = $doc->createElement("oslc:dialog", $base_url.'/cm/project/'.$project.'/tracker/'.$tracker.'/ui/selection');
	$dwidth = $doc->createElement("oslc:hintWidth", "800px");
	$dheight = $doc->createElement("oslc:hintHeight", "600px");
	$d->appendChild($dtitle);
	$d->appendChild($dlabel);
	$d->appendChild($ddialog);
	$d->appendChild($dwidth);
	$d->appendChild($dheight);
	$sD->appendChild($d);
	$service->appendChild($sD);

	// Delegated Creation UI.
	$cD = $doc->createElement("oslc:creationDialog");
	$dialog = $doc->createElement("oslc:Dialog");
	$dialogtitle = $doc->createElement("dcterms:title", "Change Requests Creation Dialog");
	$dialoglabel = $doc->createElement("oslc:label", "Tracker items creation UI");
	$dialogdialog = $doc->createElement("oslc:dialog", $base_url.'/cm/project/'.$project.'/tracker/'.$tracker.'/ui/creation');
	$dialogwidth = $doc->createElement("oslc:hintWidth", "800px");
	$dialogheight = $doc->createElement("oslc:hintHeight", "600px");
	$dialog->appendChild($dialogtitle);
	$dialog->appendChild($dialoglabel);
	$dialog->appendChild($dialogdialog);
	$dialog->appendChild($dialogwidth);
	$dialog->appendChild($dialogheight);
	$cD->appendChild($dialog);
	$service->appendChild($cD);

	$servicenode->appendChild($service);

	$provider->appendChild($servicenode);

	$root->appendChild($provider);

	return $doc->saveXML();
}
