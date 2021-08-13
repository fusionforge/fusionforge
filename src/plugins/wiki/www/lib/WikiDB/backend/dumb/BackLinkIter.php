<?php
/**
 * Copyright © 2001 Jeff Dairiki
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
 * This backlink iterator will work with any WikiDB_backend
 * which has a working get_links(,'links_from') method.
 *
 * This is mostly here for testing, 'cause it's slow,slow,slow.
 */

class WikiDB_backend_dumb_BackLinkIter
    extends WikiDB_backend_iterator
{
    function __construct($backend, $all_pages, $pagename)
    {
        $this->_pages = $all_pages;
        $this->_backend = &$backend;
        $this->_target = $pagename;
    }

    function next()
    {
        while ($page = $this->_pages->next()) {
            $pagename = $page['pagename'];
            $links = $this->_backend->get_links($pagename, false);
            while ($link = $links->next()) {
                if ($link['pagename'] == $this->_target) {
                    $links->free();
                    return $page;
                }
            }
        }
    }

    function free()
    {
        $this->_pages->free();
    }
}
