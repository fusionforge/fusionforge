<?php

/*
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2011 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * IncludePage:  include text from another wiki page in this one
 * usage:   <<IncludePage page=OtherPage rev=6 quiet=1 words=50 lines=6>>
 * author:  Joe Edelman <joe@orbis-tertius.net>
 */

class WikiPlugin_IncludePage
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Include text from another wiki page.");
    }

    function getDefaultArguments()
    {
        return array('page' => false, // the page to include
            'rev' => false, // the revision (defaults to most recent)
            'quiet' => false, // if set, inclusion appears as normal content
            'bytes' => false, // maximum number of bytes to include
            'words' => false, // maximum number of words to include
            'lines' => false, // maximum number of lines to include
            'sections' => false, // maximum number of sections to include
            'section' => false, // include a named section
            'sectionhead' => false // when including a named section show the heading
        );
    }

    function getWikiPageLinks($argstr, $basepage)
    {
        extract($this->getArgs($argstr));

        if (!isset($page))
            return false;
        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
        }
        if (!$page or !$page->name)
            return false;
        return array(array('linkto' => $page->name, 'relation' => 0));
    }

    // Avoid warning in:
    // <<IncludePages pages=<!plugin-list BackLinks page=CategoryWikiPlugin !> >>
    function handle_plugin_args_cruft($argstr, $args)
    {
        return;
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        $args = $this->getArgs($argstr, $request);
        extract($args);

        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
            $page = $page->name;
        }
        if (!$page) {
            return $this->error(sprintf(_("A required argument “%s” is missing."), 'page'));
        }

        // A page can include itself once (this is needed, e.g.,  when editing
        // TextFormattingRules).
        static $included_pages = array();
        if (in_array($page, $included_pages)) {
            return $this->error(sprintf(_("Recursive inclusion of page %s ignored"),
                $page));
        }

        // Check if page exists
        if (!($dbi->isWikiPage($page))) {
            return $this->error(sprintf(_("Page “%s” does not exist."), $page));
        }

        // Check if user is allowed to get the Page.
        if (!mayAccessPage('view', $page)) {
            return $this->error(sprintf(_("Illegal inclusion of page %s: no read access."),
                $page));
        }

        $p = $dbi->getPage($page);
        if ($rev) {
            if (!is_whole_number($rev) or !($rev > 0)) {
                return $this->error(_("Error: rev must be a positive integer."));
            }
            $r = $p->getRevision($rev);
            if ((!$r) || ($r->hasDefaultContents())) {
                return $this->error(sprintf(_("%s: no such revision %d."),
                    $page, $rev));
            }
        } else {
            $r = $p->getCurrentRevision();
        }
        $c = $r->getContent();

        // follow redirects
        if ((preg_match('/<' . '\?plugin\s+RedirectTo\s+page=(\S+)\s*\?' . '>/', implode("\n", $c), $m))
            or (preg_match('/<' . '\?plugin\s+RedirectTo\s+page=(.*?)\s*\?' . '>/', implode("\n", $c), $m))
            or (preg_match('/<<\s*RedirectTo\s+page=(\S+)\s*>>/', implode("\n", $c), $m))
            or (preg_match('/<<\s*RedirectTo\s+page="(.*?)"\s*>>/', implode("\n", $c), $m))
        ) {
            // Strip quotes (simple or double) from page name if any
            if ((string_starts_with($m[1], "'"))
                or (string_starts_with($m[1], "\""))
            ) {
                $m[1] = substr($m[1], 1, -1);
            }
            // trap recursive redirects
            if (in_array($m[1], $included_pages)) {
                return $this->error(sprintf(_("Recursive inclusion of page %s ignored"),
                    $page . ' => ' . $m[1]));
            }
            $page = $m[1];
            $p = $dbi->getPage($page);
            $r = $p->getCurrentRevision();
            $c = $r->getContent(); // array of lines
        }

        $ct = $this->extractParts($c, $page, $args);

        // exclude from expansion
        if (preg_match('/<noinclude>.+<\/noinclude>/s', $ct)) {
            $ct = preg_replace("/<noinclude>.+?<\/noinclude>/s", "", $ct);
        }
        // only in expansion
        $ct = preg_replace("/<includeonly>(.+)<\/includeonly>/s", "\\1", $ct);

        array_push($included_pages, $page);

        include_once 'lib/BlockParser.php';
        $content = TransformText($ct, $page);

        array_pop($included_pages);

        if ($quiet)
            return $content;

        if ($rev) {
            $transclusion_title = fmt("Included from %s (revision %d)", WikiLink($page), $rev);
        } else {
            $transclusion_title = fmt("Included from %s", WikiLink($page));
        }
        return HTML(HTML::p(array('class' => 'transclusion-title'), $transclusion_title),
            HTML::div(array('class' => 'transclusion'), false, $content));
    }

    /**
     * handles the arguments: section, sectionhead, lines, words, bytes,
     * for UnfoldSubpages, IncludePage, ...
     */
    protected function extractParts($c, $pagename, $args)
    {
        extract($args);

        if ($section) {
            if ($sections) {
                $c = extractSection($section, $c, $pagename, $quiet, 1);
            } else {
                $c = extractSection($section, $c, $pagename, $quiet, $sectionhead);
            }
        }
        if ($sections) {
            $c = extractSections($sections, $c, $pagename, $quiet, 1);
        }
        if ($lines) {
            $c = array_slice($c, 0, $lines);
            $c[] = sprintf(_(" ... first %d lines"), $lines);
        }
        if ($words) {
            $c = firstNWordsOfContent($words, $c);
        }
        if ($bytes) {
            $ct = implode("\n", $c); // one string
            if (strlen($ct) > $bytes) {
                $ct = substr($c, 0, $bytes);
                $c = array($ct, sprintf(_(" ... first %d bytes"), $bytes));
            }
        }
        $ct = implode("\n", $c); // one string
        return $ct;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
