<?php // -*-php-*-
rcs_id('$Id: Diff.php 6185 2008-08-22 11:40:14Z vargenau $');
/**
 Copyright 1999, 2000, 2001, 2002, 2004 $ThePhpWikiProgrammingTeam

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
 * lib/diff.php converted to a plugin by electrawn, 
 * plugin cleaned up by rurban,
 * code by dairiki
 *
 * Would make sense to see arbitrary diff's between any files or revisions.
 */

//require_once('lib/difflib.php');
require_once('lib/diff.php');

class WikiPlugin_Diff
extends WikiPlugin {

    function getName () {
        return _("Diff");
    }

    function getDescription () {
        return _("Display differences between revisions");
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6185 $");
    }

    // Establish default values for each of this plugin's arguments.
    // todo: makes only sense with more args.
    function getDefaultArguments() {
        return array('pagename' => '[pagename]',
                     'name'     => _("World"),
                     'versions' => false,
                     'version'  => false,
                     'previous' => 'major', // author, minor or major
                     );
    }

    function PageInfoRow ($label, $rev, &$request) {

        global $WikiTheme, $WikiNameRegexp;

        $row = HTML::tr(HTML::td(array('align' => 'right'), $label));
        if ($rev) {
            $author = $rev->get('author');
            $dbi = $request->getDbh();

            $iswikipage = (isWikiWord($author) && $dbi->isWikiPage($author));
            $authorlink = $iswikipage ? WikiLink($author) : $author;
            
            $linked_version = WikiLink($rev, 'existing', $rev->getVersion());
            $row->pushContent(HTML::td(fmt("version %s", $linked_version)),
                              HTML::td($WikiTheme->getLastModifiedMessage($rev,
                                                                      false)),
                              HTML::td(fmt("by %s", $authorlink)));
        } else {
            $row->pushContent(HTML::td(array('colspan' => '3'), _("None")));
        }
        return $row;
    }
    
    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));
        if (is_array($versions)) {
            // Version selection from pageinfo.php display:
            rsort($versions);
            list ($version, $previous) = $versions;
        }

        // abort if page doesn't exist
        $page = $request->getPage($pagename);
        $current = $page->getCurrentRevision();
        if ($current->getVersion() < 1) {
            $html = HTML(HTML::p(fmt("I'm sorry, there is no such page as %s.",
                                     WikiLink($pagename, 'unknown'))));
            return $html; //early return
        }

        if ($version) {
            if (!($new = $page->getRevision($version)))
                NoSuchRevision($request, $page, $version);
            $new_version = fmt("version %d", $version);
        }
        else {
            $new = $current;
            $new_version = _("current version");
        }

        if (preg_match('/^\d+$/', $previous)) {
            if ( !($old = $page->getRevision($previous)) )
                NoSuchRevision($request, $page, $previous);
            $old_version = fmt("version %d", $previous);
            $others = array('major', 'minor', 'author');
        }
        else {
            switch ($previous) {
            case 'author':
                $old = $new;
                while ($old = $page->getRevisionBefore($old)) {
                    if ($old->get('author') != $new->get('author'))
                        break;
                }
                $old_version = _("revision by previous author");
                $others = array('major', 'minor');
                break;
            case 'minor':
                $previous='minor';
                $old = $page->getRevisionBefore($new);
                $old_version = _("previous revision");
                $others = array('major', 'author');
                break;
            case 'major':
            default:
                $old = $new;
                while ($old && $old->get('is_minor_edit'))
                    $old = $page->getRevisionBefore($old);
                if ($old)
                    $old = $page->getRevisionBefore($old);
                $old_version = _("predecessor to the previous major change");
                $others = array('minor', 'author');
                break;
            }
        }
        
        $new_link = WikiLink($new, '', $new_version);
        $old_link = $old ? WikiLink($old, '', $old_version) : $old_version;
        $page_link = WikiLink($page);
        
        $html = HTML(HTML::p(fmt("Differences between %s and %s of %s.",
                                 $new_link, $old_link, $page_link)));

        $otherdiffs = HTML::p(_("Other diffs:"));
        $label = array('major' => _("Previous Major Revision"),
                       'minor' => _("Previous Revision"),
                       'author'=> _("Previous Author"));
        foreach ($others as $other) {
            $args = array('pagename' => $pagename, 'previous' => $other);
            if ($version)
                $args['version'] = $version;
            if (count($otherdiffs->getContent()) > 1)
                $otherdiffs->pushContent(", ");
            else
                $otherdiffs->pushContent(" ");
            $otherdiffs->pushContent(Button($args, $label[$other]));
        }
        $html->pushContent($otherdiffs);

        
        if ($old and $old->getVersion() == 0)
            $old = false;

        $html->pushContent(HTML::Table($this->PageInfoRow(_("Newer page:"), $new,
                                                          $request),
                                       $this->PageInfoRow(_("Older page:"), $old,
                                                          $request)));

        if ($new && $old) {
            $diff = new Diff($old->getContent(), $new->getContent());
            
            if ($diff->isEmpty()) {
                $html->pushContent(HTML::hr(),
                                   HTML::p('[', _("Versions are identical"),
                                           ']'));
            }
            else {
                // New CSS formatted unified diffs (ugly in NS4).
                $fmt = new HtmlUnifiedDiffFormatter;

                // Use this for old table-formatted diffs.
                //$fmt = new TableUnifiedDiffFormatter;
                $html->pushContent($fmt->format($diff));
            }
        }

        //$html = HTML::tt(fmt('%s: %s', $salutation, WikiLink($name, 'auto')),
        //                 THE_END);
        
        return $html;
    }
};

// $Log: not supported by cvs2svn $
// Revision 1.3  2005/09/30 18:53:10  uckelman
// 'final' is a reserved keyword as of PHP5, so shouldn't be used as a
//  function name here.
//
// Revision 1.2  2004/06/14 11:31:39  rurban
// renamed global $Theme to $WikiTheme (gforge nameclash)
// inherit PageList default options from PageList
//   default sortby=pagename
// use options in PageList_Selectable (limit, sortby, ...)
// added action revert, with button at action=diff
// added option regex to WikiAdminSearchReplace
//
// Revision 1.1  2004/02/26 23:02:17  rurban
// lib/diff.php converted to a plugin by electrawn,
// plugin cleaned up by rurban,
// code by dairiki
//
// Would make sense to see arbitrary diff's between any files or revisions.
//
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
