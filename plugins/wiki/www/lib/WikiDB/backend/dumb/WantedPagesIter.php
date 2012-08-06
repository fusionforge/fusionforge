<?php // -*-php-*-
// $Id: WantedPagesIter.php 7956 2011-03-03 17:08:31Z vargenau $

//require_once('lib/WikiDB/backend.php');

/**
 * This iterator will work with any WikiDB_backend
 * which has a working get_links(,'links_from') method.
 *
 * This is mostly here for testing, 'cause it's slow,slow,slow.
 */
class WikiDB_backend_dumb_WantedPagesIter
extends WikiDB_backend_iterator
{
    function WikiDB_backend_dumb_WantedPagesIter(&$backend, &$all_pages, $exclude='', $sortby='', $limit='') {
        $this->_allpages   = $all_pages;
        $this->_allpages_array   = $all_pages->asArray();
        $this->_backend = &$backend;
        if (!is_array($exclude))
            $this->exclude = $exclude ? PageList::explodePageList($exclude) : array();
        else
            $this->exclude = $exclude;
        $this->sortby = $sortby; // ignored
        if ($limit) { // extract from,count from limit
            list($this->from, $this->limit) = $backend->limit($limit);
        } else {
            $this->limit = 0;
            $this->from = 0;
        }
        $this->pos = 0;
        $this->pagelinks = array();
    }

    function next() {
        while ($page = $this->_allpages->next()) {
            while ($this->pagelinks) { // deferred return
            	return array_pop($this->pagelinks);
            }
    	    $this->pagelinks = array();
            if ($this->limit and $this->pos > $this->limit) break;
            $pagename = $page['pagename'];
            $links = $this->_backend->get_links($pagename, false);
            while ($link = $links->next()) {
            	if ($this->limit and $this->pos > $this->limit) break;
                if ($this->exclude and in_array($link['pagename'], $this->exclude)) continue;
                // better membership for a pageiterator?
                if (! in_array($link['pagename'], $this->_allpages_array)) {
                    if ($this->from and $this->pos < $this->from) continue;
                    // collect all links per page and return them deferred
                    $link['wantedfrom'] = $pagename;
                    $this->pagelinks[] = $link;
                    $this->pos++;
                }
            }
            $links->free();
            unset($links);
            if ($this->pagelinks) return array_pop($this->pagelinks);
        }
        return false;
    }

    function free() {
        unset($this->_allpages_array);
        $this->_allpages->free();
        unset($this->_allpages);
        unset($this->_backend);
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
