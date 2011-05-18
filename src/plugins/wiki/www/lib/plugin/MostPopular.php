<?php // -*-php-*-
// $Id: MostPopular.php 7955 2011-03-03 16:41:35Z vargenau $
/*
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('lib/PageList.php');

class WikiPlugin_MostPopular
extends WikiPlugin
{
    function getName () {
        return _("MostPopular");
    }

    function getDescription () {
        return _("List the most popular pages.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array('pagename' => '[pagename]', // hackish
                   //'exclude'  => '',
                   'limit'    => 20, // limit <0 returns least popular pages
                   'noheader' => 0,
                   'sortby'   => '-hits',
                   'info'     => false,
                   //'paging'   => 'auto'
                   ));
    }

    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges
    // sortby: only pagename or hits. mtime not!

    function run($dbi, $argstr, &$request, $basepage) {
            $args = $this->getArgs($argstr, $request);
        extract($args);
        if (strstr($sortby,'mtime')) {
            trigger_error(_("sortby=mtime not supported with MostPopular"),
                          E_USER_WARNING);
            $sortby = '';
        }
        $columns = $info ? explode(",", $info) : array();
        array_unshift($columns, 'hits');

        if (! $request->getArg('count')) {
            //$args['count'] = $dbi->numPages(false,$exclude);
            $allpages = $dbi->mostPopular(0, $sortby);
            $args['count'] = $allpages->count();
        } else {
            $args['count'] = $request->getArg('count');
        }
        //$dbi->touch();
        $pages = $dbi->mostPopular($limit, $sortby);
        $pagelist = new PageList($columns, $exclude, $args);
        while ($page = $pages->next()) {
            $hits = $page->get('hits');
            // don't show pages with no hits if most popular pages wanted
            if ($hits == 0 && $limit > 0) {
                break;
            }
            $pagelist->addPage($page);
        }
        $pages->free();

        if (! $noheader) {
            if ($limit > 0) {
                $pagelist->setCaption(fmt("The %d most popular pages of this wiki:", $limit));
            } else if ($limit < 0) {
                $pagelist->setCaption(fmt("The %d least popular pages of this wiki:", -$limit));
            } else {
                $pagelist->setCaption(_("Visited pages on this wiki, ordered by popularity:"));
            }
        }

        return $pagelist;
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
