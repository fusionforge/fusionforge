<?php // -*-php-*-
// $Id: dbaBase.php 7956 2011-03-03 17:08:31Z vargenau $

require_once('lib/WikiDB/backend.php');

// FIXME:padding of data?  Is it needed?  dba_optimize() seems to do a good
// job at packing 'gdbm' (and 'db2') databases.

/*
 * Tables:
 *
 *  page:
 *   Index: 'p' + pagename
 *  Values: latestversion . ':' . flags . ':' serialized hash of page meta data
 *           Currently flags = 1 if latest version has empty content.
 *
 *  version
 *   Index: 'v' + version:pagename
 *   Value: serialized hash of revision meta data, including:
 *          + quasi-meta-data %content
 *
 *  links
 *   index: 'o' + pagename
 *   value: serialized list of pages (names) which pagename links to.
 *   index: 'i' + pagename
 *   value: serialized list of pages which link to pagename
 *
 *  TODO:
 *  Don't keep tables locked the whole time.
 *
 *  More index tables:
 *   - Yes - RecentChanges support. Lists of most recent edits (major, minor, either).
 *     't' + mtime => 'a|i' + version+':'+pagename ('a': major, 'i': minor)
 *     Cost: Currently we have to get_all_pages and sort it by mtime.
 *     With a seperate t table we have to update this table on every version change.
 *   - No - list of pagenames for get_all_pages (very cheap: iterate page table)
 *   - Maybe - mostpopular list? 'h' + pagename => hits
  *
 *  Separate hit table, so we don't have to update the whole page entry
 *  each time we get a hit. Maybe not so important though.
 */   

require_once('lib/DbaPartition.php');

