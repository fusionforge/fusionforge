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

	// rdf:about
	$rdfabout = $doc->createElement("rdf:about", $base_url.'/cm/oslc-cm-service/'.$project.'/tracker/'.$tracker);
	$root->appendChild($rdfabout);

	// rdf:type
	$rdftype = $doc->createElement("rdf:type");
	$rdftyperessource = $doc->createElement("rdf:ressource", "http://open-services.net/ns/core#Service");

	// oslc:Publisher ressource inside a dcterms:publisher node.
	$publishernode = $doc->createElement("dcterms:publisher");
	$publishernodecontent = $doc->createElement("oslc:Publisher");
	$publishernodecontentid = $doc->createElement("dcterms:identifier", $base_url);
	$publishernodecontenttitle = $doc->createElement("dcterms:title", "FusionForge OSLC V2 plugin");
	$publishernodecontent->appendChild($publishernodecontentid);
	$publishernodecontent->appendChild($publishernodecontenttitle);
	$publishernode->appendChild($publishernodecontent);
	// Add created dcterms:publisher node in the ServiceProvider node.
	$root->appendChild($publishernode);

	// dcterms:title
	$title = $doc->createElement("dcterms:title","OSLC-CM V2 service description document");
	$root->appendChild($title);

	//dcterms:description
	$desc = $doc->createElement("dcterms:description","FusionForge Tracker services");
	$root->appendChild($desc);

	/**
	 * Services description
	 */

	$service = $doc->createElement("oslc:service");

	// oslc:domain
	$sdomain = $doc->createElement("oslc:domain");
	$sdomainressource = $doc->createElement("rdf:ressource", "http://open-services.net/ns/cm#");
	$sdomain->appendChild($sdomainressource);
	$service->appendChild($sdomain);

	// Creation Factory.
	$cfact = $doc->createElement("creationFactory");
	$cfacttitle = $doc->createElement("dcterms:title", "Location for creation of change Requests with a POST HTTP request");
	$cfactlabel = $doc->createElement("oslc_label", "New Tracker items Creation");
	$cfactcreation = $doc->createElement("oslc:creation");
	$cfactcreationressource = $doc->createElement("rdf:ressource", $base_url.'/cm/project/'.$project.'/tracker/'.$tracker);
	$cfactcreation->appendChild($cfactcreationressource);
	$cfact->appendChild($cfacttitle);
	$cfact->appendChild($cfactlabel);
	$cfact->appendChild($cfactcreation);
	$service->appendChild($cfact);

	// Query capabilities.
	$qc = $doc->createElement("queryCapability");
	$qctitle = $doc->createElement("dcterms:title", "GET-Based Tracker items query");
	$qclabel = $doc->createElement("oslc:label", "Tracker items query");
	$qcqbase = $doc->createElement("oslc:queryBase");
	$qcqbaseressource = $doc->createElement("rdf:ressource",$base_url.'/cm/project/'.$project.'/tracker/'.$tracker);
	$qcqbase->appendChild($qcqbaseressource);
	$qc->appendChild($qctitle);
	$qc->appendChild($qclabel);
	$qc->appendChild($qcqbase);
	$service->appendChild($qc);

	// Delegated Selection UI.
	$sD = $doc->createElement("selectionDialog");
	$d = $doc->createElement("Dialog");
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
	$cD = $doc->createElement("creationDialog");
	$dialog = $doc->createElement("Dialog");
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


	$root->appendChild($service);


	return $doc->saveXML();
}
