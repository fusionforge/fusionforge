<?php // -*-php-*-
// $Id: SiteMap.php 8071 2011-05-18 14:56:14Z vargenau $
/**
 * Copyright 1999,2000,2001,2002,2004 $ThePhpWikiProgrammingTeam
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

/**
 * http://sourceforge.net/tracker/?func=detail&aid=537380&group_id=6121&atid=306121
 *
 * Submitted By: Cuthbert Cat (cuthbertcat)
 *
 * This is a quick mod of BackLinks to do the job recursively. If your
 * site is categorized correctly, and all the categories are listed in
 * CategoryCategory, then a RecBackLinks there will produce a contents
 * page for the entire site.
 *
 * The list is as deep as the recursion level.
 *
 * direction: Get BackLinks or forward links (links listed on the page)
 *
 * firstreversed: If true, get BackLinks for the first page and forward
 * links for the rest. Only applicable when direction = 'forward'.
 *
 * excludeunknown: If true (default) then exclude any mentioned pages
 * which don't exist yet.  Only applicable when direction = 'forward'.
 */
require_once('lib/PageList.php');

class WikiPlugin_SiteMap
extends WikiPlugin
{
    var $_pagename;

    function getName () {
        return _("SiteMap");
    }

    function getDescription () {
        return _("Recursively get BackLinks or links");
    }

    function getDefaultArguments() {
        return array('exclude'        => '',
                     'include_self'   => 0,
                     'noheader'       => 0,
                     'page'           => '[pagename]',
                     'description'    => $this->getDescription(),
                     'reclimit'       => 4,
                     'info'           => false,
                     'direction'      => 'back',
                     'firstreversed'  => false,
                     'excludeunknown' => true,
                     'includepages'   => '', // only for IncludeSiteMap and IncludeTree
                     'category'       => '', // optional category filter (comma-delimited)
                     'dtree'          => false, // optional for IncludeTree
                     );
    }
    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor
    // exclude arg allows multiple pagenames
    // exclude=HomePage,RecentChanges

    // Fixme: overcome limitation if two SiteMap plugins are in the same page!
    // static $VisitedPages still holds it
    function recursivelyGetBackLinks($startpage, $pagearr, $level = '*',
                                     $reclimit = '***') {
        static $VisitedPages = array();

        $startpagename = $startpage->getName();
        //trigger_error("DEBUG: recursivelyGetBackLinks( $startpagename , $level )");
        if ($level == $reclimit)
            return $pagearr;
        if (in_array($startpagename, $VisitedPages))
            return $pagearr;
        array_push($VisitedPages, $startpagename);
        $pagelinks = $startpage->getLinks();
        while ($link = $pagelinks->next()) {
            $linkpagename = $link->getName();
            if (($linkpagename != $startpagename)
                and (!$this->ExcludedPages or !preg_match("/".$this->ExcludedPages."/", $linkpagename)))
            {
                $pagearr[$level . " [$linkpagename]"] = $link;
                $pagearr = $this->recursivelyGetBackLinks($link, $pagearr,
                                                          $level . '*',
                                                          $reclimit);
            }
        }
        return $pagearr;
    }

    function recursivelyGetLinks($startpage, $pagearr, $level = '*',
                                 $reclimit = '***') {
        static $VisitedPages = array();

        $startpagename = $startpage->getName();
        //trigger_error("DEBUG: recursivelyGetLinks( $startpagename , $level )");
        if ($level == $reclimit)
            return $pagearr;
        if (in_array($startpagename, $VisitedPages))
            return $pagearr;
        array_push($VisitedPages, $startpagename);
        $reversed = (($this->firstreversed)
                     && ($startpagename == $this->initialpage));
        //trigger_error("DEBUG: \$reversed = $reversed");
        $pagelinks = $startpage->getLinks($reversed);
        while ($link = $pagelinks->next()) {
            $linkpagename = $link->getName();
            if (($linkpagename != $startpagename) and
                (!$this->ExcludedPages or !preg_match("/$this->ExcludedPages/", $linkpagename)))
            {
                if (!$this->excludeunknown or $this->dbi->isWikiPage($linkpagename)) {
                    $pagearr[$level . " [$linkpagename]"] = $link;
                    $pagearr = $this->recursivelyGetLinks($link, $pagearr,
                                                          $level . '*',
                                                          $reclimit);
                }
            }
        }
        return $pagearr;
    }


    function run($dbi, $argstr, &$request, $basepage) {
        include_once('lib/BlockParser.php');

        $args = $this->getArgs($argstr, $request, false);
        extract($args);
        if (!$page)
            return '';
        $this->_pagename = $page;
        $out = ''; // get rid of this
        $html = HTML();
        if (empty($exclude)) $exclude = array();
        if (!$include_self)
            $exclude[] = $page;
        $this->ExcludedPages = empty($exclude) ? "" : ("^(?:" . join("|", $exclude) . ")");
        $this->_default_limit = str_pad('', 3, '*');
        if (is_numeric($reclimit)) {
            if ($reclimit < 0)
                $reclimit = 0;
            if ($reclimit > 10)
                $reclimit = 10;
            $limit = str_pad('', $reclimit + 2, '*');
        } else {
            $limit = '***';
        }
        //Fixme:  override given arg
        $description = $this->getDescription();
        if (! $noheader) {
            $out = $this->getDescription() ." ". sprintf(_("(max. recursion level: %d)"),
                                                         $reclimit) . ":\n\n";
            $html->pushContent(TransformText($out, 1.0, $page));
        }
        $pagelist = new PageList($info, $exclude);
        $p = $dbi->getPage($page);

        $pagearr = array();
        if ($direction == 'back') {
            $pagearr = $this->recursivelyGetBackLinks($p, $pagearr, "*", $limit);
        }
        else {
            $this->dbi = $dbi;
            $this->initialpage = $page;
            $this->firstreversed = $firstreversed;
            $this->excludeunknown = $excludeunknown;
            $pagearr = $this->recursivelyGetLinks($p, $pagearr, "*", $limit);
        }

        reset($pagearr);
        if (!empty($includepages)) {
            // disallow direct usage, only via child class IncludeSiteMap
            if (!isa($this,"WikiPlugin_IncludeSiteMap") and !isa($this,"WikiPlugin_IncludeTree"))
                $includepages = '';
            if (!is_string($includepages))
                $includepages = ' '; // avoid plugin loader problems
            $loader = new WikiPluginLoader();
            $plugin = $loader->getPlugin($dtree ? 'DynamicIncludePage' : 'IncludePage', false);
            $nothing = '';
        }

        while (list($key, $link) = each($pagearr)) {
            if (!empty($includepages)) {
                $a = substr_count($key, '*');
                $indenter = str_pad($nothing, $a);
                //$request->setArg('IncludePage', 1);
                // quote linkname, by Stefan Schorn
                $plugin_args = 'page=\'' . $link->getName() . '\' ' . $includepages;
                $pagehtml = $plugin->run($dbi, $plugin_args, $request, $basepage);
                $html->pushContent($pagehtml);
                //$html->pushContent( HTML(TransformText($indenter, 1.0, $page), $pagehtml));
                //$out .= $indenter . $pagehtml . "\n";
            }
            else {
                $out .= $key . "\n";
            }
        }
        if (empty($includepages)) {
            return TransformText($out, 2.0, $page);
        } else {
            return $html;
        }
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