class WikiDB_backend_dbaBase
extends WikiDB_backend
{
    function WikiDB_backend_dbaBase (&$dba) {
        $this->_db = &$dba;
        // TODO: page and version tables should be in their own files, probably.
        // We'll pack them all in one for now (testing).
        // 2004-07-09 10:07:30 rurban: It's fast enough this way.
        $this->_pagedb = new DbaPartition($dba, 'p');
        $this->_versiondb = new DbaPartition($dba, 'v');
        $linkdbpart = new DbaPartition($dba, 'l');
        $this->_linkdb = new WikiDB_backend_dbaBase_linktable($linkdbpart);
        $this->_dbdb = new DbaPartition($dba, 'd');
    }

    function sortable_columns() {
        return array('pagename','mtime'/*,'author_id','author'*/);
    }
  
    function close() {
        $this->_db->close();
    }

    function optimize() {
        $this->_db->optimize();
    }

    function sync() {
        $this->_db->sync();
    }

    function rebuild($args=false) {
    	if (!empty($args['all'])) {
    	    parent::rebuild();
    	}
 	// rebuild backlink table
        $this->_linkdb->rebuild();
        $this->optimize();
    }
  
    function check($args=false) {
    	// cleanup v?Pagename UNKNOWN0x0
    	$errs = array();
        $pagedb = &$this->_pagedb;
        for ($page = $pagedb->firstkey();
             $page !== false;
             $page = $pagedb->nextkey())
        {
            if (!$page) {
            	$errs[] = "empty page $page";
                trigger_error("empty page $page deleted", E_USER_WARNING);
                $this->purge_page($page);
                continue;
            }
            if (!($data = $pagedb->get($page))) continue;
            list($version,$flags,) = explode(':', $data, 3);
            $vdata = $this->_versiondb->get($version.":".$page);
            if ($vdata === false)
                continue; // linkrelations
            // we also had for some internal version vdata is serialized strings,
            // need to unserialize it twice. We rather purge it.
            if (!is_string($vdata)
                or $vdata == 'UNKNOWN '
                or !is_array(unserialize($vdata)))
            {
            	$errs[] = "empty revision $version for $page";
                trigger_error("empty revision $version for $page deleted", E_USER_WARNING);
            	$this->delete_versiondata($page, $version);
            }
        }
        // check links per default
        return array_merge($errs, $this->_linkdb->check());
    }

    function get_pagedata($pagename) {
        $result = $this->_pagedb->get($pagename);
        if (!$result)
            return false;
        list(,,$packed) = explode(':', $result, 3);
        $data = unserialize($packed);
        return $data;
    }
          
    function update_pagedata($pagename, $newdata) {
        $result = $this->_pagedb->get($pagename);
        if ($result) {
            list($latestversion,$flags,$data) = explode(':', $result, 3);
            $data = unserialize($data);
        }
        else {
            $latestversion = $flags = 0;
            $data = array();
        }
      
        foreach ($newdata as $key => $val) {
            if (empty($val))
                unset($data[$key]);
            else
                $data[$key] = $val;
        }
        $this->_pagedb->set($pagename,
                            (int)$latestversion . ':'
                            . (int)$flags . ':'
                            . serialize($data));
    }

    function get_latest_version($pagename) {
        return (int) $this->_pagedb->get($pagename);
    }

    function get_previous_version($pagename, $version) {
        $versdb = &$this->_versiondb;

        while (--$version > 0) {
            if ($versdb->exists($version . ":$pagename"))
                return $version;
        }
        return false;
    }

    //check $want_content
    function get_versiondata($pagename, $version, $want_content=false) {
        $data = $this->_versiondb->get((int)$version . ":$pagename");
        if (empty($data) or $data == 'UNKNOWN ') return false;
        else {
            $vdata = unserialize($data);
	    if (DEBUG and empty($vdata)) { // requires ->check
		trigger_error("Delete empty revision: $pagename: ".$data, E_USER_WARNING);
		$this->delete_versiondata($pagename, (int)$version);
	    }
            if (!$want_content)
                $vdata['%content'] = !empty($vdata['%content']);
            return $vdata;
        }
    }
      
    /**
     * Can be undone and is seen in RecentChanges.
     * See backend.php
     */
    function delete_page($pagename) {
    	$version = $this->get_latest_version($pagename);
    	$data = $this->_versiondb->get((int)$version . ":$pagename");
        // returns serialized string
        if (!is_array($data) or empty($data)) {
            if (is_string($data) and ($vdata = @unserialize($data))) {
            	$data = $vdata;
            	unset($vdata);
            } else // already empty page
            	$data = array();
        }
	assert(is_array($data) and !empty($data)); // mtime
    	$data['%content'] = '';
    	$data['mtime'] = time();
    	$data['summary'] = "removed by ".$GLOBALS["request"]->_deduceUsername();
	$this->set_versiondata($pagename, $version+1, $data);
	$this->set_links($pagename, false);
    }

    /**
     * Completely delete all page revisions from the database.
     */
    function purge_page($pagename) {
        $pagedb = &$this->_pagedb;
        $versdb = &$this->_versiondb;

        $version = $this->get_latest_version($pagename);
        while ($version > 0) {
            $versdb->set($version-- . ":$pagename", false);
        }
        $pagedb->set($pagename, false);

        $this->set_links($pagename, false);
    }

    function rename_page($pagename, $to) {
        $result = $this->_pagedb->get($pagename);
        if ($result) {
            list($version, $flags, $data) = explode(':', $result, 3);
            $data = unserialize($data);
        }
        else
            return false;

        $links = $this->_linkdb->get_links($pagename, false, false);
        $data['pagename'] = $to;
        $this->_pagedb->set($to,
                            (int)$version . ':'
                            . (int)$flags . ':'
                            . serialize($data));
        // move over the latest version only
        $pvdata = $this->get_versiondata($pagename, $version, true);
    	$data['mtime'] = time();
    	$data['summary'] = "renamed from ".$pagename
    	                  ." by ".$GLOBALS["request"]->_deduceUsername();
        $this->set_versiondata($to, $version, $pvdata);

	// update links and backlinks
        $this->_linkdb->set_links($to, $links);
        // better: update all back-/inlinks for all outlinks.

        $this->_pagedb->delete($pagename);
	return true;
    }
          
    /**
     * Delete an old revision of a page.
     */
    function delete_versiondata($pagename, $version) {
        $versdb = &$this->_versiondb;

        $latest = $this->get_latest_version($pagename);

        assert($version > 0);
        assert($version <= $latest);
      
        $versdb->set((int)$version . ":$pagename", false);

        if ($version == $latest) {
            $previous = $this->get_previous_version($pagename, $version);
            if ($previous > 0) {
                $pvdata = $this->get_versiondata($pagename, $previous);
                $is_empty = empty($pvdata['%content']);
            }
            else
                $is_empty = true;
            $this->_update_latest_version($pagename, $previous, $is_empty);
        }
    }

    /**
     * Create a new revision of a page.
     */
    function set_versiondata($pagename, $version, $data) {
        $versdb = &$this->_versiondb;
        // fix broken pages
        if (!is_array($data) or empty($data)) {
            if (is_string($data) and ($vdata = @unserialize($data))) {
            	trigger_error("broken page version $pagename. Run Check WikiDB",
                              E_USER_NOTICE);
            	$data = $vdata;
            } else
            	$data = array();
        }
	assert(is_array($data) and !empty($data)); // mtime
        $versdb->set((int)$version . ":$pagename", serialize($data));
        if ($version > $this->get_latest_version($pagename))
            $this->_update_latest_version($pagename, $version, empty($data['%content']));
    }

    function _update_latest_version($pagename, $latest, $flags) {
        $pagedb = &$this->_pagedb;

        $pdata = $pagedb->get($pagename);
        if ($pdata)
            list(,,$pagedata) = explode(':',$pdata,3);
        else
            $pagedata = serialize(array());
      
        $pagedb->set($pagename, (int)$latest . ':' . (int)$flags . ":$pagedata");
    }

    function numPages($include_empty=false, $exclude='') {
        $pagedb = &$this->_pagedb;
        $count = 0;
        for ($page = $pagedb->firstkey(); $page!== false; $page = $pagedb->nextkey()) {
            if (!$page) {
                assert(!empty($page));
                continue;
            }
            if ($exclude and in_array($page, $exclude)) continue;
            if (!$include_empty) {
            	if (!($data = $pagedb->get($page))) continue;
                list($latestversion,$flags,) = explode(':', $data, 3);
                unset($data);
                if ($latestversion == 0 || $flags != 0)
                    continue;   // current content is empty
            }
            $count++;
        }
        return $count;
    }

    function get_all_pages($include_empty=false, $sortby='', $limit='', $exclude='') {
        $pagedb = &$this->_pagedb;
        $pages = array();
        $from = 0; $i = 0; $count = 0;
        if ($limit) { // extract from,count from limit
            list($from,$count) = $this->limit($limit);
        }
        for ($page = $pagedb->firstkey(); $page!== false; $page = $pagedb->nextkey()) {
            if (!$page) {
                assert(!empty($page));
                continue;
            }
            if ($exclude and in_array($page, $exclude)) continue;
            if ($limit and $from) {
		$i++;
		if ($i < $from) continue;
	    }
            if ($limit and count($pages) >= $count) break;
            if (!$include_empty) {
            	if (!($data = $pagedb->get($page))) continue;
                list($latestversion,$flags,) = explode(':', $data, 3);
                unset($data);
                if ($latestversion == 0 || $flags != 0)
                    continue;   // current content is empty
            }
            $pages[] = $page;
        }
        return new WikiDB_backend_dbaBase_pageiter
            ($this, $pages,
             array('sortby'=>$sortby)); // already limited
    }

    function set_links($pagename, $links) {
        $this->_linkdb->set_links($pagename, $links);
    }

    function get_links($pagename, $reversed=true, $include_empty=false,
                       $sortby='', $limit='', $exclude='',
                       $want_relations=false)
    {
    	// optimization: if no relation at all is found, mark it in the iterator.
        $links = $this->_linkdb->get_links($pagename, $reversed, $want_relations);

        return new WikiDB_backend_dbaBase_pageiter
	    ($this, $links,
	     array('sortby'=>$sortby,
		   'limit' =>$limit,
		   'exclude'=>$exclude,
		   'want_relations'=>$want_relations,
		   'found_relations' => $want_relations
                     ? $this->_linkdb->found_relations : 0
		   ));
    }
  
    /**
     * @access public
     *
     * @return array of all linkrelations
     * Faster than the dumb WikiDB method.
     */
    function list_relations($also_attributes=false,
                            $only_attributes=false,
                            $sorted=true)
    {
        $linkdb = &$this->_linkdb;
        $relations = array();
        for ($link = $linkdb->_db->firstkey();
             $link!== false;
             $link = $linkdb->_db->nextkey())
        {
            if ($link[0] != 'o') continue;
            $links = $linkdb->_get_links('o', substr($link,1));
            foreach ($links as $link) { // linkto => page, linkrelation => page
                if (is_array($link)
                    and $link['relation']
                    and !in_array($link['relation'], $relations))
		{
		    $is_attribute = empty($link['linkto']); // a relation has both
		    if ($is_attribute) {
			if ($only_attributes or $also_attributes)
			    $relations[] = $link['relation'];
		    } elseif (!$only_attributes) {
			  $relations[] = $link['relation'];
		    }
		}
            }
        }
	if ($sorted) {
	    sort($relations);
	    reset($relations);
	}
        return $relations;
    }

    /**
     * WikiDB_backend_dumb_LinkSearchIter searches over all
     * pages and then all its links.  Since there are less
     * links than pages, and we easily get the pagename from
     * the link key, we iterate here directly over the
     * linkdb and check the pagematch there.
     *
     * @param $pages     object A TextSearchQuery object for the pagename filter.
     * @param $query     object A SearchQuery object (Text or Numeric) for the linkvalues,
     *                          linkto, linkfrom (=backlink), relation or attribute values.
     * @param $linktype  string One of the 4 linktypes "linkto",
     *                          "linkfrom" (=backlink), "relation" or "attribute".
     * 				Maybe also "relation+attribute" for the advanced search.
     * @param $relation  object A TextSearchQuery for the linkname or false.
     * @param $options   array Currently ignored. hash of sortby, limit, exclude.
     * @return object A WikiDB_backend_iterator.
     * @see WikiDB::linkSearch
     */
    function link_search( $pages, $query, $linktype,
                          $relation=false, $options=array() )
    {
        $linkdb = &$this->_linkdb;
        $links = array();
	$reverse = false;
	$want_relations = false;
	if ($linktype == 'relation') {
	    $want_relations = true;
	    $field = 'linkrelation';
	}
	if ($linktype == 'attribute') {
	    $want_relations = true;
	    $field = 'attribute';
	}
	if ($linktype == 'linkfrom') {
	    $reverse = true;
	}

        for ($link = $linkdb->_db->firstkey();
             $link!== false;
             $link = $linkdb->_db->nextkey())
        {
	    $type = $reverse ? 'i' : 'o';
            if ($link[0] != $type) continue;
	    $pagename = substr($link, 1);
	    if (!$pages->match($pagename)) continue;
	    if ($linktype == 'attribute') {
		$page = $GLOBALS['request']->_dbi->getPage($pagename);
		$attribs = $page->get('attributes');
		if ($attribs) {
		    /* Optimization on expressive searches:
		       for queries with multiple attributes.
		       Just take the defined placeholders from the query(ies)
		       if there are more attributes than query variables.
		    */
		    if ($query->getType() != 'text'
		        and !$relation
			and ((count($vars = $query->getVars()) > 1)
			     or (count($attribs) > count($vars))))
		    {
			// names must strictly match. no * allowed
			if (!$query->can_match($attribs)) continue;
			if (!($result = $query->match($attribs))) continue;
			foreach ($result as $r) {
			    $r['pagename'] = $pagename;
			    $links[] = $r;
			}
		    } else {
			// textsearch or simple value. no strict bind by name needed
			foreach ($attribs as $attribute => $value) {
			    if ($relation and !$relation->match($attribute)) continue;
			    if (!$query->match($value)) continue;
			    $links[] = array('pagename'  => $pagename,
					     'linkname'  => $attribute,
					     'linkvalue' => $value);
			}
		    }
		}
	    }
	    else {
		// TODO: honor limits. this can get large.
		if ($want_relations) {
		    // MAP linkrelation : pagename => thispagename : linkname : linkvalue
		    $_links = $linkdb->_get_links('o', $pagename);
		    foreach ($_links as $link) { // linkto => page, linkrelation => page
			if (!isset($link['relation']) or !$link['relation']) continue;
			if ($relation and !$relation->match($link['relation'])) continue;
			if (!$query->match($link['linkto'])) continue;
			$links[] = array('pagename'  => $pagename,
					 'linkname'  => $link['relation'],
					 'linkvalue' => $link['linkto']);
		    }
		} else {
		    $_links = $linkdb->_get_links($reverse ? 'i' : 'o', $pagename);
		    foreach ($_links as $link) { // linkto => page
			if (is_array($link))
			    $link = $link['linkto'];
			if (!$query->match($link)) continue;
			$links[] = array('pagename'  => $pagename,
					 'linkname'  => '',
					 'linkvalue' => $link);
		    }
		}
	    }
        }
	$options['want_relations'] = true; // Iter hack to force return of the whole hash
        return new WikiDB_backend_dbaBase_pageiter($this, $links, $options);
    }

    /**
     * Handle multi-searches for many relations and attributes in one expression.
     * Bind all required attributes and relations per page together and pass it
     * to one query.
     *   (is_a::city and population < 20000) and (*::city and area > 1000000)
     *   (is_a::city or linkto::CategoryCountry) and population < 20000 and area > 1000000
     * Note that the 'linkto' and 'linkfrom' links are relations, containing an array.
     *
     * @param $pages     object A TextSearchQuery object for the pagename filter.
     * @param $query     object A SemanticSearchQuery object for the links.
     * @param $options   array  Currently ignored. hash of sortby, limit, exclude
     *                          for the pagelist.
     * @return object A WikiDB_backend_iterator.
     * @see WikiDB::linkSearch
     */
    function relation_search( $pages, $query, $options=array() ) {
        $linkdb = &$this->_linkdb;
        $links = array();
	// We need to detect which attributes and relation names we should look for. NYI
	$want_attributes = $query->hasAttributes();
	$want_relation = $query->hasRelations();
	$linknames = $query->getLinkNames();
	// create a hash for faster checks
	$linkcheck = array();
	foreach ($linknames as $l) $linkcheck[$l] = 1;

        for ($link = $linkdb->_db->firstkey();
             $link!== false;
             $link = $linkdb->_db->nextkey())
        {
	    $type = $reverse ? 'i' : 'o';
            if ($link[0] != $type) continue;
	    $pagename = substr($link, 1);
	    if (!$pages->match($pagename)) continue;
	    $pagelinks = array();
	    if ($want_attributes) {
		$page = $GLOBALS['request']->_dbi->getPage($pagename);
		$attribs = $page->get('attributes');
		$pagelinks = $attribs;
	    }
	    if ($want_relations) {
		// all links contain arrays of pagenames, just the attributes
		// are guaranteed to be singular
		if (isset($linkcheck['linkfrom'])) {
		    $pagelinks['linkfrom'] = $linkdb->_get_links('i', $pagename);
		}
		$outlinks = $linkdb->_get_links('o', $pagename);
		$want_to = isset($linkcheck['linkto']);
		foreach ($outlinks as $link) { // linkto => page, relation => page
		    // all named links
		    if ((isset($link['relation'])) and $link['relation']
		        and isset($linkcheck[$link['relation']]))
			$pagelinks[$link['relation']][] = $link['linkto'];
		    if ($want_to)
			$pagelinks['linkto'][] = is_array($link) ? $link['linkto'] : $link;
		}
	    }
	    if ($result = $query->match($pagelinks)) {
		$links = array_merge($links, $result);
	    }
        }
	$options['want_relations'] = true; // Iter hack to force return of the whole hash
        return new WikiDB_backend_dbaBase_pageiter($this, $links, $options);
    }
};

