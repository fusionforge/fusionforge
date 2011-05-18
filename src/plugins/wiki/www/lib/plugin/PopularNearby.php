<?php // -*-php-*-
// $Id: PopularNearby.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/** Re-implement the classic phpwiki-1.2 feature of the
 *  popular nearby pages, specific to the from/to links:
 *    5 best incoming links: xx, xx, xx, ...
 *    5 best outgoing links: xx, xx, xx, ...
 *    5 most popular nearby: xx, xx, xx, ...
 */
/* Usage:

* <<PopularNearby mode=incoming >>
* <<PopularNearby mode=outgoing >>
* <<PopularNearby mode=nearby >>

*/


require_once('lib/PageList.php');

class WikiPlugin_PopularNearby
extends WikiPlugin
{
    function getName () {
        return _("PopularNearby");
    }

    function getDescription () {
        return _("List the most popular pages nearby.");
    }

    function getDefaultArguments() {
        return array('pagename' => '[pagename]',
                     'mode'     => 'nearby', // or 'incoming' or 'outgoing'
                     //'exclude'  => false,  // not yet
                     'limit'    => 5,
                     'noheader' => 0,
                    );
    }

    function run($dbi, $argstr, &$request, $basepage) {
            $args = $this->getArgs($argstr, $request);
        extract($args);
        $header = '';
        $page = $dbi->getPage($pagename);
        switch ($mode) {
        case 'incoming': // not the hits, but the number of links
            if (! $noheader )
                $header = sprintf(_("%d best incoming links: "),$limit);
            $links = $this->sortedLinks($page->getLinks("reversed"),"reversed",$limit);
            break;
        case 'outgoing': // not the hits, but the number of links
            if (! $noheader )
                $header = sprintf(_("%d best outgoing links: "),$limit);
            $links = $this->sortedLinks($page->getLinks(),false,$limit);
            break;
        case 'nearby':  // all linksfrom and linksto, sorted by hits
            if (! $noheader )
                $header = sprintf(_("%d most popular nearby: "),$limit);
            $inlinks = $page->getLinks();
            $outlinks = $page->getLinks('reversed');
            // array_merge doesn't sort out duplicate page objects here.
            $links = $this->sortedLinks(array_merge($inlinks->asArray(),
                                                    $outlinks->asArray()),
                                        false, $limit);
            break;
        }
        $html = HTML($header);
        for ($i=0; $i<count($links); $i++) {
            $html->pushContent($links[$i]['format'],$i<count($links)-1?', ':'');
        }
        return $html;
    }

    /**
     * Get and sort the links:
     *   mode=nearby:   $pages Array
     *   mode=incoming: $pages iter and $direction=true
     *   mode=outgoing: $pages iter and $direction=false
     *
     * @access private
     *
     * @param $pages array of WikiDB_Page's or a Page_iterator
     * @param $direction boolean: true if incoming links
     *
     * @return Array of sorted links
     */
    function sortedLinks($pages, $direction=false, $limit=5) {
            $links = array();
        if (is_array($pages)) {
            $already = array(); // need special duplicate check
            foreach ($pages as $page) {
                if (isset($already[$page->_pagename])) continue;
                else $already[$page->_pagename] = 1;
                // just the number of hits
                $hits = $page->get('hits');
                if (!$hits) continue;
                $links[] = array('hits' => $hits,
                                 'pagename' => $page->_pagename,
                                 'format' => HTML(WikiLink($page->_pagename),' (' . $hits . ')'));
            }
        } else {
            while ($page = $pages->next()) {
                // different score algorithm:
                //   the number of links to/from the page
                $l = $page->getLinks(!$direction);
                $score = $l->count();
                if (!$score) continue;
                $name = $page->_pagename;
                $links[] = array('hits' => $score,
                                 'pagename' => $name,
                                 'format' => HTML(WikiLink($name),' (' . $score . ')'));
            }
            $pages->free();
        }
        if (count($links) > $limit)
            array_splice($links, $limit);
        return $this->sortByHits($links);
    }

    function sortByHits($links) {
        if (!$links) return array();
        usort($links,'cmp_by_hits'); // php-4.0.6 cannot use methods
        reset($links);
        return $links;
    }
};

function cmp_by_hits($a, $b) {
     if ($a['hits'] == $b['hits']) return 0;
     return $a['hits'] < $b['hits'] ? 1 : -1;
}


// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
