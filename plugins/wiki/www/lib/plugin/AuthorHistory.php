<?php // -*-php-*-
// $Id: AuthorHistory.php 8071 2011-05-18 14:56:14Z vargenau $
/**
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/*
 *** EXPERIMENTAL PLUGIN ******************
 Needs a lot of work! Use at your own risk.
 ******************************************

 try this in a page called AuthorHistory:

<?plugin AuthorHistory page=username includeminor=true ?>
----
<?plugin AuthorHistory page=all ?>


 try this in a subpage of your UserName: (UserName/AuthorHistory)

<?plugin AuthorHistory page=all includeminor=true ?>


* Displays a list of revision edits by one particular user, for the
* current page, a specified page, or all pages.

* This is a big hack to create a PageList like table. (PageList
* doesn't support page revisions yet, only pages.)

* Make a new subclass of PageHistory to filter changes of one (or all)
* page(s) by a single author?

*/

/*
 reference
 _PageHistory_PageRevisionIter
 WikiDB_PageIterator(&$wikidb, &$pages
 WikiDB_PageRevisionIterator(&$wikidb, &$revisions)
*/

require_once('lib/PageList.php');

class WikiPlugin_AuthorHistory
extends WikiPlugin
{
    function getName() {
        return _("AuthorHistory");
    }

    function getDescription() {
        return sprintf(_("List all page revisions edited by one user with diff links, or show a PageHistory-like list of a single page for only one user."));
    }

    function getDefaultArguments() {
        global $request;
        return array('exclude'      => '',
                     'noheader'     => false,
                     'includeminor' => false,
                     'includedeleted' => false,
                     'author'       => $request->_user->UserName(),
                     'page'         => '[pagename]',
                     'info'         => 'version,minor,author,summary,mtime'
                     );
    }
    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges

    function run($dbi, $argstr, &$request, $basepage) {
        $this->_args = $this->getArgs($argstr, $request);
        extract($this->_args);
        //trigger_error("1 p= $page a= $author");
        if ($page && $page == 'username') //FIXME: use [username]!!!!!
            $page = $author;
        //trigger_error("2 p= $page a= $author");
        if (!$page || !$author) //user not signed in or no author specified
            return '';
        //$pagelist = new PageList($info, $exclude);
        ///////////////////////////

        $nbsp = HTML::raw('&nbsp;');

        global $WikiTheme; // date & time formatting

        $table = HTML::table(array('class'=> 'pagelist'));
        $thead = HTML::thead();
        $tbody = HTML::tbody();

        if (! ($page == 'all')) {
            $p = $dbi->getPage($page);

            $thead->pushContent(HTML::tr(HTML::th(array('align'=> 'right'),
                                               _("Version")),
                                      $includeminor ? HTML::th(_("Minor")) : "",
                                      HTML::th(_("Author")),
                                      HTML::th(_("Summary")),
                                      HTML::th(_("Modified"))
                                      ));

            $allrevisions_iter = $p->getAllRevisions();
            while ($rev = $allrevisions_iter->next()) {

                $isminor = $rev->get('is_minor_edit');
                $authordoesmatch = $author == $rev->get('author');

                if ($authordoesmatch && (!$isminor || ($includeminor && $isminor))) {
                    $difflink = Button(array('action' => 'diff',
                                             'previous' => 'minor'),
                                       $rev->getversion(), $rev);
                    $tr = HTML::tr(HTML::td(array('align'=> 'right'),
                                            $difflink, $nbsp),
                                   $includeminor ? (HTML::td($nbsp, ($isminor ? "minor" : "major"), $nbsp)) : "",
                                   HTML::td($nbsp, WikiLink($rev->get('author'),
                                                            'if_known'), $nbsp),
                                   HTML::td($nbsp, $rev->get('summary')),
                                   HTML::td(array('align'=> 'right'),
                                            $WikiTheme->formatdatetime($rev->get('mtime')))
                                   );

                    $class = $isminor ? 'evenrow' : 'oddrow';
                    $tr->setAttr('class', $class);
                    $tbody->pushContent($tr);
                    //$pagelist->addPage($rev->getPage());
                }
            }
            $captext = fmt($includeminor ? "History of all major and minor edits by %s to page %s."  : "History of all major edits by %s to page %s." ,
                           WikiLink($author, 'auto'),
                           WikiLink($page, 'auto'));
        }
        else {

            //search all pages for all edits by this author

            $thead->pushContent(HTML::tr(HTML::th(_("Page Name")),
                                      HTML::th(array('align'=> 'right'),
                                               _("Version")),
                                      $includeminor ? HTML::th(_("Minor")) : "",
                                      HTML::th(_("Summary")),
                                      HTML::th(_("Modified"))
                                      ));

            $allpages_iter = $dbi->getAllPages($includedeleted);
            while ($p = $allpages_iter->next()) {

                $allrevisions_iter = $p->getAllRevisions();
                while ($rev = $allrevisions_iter->next()) {
                    $isminor = $rev->get('is_minor_edit');
                    $authordoesmatch = $author == $rev->get('author');
                    if ($authordoesmatch && (!$isminor || ($includeminor && $isminor))) {
                        $difflink = Button(array('action' => 'diff',
                                                 'previous' => 'minor'),
                                           $rev->getversion(), $rev);
                        $tr = HTML::tr(
                                       HTML::td($nbsp,
                                                ($isminor ? $rev->_pagename : WikiLink($rev->_pagename, 'auto'))
                                                ),
                                       HTML::td(array('align'=> 'right'),
                                                $difflink, $nbsp),
                                       $includeminor ? (HTML::td($nbsp, ($isminor ? "minor" : "major"), $nbsp)) : "",
                                       HTML::td($nbsp, $rev->get('summary')),
                                       HTML::td(array('align'=> 'right'),
                                                $WikiTheme->formatdatetime($rev->get('mtime')), $nbsp)
                                       );

                        $class = $isminor ? 'evenrow' : 'oddrow';
                        $tr->setAttr('class', $class);
                        $tbody->pushContent($tr);
                        //$pagelist->addPage($rev->getPage());
                    }
                }
            }

            $captext = fmt($includeminor ? "History of all major and minor modifications for any page edited by %s."  : "History of major modifications for any page edited by %s." ,
                           WikiLink($author, 'auto'));
        }

        $table->pushContent(HTML::caption($captext));
        $table->pushContent($thead, $tbody);

        //        if (!$noheader) {
        // total minor, major edits. if include minoredits was specified
        //        }
        return $table;

        //        if (!$noheader) {
        //            $pagelink = WikiLink($page, 'auto');
        //
        //            if ($pagelist->isEmpty())
        //                return HTML::p(fmt("No pages link to %s.", $pagelink));
        //
        //            if ($pagelist->getTotal() == 1)
        //                $pagelist->setCaption(fmt("One page links to %s:",
        //                                          $pagelink));
        //            else
        //                $pagelist->setCaption(fmt("%s pages link to %s:",
        //                                          $pagelist->getTotal(), $pagelink));
        //        }
        //
        //        return $pagelist;
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
