<?php // -*-php-*-
// rcs_id('$Id: ListPages.php 7506 2010-06-09 10:06:37Z vargenau $');
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * ListPages - List pages that are explicitly given as the pages argument.
 *
 * Mainly used to see some ratings and recommendations.
 * But also possible to list some Categories or Users, or as generic
 * frontend for plugin-list page lists.
 *
 * @author: Dan Frankowski
 */
class WikiPlugin_ListPages
extends WikiPlugin
{
    function getName() {
        return _("ListPages");
    }

    function getDescription() {
        return _("List pages that are explicitly given as the pages argument.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array('pages'    => false,
                   //'exclude'  => false,
                   'info'     => 'pagename',
                   'dimension' => 0,
                   ));
    }

    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor
    // additional info args:
    //   top3recs      : recommendations
    //   numbacklinks  : number of backlinks (links to the given page)
    //   numpagelinks  : number of forward links (links at the given page)

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);

        extract($args);
        // If the ratings table does not exist, or on dba it will break otherwise.
        // Check if WikiTheme isa 'wikilens'
        if ($info == 'pagename' and isa($GLOBALS['WikiTheme'], 'wikilens'))
            $info .= ",top3recs";
        if ($info)
            $info = explode(',', $info);
        else
            $info = array();

        if (in_array('top3recs', $info)) {
            require_once('lib/wikilens/Buddy.php');
            require_once('lib/wikilens/PageListColumns.php');

            $active_user   = $request->getUser();
            $active_userid = $active_user->_userid;

            // if userids is null or empty, fill it with just the active user
            if (!isset($userids) || !is_array($userids) || !count($userids)) {
                // TKL: moved getBuddies call inside if statement because it was
                // causing the userids[] parameter to be ignored
                if (is_string($active_userid)
                    and strlen($active_userid)
                    and $active_user->isSignedIn())
                {
                    $userids = getBuddies($active_userid, $dbi);
                } else {
                    $userids = array();
                    // XXX: this wipes out the category caption...
                    $caption = _("You must be logged in to view ratings.");
                }
            }

            // find out which users we should show ratings for
            $allowed_users = array();
            foreach ($userids as $userid) {
                $user = new RatingsUser($userid);
                if ($user->allow_view_ratings($active_user)) {
                    array_push($allowed_users, $user);
                }
                // PHP's silly references... (the behavior with this line commented
                // out is... odd)
                unset($user);
            }
            $options = array('dimension' => $dimension,
                             'users' => $allowed_users);
            $args = array_merge($options, $args);
        }
        if (empty($pages) and $pages != '0')
            return '';

        if (in_array('numbacklinks', $info)) {
            $args['types']['numbacklinks'] = new _PageList_Column_ListPages_count('numbacklinks', _("#"), true);
        }
        if (in_array('numpagelinks', $info)) {
            $args['types']['numpagelinks'] = new _PageList_Column_ListPages_count('numpagelinks', _("#"));
        }

        $pagelist = new PageList($info, $exclude, $args);
        $pages_array = is_string($pages) ? explodePageList($pages) : (is_array($pages) ? $pages : array());
        $pagelist->addPageList($pages_array);
        return $pagelist;
    }
};

// how many back-/forwardlinks for this page
class _PageList_Column_ListPages_count extends _PageList_Column {
    function _PageList_Column_ListPages_count($field, $display, $backwards = false) {
        $this->_direction = $backwards;
        return $this->_PageList_Column($field, $display, 'center');
    }
    function _getValue($page, &$revision_handle) {
        $iter = $page->getLinks($this->_direction);
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
