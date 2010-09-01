<?php // rcs_id('$Id: SemanticWeb.php 7663 2010-08-31 15:23:17Z vargenau $');
/**
 * What to do on ?format=rdf  What to do on ?format=owl
 *
 * Map relations on a wikipage to a RDF ressource to build a "Semantic Web"
 * - a web ontology frontend compatible to OWL (web ontology language).
 * http://www.w3.org/2001/sw/Activity
 * Simple RDF ontologies contain facts and rules, expressed by RDF triples:
 *   Subject (page) -> Predicate (verb, relation) -> Object (links)
 * OWL extents that to represent a typical OO framework.
 *  OO predicates:
 *    is_a, has_a, ...
 *  OWL predicates:
 *    subClassOf, restrictedBy, onProperty, intersectionOf, allValuesFrom, ...
 *    someValuesFrom, unionOf, equivalentClass, disjointWith, ...
 *    plus the custom vocabulary (ontology): canRun, canBite, smellsGood, ...
 *  OWL Subjects: Class, Restriction, ...
 *  OWL Properties: type, label, comment, ...
 * DAML should also be supported.
 *
 * Purpose:
 * - Another way to represent various KB models in various DL languages.
 *   (OWL/DAML/other DL)
 * - Frontend to various KB model reasoners and representations.
 * - Generation/update of static wiki pages based on external OWL/DL/KB
 *   (=> ModelTest/Categories)
 *   KB Blackboard and Visualization.
 * - OWL generation based on static wiki pages (ModelTest?format=owl)
 *
 * Facts: (may be represented by special links on a page)
 *  - Each page must be representable with an unique URL.
 *  - Each fact must be representable with an unique RDF triple.
 *  - A class is represented by a category page.
 *  - To represent more expressive description logic, "enriched"
 *    links will not be enough (? variable symbolic objects).
 *
 * Rules: (may be represented by special content on a page)
 *  - Syntax: reasoner backend specific, or common or ?
 *
 * RDF Triple: (representing facts)
 *   Subject (page) -> Predicate (verb, relation) -> Object (links)
 * Subject: a page
 * Verb:
 *   Special link qualifiers represent RDF triples, based on RDF standard notation.
 *   See RDF standard DTD's on daml.org and w3.org, plus your custom predicates.
 *   (need your own DTD)
 *   Example: page [Ape] isa:Animal, ...
 * Object: special links on a page.
 * Class: WikiCategory
 * Model: Basepage for a KB. (parametrizeable pages or copies of modified snapshots?)
 *
 * DL: Description Logic
 * KB: Knowledge Base
 *
 * Discussion:
 * Of course *real* expert systems ("reasoners") will help/must be used in
 * optimization and maintainance of the SemanticWeb KB (Knowledge
 * Base). Hooks will be tested to KM (an interactive KB playground),
 * LISA (standard unifier), FaCT, RACER, ...

 * Maybe also ZEBU (parser generator) is needed to convert the wiki KB
 * syntax to the KB reasoner backend (LISA, KM, CLIPS, JESS, FaCT,
 * ...) and forth.

 * pOWL is a simple php backend with some very simple AI logic in PHP,
 * though I strongly doubt the usefulness of reasoners not written in
 * Common Lisp.
 *
 * SEAL (omntoweb.org) is similar to that, just on top of the Zope CMF.
 * FaCT uses e.g. this KB DTD:
<!ELEMENT KNOWLEDGEBASE (DEFCONCEPT|DEFROLE|IMPLIESC|EQUALC|IMPLIESR|EQUALR|TRANSITIVE|FUNCTIONAL)*>
<!ELEMENT CONCEPT (PRIMITIVE|TOP|BOTTOM|AND|OR|NOT|SOME|ALL|ATMOST|ATLEAST)>
<!ELEMENT ROLE (PRIMROLE|INVROLE)>
... (facts and rules described in XML)
 *
 * Links:
 *   http://phpwiki.org/SemanticWeb,
 *   http://en.wikipedia.org/wiki/Knowledge_representation
 *   http://www.ontoweb.org/
 *   http://www.semwebcentral.org/ (OWL on top of FusionForge)
 *
 *
 * Author: Reini Urban <rurban@x-ray.at>
 */
