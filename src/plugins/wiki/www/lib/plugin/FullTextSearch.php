<?php // -*-php-*-
// rcs_id('$Id: FullTextSearch.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright 1999-2002,2004,2005,2007,2009 $ThePhpWikiProgrammingTeam
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
require_once("lib/PageList.php");

/**
 * Case insensitive fulltext search
 * Options: case_exact, regex, hilight
 *          Stoplist
 *
 * See also:
 *   Hooks to search in external documents: ExternalTextSearch
 *   Only uploaded: textfiles, PDF, HTML, DOC, XLS, ... or
 *   External apps: xapian-omages seems to be the better than lucene,
 *   lucene.net, swish, nakamazu, ...
 *
 * See http://sf.net/tracker/index.php?aid=927395&group_id=6121&atid=106121
 * Wordaround to let the dead locks occur somewhat later:
 *   Increase the memory limit of PHP from 8 MB to 32 MB
 *   php.ini: memory_limit = 32 MB
 */
class WikiPlugin_FullTextSearch
extends WikiPlugin
{
    function getName() {
        return _("FullTextSearch");
    }

    function getDescription() {
        return _("Search the content of all pages in this wiki.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(), // paging and more.
             array('s'        => false,
                   'hilight'  => true,
                   'case_exact' => false,
                   'regex'    => 'auto',
                   'sortby'   => '-hi_content',
                   'noheader' => false,
                   'exclude'  => false,   // comma-seperated list of glob
                   'quiet'    => true));  // be less verbose
    }

    function run($dbi, $argstr, &$request, $basepage) {

        $args = $this->getArgs($argstr, $request);

        if (empty($args['s'])) {
            return HTML();
        }
        extract($args);

        $query = new TextSearchQuery($s, $case_exact, $regex);
        $pages = $dbi->fullSearch($query, $sortby, $limit, $exclude);
        $lines = array();
        $hilight_re = $hilight ? $query->getHighlightRegexp() : false;
        $count = 0;

        if ($quiet) { // see how easy it is with PageList...
            unset($args['info']);
            $args['listtype'] = 'dl';
            $args['types'] = array(new _PageList_Column_content
              ('rev:hi_content', _("Content"), "left", $s, $hilight_re));
            $list = new PageList(false, $exclude, $args);
            $list->setCaption(fmt("Full text search results for '%s'", $s));
            while ($page = $pages->next()) {
                $list->addPage( $page );
            }
            return $list;
        }

        // Todo: we should better define a new PageListDL class for dl/dt/dd lists
        // But the new column types must have a callback then. (showhits)
        // See e.g. WikiAdminSearchReplace for custom pagelist columns
        $list = HTML::dl();
        if (!$limit or !is_int($limit))
            $limit = 0;
        // expand all page wildcards to a list of pages which should be ignored
        if ($exclude) $exclude = explodePageList($exclude);
        while ($page = $pages->next() and (!$limit or ($count < $limit))) {
            $name = $page->getName();
            if ($exclude and in_array($name,$exclude)) continue;
            $count++;
            $list->pushContent(HTML::dt(WikiLink($page)));
            if ($hilight_re)
                $list->pushContent($this->showhits($page, $hilight_re));
            unset($page);
        }
        if ($limit and $count >= $limit) //todo: pager link to list of next matches
            $list->pushContent(HTML::dd(fmt("only %d pages displayed",$limit)));
        if (!$list->getContent())
            $list->pushContent(HTML::dd(_("<no matches>")));

        if (!empty($pages->stoplisted))
            $list = HTML(HTML::p(fmt(_("Ignored stoplist words '%s'"),
                                     join(', ', $pages->stoplisted))),
                         $list);
        if ($noheader)
            return $list;
        return HTML(HTML::p(fmt("Full text search results for '%s'", $s)),
                    $list);
    }

    function showhits($page, $hilight_re) {
        $current = $page->getCurrentRevision();
        $matches = preg_grep("/$hilight_re/i", $current->getContent());
        $html = array();
        foreach ($matches as $line) {
            $line = $this->highlight_line($line, $hilight_re);
            $html[] = HTML::dd(HTML::small(array('class' => 'search-context'),
                                           $line));
        }
        return $html;
    }

    function highlight_line ($line, $hilight_re) {
        while (preg_match("/^(.*?)($hilight_re)/i", $line, $m)) {
            $line = substr($line, strlen($m[0]));
            $html[] = $m[1];    // prematch
            $html[] = HTML::strong(array('class' => 'search-term'), $m[2]); // match
        }
        $html[] = $line;        // postmatch
        return $html;
    }
};

/*
 * List of Links and link to ListLinks
 */
class _PageList_Column_hilight extends _PageList_Column {
    function _PageList_Column_WantedPages_links (&$params) {
        $this->parentobj =& $params[3];
        $this->_PageList_Column($params[0],$params[1],$params[2]);
    }
    function _getValue(&$page, $revision_handle) {
            $html = false;
        $pagename = $page->getName();
        $count = count($this->parentobj->_wpagelist[$pagename]);
        return LinkURL(WikiURL($page, array('action' => 'BackLinks'), false),
                        fmt("(%d Links)", $count));
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
