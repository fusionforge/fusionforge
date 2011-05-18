<?php // -*-php-*-
// $Id: TitleSearch.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 1999,2000,2001,2002,2004,2005,2010 $ThePhpWikiProgrammingTeam
 * Copyright 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('lib/TextSearchQuery.php');
require_once('lib/PageList.php');

/**
 * Display results of pagename search.
 * Provides no own input box, just <?plugin-form TitleSearch?> is enough.
 * Fancier Inputforms can be made using <<WikiFormRich ...>> to support regex and case_exact args.
 *
 * If only one pages is found and auto_redirect is true, this page is displayed immediatly,
 * otherwise the found pagelist is displayed.
 * The workhorse TextSearchQuery converts the query string from google-style words
 * to the required DB backend expression.
 *   (word and word) OR word, -word, "two words"
 * regex=auto tries to detect simple glob-style wildcards and expressions,
 * like xx*, *xx, ^xx, xx$, ^word$.
 */
class WikiPlugin_TitleSearch
extends WikiPlugin
{
    function getName () {
        return _("TitleSearch");
    }

    function getDescription () {
        return _("Search the titles of all pages in this wiki.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(), // paging and more.
             array('s'             => false,
                   'auto_redirect' => false,
                   'noheader'      => false,
                   'exclude'       => false,
                   'info'          => false,
                   'case_exact'    => false,
                   'regex'         => 'auto',
                   'format'        => false,
                   ));
    }
    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor
    // exclude arg allows multiple pagenames exclude=Php*,RecentChanges

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        if (empty($args['s'])) {
            return HTML();
        }

        // ^S != S*   ^  matches only beginning of phrase, not of word.
        //            x* matches any word beginning with x
        $query = new TextSearchQuery($args['s'], $args['case_exact'], $args['regex']);
        $pages = $dbi->titleSearch($query,$args['sortby'],$args['limit'],$args['exclude']);

        $pagelist = new PageList($args['info'], $args['exclude'], $args);
        $pagelist->addPages($pages);

        // Provide an unknown WikiWord link to allow for page creation
        // when a search returns no results
        if (!$args['noheader']) {
            $s = $args['s'];
            $total = $pagelist->getTotal();
            if (!$total and !$query->_regex) {
                $s = WikiLink($args['s'], 'auto');
            }
            if ($total) {
                $pagelist->setCaption(fmt("Title search results for '%s' (%d total)", $s, $total));
            } else {
                $pagelist->setCaption(fmt("Title search results for '%s'", $s));
            }
        }

        if ($args['auto_redirect'] && ($pagelist->getTotal() == 1)) {
            $page = $pagelist->first();
            $request->redirect(WikiURL($page->getName(), false, 'absurl'), false);
        }

        return $pagelist;
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