function WikiDB_backend_dbaBase_sortby_pagename_ASC ($a, $b) {
    return strcasecmp($a, $b);
}
function WikiDB_backend_dbaBase_sortby_pagename_DESC ($a, $b) {
    return strcasecmp($b, $a);
}
function WikiDB_backend_dbaBase_sortby_mtime_ASC ($a, $b) {
    return WikiDB_backend_dbaBase_sortby_num($a, $b, 'mtime');
}
function WikiDB_backend_dbaBase_sortby_mtime_DESC ($a, $b) {
    return WikiDB_backend_dbaBase_sortby_num($b, $a, 'mtime');
}
/*
function WikiDB_backend_dbaBase_sortby_hits_ASC ($a, $b) {
    return WikiDB_backend_dbaBase_sortby_num($a, $b, 'hits');
}
function WikiDB_backend_dbaBase_sortby_hits_DESC ($a, $b) {
    return WikiDB_backend_dbaBase_sortby_num($b, $a, 'hits');
}
*/
function WikiDB_backend_dbaBase_sortby_num($aname, $bname, $field) {
    global $request;
    $dbi = $request->getDbh();
    // fields are stored in versiondata
    $av = $dbi->_backend->get_latest_version($aname);
    $bv = $dbi->_backend->get_latest_version($bname);
    $a = $dbi->_backend->get_versiondata($aname, $av, false);
    if (!$a) return -1;
    $b = $dbi->_backend->get_versiondata($bname, $bv, false);
    if (!$b or !isset($b[$field])) return 0;
    if (empty($a[$field])) return -1;
    if ((!isset($a[$field]) and !isset($b[$field])) or ($a[$field] === $b[$field])) {
        return 0;
    } else {
        return ($a[$field] < $b[$field]) ? -1 : 1;
    }
}