/*============================================================================*/
/*
 * Copyright 2004,2007 Reini Urban
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('lib/RssWriter.php');
require_once('lib/TextSearchQuery.php');
require_once('lib/Units.php');


/**
 * RdfWriter - A class to represent the links of a list of wikipages as RDF.
 * Supports ?format=rdf
 *
 * RdfWriter (unsorted)
 *  - RssWriter (timesorted)
 *    - RecentChanges (?action=RecentChanges&format=rdf) (filtered)
 */
class RdfWriter extends RssWriter // in fact it should be rewritten to be other way round.
{
    function RdfWriter (&$request, &$pagelist) {
	$this->_request =& $request;
	$this->_pagelist =& $pagelist;
        $this->XmlElement('rdf:RDF',
                          array('xmlns' => "http://purl.org/rss/1.0/",
                                'xmlns:rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'));

	$this->_modules = array(
            //Standards
	    'content'	=> "http://purl.org/rss/1.0/modules/content/",
	    'dc'	=> "http://purl.org/dc/elements/1.1/",
	    );

	$this->_uris_seen = array();
        $this->_items = array();

	$this->wiki_xmlns_xml = WikiURL(_("UriResolver")."?",false,true);
	$this->wiki_xmlns_url = PHPWIKI_BASE_URL;

	$this->pre_ns_buffer =
	    "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
	    "<!DOCTYPE rdf:RDF[\n" .
	    "\t"."<!ENTITY rdf 'http://www.w3.org/1999/02/22-rdf-syntax-ns#'>\n" .
	    "\t"."<!ENTITY rdfs 'http://www.w3.org/2000/01/rdf-schema#'>\n" .
	    "\t"."<!ENTITY owl 'http://www.w3.org/2002/07/owl#'>\n" .
	    "\t"."<!ENTITY smw 'http://smw.ontoware.org/2005/smw#'>\n" .
	    "\t"."<!ENTITY smwdt 'http://smw.ontoware.org/2005/smw-datatype#'>\n" .
	    // A note on "wiki": this namespace is crucial as a fallback when it would be illegal to start e.g. with a number. In this case, one can always use wiki:... followed by "_" and possibly some namespace, since _ is legal as a first character.
	    "\t"."<!ENTITY wiki '" . $this->wiki_xmlns_xml .  "'>\n" .
	    "\t"."<!ENTITY relation '" . $this->wiki_xmlns_xml .
	    $this->makeXMLExportId(urlencode(str_replace(' ', '_', _("Relation") . ':'))) .  "'>\n" .
	    "\t"."<!ENTITY attribute '" . $this->wiki_xmlns_xml .
	    $this->makeXMLExportId(urlencode(str_replace(' ', '_', _("Attribute") . ':'))) .  "'>\n" .
	    "\t"."<!ENTITY wikiurl '" . $this->wiki_xmlns_url .  "'>\n" .
	    "]>\n\n" .
	    "<rdf:RDF\n" .
	    "\t"."xmlns:rdf=\"&rdf;\"\n" .
	    "\t"."xmlns:rdfs=\"&rdfs;\"\n" .
	    "\t"."xmlns:owl =\"&owl;\"\n" .
	    "\t"."xmlns:smw=\"&smw;\"\n" .
	    "\t"."xmlns:wiki=\"&wiki;\"\n" .
	    "\t"."xmlns:relation=\"&relation;\"\n" .
	    "\t"."xmlns:attribute=\"&attribute;\"";
	$this->post_ns_buffer =
	    "\n\t<!-- reference to the Semantic MediaWiki schema -->\n" .
	    "\t"."<owl:AnnotationProperty rdf:about=\"&smw;hasArticle\">\n" .
	    "\t\t"."<rdfs:isDefinedBy rdf:resource=\"http://smw.ontoware.org/2005/smw\"/>\n" .
	    "\t"."</owl:AnnotationProperty>\n" .
	    "\t"."<owl:AnnotationProperty rdf:about=\"&smw;hasType\">\n" .
	    "\t\t"."<rdfs:isDefinedBy rdf:resource=\"http://smw.ontoware.org/2005/smw\"/>\n" .
	    "\t"."</owl:AnnotationProperty>\n" .
	    "\t"."<owl:Class rdf:about=\"&smw;Thing\">\n" .
	    "\t\t"."<rdfs:isDefinedBy rdf:resource=\"http://smw.ontoware.org/2005/smw\"/>\n" .
	    "\t"."</owl:Class>\n" .
	    "\t<!-- exported page data -->\n";
    }

