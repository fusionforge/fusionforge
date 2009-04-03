<?php // -*-php-*-
rcs_id('$Id: IncludePage.php 6185 2008-08-22 11:40:14Z vargenau $');
/*
 Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam

 This file is part of PhpWiki.

 PhpWiki is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 PhpWiki is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with PhpWiki; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/**
 * IncludePage:  include text from another wiki page in this one
 * usage:   <?plugin IncludePage page=OtherPage rev=6 quiet=1 words=50 lines=6?>
 * author:  Joe Edelman <joe@orbis-tertius.net>
 */

class WikiPlugin_IncludePage
extends WikiPlugin
{
    function getName() {
        return _("IncludePage");
    }

    function getDescription() {
        return _("Include text from another wiki page.");
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6185 $");
    }

    function getDefaultArguments() {
        return array( 'page'    => false, // the page to include
                      'rev'     => false, // the revision (defaults to most recent)
                      'quiet'   => false, // if set, inclusion appears as normal content
                      'bytes'   => false, // maximum number of bytes to include
                      'words'   => false, // maximum number of words to include
                      'lines'   => false, // maximum number of lines to include
                      'sections' => false, // maximum number of sections to include
                      'section' => false, // include a named section
                      'sectionhead' => false // when including a named section show the heading
                      );
    }

    function getWikiPageLinks($argstr, $basepage) {
        extract($this->getArgs($argstr));

        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
        }
        if (!$page or !$page->name)
            return false;
        return array(array('linkto' => $page->name, 'relation' => 0));
    }
                
    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
            $page = $page->name;
        }
        if (!$page) {
            return $this->error(_("no page specified"));
        }

        // A page can include itself once (this is needed, e.g.,  when editing
        // TextFormattingRules).
        static $included_pages = array();
        if (in_array($page, $included_pages)) {
            return $this->error(sprintf(_("recursive inclusion of page %s ignored"),
                                        $page));
        }

        $p = $dbi->getPage($page);
        if ($rev) {
            $r = $p->getRevision($rev);
            if (!$r) {
                return $this->error(sprintf(_("%s(%d): no such revision"),
                                            $page, $rev));
            }
        } else {
            $r = $p->getCurrentRevision();
        }
        $c = $r->getContent();
        
        // follow redirects
        if (preg_match('/<'.'\?plugin\s+RedirectTo\s+page=(\w+)\s+\?'.'>/', 
                       implode("\n", $c), $m))
        {
            // trap recursive redirects
            if (in_array($m[1], $included_pages)) {
                return $this->error(sprintf(_("recursive inclusion of page %s ignored"),
                                                $page.' => '.$m[1]));
            }
	    $page = $m[1];
	    $p = $dbi->getPage($page);
            $r = $p->getCurrentRevision();
            $c = $r->getContent();   // array of lines
        }
        
        $ct = $this->extractParts ($c, $page, $args);

        array_push($included_pages, $page);

        include_once('lib/BlockParser.php');
        $content = TransformText($ct, $r->get('markup'), $page);

        array_pop($included_pages);

        if ($quiet)
            return $content;

        return HTML(HTML::p(array('class' => 'transclusion-title'),
                            fmt("Included from %s", WikiLink($page))),

                    HTML::div(array('class' => 'transclusion'),
                              false, $content));
    }
    
    /** 
     * handles the arguments: section, sectionhead, lines, words, bytes,
     * for UnfoldSubpages, IncludePage, ...
     */
    function extractParts ($c, $pagename, $args) {
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
};

// This is an excerpt from the css file I use:
//
// .transclusion-title {
//   font-style: oblique;
//   font-size: 0.75em;
//   text-decoration: underline;
//   text-align: right;
// }
//
// DIV.transclusion {
//   background: lightgreen;
//   border: thin;
//   border-style: solid;
//   padding-left: 0.8em;
//   padding-right: 0.8em;
//   padding-top: 0px;
//   padding-bottom: 0px;
//   margin: 0.5ex 0px;
// }

// KNOWN ISSUES:
// - line & word limit doesn't work if the included page itself
//   includes a plugin


// $Log: not supported by cvs2svn $
// Revision 1.30  2008/07/02 17:48:01  vargenau
// Fix mix-up of bytes and lines
//
// Revision 1.29  2007/06/03 21:58:51  rurban
// Fix for Bug #1713784
// Includes this patch and a refactoring.
// RedirectTo is still not handled correctly.
//
// Revision 1.28  2006/04/17 17:28:21  rurban
// honor getWikiPageLinks change linkto=>relation
//
// Revision 1.27  2004/11/17 20:07:18  rurban
// just whitespace
//
// Revision 1.26  2004/09/25 16:35:09  rurban
// use stdlib firstNWordsOfContent, extractSection
//
// Revision 1.25  2004/07/08 20:30:07  rurban
// plugin->run consistency: request as reference, added basepage.
// encountered strange bug in AllPages (and the test) which destroys ->_dbi
//
// Revision 1.24  2004/02/17 12:11:36  rurban
// added missing 4th basepage arg at plugin->run() to almost all plugins. This caused no harm so far, because it was silently dropped on normal usage. However on plugin internal ->run invocations it failed. (InterWikiSearch, IncludeSiteMap, ...)
//
// Revision 1.23  2003/03/25 21:01:52  dairiki
// Remove debugging cruft.
//
// Revision 1.22  2003/03/13 18:57:56  dairiki
// Hack so that (when using the IncludePage plugin) the including page shows
// up in the BackLinks of the included page.
//
// Revision 1.21  2003/02/21 04:12:06  dairiki
// Minor fixes for new cached markup.
//
// Revision 1.20  2003/01/18 21:41:02  carstenklapp
// Code cleanup:
// Reformatting & tabs to spaces;
// Added copyleft, getVersion, getDescription, rcs_id.
//

// For emacs users
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