class WikiDB_backend_dbaBase_pageiter
extends WikiDB_backend_iterator
{
    // fixed for linkrelations
    function WikiDB_backend_dbaBase_pageiter(&$backend, &$pages, $options=false) {
        $this->_backend = $backend;
        $this->_options = $options;
        if ($pages) {
            if (!empty($options['sortby'])) {
                $sortby = WikiDB_backend::sortby($options['sortby'], 'db',
                                                 array('pagename','mtime'));
                // check for which column to sortby
                if ($sortby and !strstr($sortby, "hits ")) {
                    usort($pages, 'WikiDB_backend_dbaBase_sortby_'
                          .str_replace(' ','_',$sortby));
                }
            }
            if (!empty($options['limit'])) {
                list($offset,$limit) = WikiDB_backend::limit($options['limit']);
                $pages = array_slice($pages, $offset, $limit);
            }
            $this->_pages = $pages;
        } else
            $this->_pages = array();
    }

    // fixed for relations
    function next() {
        if ( ! ($page = array_shift($this->_pages)) )
            return false;
	if (!empty($this->_options['want_relations'])) {
	    // $linkrelation = $page['linkrelation'];
	    $pagename = $page['pagename'];
            if (!empty($this->_options['exclude'])
                and in_array($pagename, $this->_options['exclude']))
        	return $this->next();
	    return $page;
    	}
        if (!empty($this->_options['exclude'])
            and in_array($page, $this->_options['exclude']))
            return $this->next();
        return array('pagename' => $page);
    }

    function reset() {
        reset($this->_pages);
    }
    function free() {
        $this->_pages = array();
    }
};

