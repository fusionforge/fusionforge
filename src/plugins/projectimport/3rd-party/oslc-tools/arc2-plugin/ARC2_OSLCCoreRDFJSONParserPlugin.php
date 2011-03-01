<?php

/*

RDF Parser for ARC2 to OSLC Core RDF JSON format

Copyright (c) 2010 Olivier Berger, Institut T�l�com

homepage: See http://oslc-tools.svn.sourceforge.net/viewvc/oslc-tools/language-libs/php/arc2-plugin/
license:  

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0 or in the acompanying COPYING file

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

class:    ARC2_OSLCCoreRDFJSONParserPlugin
author:   Olivier Berger
version:  2010-12-23

This program was developped in the frame of the COCLICO project
(http://www.coclico-project.org/) with financial support of the Paris
Region council.

It was initially developped to in order to provide a JSON parser for
RDF content formatted along the OSLC (Open Services for Lifecycle
Collaboration) Core specs guidelines, which could be used in the
FusionForge project import plugin.

The most up-to-date version is maintained in the frame of the OSLC
support Open Source project at http://oslc-tools.sourceforge.net/.

$Id$

*/

/*

Will parse JSON containing RDF in the format of OSLC Core [0], like :

 {
     "dcterms:content": "Anything dirty or dingy or dusty. \\nAnything ragged or rotten or rusty.", 
     "dcterms:creator": {
         "foaf:name": "Oscar T. Grouch"
     }, 
     "dcterms:modified": "2002-10-10T12:00:00-05:00", 
     "dcterms:title": "I love trash", 
     "prefixes": {
         "dcterms": "http://purl.org/dc/terms/", 
         "foaf": "http://http://xmlns.com/foaf/0.1/", 
         "oslc": "http://open-services.net/ns/core#", 
         "rdf": "http://www.w3.org/1999/02/22-rdf-syntax-ns#"
     }, 
     "rdf:about": "http://example.com/blogs/entry/1", 
     "rdf:type": {
         "rdf:resource": "http://open-services.net/ns/bogus/blogs#Entry"
     }
 }

[0] http://open-services.net/bin/view/Main/OSLCCoreSpecAppendixRepresentations#Guidelines_for_JSON

Use like :

 # install it in arc2/plugins or include it like this
 #include_once("./ARC2_OSLCCoreRDFJSONParserPlugin.php");

 # then use it like this
 $parser = ARC2::getComponent("OSLCCoreRDFJSONParserPlugin");
 $parser->parse('example.json');

 $triples = $parser->getTriples();

 */

ARC2::inc('JSONParser');

function is_assoc($array) {
    return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
}

class ARC2_OSLCCoreRDFJSONParserPlugin extends ARC2_JSONParser {

  function __construct($a, &$caller) {
    parent::__construct($a, $caller);
  }
  
  function ARC2_OSLCCoreRDFJSONParserPlugin ($a = '', &$caller) {
    $this->__construct($a, $caller);
  }

  function __init() {/* reader */
    parent::__init();
    $this->rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    $this->nsp = array($this->rdf => 'rdf');
  }
  
  /*  */

  function done() {
    $this->extractRDF();
  }

  // Extract from a substructure $x
  function extractRDFFromSubStruct($subject, $predicate, $x, $parent_prefixes)
  {
    // if it's an array, then need new resource
    if(is_array($x)) {
      $subcontext = $this->getContext($x);
      // if it has no own subject (rdf:about), then create a blank node
      if(!$subcontext) {
	$bnode = $this->createBnodeID();
	$subcontext = $bnode;
      }
      // add pointer to the new resource
      $this->addT($subject, $predicate, $subcontext, 'uri');
      // process new resource
      $this->extractRDFFromStruct($x, $subcontext, $parent_prefixes);
    }
    // else, just adding another predicate to the subject
    else {
      $this->addT($subject, $predicate, $x);
    }
  }

  // Extract from a structure, giving it an already computed subject if needed (blank nodes)
  function extractRDFFromStruct($struct, $subject=false, $parent_prefixes=false)
  {
    if(!$struct) return;

    // Extract subject from 'rdf:about' of the struct
    if(!$subject) {
      $subject = $this->getContext($struct);
    }
    $type = $this->getType($struct);
    if($type) {
      if(is_array($type)) {
	foreach ($type as $t) {
	  $this->addT($subject, $this->rdf . 'type', $t, 'uri', 'uri');
	}
      }
      else {
	$this->addT($subject, $this->rdf . 'type', $type, 'uri', 'uri');
      }
    }
    $prefixes = array();
    if (is_array($parent_prefixes)) {
      foreach ($parent_prefixes as $k => $v) {
	$prefixes[$k]=$v;
      }
    }
    if (isset($struct['prefixes'])) {
      foreach ($struct['prefixes'] as $k => $v) {
	$prefixes[$k]=$v;
      }
    }
    foreach ($struct as $p => $o) {
      // in case there's no prefix, use fake 'forgeplucker' prefix
      if (preg_match('/\:/', $p)) {
	preg_match('/^([^:]*)\:(.*)$/', $p, $m);
	$shortpref = $m[1];
	$suffix = $m[2];
      }
      else {
	if($p != 'prefixes') {
	  $shortpref='forgeplucker';
	  $suffix = $p;
	}
      }
      $predicate = $prefixes[$shortpref].$suffix;
      // process all "regular" attributes (all but reserved ones)
      $reserved = array('rdf:type', 'rdf:about', 'prefixes');
      if (!in_array($p, $reserved)){
	// treat litterals first
	if (!is_array($o)) {
	  $this->addT($subject, $predicate, $o);
	} 
	// treat sub-structs
	else {
	  // treat only non-empty sub structs
	  if(count($o)) {
	    // treat lists of elements for same predicate
	    if(!is_assoc($o)) {
	      foreach($o as $x) {
		$this->extractRDFFromSubStruct($subject, $predicate, $x, $prefixes);
	      }
	    }
	    // treat single element sub-struct
	    else {
	      $this->extractRDFFromSubStruct($subject, $predicate, $o, $prefixes);
	    }
	  }
	}
      }
    }
    /*
    $os = $this->getURLs($this->struct);
    foreach ($os as $o) {
      if ($o != $subject) $this->addT($subject, 'http://www.w3.org/2000/01/rdf-schema#seeAlso', $o, 'uri', 'uri');
    }
    */
    

  }
  function extractRDF() {
    $this->extractRDFFromStruct($this->struct);
  }

  // retrieves the subject of a triple (rdf:about)
  function getContext($struct) {
    if (!isset($struct['rdf:about'])) return '';
    return $struct['rdf:about'];
  }
  
  function getType($struct) {
    if (!isset($struct['rdf:type'])) return '';
    $type = $struct['rdf:type'];
    if (!is_array($type))
      return $type;
    else {
      if (!isset($type['rdf:resource'])) 
	return $type;
      else 
	return $type['rdf:resource'];
    }
  }
  
  function getURLs($struct) {
    $r =array();
    if (is_array($struct)) {
      foreach ($struct as $k => $v) {
        if (preg_match('/^http:\/\//', $k) && !in_array($k, $r)) $r[] = $k;
        $sub_r = $this->getURLs($v);
        foreach ($sub_r as $sub_v) {
          if (!in_array($sub_v, $r)) $r[] = $sub_v;
        }
      }
    }
    elseif (preg_match('/^http:\/\//', $struct) && !in_array($struct, $r)) {
      $r[] = $struct;
    }
    return $r;
  }
  
  /*  */

}

?>
