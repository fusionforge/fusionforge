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

function encodeResource($doc, $container, $resource) {
	foreach ($resource as $field => $value) {
		$element = $doc->createElement($field, $resource[$field]);
		$child = $container->appendChild($element);
	}
}

function createRessourceCollectionView($view){
	$feedcharset = 'UTF-8';
	$feedauthor = 'FusionForge OSLC-CM plugin';

	$feedtitle = TRACKER_TYPE.' OSLC-CM ChangeRequests found in Tracker'. $view->tracker;

	$doc = new DOMDocument('1.0',$feedcharset);
	$doc->formatOutput = true;

	// process the ATOM feed header
	$root = $doc->createElementNS("http://www.w3.org/2005/Atom", "feed");
	$feed = $doc->appendChild($root);

	// Adds other namespaces to the 'feed' node
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', 'http://purl.org/dc/terms/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:foaf', 'http://http://xmlns.com/foaf/0.1/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc', 'http://open-services.net/ns/core#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc_cm', 'http://open-services.net/ns/cm#');

	$title = $doc->createElement('title', $feedtitle);
	$child = $feed->appendChild($title);

	$id = $doc->createElement('id', $view->id);
	$child = $feed->appendChild($id);

	$author = $doc->createElement('author');
	$child = $feed->appendChild($author);
	$name = $doc->createElement('name', $feedauthor);
	$child = $child->appendChild($name);

	// process all entries
	foreach ($view->collection as $entry) {
		$entryel = $doc->createElement('entry');
		$entryel = $feed->appendChild($entryel);

		$title = $doc->createElement('title', $entry['title']);
		$child = $entryel->appendChild($title);

		$id = $doc->createElement('id', $entry['id']);
		$child = $entryel->appendChild($id);

		if( count($entry['resource']) ) {
			$content = $doc->createElement('content');
			$content = $entryel->appendChild($content);
			$type = $doc->createAttribute('type');
			$child = $content->appendChild($type);
			$type = $doc->createTextNode('application/rdf+xml');
			$child = $child->appendChild($type);

			encodeResource($doc, $content, $entry['resource']);
		}
	}
	$doc->normalizeDocument();
	return $doc->saveXML();
}

function createResourceView($view)
{
	$doc = new DOMDocument();
	$doc->formatOutput = true;

	$root = $doc->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF");
	$ressource = $doc->appendChild($root);

	// Adds other namespaces to the RDF ressource node.
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', 'http://purl.org/dc/terms/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:foaf', 'http://http://xmlns.com/foaf/0.1/');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc', 'http://open-services.net/ns/core#');
	$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc_cm', 'http://open-services.net/ns/cm#');

	$child = $doc->createElement("oslc_cm:ChangeRequest");
	$changerequest = $ressource->appendChild($child);
	$changerequest->setAttributeNode(new DOMAttr('rdf:about', $view->id));

	/*$child = $doc->createAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:about");
	$about = $changerequest->appendChild($child);
	$child = $doc->createTextNode($view->id);
	$child = $about->appendChild($child);*/

	encodeResource($doc, $changerequest, $view->resource);

	return $doc->saveXML();
}