class WikiDB_backend_dbaBase_linktable
{
    function WikiDB_backend_dbaBase_linktable(&$dba) {
        $this->_db = &$dba;
    }

    //TODO: try storing link lists as hashes rather than arrays.
    //      backlink deletion would be faster.
    function get_links($page, $reversed=true, $want_relations=false) {
        if ($want_relations) {
            $this->found_relations = 0;
            $links = $this->_get_links($reversed ? 'i' : 'o', $page);
            $linksonly = array();
            foreach ($links as $link) { // linkto => page, linkrelation => page
                if (is_array($link) and isset($link['relation'])) {
                    if ($link['relation'])
                        $this->found_relations++;
                    $linksonly[] = array('pagename'     => $link['linkto'],
                    		         'linkrelation' => $link['relation']);
                } else { // empty relations are stripped
                    $linksonly[] = array('pagename' => $link['linkto']);
                }
            }
            return $linksonly;
        } else {
            $links = $this->_get_links($reversed ? 'i' : 'o', $page);
            $linksonly = array();
            foreach ($links as $link) {
                if (is_array($link)) {
                    $linksonly[] = $link['linkto'];
                } else
                    $linksonly[] = $link;
            }
            return $linksonly;
        }
    }
  
    // fixed: relations ready
    function set_links($page, $links) {

        $oldlinks = $this->get_links($page, false, false);

        if (!is_array($links)) {
            assert(empty($links));
            $links = array();
        }
        $this->_set_links('o', $page, $links);
      
        /* Now for the backlink update we squash the linkto hashes into a simple array */
        $newlinks = array();
        foreach ($links as $hash) {
            if (!empty($hash['linkto']) and !in_array($hash['linkto'], $newlinks))
                 // for attributes it's empty
                $newlinks[] = $hash['linkto'];
            elseif (is_string($hash) and !in_array($hash, $newlinks))    
                $newlinks[] = $hash;
        }
        //$newlinks = array_unique($newlinks);
        sort($oldlinks);
        sort($newlinks);

        reset($newlinks);
        reset($oldlinks);
        $new = current($newlinks);
        $old = current($oldlinks);
        while ($new !== false || $old !== false) {
            if ($old === false || ($new !== false && $new < $old)) {
                // $new is a new link (not in $oldlinks).
                $this->_add_backlink($new, $page);
                $new = next($newlinks);
            }
            elseif ($new === false || $old < $new) {
                // $old is a obsolete link (not in $newlinks).
                $this->_delete_backlink($old, $page);
                $old = next($oldlinks);
            }
            else {
                // Unchanged link (in both $newlist and $oldlinks).
                assert($new == $old);
                $new = next($newlinks);
                $old = next($oldlinks);
            }
        }
    }

