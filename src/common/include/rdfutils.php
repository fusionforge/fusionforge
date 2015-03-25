<?php
/**
 * FusionForge RDF utils
 *
 * Copyright 2012, Olivier Berger and Institut Mines-Telecom
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'arc/ARC2.php';

/**
 * rdfutils_setPropToUri() - Add a relation (a link to a URI) to an ARC2_Resource
 *
 * example : rdfutils_setPropToUri($res, 'rdf:type', 'doap:Project');
 *
 * @param	ARC2_Resource	$res
 * @param	string	$prop
 * @param	string	$value
 */
function rdfutils_setPropToUri(&$res, $prop, $value) {
	// ARC2_Resource may not yet have a setRel() method
	if (method_exists('ARC2_Resource','setRel')) {
		$res->setRel($prop, $value);
	}
	else {
		if(!is_array($value)) {
			$uri = array (
					'type' => 'uri',
					'value' => $res->expandPName($value));
			$res->setProp($prop, $uri);
		} else {
			$s = $res->uri;
			foreach($value as $i => $x) {
				if(!is_array($x)) {
					$uri = array (
							'type' => 'uri',
							'value' => $res->expandPName($x));
					$value[$i] = $uri;
				}
			}
			$res->index[$s][$res->expandPName($prop)] = $value;
		}
	}
}

/**
 * rdfutils_setPropToXSDdateTime() - Add a xsd:dateTime to an ARC2_Resource
 *
 * example : rdfutils_setPropToXSDdateTime($res, 'dcterms:created', date('c'));
 *
 * @param	ARC2_Resource	$res
 * @param	string	$prop
 * @param	string	$date
 */
function rdfutils_setPropToXSDdateTime(&$res, $prop, $date) {
	$datecreated=array('value' => $date,
		'type' => 'literal',
		'datatype' => 'http://www.w3.org/2001/XMLSchema#dateTime');
	$res->setProp($prop, $datecreated);
}

/**
 * rdfutils_setPropToString() - Add a string property to an ARC2_Resource, with optional language tag
 *
 * example : rdfutils_setPropToString($res, 'dct:description', 'Olivier was here', 'en');
 *
 * @param	ARC2_Resource	$res
 * @param	string	$prop
 * @param	string	$value
 * @param	string	$lang (optional)
 */
function rdfutils_setPropToString(&$res, $prop, $value, $lang = '') {
	if (!$lang) {
		$res->setProp($prop, $value);
	} else {
		$res->setProp($prop, array('type' => 'literal', 'value' => $value, 'lang' => $lang) );
	}
}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
