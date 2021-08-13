<?php
/**
 * Copyright © 2001 Jeff Dairiki
 * Copyright © 2004,2008 Reini Urban
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

/**
 * An iterator which returns all revisions of page.
 *
 * This iterator uses  only the WikiDB_backend::get_versiondata interface
 * of a WikiDB_backend, and so it should work with all backends.
 */

class WikiDB_backend_dumb_AllRevisionsIter
    extends WikiDB_backend_iterator
{
    /**
     * @param WikiDB_backend $backend
     * @param string $pagename Page whose revisions to get.
     */
    function __construct($backend, $pagename)
    {
        $this->_backend = &$backend;
        $this->_pagename = $pagename;
        $this->_lastversion = -1;
    }

    /**
     * Get next revision in sequence.
     *
     * @see WikiDB_backend_iterator_next;
     */
    function next()
    {
        $backend = &$this->_backend;
        $pagename = &$this->_pagename;
        $version = &$this->_lastversion;

        //$backend->lock();
        if ($this->_lastversion == -1)
            $version = $backend->get_latest_version($pagename);
        elseif ($this->_lastversion > 0)
            $version = $backend->get_previous_version($pagename, $version);

        if ($version)
            $vdata = $backend->get_versiondata($pagename, $version);
        //$backend->unlock();

        if ($version == 0)
            return false;

        if (is_string($vdata) and !empty($vdata)) {
            $vdata1 = @unserialize($vdata);
            if (empty($vdata1)) {
                if (DEBUG) // string but unseriazible
                    trigger_error("Broken page $pagename ignored. Run Check WikiDB", E_USER_WARNING);
                return false;
            }
            $vdata = $vdata1;
        }
        $rev = array('versiondata' => $vdata,
            'pagename' => $pagename,
            'version' => $version);

        if (!empty($vdata['%pagedata'])) {
            $rev['pagedata'] = $vdata['%pagedata'];
        }

        return $rev;
    }
}
