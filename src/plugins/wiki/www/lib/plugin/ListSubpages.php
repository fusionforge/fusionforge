<?php // -*-php-*-
// rcs_id('$Id: ListSubpages.php 7638 2010-08-11 11:58:40Z vargenau $');
/*
 * Copyright 2002 $ThePhpWikiProgrammingTeam
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

/**
 * ListSubpages:  Lists the names of all SubPages of the current page.
 *                Based on UnfoldSubpages.
 * Usage:   <<ListSubpages noheader=1 info=pagename,hits,mtime >>
 */
require_once('lib/PageList.php');

class WikiPlugin_ListSubpages
extends WikiPlugin
{
    function getName() {
        return _("ListSubpages");
    }

    function getDescription () {
        return _("Lists the names of all SubPages of the current page.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
               array('noheader' => false, // no header
                     'basepage' => false, // subpages of which page, default: current
                     'maxpages' => '',    // maximum number of pages to include, change that to limit
                     //'exclude'  => '',
                     /*'relative' => false, */
                     'info'     => ''
                     ));
    }
    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor,count
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        if ($args['basepage'])
            $pagename = $args['basepage'];
        else
            $pagename = $request->getArg('pagename');

        // FIXME: explodePageList from stdlib doesn't seem to work as
        // expected when there are no subpages. (see also
        // UnfoldSubPages plugin)
        $subpages = explodePageList($pagename . SUBPAGE_SEPARATOR . '*');
        if (! $subpages) {
            return $this->error(_("The current page has no subpages defined."));
        }
        extract($args);

        $content = HTML();
        //$subpages = array_reverse($subpages); // TODO: why?
        if ($maxpages) {
            $subpages = array_slice ($subpages, 0, $maxpages);
        }

        $descrip = fmt("SubPages of %s:",
                       WikiLink($pagename, 'auto'));
        if ($info) {
            $info = explode(",", $info);
            if (in_array('count',$info))
                $args['types']['count'] = new _PageList_Column_ListSubpages_count('count', _("#"), 'center');
        }
        $pagelist = new PageList($info, $exclude, $args);
        if (!$noheader)
            $pagelist->setCaption($descrip);

        foreach ($subpages as $page) {
            // A page cannot include itself. Avoid doublettes.
            static $included_pages = array();
            if (in_array($page, $included_pages)) {
                $content->pushContent(HTML::p(sprintf(_("recursive inclusion of page %s ignored"),
                                                      $page)));
                continue;
            }
            array_push($included_pages, $page);
            //if ($relative) {
            // TODO: add relative subpage name display to PageList class
            //}
            $pagelist->addPage($page);

            array_pop($included_pages);
        }
        $content->pushContent($pagelist);
        return $content;
    }
};

// how many backlinks for this subpage
class _PageList_Column_ListSubpages_count extends _PageList_Column {
    function _getValue($page, &$revision_handle) {
        $iter = $page->getBackLinks();
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