    function format() {
	header( "Content-type: application/rdf+xml; charset=UTF-8" );
	echo $this->pre_ns_buffer;
	echo ">\n";

	$first = true;
	$dbi =	$this->_request->_dbi;
	/* Elements per page:
	   out-links internal, out-links external
	   backlinks
	   relations
	   attributes
	*/
	foreach ($this->_pagelist->_pages as $page) {
	    $relation = new TextSearchQuery("*");
	    foreach (array('linkto','linkfrom','relation','attribute') as $linktype) {
		$linkiter = $dbi->linkSearch($pages, $search, $linktype, $relation);
	    }
	    while ($link = $linkiter->next()) {
		if (mayAccessPage('view', $rev->_pagename)) {
		    $linkto->addItem($this->item_properties($rev),
				     $this->pageURI($rev));
		    if ($first)
			$this->setValidators($rev);
		    $first = false;
		}
	    }
	}

	echo $this->post_ns_buffer;
	echo "</rdf:RDF>\n";
    }

    /** This function transforms a valid url-encoded URI into a string
     *  that can be used as an XML-ID. The mapping should be injective.
     */
    function makeXMLExportId($uri) {
	$uri = str_replace( '-', '-2D', $uri);
	//$uri = str_replace( ':', '-3A', $uri); //already done by PHP
	//$uri = str_replace( '_', '-5F', $uri); //not necessary
	$uri = str_replace( array('"',  '#',   '&', "'",  '+',  '=',  '%'),
			    array('-22','-23','-26','-27','-2B','-3D','-'),
			    $uri);
	return $uri;
    }

    /** This function transforms an XML-ID string into a valid
     *  url-encoded URI. This is the inverse to makeXMLExportID.
     */
    function makeURIfromXMLExportId($id) {
	$id = str_replace( array('-22','-23','-26','-27','-2B','-3D','-'),
			   array('"',  '#',  '&',  "'",  '+',  '=',  '%'),
			   $id);
	$id = str_replace( '-2D', '-', $id);
	return $id;
    }
}

/**
 */
class RdfsWriter extends RdfWriter {
};

/**
 * OwlWriter - A class to represent a set of wiki pages (a DL model) as OWL.
 * Requires an actionpage returning a pagelist.
 * Supports ?format=owl
 *
 * OwlWriter
 *  - RdfWriter
 *  - Reasoner
*/
class OwlWriter extends RdfWriter {
};

/**
 * ModelWriter - Export a KB as set of wiki pages.
 * Requires an actionpage returning a pagelist.
 * Probably based on some convenient DL expression syntax. (deffact, defrule, ...)
 *
 * ModelWriter
 *  - OwlWriter
 *  - ReasonerBackend
*/
class ModelWriter extends OwlWriter {
};

