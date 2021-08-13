<?php
/**
 * Copyright © 2004-2005 Reini Urban
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
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

/*
 * Extensions/modifications to the stock RecentChanges (and PageHistory) format.
 */

require_once 'lib/plugin/RecentChanges.php';

class _blog_RecentChanges_BoxFormatter
    extends _RecentChanges_BoxFormatter
{
    function pageLink($rev, $link_text = '')
    {
        if (!$link_text and $rev->get('pagetype') == 'wikiblog')
            $link_text = $rev->get('summary');
        elseif (preg_match("/\/Blog\b/", $rev->_pagename))
            return '';
        if ($link_text and strlen($link_text) > 20)
            $link_text = substr($link_text, 0, 20) . "...";
        return WikiLink($rev->getPage(), 'auto', $link_text);
    }
}

class _blog_RecentChanges_Formatter
    extends _RecentChanges_HtmlFormatter
{
    function pageLink($rev, $link_text = '')
    {
        if (!$link_text and $rev->get('pagetype') == 'wikiblog')
            $link_text = $rev->get('summary');
        return WikiLink($rev, 'auto', $link_text);
    }
}