    /**
     * Rebuild the back-link index.
     *
     * This should never be needed, but if the database gets hosed for some reason,
     * this should put it back into a consistent state.
     *
     * We assume the forward links in the our table are correct, and recalculate
     * all the backlinks appropriately.
     */
    function rebuild () {
        $db = &$this->_db;

        // Delete the backlink tables, make a list of lo.page keys.
        $okeys = array();
        for ($key = $db->firstkey(); $key; $key = $db->nextkey()) {
            if ($key[0] == 'i')
                $db->delete($key);
            elseif ($key[0] == 'o')
                $okeys[] = $key;
            else {
                trigger_error("Bad key in linktable: '$key'", E_USER_WARNING);
            $db->delete($key);
        }
        }
        foreach ($okeys as $key) {
            $page = substr($key,1);
            $links = $this->_get_links('o', $page);
            $db->delete($key);
            $this->set_links($page, $links);
        }
    }

    function check() {
        $db = &$this->_db;

        // FIXME: check for sortedness and uniqueness in links lists.

        for ($key = $db->firstkey(); $key; $key = $db->nextkey()) {
            if (strlen($key) < 1 || ($key[0] != 'i' && $key[0] != 'o')) {
                $errs[] = "Bad key '$key' in table";
                continue;
            }
            $page = substr($key, 1);
            if ($key[0] == 'o') {
                // Forward links.
                foreach($this->_get_links('o', $page) as $link) {
                    $link = $link['linkto'];
                    if (!$this->_has_link('i', $link, $page))
                        $errs[] = "backlink entry missing for link '$page'->'$link'";
                }
            }
            else {
                assert($key[0] == 'i');
                // Backlinks.
                foreach($this->_get_links('i', $page) as $link) {
                    if (!$this->_has_link('o', $link, $page))
                        $errs[] = "link entry missing for backlink '$page'<-'$link'";
                }
            }
        }
 	//if ($errs) $this->rebuild();
        return isset($errs) ? $errs : false;
    }
  
