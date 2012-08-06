<?php

/*
 * This file is (c) Copyright 2009 by Madhumita DHAR, Institut
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

function createErrorResourceView($view)
{
	$doc = new DOMDocument();
	$doc->formatOutput = true;

	$root = $doc->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF");
	$root = $doc->appendChild($root);

	$errornode = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/","oslc_cm:Error");
	$errornode = $doc->appendChild($errornode);

	$code = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/","oslc_cm:statusCode", $view->code);
	$errornode->appendChild($code);

	$message = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/","oslc_cm:message", $view->exception->getMessage());
	$errornode->appendChild($message);

	$message = $doc->createElementNS("http://open-services.net/xmlns/cm/1.0/","oslc_cm:trace", $view->exception->getTraceAsString());
	$errornode->appendChild($message);

	$errornode = $root->appendChild($errornode);

	$doc->normalizeDocument();
	return $doc->saveXML();
}
