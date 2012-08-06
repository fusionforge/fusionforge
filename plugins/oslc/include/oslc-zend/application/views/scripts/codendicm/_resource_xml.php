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
	// oslc_cm attributes
	foreach ($resource as $field => $value) {
		$tokens = explode(':', $field);
		if (count($tokens) != 2)
			throw new Exception('Bad internal resource filed type '.$field.' : missing prefix !');
		$prefix = $tokens[0];
		switch ($prefix) {
			case 'dc' :
				$prefix = 'http://purl.org/dc/terms/';
				break;
			case 'helios_bt' :
				$prefix = 'http://heliosplatform.sourceforge.net/ontologies/2010/05/helios_bt.owl';
				break;
			case 'mantisbt' :
				$prefix = 'http://helios-platform.org/ontologies/mantisbt/';
				break;
			default :
				throw new Exception('Unknown ontology prefix '.$prefix.' !');
				break;
		}
		$element = $doc->createElementNS($prefix, $field, $resource[$field]);
		$child = $container->appendChild($element);
	}

	$mandatorytags = array('dc:title', 'dc:identifier');

}

function createRessourceCollectionView($view){
	$feedcharset = 'UTF-8';
	$feedauthor = 'OSLC-CM-V1 Demo server ( '.TRACKER_TYPE.' version)';
	if(isset($view->tracker)){
		$feedtitle = TRACKER_TYPE.' OSLC-CM Change requests';
	}else{
		$feedtitle = 'All '.TRACKER_TYPE.' OSLC-CM Change requests';
	}

	$doc = new DOMDocument('1.0',$feedcharset);
	$doc->formatOutput = true;

	// process the ATOM feed header
	$root = $doc->createElementNS("http://www.w3.org/2005/Atom", "feed");
	$feed = $doc->appendChild($root);

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
		$link = $doc->createElement('link');
		$child = $entryel->appendChild($link);
		$href = $doc->createAttribute('href');
		$child = $child->appendChild($href);
		$href = $doc->createTextNode($entry['id']);
		$child = $child->appendChild($href);


		if( count($entry['resource']) ) {
			$content = $doc->createElement('content');
			$content = $entryel->appendChild($content);
			$type = $doc->createAttribute('type');
			$child = $content->appendChild($type);
			$type = $doc->createTextNode('application/xml');
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
	$root = $doc->appendChild($root);

	$child = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/","oslc_cm:ChangeRequest");
	$changerequest = $root->appendChild($child);

	$child = $doc->createAttributeNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:about");
	$about = $changerequest->appendChild($child);
	$child = $doc->createTextNode($view->id);
	$child = $about->appendChild($child);

	encodeResource($doc, $changerequest, $view->resource);

	return $doc->saveXML();
}
