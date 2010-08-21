<?php // -*-php-*-
// rcs_id('$Id: BlogJournal.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright (C) 2005 $ThePhpWikiProgrammingTeam
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
 * BlogJournal - Include the latest blog entries for the current users blog if signed,
 *               or the ADMIN_USER's Blog if not.
 * UnfoldSubpages for blogs.
 * Rui called this plugin "JournalLast", but this was written completely independent,
 * without having seen the src.
 *
 * @author: Reini Urban
 */
class WikiPlugin_BlogJournal
extends WikiPlugin_WikiBlog
{
    function getName() {
        return _("BlogJournal");
    }

    function getDescription() {
        return _("Include latest blog entries for the current or ADMIN user");
    }

    function getDefaultArguments() {
        return array('count'    => 7,
                     'user'     => '',
                     'order'    => 'reverse',        // latest first
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
        $user = $request->getUser();
        if (empty($args['user'])) {
            if ($user->isAuthenticated()) {
                $args['user'] = $user->UserName();
            } else {
                $args['user'] = '';
            }
        }
        if (!$args['user'] or $args['user'] == ADMIN_USER) {
            if (BLOG_DEFAULT_EMPTY_PREFIX) {
                $args['user'] = '';             // "Blogs/day" pages
            } else {
                $args['user'] = ADMIN_USER; // "Admin/Blogs/day" pages
            }
        }
        $parent = (empty($args['user']) ? '' : $args['user'] . SUBPAGE_SEPARATOR);

        $sp = HTML::Raw('&middot; ');
        $prefix = $base = $parent . $this->_blogPrefix('wikiblog');
        if ($args['month'])
            $prefix .= (SUBPAGE_SEPARATOR . $args['month']);
        $pages = $dbi->titleSearch(new TextSearchQuery("^".$prefix.SUBPAGE_SEPARATOR, true, 'posix'));
        $html = HTML(); $i = 0;
        while (($page = $pages->next()) and $i < $args['count']) {
            $rev = $page->getCurrentRevision(false);
            if ($rev->get('pagetype') != 'wikiblog') continue;
            $i++;
            $blog = $this->_blog($rev);
            //$html->pushContent(HTML::h3(WikiLink($page, 'known', $rev->get('summary'))));
            $html->pushContent($rev->getTransformedContent('wikiblog'));
        }
        if ($args['user'] == $user->UserName() or $args['user'] == '')
            $html->pushContent(Button(array('action'=>'WikiBlog',
                                            'mode'=>'add'),
                                      _("New entry"), $base));
        if (!$i)
            return HTML(HTML::h3(_("No Blog Entries")), $html);
        if (!$args['noheader'])
            return HTML(HTML::h3(sprintf(_("Blog Entries for %s:"), $this->_monthTitle($args['month']))),
                        $html);
        else
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
