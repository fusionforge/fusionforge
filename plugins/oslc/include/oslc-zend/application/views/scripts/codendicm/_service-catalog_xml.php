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

// Generate a OSLC-CM V1 Service Catalog document (http://open-services.net/bin/view/Main/OslcServiceProviderCatalogV1)

function projects_to_service_catalog($base_url, $projects) {

	$doc = new DOMDocument();
	$doc->formatOutput = true;

	$root = $doc->createElementNS("http://open-services.net/xmlns/discovery/1.0/", "oslc_disc:ServiceProviderCatalog");
	$root = $doc->appendChild($root);

	$child = $doc->createAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:about");
	$about = $root->appendChild($child);
	$child = $doc->createTextNode("");
	$child = $about->appendChild($child);

	$child = $doc->createElementNS("http://purl.org/dc/terms/", "dc:title");
	$title = $root->appendChild($child);

	// TODO ? : MAY have an oslc_disc:details child element.

	$child = $doc->createTextNode(TRACKER_TYPE. " Change management service provider catalog");
	$child = $title->appendChild($child);

	foreach ($projects as $proj) {

		if(count($proj)>0)
		{
			// entry
			$child = $doc->createElementNS("http://open-services.net/xmlns/discovery/1.0/", "oslc_disc:entry");
			$entry = $root->appendChild($child);

			$child = $doc->createElementNS("http://open-services.net/xmlns/discovery/1.0/", "oslc_disc:ServiceProvider");
			$sp = $entry->appendChild($child);

			$child = $doc->createElementNS("http://purl.org/dc/terms/", "dc:title");
			$title = $sp->appendChild($child);
			$child = $doc->createTextNode($proj['name']);
			$child = $title->appendChild($child);

			$child = $doc->createElementNS("http://open-services.net/xmlns/discovery/1.0/", "oslc_disc:services");
			$services = $sp->appendChild($child);
			$child = $doc->createAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:resource");
			$resource = $services->appendChild($child);
			$child = $doc->createTextNode($base_url.'/cm/oslc-cm-service/'.$proj['id']);
			$child = $resource->appendChild($child);


		}
	}
	return $doc->saveXML();
}
