<?php

/*
 * This file is (c) Copyright 2009 by Olivier BERGER, Institut
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

// Generate a OSLC-CM V1 Change Management Service Description document (http://open-services.net/bin/view/Main/CmServiceDescriptionV1)

function project_to_service_description($base_url, $project, $tracker) {

	$doc = new DOMDocument();
	$doc->formatOutput = true;

	$root = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:ServiceDescriptor");
	$root = $doc->appendChild($root);

	$child = $doc->createAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:about");
	$about = $root->appendChild($child);
	$child = $doc->createTextNode("");
	$child = $about->appendChild($child);

	$child = $doc->createElementNS("http://purl.org/dc/terms/", "dc:title");
	$title = $root->appendChild($child);
	$child = $doc->createTextNode("OSLC CM service description document describing a FusionForge tracker services");
	$child = $title->appendChild($child);

	// changeRequests
	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:changeRequests");
	$cr = $root->appendChild($child);

	$child = $doc->createAttribute("version");
	$version = $cr->appendChild($child);
	$child = $doc->createTextNode("1.0");
	$child = $version->appendChild($child);

	// Simple GET-based URL-encoded query

	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:simpleQuery");
	$sq = $cr->appendChild($child);
	
	$child = $doc->createElementNS("http://purl.org/dc/terms/", "dc:title");
	$title = $sq->appendChild($child);
	$child = $doc->createTextNode("Simple Tracker Query");
	$child = $title->appendChild($child);
	
	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:url");
	$url = $sq->appendChild($child);
	$child = $doc->createTextNode($base_url.'/cm/project/'.$project.'/tracker/'.$tracker);
	$child = $url->appendChild($child);
	
	//creation factory
	
	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:factory");
	$crdl = $cr->appendChild($child);	
	
	$child = $doc->createAttribute("oslc_cm:default");
	$option = $crdl->appendChild($child);
	$child = $doc->createTextNode("true");
	$child = $option->appendChild($child);
	
	$child = $doc->createElementNS("http://purl.org/dc/terms/", "dc:title");
	$title = $crdl->appendChild($child);
	$child = $doc->createTextNode("Location for creation of change requests (with a POST HTTP request)");
	$child = $title->appendChild($child);

	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:url");
	$url = $crdl->appendChild($child);
	$child = $doc->createTextNode($base_url.'/cm/project/'.$project.'/tracker/'.$tracker);
	$child = $url->appendChild($child);
	
	//creation dialog
	
	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:creationDialog");
	$crdl = $cr->appendChild($child);	
	
	$child = $doc->createAttribute("oslc_cm:default");
	$option = $crdl->appendChild($child);
	$child = $doc->createTextNode("true");
	$child = $option->appendChild($child);
	
	$child = $doc->createAttribute("oslc_cm:hintWidth");
	$option = $crdl->appendChild($child);
	$child = $doc->createTextNode("740px");
	$child = $option->appendChild($child);
	
	$child = $doc->createAttribute("oslc_cm:hintHeight");
	$option = $crdl->appendChild($child);
	$child = $doc->createTextNode("540px");
	$child = $option->appendChild($child);

	$child = $doc->createElementNS("http://purl.org/dc/terms/", "dc:title");
	$title = $crdl->appendChild($child);
	$child = $doc->createTextNode("New Change Request Creation Dialog");
	$child = $title->appendChild($child);

	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:url");
	$url = $crdl->appendChild($child);
	$child = $doc->createTextNode($base_url.'/cm/project/'.$project.'/tracker/'.$tracker.'/ui/creation');
	$child = $url->appendChild($child);
	
	//selection dialog
	
	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:selectionDialog");
	$sldl = $cr->appendChild($child);
	
	$child = $doc->createAttribute("oslc_cm:default");
	$option = $sldl->appendChild($child);
	$child = $doc->createTextNode("true");
	$child = $option->appendChild($child);
	
	$child = $doc->createAttribute("oslc_cm:hintWidth");
	$option = $sldl->appendChild($child);
	$child = $doc->createTextNode("800px");
	$child = $option->appendChild($child);
	
	$child = $doc->createAttribute("oslc_cm:hintHeight");
	$option = $sldl->appendChild($child);
	$child = $doc->createTextNode("600px");
	$child = $option->appendChild($child);
	
	$child = $doc->createElementNS("http://purl.org/dc/terms/", "dc:title");
	$title = $sldl->appendChild($child);
	$child = $doc->createTextNode("Change Request Selection Dialog");
	$child = $title->appendChild($child);

	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/", "oslc_cm:url");
	$url = $sldl->appendChild($child);
	$child = $doc->createTextNode($base_url.'/cm/project/'.$project.'/tracker/'.$tracker.'/ui/selection');
	$child = $url->appendChild($child);

	return $doc->saveXML();
}