    /* TODO: Add another lrRelationName key for relations.
     * lrRelationName: frompage => topage
     */

    function _add_relation($page, $linkedfrom) {
        $relations = $this->_get_links('r', $page);
        $backlinks[] = $linkedfrom;
        sort($backlinks);
        $this->_set_links('i', $page, $backlinks);
    }
      
    function _add_backlink($page, $linkedfrom) {
        $backlinks = $this->_get_links('i', $page);
        $backlinks[] = $linkedfrom;
        sort($backlinks);
        $this->_set_links('i', $page, $backlinks);
    }
  
    function _delete_backlink($page, $linkedfrom) {
        $backlinks = $this->_get_links('i', $page);
        foreach ($backlinks as $key => $backlink) {
            if ($backlink == $linkedfrom)
                unset($backlinks[$key]);
        }
        $this->_set_links('i', $page, $backlinks);
    }
  
    function _has_link($which, $page, $link) {
        $links = $this->_get_links($which, $page);
        // since links are always sorted, break if >
	// TODO: binary search
        foreach($links as $l) {
            if ($l['linkto'] == $link)
                return true;
            if ($l['linkto'] > $link)
                return false;
        }
        return false;
    }
  
    function _get_links($which, $page) {
        $data = $this->_db->get($which . $page);
        return $data ? unserialize($data) : array();
    }

    function _set_links($which, $page, &$links) {
        $key = $which . $page;
        if ($links)
            $this->_db->set($key, serialize($links));
        else
            $this->_db->set($key, false);
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
