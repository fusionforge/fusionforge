<?php
/**
 * Copyright © 2001-2002 Jeff Dairiki
 * Copyright © 2002 Lawrence Akka
 * Copyright © 2004,2006 Reini Urban
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

require_once 'lib/WikiDB/backend.php';

/**
 * An inefficient but general most_popular iterator.
 *
 * This iterator will work with any backend which implements the
 * backend::get_all_pages() and backend::get_pagedata()
 * methods.
 */

class WikiDB_backend_dumb_MostPopularIter
    extends WikiDB_backend_iterator
{
    function __construct($backend, $all_pages, $limit)
    {
        $this->_pages = array();
        $pages = &$this->_pages;

        while ($page = $all_pages->next()) {
            if (!isset($page['pagedata']))
                $page['pagedata'] = $backend->get_pagedata($page['pagename']);
            $pages[] = $page;
        }

        if ($limit < 0) { //sort pages in reverse order - ie least popular first.
            usort($pages, 'WikiDB_backend_dumb_MostPopularIter_sortf_rev');
            $limit = -$limit;
        } else usort($pages, 'WikiDB_backend_dumb_MostPopularIter_sortf');

        if ($limit < 0) {
            $pages = array_reverse($pages);
            $limit = -$limit;
        }

        if ($limit && $limit < count($pages))
            array_splice($pages, $limit);
    }

    function next()
    {
        return array_shift($this->_pages);
    }

    function free()
    {
        unset($this->_pages);
    }
}

function WikiDB_backend_dumb_MostPopularIter_sortf($a, $b)
{
    $ahits = $bhits = 0;
    if (isset($a['pagedata']['hits']))
        $ahits = (int)$a['pagedata']['hits'];
    if (isset($b['pagedata']['hits']))
        $bhits = (int)$b['pagedata']['hits'];
    return $bhits - $ahits;
}

function WikiDB_backend_dumb_MostPopularIter_sortf_rev($a, $b)
{
    $ahits = $bhits = 0;
    if (isset($a['pagedata']['hits']))
        $ahits = (int)$a['pagedata']['hits'];
    if (isset($b['pagedata']['hits']))
        $bhits = (int)$b['pagedata']['hits'];
    return $ahits - $bhits;
}
