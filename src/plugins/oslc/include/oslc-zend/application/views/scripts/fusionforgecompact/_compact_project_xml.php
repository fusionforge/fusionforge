<?php 
/**
 * This file is (c) Copyright 2010 by Sabri LABBENE, Institut
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
$doc = new DOMDocument();
$doc->formatOutput = true;

$root = $doc->createElementNS("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf:RDF");
$root = $doc->appendChild($root);
$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dcterms', 'http://purl.org/dc/terms/');
$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:oslc', 'http://open-services.net/ns/core#');

// oslc:Compact
$Compact = $doc->createElement('oslc:Compact');
$Compact->setAttribute("rdf:about", $this->serverUrl().'/plugins/oslc/compact/project/'. $this->project->getUnixName());

$titlenode = $doc->createElement('dcterms:title', $this->project->getPublicName());
$Compact->appendChild($titlenode);

$short_title_node = $doc->createElement('oslc:shortTitle', $this->project->getUnixName());
$Compact->appendChild($short_title_node);

$smallPreview = $doc->createElement('oslc:smallPreview');
$Preview = $doc->createElement('oslc:Preview');
$Doc = $doc->createElement('oslc:document');
$Doc->setAttribute('rdf:ressource', $this->serverUrl().'/plugins/oslc/compact/project/'. $this->project->getUnixName().'/type/small'); 
$hintW = $doc->createElement('oslc:hintWidth', '500px');
$hintH = $doc->createElement('oslc:hintHeight', '150px');
$Preview->appendChild($Doc);
$Preview->appendChild($hintW);
$Preview->appendChild($hintH);
$smallPreview->appendChild($Preview);
$Compact->appendChild($smallPreview);

$root->appendChild($Compact);
echo $doc->saveXML();

?>