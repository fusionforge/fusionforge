<?php // -*-php-*-
// $Id: BackLinks.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 1999,2000,2001,2002,2006 $ThePhpWikiProgrammingTeam
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

require_once('lib/PageList.php');

class WikiPlugin_BackLinks
extends WikiPlugin
{
    function getName() {
        return _("BackLinks");
    }

    function getDescription() {
        return sprintf(_("List all pages which link to %s."), '[pagename]');
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array('include_self' => false,
                   'noheader'     => false,
                   'page'         => '[pagename]',
                   'linkmore'     => '',  // If count>0 and limit>0 display a link with
                     // the number of all results, linked to the given pagename.
                   ));
    }

    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges
    // NEW: info=count : number of links
    // page=foo,bar : backlinks to both pages
    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);

        extract($args);
        if (empty($page) and $page != '0')
            return '';
        // exclude is now already expanded in WikiPlugin::getArgs()
        if (empty($exclude)) $exclude = array();
        if (!$include_self)
            $exclude[] = $page;
        if ($info) {
            $info = explode(",", $info);
            if (in_array('count',$info))
                $args['types']['count'] =
                    new _PageList_Column_BackLinks_count('count', _("#"), 'center');
        }
        if (!empty($limit))
            $args['limit'] = $limit;
        $args['dosort'] = !empty($args['sortby']); // override DB sort (??)
        $pagelist = new PageList($info, $exclude, $args);

        // support logical AND: page1,page2
        $pages = explodePageList($page);
        $count = count($pages);
        if (count($pages) > 1) {
            // AND: the intersection of all these pages
            $bl = array();
            foreach ($pages as $p) {
                $dp = $dbi->getPage($p);
                $bi = $dp->getBackLinks(false, $sortby, 0, $exclude);
                while ($b = $bi->next()) {
                    $name = $b->getName();
                    if (isset($bl[$name]))
                        $bl[$name]++;
                    else
                        $bl[$name] = 1;
                }
            }
            foreach ($bl as $b => $v)
                if ($v == $count)
                    $pagelist->addPage($b);
        } else {
            $p = $dbi->getPage($page);
            $pagelist->addPages($p->getBackLinks(false, $sortby, 0, $exclude));
        }
        $total = $pagelist->getTotal();

        // Localization note: In English, the differences between the
        // various phrases spit out here may seem subtle or negligible
        // enough to tempt you to combine/normalize some of these
        // strings together, but the grammar employed most by other
        // languages does not always end up with so subtle a
        // distinction as it does with English in this case. :)
        if (!$noheader) {
            if ($page == $request->getArg('pagename')
                and !$dbi->isWikiPage($page))
            {
                    // BackLinks plugin is more than likely being called
                    // upon for an empty page on said page, while either
                    // 'browse'ing, 'create'ing or 'edit'ing.
                    //
                    // Don't bother displaying a WikiLink 'unknown', just
                    // the Un~WikiLink~ified (plain) name of the uncreated
                    // page currently being viewed.
                    $pagelink = $page;

                    if ($pagelist->isEmpty())
                        return HTML::p(fmt("No other page links to %s yet.", $pagelink));

                    if ($total == 1)
                        $pagelist->setCaption(fmt("One page would link to %s:",
                                                  $pagelink));
                    // Some future localizations will actually require
                    // this... (BelieveItOrNot, English-only-speakers!(:)
                    //
                    // else if ($pagelist->getTotal() == 2)
                    //     $pagelist->setCaption(fmt("Two pages would link to %s:",
                    //                               $pagelink));
                    else
                        $pagelist->setCaption(fmt("%s pages would link to %s:",
                                                  $total, $pagelink));
            }
            else {
                if ($count) {
                    $tmp_pages = $pages;
                    $p = array_shift($tmp_pages);
                    $pagelink = HTML(WikiLink($p, 'auto'));
                    foreach ($tmp_pages as $p)
                        $pagelink->pushContent(" ",_("AND")," ",WikiLink($p, 'auto'));
                } else
                        // BackLinks plugin is being displayed on a normal page.
                    $pagelink = WikiLink($page, 'auto');

                if ($pagelist->isEmpty())
                    return HTML::p(fmt("No page links to %s.", $pagelink));

                //trigger_error("DEBUG: " . $pagelist->getTotal());

                if ($total == 1)
                    $pagelist->setCaption(fmt("One page links to %s:",
                                              $pagelink));
                // Some future localizations will actually require
                // this... (BelieveItOrNot, English-only-speakers!(:)
                //
                // else if ($pagelist->getTotal() == 2)
                //     $pagelist->setCaption(fmt("Two pages link to %s:",
                //                               $pagelink));
                else
                    $pagelist->setCaption(fmt("%s pages link to %s:",
                                              $limit > 0 ? $total : _("Those"),
                                              $pagelink));
            }
        }
        if (!empty($args['linkmore'])
            and $dbi->isWikiPage($args['linkmore'])
            and $limit > 0 and $total > $limit
            )
            $pagelist->addCaption(WikiLink($args['linkmore'], "auto", _("More...")));
        return $pagelist;
    }

};

// how many links from this backLink to other pages
class _PageList_Column_BackLinks_count extends _PageList_Column {
    function _getValue($page, &$revision_handle) {
        $iter = $page->getPageLinks();
        $count = $iter->count();
        return $count;
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
