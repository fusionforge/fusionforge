<?php // -*-php-*-
// rcs_id('$Id: AllPages.php 7681 2010-09-10 11:31:37Z vargenau $');
/**
 * Copyright 1999,2000,2001,2002,2004,2005 $ThePhpWikiProgrammingTeam
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

/**
 * Supports author=[] (current user), owner=[] and creator=[]
 * to be able to have the action pages:
 *   AllPagesCreatedByMe, AllPagesOwnedByMe, AllPagesLastAuthoredByMe
 */
class WikiPlugin_AllPages
extends WikiPlugin
{
    function getName () {
        return _("AllPages");
    }

    function getDescription () {
        return _("List all pages in this wiki.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array(
                   'noheader'      => false,
                   'include_empty' => true, // is faster
                   //'pages'         => false, // DONT, this would be ListPages then.
                   'info'          => '',
                   'debug'         => false,
                   'userpages'     => false
                   ));
    }

    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor,markup or all
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges
    // sortby: [+|-] pagename|mtime|hits

    // 2004-07-08 22:05:35 rurban: turned off &$request to prevent from strange bug below
    function run($dbi, $argstr, $request, $basepage) {
        $args = $this->getArgs($argstr, $request);

        $pages = false;
        // Todo: extend given _GET args
        if (DEBUG && $args['debug']) {
            $timer = new DebugTimer;
        }
        $caption = _("All pages in this wiki (%d total):");

        if ( !empty($args['userpages']) ) {
            $pages = PageList::allUserPages($args['include_empty'],
                                               $args['sortby'], ''
                                               );
            $caption = _("List of user-created pages (%d total):");
            $args['count'] = $request->getArg('count');
        } elseif ( !empty($args['owner']) ) {
            $pages = PageList::allPagesByOwner($args['owner'], $args['include_empty'],
                                               $args['sortby'], ''
                                               );
            $args['count'] = $request->getArg('count');
            if (!$args['count'])
                $args['count'] = $dbi->numPages($args['include_empty'], $args['exclude']);
            $caption = fmt("List of pages owned by [%s] (%d total):",
                           WikiLink($args['owner'] == '[]'
                                    ? $request->_user->getAuthenticatedId()
                                    : $args['owner'],
                                    'if_known'), $args['count']);
            $pages->_options['count'] = $args['count'];
        } elseif ( !empty($args['author']) ) {
            $pages = PageList::allPagesByAuthor($args['author'], $args['include_empty'],
                                                $args['sortby'], ''
                                                );
            $args['count'] = $request->getArg('count');
            if (!$args['count'])
                $args['count'] = $dbi->numPages($args['include_empty'], $args['exclude']);
            $caption = fmt("List of pages last edited by [%s] (%d total):",
                           WikiLink($args['author'] == '[]'
                                    ? $request->_user->getAuthenticatedId()
                                    : $args['author'],
                                    'if_known'), $args['count']);
            $pages->_options['count'] = $args['count'];
        } elseif ( !empty($args['creator']) ) {
            $pages = PageList::allPagesByCreator($args['creator'], $args['include_empty'],
                                                 $args['sortby'], ''
                                                 );
            $args['count'] = $request->getArg('count');
            if (!$args['count'])
                $args['count'] = $dbi->numPages($args['include_empty'], $args['exclude']);
            $caption = fmt("List of pages created by [%s] (%d total):",
                           WikiLink($args['creator'] == '[]'
                                    ? $request->_user->getAuthenticatedId()
                                    : $args['creator'],
                                    'if_known'), $args['count']);
            $pages->_options['count'] = $args['count'];
        //} elseif ($pages) {
        //    $args['count'] = count($pages);
        } else {
            if (! $request->getArg('count'))
                $args['count'] = $dbi->numPages($args['include_empty'], $args['exclude']);
            else
                $args['count'] = $request->getArg('count');
        }
        if (empty($args['count']) and !empty($pages))
            $args['count'] = count($pages);
        $pagelist = new PageList($args['info'], $args['exclude'], $args);
        if (!$args['noheader']) $pagelist->setCaption($caption);

        // deleted pages show up as version 0.
        //if ($args['include_empty'])
        //    $pagelist->_addColumn('version');

        if ($pages !== false)
            $pagelist->addPageList($pages);
        else
            $pagelist->addPages( $dbi->getAllPages($args['include_empty'], $args['sortby'],
                                                   $args['limit']) );
        if (DEBUG && $args['debug']) {
            return HTML($pagelist,
                        HTML::p(fmt("Elapsed time: %s s", $timer->getStats())));
        } else {
            return $pagelist;
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
