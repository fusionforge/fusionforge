<?php // -*-php-*-
// rcs_id('$Id: TextSearchIter.php 7638 2010-08-11 11:58:40Z vargenau $');

class WikiDB_backend_dumb_TextSearchIter
extends WikiDB_backend_iterator
{
    function WikiDB_backend_dumb_TextSearchIter(&$backend, &$pages, $search, $fulltext=false,
                                                $options=array())
    {
        $this->_backend = &$backend;
        $this->_pages = $pages;
        $this->_fulltext = $fulltext;
        $this->_search  =& $search;
        $this->_index   = 0;
        $this->_stoplist =& $search->_stoplist;
        $this->stoplisted = array();

	$this->_from = 0;
        if (isset($options['limit']))  // extract from,count from limit
	    list($this->_from, $this->_count) = WikiDB_backend::limit($options['limit']);
        else
	    $this->_count = 0;
        if (isset($options['exclude'])) $this->_exclude = $options['exclude'];
        else $this->_exclude = false;
    }

    function _get_content(&$page) {
        $backend = &$this->_backend;
        $pagename = $page['pagename'];
      
        if (!isset($page['versiondata'])) {
            $version = $backend->get_latest_version($pagename);
            $page['versiondata'] = $backend->get_versiondata($pagename, $version, true);
        }
        return $page['versiondata']['%content'];
    }
      
    function _match(&$page) {
        $text = $page['pagename'];
        if ($result = $this->_search->match($text)) { // first match the pagename only
            return $this->_search->score($text) * 2.0;
	}

        if ($this->_fulltext) {
            // eliminate stoplist words from fulltext search
            if (preg_match("/^".$this->_stoplist."$/i", $text)) {
                $this->stoplisted[] = $text;
                return $result;
            }
            $text .= "\n" . $this->_get_content($page);
            // Todo: Bonus for meta keywords (* 1.5) and headers
            if ($this->_search->match($text))
		return $this->_search->score($text);
        } else {
            return $result;
	}
    }

    function next() {
        $pages = &$this->_pages;
        while ($page = $pages->next()) {
            if ($score = $this->_match($page)) {
	        $this->_index++;
	        if (($this->_from > 0) and ($this->_index <= $this->_from))
                    // not yet reached the offset
		    continue;
                /*if ($this->_count and ($this->_index > $this->_count)) {
                    // reached the limit, but need getTotal
                    $this->_count++;
                    return false;
                }*/
                if (is_array($page))
		    $page['score'] = $score;
		else  
		    $page->score = $score;
                return $page;
            }
        }
        return false;
    }

    function free() {
        $this->_pages->free();
    }
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End: 
?>
