<?php // -*-php-*-
// $Id: LinkSearchIter.php 7956 2011-03-03 17:08:31Z vargenau $
/*
 * Copyright 2007 Reini Urban
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

/**
 * Ad-hoc support for an RDF-Triple search in our link database.
 *
 * This iterator will work with any WikiDB_backend
 * which has a working get_links() method with want_relation support.
 * Currently we iterate over all pages, check for all matching links.
 * As optimization a backend could iterate over the linktable instead
 * and check the matching page there. There are most likely less links
 * than pages and links are smaller than pages.
 *
 * This is mostly here for testing, very slow.
 *
 * @author Reini Urban
 * @see WikiDB_backend::link_search
 */

class WikiDB_backend_dumb_LinkSearchIter
extends WikiDB_backend_iterator
{
    function WikiDB_backend_dumb_LinkSearchIter(&$backend, &$pageiter, $search, $linktype,
						$relation=false, $options=array())
    {
        $this->_backend  = &$backend;
        $this->_pages    = $pageiter;
        $this->search    = $search;   // search the linkvalue. it should be the value or pagename
        $this->relation  = $relation; // limit the search to this linkname
        $this->linktype  = $linktype;
	$this->_reverse  = false;
	$this->_want_relations = true;
	$this->sortby  = isset($options['sortby'])  ? $options['sortby']  : '';
	$this->limit   = isset($options['limit'])   ? $options['limit']   : '';
	$this->exclude = isset($options['exclude']) ? $options['exclude'] : '';
	$this->_field = 'pagename'; // the name of the linkvalue field to apply the search
	$this->_dbi =& $GLOBALS['request']->_dbi;
	if ($linktype == 'relation') {
	    $this->_want_relations = true;
	    $this->_field = 'linkrelation';
	}
	if ($linktype == 'attribute') {
	    $this->_want_relations = true;
	    $this->_field = 'attribute';
	}
	if ($linktype == 'linkfrom') {
	    $this->_reverse = true;
	}
	$this->_page = false;
    }

    // iterate a nested page-links loop. there will be multiple results per page.
    // we must keep the page iter internally.
    function next() {
    	while (1) {
	    if (!isset($this->_links) or count($this->_links) == 0) {
		$page = $this->_next_page(); // initialize all links of this page
		if (!$page) return false;
	    } else {
	    	$page = $this->_page;
	    }
	    // iterate the links. the links are pushed into the handy triple by _get_links
	    while ($link = array_shift($this->_links)) {
		// unmatching relations are already filtered out
		if ($this->search->match($link['linkvalue'])) { //pagename or attr-value
		    if ($link['linkname'])
			return array('pagename' => $page,
				     'linkname' => $link['linkname'],
				     'linkvalue'=> $link['linkvalue']);
		    else
			return array('pagename' => $page,
				     'linkvalue'=> $link['linkvalue']);
		}
	    }
	    // no links on this page anymore.
    	}
    }

    // initialize the links also
    function _next_page() {
	unset($this->_links);
	if (!($next = $this->_pages->next()))
	    return false;
	$this->_page = $next['pagename'];
	while(!($this->_links = $this->_get_links($this->_page))) {
	    if (!($next = $this->_pages->next()))
	        return false;
	    $this->_page = $next['pagename'];
	}
	return $this->_page;
    }

    // get the links of each page in advance
    function _get_links($pagename) {
	$links = array();
	if ($this->linktype == 'attribute') {
	    $page = $this->_dbi->getPage($pagename);
	    $attribs = $page->get('attributes');
	    if ($attribs) {
	      foreach ($attribs as $attribute => $value) {
		  if ($this->relation and !$this->relation->match($attribute)) continue;
		  // The logical operator and unit unification (not yet) is encoded into
		  // a seperate search object.
		  if (!$this->search->match($value)) continue;
		  $links[] = array('pagename'  => $pagename,
				   'linkname'  => $attribute,
				   'linkvalue' => $value);
	      }
	    }
	    unset($attribs);
	} else {
	    $link_iter = $this->_backend->get_links($pagename, $this->_reverse, true,
						    $this->sortby, $this->limit,
						    $this->exclude, $this->_want_relations);
	    // we already stepped through all links. make use of that.
	    if ($this->_want_relations
	        and isset($link_iter->_options['found_relations'])
		and $link_iter->_options['found_relations'] == 0)
	    {
		$link_iter->free();
		return $links;
	    }
	    while ($link = $link_iter->next()) {
	    	if (empty($link[$this->_field])) continue;
		if ($this->_want_relations and $this->relation
		    and !$this->relation->match($link['linkrelation'])) continue;
		// check hash values, with/out want_relations
		$links[] = array('pagename'  => $pagename,
		                 'linkname'  => $this->_want_relations ? $link['linkrelation'] : '',
				 'linkvalue' => $link['pagename']);
	    }
	    $link_iter->free();
	}
	return $links;
    }

    function free() {
	$this->_page = false;
	unset($this->_links);
        $this->_pages->free();
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:

?>
