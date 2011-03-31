<?php // -*-php-*-
// $Id: BlogArchives.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright (C) 2004 $ThePhpWikiProgrammingTeam
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

require_once('lib/plugin/WikiBlog.php');

/**
 * BlogArchives - List monthly links for the current users blog if signed,
 * or the ADMIN_USER's Blog if not.
 * On month=... list the blog titles per month.
 *
 * TODO: year=
 *       support PageList (paging, limit, info filters: title, num, month, year, ...)
 *       leave off time subpage? Blogs just per day with one summary title only?
 * @author: Reini Urban
 */
class WikiPlugin_BlogArchives
extends WikiPlugin_WikiBlog
{
    function getName() {
        return _("Archives");
    }

    function getDescription() {
        return _("List blog months links for the current or ADMIN user");
    }

    function getDefaultArguments() {
        return //array_merge
               //(
               //PageList::supportedArgs(),
             array('user'     => '',
                   'order'    => 'reverse',        // latest first
                   'info'     => 'month,numpages', // ignored
                   'month'    => false,
                   'noheader' => 0
                   );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if (is_array($argstr)) { // can do with array also.
            $args =& $argstr;
            if (!isset($args['order'])) $args['order'] = 'reverse';
        } else {
            $args = $this->getArgs($argstr, $request);
        }
        if (empty($args['user'])) {
            $user = $request->getUser();
            if ($user->isAuthenticated()) {
                $args['user'] = $user->UserName();
            } else {
                $args['user'] = '';
            }
        }
        if (!$args['user'] or $args['user'] == ADMIN_USER) {
            if (BLOG_DEFAULT_EMPTY_PREFIX)
                $args['user'] = '';             // "Blogs/day" pages
            else
                $args['user'] = ADMIN_USER; // "Admin/Blogs/day" pages
        }
        $parent = (empty($args['user']) ? '' : $args['user'] . SUBPAGE_SEPARATOR);

        //$info = explode(',', $args['info']);
        //$pagelist = new PageList($args['info'], $args['exclude'], $args);
        //if (!is_array('pagename'), explode(',', $info))
        //    unset($pagelist->_columns['pagename']);

        $sp = HTML::Raw('&middot; ');
        if (!empty($args['month'])) {
            $prefix = $parent . $this->_blogPrefix('wikiblog') . SUBPAGE_SEPARATOR . $args['month'];
            $pages = $dbi->titleSearch(new TextSearchQuery("^".$prefix, true, 'posix'));
            $html = HTML::ul();
            while ($page = $pages->next()) {
                    $rev = $page->getCurrentRevision(false);
                    if ($rev->get('pagetype') != 'wikiblog') continue;
                $blog = $this->_blog($rev);
                $html->pushContent(HTML::li(WikiLink($page, 'known', $rev->get('summary'))));
            }
            if (!$args['noheader'])
                return HTML(HTML::h3(sprintf(_("Blog Entries for %s:"), $this->_monthTitle($args['month']))),
                           $html);
            else
                return $html;
        }

        $blogs = $this->findBlogs ($dbi, $args['user'], 'wikiblog');
        if ($blogs) {
            if (!$basepage) $basepage = _("BlogArchives");
            $html = HTML::ul();
            usort($blogs, array("WikiPlugin_WikiBlog", "cmp"));
            if ($args['order'] == 'reverse')
                $blogs = array_reverse($blogs);
            // collapse pagenames by month
            $months = array();
            foreach ($blogs as $rev) {
                $blog = $this->_blog($rev);
                    $mon = $blog['month'];
                if (empty($months[$mon]))
                    $months[$mon] =
                        array('title' => $this->_monthTitle($mon),
                              'num'   => 1,
                              'month' => $mon,
                              'link'  => WikiURL($basepage,
                                         $this->_nonDefaultArgs(array('month' => $mon))));
                else
                    $months[$mon]['num']++;
            }
            foreach ($months as $m) {
                $html->pushContent(HTML::li(HTML::a(array('href'=>$m['link'],
                                                          'class' => 'named-wiki'),
                                                    $m['title'] . " (".$m['num'].")")));
            }
            if (!$args['noheader'])
                return HTML(HTML::h3(_("Blog Archives:")), $html);
            else
                return $html;
        } else
            return '';
    }

    // box is used to display a fixed-width, narrow version with common header
    function box($args=false, $request=false, $basepage=false) {
        if (!$request) $request =& $GLOBALS['request'];
        if (!$args or empty($args['limit'])) $args['limit'] = 10;
        $args['noheader'] = 1;
        return $this->makeBox(_("Archives"), $this->run($request->_dbi, $args, $request, $basepage));
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
