<?php

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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'lib/PageList.php';

/**
 * Supports author=[] (current user), owner=[] and creator=[]
 * to be able to have the action pages:
 *   AllPagesCreatedByMe, AllPagesOwnedByMe, AllPagesLastAuthoredByMe
 */
class WikiPlugin_AllPages
    extends WikiPlugin
{
    function getDescription()
    {
        return _("List all pages in this wiki.");
    }

    function getDefaultArguments()
    {
        return array_merge
        (
            PageList::supportedArgs(),
            array(
                'noheader' => false,
                'include_empty' => false,
                'info' => '',
                'userpages' => false
            ));
    }

    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor,markup or all
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges
    // sortby: [+|-] pagename|mtime|hits

    /**
     * @param WikiDB $dbi
     * @param string $argstr
     * @param WikiRequest $request
     * @param string $basepage
     * @return mixed
     */
    function run($dbi, $argstr, &$request, $basepage)
    {
        $args = $this->getArgs($argstr, $request);

        if (isset($args['limit']) && !is_limit($args['limit'])) {
            return HTML::p(array('class' => "error"),
                           _("Illegal “limit” argument: must be an integer or two integers separated by comma"));
        }

        if (empty($args['sortby'])) {
            $args['sortby'] = 'pagename';
        }

        $pages = false;
        // Todo: extend given _GET args
        $caption = _("All pages in this wiki (%d total):");

        if (!empty($args['userpages'])) {
            $pages = PageList::allUserPages($args['include_empty'], $args['sortby'], '');
            $caption = _("List of user-created pages (%d total):");
            $args['count'] = count($pages);
        } elseif (!empty($args['owner'])) {
            $pages = PageList::allPagesByOwner($args['owner'], $args['include_empty'], $args['sortby'], '');
            $args['count'] = count($pages);
            $caption = fmt("List of pages owned by %s (%d total):",
                WikiLink($args['owner'] == '[]'
                        ? $request->_user->getAuthenticatedId()
                        : $args['owner'],
                    'if_known'), $args['count']);
        } elseif (!empty($args['author'])) {
            $pages = PageList::allPagesByAuthor($args['author'], $args['include_empty'], $args['sortby'], '');
            $args['count'] = count($pages);
            $caption = fmt("List of pages last edited by %s (%d total):",
                WikiLink($args['author'] == '[]'
                        ? $request->_user->getAuthenticatedId()
                        : $args['author'],
                    'if_known'), $args['count']);
        } elseif (!empty($args['creator'])) {
            $pages = PageList::allPagesByCreator($args['creator'], $args['include_empty'], $args['sortby'], '');
            $args['count'] = count($pages);
            $caption = fmt("List of pages created by %s (%d total):",
                WikiLink($args['creator'] == '[]'
                        ? $request->_user->getAuthenticatedId()
                        : $args['creator'],
                    'if_known'), $args['count']);
        } elseif ($pages) {
            $args['count'] = count($pages);
        } else {
            if (!$request->getArg('count'))
                $args['count'] = $dbi->numPages($args['include_empty'], $args['exclude']);
            else
                $args['count'] = $request->getArg('count');
        }
        if (empty($args['count']) and !empty($pages)) {
            $args['count'] = count($pages);
        }
        $pagelist = new PageList($args['info'], $args['exclude'], $args);
        if (!$args['noheader']) {
            $pagelist->setCaption($caption);
        }

        if ($pages !== false)
            $pagelist->addPageList($pages);
        else
            $pagelist->addPages($dbi->getAllPages($args['include_empty'], $args['sortby'],
                $args['limit']));
        return $pagelist;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
