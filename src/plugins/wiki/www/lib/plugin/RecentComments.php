<?php

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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * List of basepages with recently added comments.
 * Idea from http://www.wakkawiki.com/RecentlyCommented
 * @author: Reini Urban
 */

require_once 'lib/plugin/RecentChanges.php';
require_once 'lib/plugin/WikiBlog.php';

class WikiPlugin_RecentComments
    extends WikiPlugin_RecentChanges
{
    function getDescription()
    {
        return _("List basepages with recently added comments.");
    }

    function getDefaultArguments()
    {
        $args = parent::getDefaultArguments();
        $args['show_minor'] = false;
        $args['show_all'] = true;
        $args['caption'] = _("Recent Comments");
        return $args;
    }

    function format($changes, $args)
    {
        $fmt = new _RecentChanges_CommentFormatter($args);
        $fmt->action = _("RecentComments");
        return $fmt->format($changes);
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        $args = $this->getArgs($argstr, $request);
        // HACKish: fix for SF bug #622784  (1000 years of RecentChanges ought
        // to be enough for anyone.)
        $args['days'] = min($args['days'], 365000);
        return $this->format($this->getChanges($request->_dbi, $args), $args);
    }

    function getChanges($dbi, $args)
    {
        $changes = $dbi->mostRecent($this->getMostRecentParams($args));
        $show_deleted = $args['show_deleted'];
        if ($show_deleted == 'sometimes')
            $show_deleted = $args['show_minor'];
        if (!$show_deleted)
            $changes = new NonDeletedRevisionIterator($changes, !$args['show_all']);
        // sort out pages with no comments
        $changes = new RecentCommentsRevisionIterator($changes, $dbi);
        return $changes;
    }
}

class _RecentChanges_CommentFormatter
    extends _RecentChanges_HtmlFormatter
{

    function empty_message()
    {
        return _("No comments found");
    }

    function title()
    {
        return;
    }

    function format_revision($rev)
    {
        static $doublettes = array();
        if (isset($doublettes[$rev->getPageName()])) {
            return HTML::raw('');
        }
        $doublettes[$rev->getPageName()] = 1;
        $args = &$this->_args;
        $class = 'rc-' . $this->importance($rev);
        $time = $this->time($rev);
        if (!$rev->get('is_minor_edit'))
            $time = HTML::strong(array('class' => 'pageinfo-majoredit'), $time);
        $line = HTML::li(array('class' => $class));
        if ($args['difflinks'])
            $line->pushContent($this->diffLink($rev), ' ');

        if ($args['historylinks'])
            $line->pushContent($this->historyLink($rev), ' ');

        $line->pushContent($this->pageLink($rev), ' ',
            $time, ' ',
            ' . . . . ',
            _("latest comment by "),
            $this->authorLink($rev));
        return $line;
    }
}

/**
 * List of pages which have comments
 * i.e. sort out all non-commented pages.
 */
class RecentCommentsRevisionIterator extends WikiDB_PageRevisionIterator
{
    function RecentCommentsRevisionIterator($revisions, &$dbi)
    {
        $this->_revisions = $revisions;
        $this->_wikidb = $dbi;
        $this->_current = 0;
        $this->_blog = new WikiPlugin_WikiBlog();
    }

    function next()
    {
        if (!empty($this->comments) and $this->_current) {
            if (isset($this->comments[$this->_current])) {
                return $this->comments[$this->_current++];
            } else {
                $this->_current = 0;
            }
        }
        while (($rev = $this->_revisions->next())) {
            $this->comments = $this->_blog->findBlogs($this->_wikidb, $rev->getPageName(), 'comment');
            if ($this->comments) {
                if (count($this->comments) > 2)
                    usort($this->comments, array("WikiPlugin_WikiBlog",
                        "cmp"));
                if (isset($this->comments[$this->_current])) {
                    //$this->_current++;
                    return $this->comments[$this->_current++];
                }
            } else {
                $this->_current = 0;
            }
        }
        $this->free();
        return false;
    }

}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
