<?php // -*-php-*-
// $Id: SearchHighlight.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2004,2007 $ThePhpWikiProgrammingTeam
 *
 * This file is NOT part of PhpWiki.
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once("lib/TextSearchQuery.php");
require_once("lib/PageList.php");

/**
 * When someone is referred from a search engine like Google, Yahoo
 * or our own fulltextsearch, the terms they search for are highlighted.
 * See http://wordpress.org/about/shots/1.2/plugins.png
 *
 * This plugin is normally just used to print a header through an action page.
 * The highlighting is done through InlineParser automatically if ENABLE_SEARCHHIGHLIGHT is enabled.
 * If hits = 1, then the list of found terms is also printed.
 */
class WikiPlugin_SearchHighlight
extends WikiPlugin
{
    function getName() {
        return _("SearchHighlight");
    }

    function getDescription() {
        return _("Hilight referred search terms.");
    }

    function getDefaultArguments() {
        // s, engine and engine_url are picked from the request
        return array('noheader' => false,    //don't print the header
                     'hits'     => false,    //print the list of lines with lines terms additionally
                     's'        => false,
                     'case_exact' => false,  //not yet supported
                     'regex'    => false,    //not yet supported
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        if (empty($args['s']) and isset($request->_searchhighlight)) {
            $args['s'] = $request->_searchhighlight['query'];
        }
        if (empty($args['s']))
            return '';
        extract($args);
        $html = HTML();
        if (!$noheader and isset($request->_searchhighlight)) {
            $engine = $request->_searchhighlight['engine'];
            $html->pushContent(HTML::div(array('class' => 'search-context'),
                                             fmt("%s: Found %s through %s",
                                                 $basepage,
                                             $request->_searchhighlight['query'],
                                             $engine)));
        }
        if ($hits) {
            $query = new TextSearchQuery($s, $case_exact, $regex);
            $lines = array();
            $hilight_re = $query->getHighlightRegexp();
            $page = $request->getPage();
            $html->pushContent($this->showhits($page, $hilight_re));
        }
        return $html;
    }

    function showhits($page, $hilight_re) {
        $current = $page->getCurrentRevision();
        $matches = preg_grep("/$hilight_re/i", $current->getContent());
        $html = HTML::dl();
        foreach ($matches as $line) {
            $line = $this->highlight_line($line, $hilight_re);
            $html->pushContent(HTML::dd(array('class' => 'search-context'),
                                        HTML::small($line)));
        }
        return $html;
    }

    function highlight_line ($line, $hilight_re) {
        $html = HTML();
        while (preg_match("/^(.*?)($hilight_re)/i", $line, $m)) {
            $line = substr($line, strlen($m[0]));
            // prematch + match
            $html->pushContent($m[1], HTML::strong(array('class' => 'search-term'), $m[2]));
        }
        $html->pushContent($line);       // postmatch
        return $html;
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
