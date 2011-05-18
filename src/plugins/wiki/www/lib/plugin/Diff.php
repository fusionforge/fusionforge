<?php // -*-php-*-
// $Id: Diff.php 8071 2011-05-18 14:56:14Z vargenau $
/**
 * Copyright 1999, 2000, 2001, 2002, 2004 $ThePhpWikiProgrammingTeam
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
 * lib/diff.php converted to a plugin by electrawn,
 * plugin cleaned up by rurban,
 * code by dairiki
 *
 * Would make sense to see arbitrary diff's between any files or revisions.
 */

require_once('lib/diff.php');

class WikiPlugin_Diff
extends WikiPlugin {

    function getName () {
        return _("Diff");
    }

    function getDescription () {
        return _("Display differences between revisions");
    }

    // Establish default values for each of this plugin's arguments.
    // todo: makes only sense with more args.
    function getDefaultArguments() {
        return array('pagename' => '[pagename]',
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

        // Check if user is allowed to get the Page.
        if (!mayAccessPage ('view', $pagename)) {
                return $this->error(sprintf(_("Illegal access to page %s: no read access"),
                                        $pagename));
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
                                   HTML::p(_("Content of versions "), $old->getVersion(),
                                           _(" and "), $new->getVersion(), _(" is identical.")));
                // If two consecutive versions have the same content, it is because the page was
                // renamed, or metadata changed: ACL, owner, markup.
                // We give the reason by printing the summary.
                if (($new->getVersion() - $old->getVersion()) == 1) {
                    $html->pushContent(HTML::p(_("Version "), $new->getVersion(),
                                               _(" was created because: "), $new->get('summary')));
                }
            } else {
                $fmt = new HtmlUnifiedDiffFormatter;
                $html->pushContent($fmt->format($diff));
            }
        }

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
