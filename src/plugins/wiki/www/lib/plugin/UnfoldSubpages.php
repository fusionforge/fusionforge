<?php // -*-php-*-
// rcs_id('$Id: UnfoldSubpages.php 7638 2010-08-11 11:58:40Z vargenau $');
/*
 * Copyright 2002,2004,2005 $ThePhpWikiProgrammingTeam
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
 * UnfoldSubpages:  Lists the content of all SubPages of the current page.
 *   This is e.g. useful for the CalendarPlugin, to see all entries at once.
 *   Warning: Better don't use it with non-existant sections!
 *              The section extractor is currently quite unstable.
 * Usage:   <<UnfoldSubpages sortby=-mtime words=50 maxpages=5 >>
 * Author:  Reini Urban <rurban@x-ray.at>
 */

require_once("lib/PageList.php");
require_once("lib/TextSearchQuery.php");
require_once("lib/plugin/IncludePage.php");

class WikiPlugin_UnfoldSubpages
extends WikiPlugin_IncludePage
{
    function getName() {
        return _("UnfoldSubpages");
    }

    function getDescription () {
        return _("Includes the content of all SubPages of the current page.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array(
                   'pagename' => '[pagename]', // default: current page
                   //'header'  => '',  // expandable string
                   'quiet'   => false, // print no header
                   'sortby'   => '',    // [+|-]pagename, [+|-]mtime, [+|-]hits
                   'maxpages' => false, // maximum number of pages to include (== limit)
                   'smalltitle' => false, // if set, hide transclusion-title,
                                           //  just have a small link at the start of
                                            //  the page.
                   'words'   => false,         // maximum number of words
                                        //  per page to include
                   'lines'   => false,         // maximum number of lines
                                        //  per page to include
                   'bytes'   => false,         // maximum number of bytes
                                        //  per page to include
                   'sections' => false, // maximum number of sections per page to include
                   'section' => false,         // this named section per page only
                   'sectionhead' => false // when including a named
                                           //  section show the heading
                   ));
    }

    function run($dbi, $argstr, &$request, $basepage) {
        static $included_pages = false;
        if (!$included_pages) $included_pages = array($basepage);

        $args = $this->getArgs($argstr, $request);
        extract($args);
        $query = new TextSearchQuery($pagename . SUBPAGE_SEPARATOR . '*', true, 'glob');
        $subpages = $dbi->titleSearch($query, $sortby, $limit, $exclude);
        //if ($sortby)
        //    $subpages = $subpages->applyFilters(array('sortby' => $sortby, 'limit' => $limit, 'exclude' => $exclude));
        //$subpages = explodePageList($pagename . SUBPAGE_SEPARATOR . '*', false,
        //                            $sortby, $limit, $exclude);
        if (is_string($exclude) and !is_array($exclude))
            $exclude = PageList::explodePageList($exclude, false, false, $limit);
        $content = HTML();

        include_once('lib/BlockParser.php');
        $i = 0;
        while ($page = $subpages->next()) {
            $cpagename = $page->getName();
                 if ($maxpages and ($i++ > $maxpages)) {
                return $content;
            }
            if (in_array($cpagename, $exclude))
                    continue;
            // A page cannot include itself. Avoid doublettes.
            if (in_array($cpagename, $included_pages)) {
                $content->pushContent(HTML::p(sprintf(_("recursive inclusion of page %s ignored"),
                                                      $cpagename)));
                continue;
            }

            // Check if user is allowed to get the Page.
            if (!mayAccessPage ('view', $cpagename)) {
                    return $this->error(sprintf(_("Illegal inclusion of page %s: no read access"),
                    $cpagename));
            }

            // trap any remaining nonexistant subpages
            if ($page->exists()) {
                $r = $page->getCurrentRevision();
                $c = $r->getContent();   // array of lines
                // follow redirects
                if ((preg_match('/<'.'\?plugin\s+RedirectTo\s+page=(\S+)\s*\?'.'>/', implode("\n", $c), $m))
                  or (preg_match('/<'.'\?plugin\s+RedirectTo\s+page=(.*?)\s*\?'.'>/', implode("\n", $c), $m))
                  or (preg_match('/<<\s*RedirectTo\s+page=(\S+)\s*>>/', implode("\n", $c), $m))
                  or (preg_match('/<<\s*RedirectTo\s+page="(.*?)"\s*>>/', implode("\n", $c), $m)))
                {
                    // Strip quotes (simple or double) from page name if any
                    if ((string_starts_with($m[1], "'"))
                      or (string_starts_with($m[1], "\""))) {
                        $m[1] = substr($m[1], 1, -1);
                    }
                    // trap recursive redirects
                    if (in_array($m[1], $included_pages)) {
                            if (!$quiet)
                            $content->pushContent(
                                HTML::p(sprintf(_("recursive inclusion of page %s ignored"),
                                                $cpagename.' => '.$m[1])));
                        continue;
                    }
                        $cpagename = $m[1];

                            // Check if user is allowed to get the Page.
                            if (!mayAccessPage ('view', $cpagename)) {
                                    return $this->error(sprintf(_("Illegal inclusion of page %s: no read access"),
                                    $cpagename));
                            }

                        $page = $dbi->getPage($cpagename);
                    $r = $page->getCurrentRevision();
                    $c = $r->getContent();   // array of lines
                }

                // moved to IncludePage
                $ct = $this->extractParts ($c, $cpagename, $args);

                array_push($included_pages, $cpagename);
                if ($smalltitle) {
                    $pname = array_pop(explode(SUBPAGE_SEPARATOR, $cpagename)); // get last subpage name
                    // Use _("%s: %s") instead of .": ". for French punctuation
                    $ct = TransformText(sprintf(_("%s: %s"), "[$pname|$cpagename]",
                                                $ct),
                                        $r->get('markup'), $cpagename);
                }
                else {
                    $ct = TransformText($ct, $r->get('markup'), $cpagename);
                }
                array_pop($included_pages);
                if (! $smalltitle) {
                    $content->pushContent(HTML::p(array('class' => $quiet ?
                                                        '' : 'transclusion-title'),
                                                  fmt("Included from %s:",
                                                      WikiLink($cpagename))));
                }
                $content->pushContent(HTML(HTML::div(array('class' => $quiet ?
                                                           '' : 'transclusion'),
                                                     false, $ct)));
            }
        }
        if (! isset($cpagename)) {
            return $this->error(sprintf(_("%s has no subpages defined."), $pagename));
        }
        return $content;
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