/**
 *  NumericSearchQuery can do:
 *         ("population < 20000 and area > 1000000", array("population", "area"))
 *  ->match(array('population' => 100000, 'area' => 10000000))
 * @see NumericSearchQuery
 *
 *  SemanticAttributeSearchQuery can detect and unify units in numbers.
 *         ("population < 2million and area > 100km2", array("population", "area"))
 *  ->match(array('population' => 100000, 'area' => 10000000))
 *
 * Do we need a real parser or can we just regexp over some allowed unit
 * suffixes to detect the numbers?
 * See man units(1) and /usr/share/units.dat
 * base units: $ units "1 million miles"
 *                     Definition: 1.609344e+09 m
 */
class SemanticAttributeSearchQuery
extends NumericSearchQuery
{
    /*
    var $base_units = array('m'   => explode(',','km,miles,cm,dm,mm,ft,inch,inches,meter'),
			    'm^2' => explode(',','km^2,ha,cm^2,mi^2'),
			    'm^3' => explode(',','km^3,lit,cm^3,dm^3,gallons'),
			    );
    */

    /**
     * We need to detect units from the freetext query:
     * population > 1 million
     */
    function SemanticAttributeSearchQuery($search_query, $placeholders, $unit = '') {
	$this->NumericSearchQuery($search_query, $placeholders);
	$this->_units = new Units();
	$this->unit = $unit;
    }

    /**
     * Strip non-numeric chars from the variable (as the groupseperator) and replace
     * it in the symbolic query for evaluation.
     * This version unifies the attribute values from the database to a
     * numeric basevalue before comparison. (area:=963.6km^2 => 9.366e+08 m^2)
     *
     * @access private
     * @param $value number   A numerical value: integer, float or string.
     * @param $x string       The variable name to be replaced in the query.
     * @return string
     */
    function _bind($value, $x) {
    	$ori_value = $value;
	$value = preg_replace("/,/", "", $value);
	$this->_bound[] = array('linkname'  => $x,
	        		'linkvalue' => $value);
	// We must ensure that the same baseunits are matched against.
	// We cannot compare m^2 to m or ''
	$val_base = $this->_units->basevalue($value);
        if (!DISABLE_UNITS and $this->_units->baseunit($value) != $this->unit) {
	    // Poor user has selected an attribute, but no unit. assume he means the baseunit
	    if (count($this->getVars() == 1) and $this->unit == '') {
		;
	    } else {
		// non-matching units are silently ignored
		$this->_workquery = '';
		return '';
	    }
        }
        $value = $val_base;
	if (!is_numeric($value)) {
	    $this->_workquery = ''; //must return false
	    trigger_error("Cannot match against non-numeric attribute value $x := $ori_value",
			  E_USER_NOTICE);
	    return '';
	}

	$this->_workquery = preg_replace("/\b".preg_quote($x,"/")."\b/", $value, $this->_workquery);
	return $this->_workquery;
    }

}

/**
 *  SemanticSearchQuery can do:
 *     (is_a::city and population < 20000) and (*::city and area > 1000000)
 *  ->match(array('is_a' => 'city', 'linkfrom' => array(),
 *          population' => 100000, 'area' => 10000000))
 * @return array  A list of found and bound matches
 */
class SemanticSearchQuery
extends SemanticAttributeSearchQuery
{
    function hasAttributes() { // TODO
    }
    function hasRelations()  { // TODO
    }
    function getLinkNames()  { // TODO
    }
}

/**
 * ReasonerBackend - hooks to reasoner backends.
 * via http as with DIG,
 * or internally
 */
class ReasonerBackend {
    function ReasonerBackend () {
        ;
    }
    /**
     * transform to reasoner syntax
     */
    function transformTo () {
        ;
    }
    /**
     * transform from reasoner syntax
     */
    function transformFrom () {
        ;
    }
    /**
     * call the reasoner
     */
    function invoke () {
        ;
    }
};

class ReasonerBackend_LISA extends ReasonerBackend {
};

class ReasonerBackend_Racer extends ReasonerBackend {
};

class ReasonerBackend_KM extends ReasonerBackend {
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
