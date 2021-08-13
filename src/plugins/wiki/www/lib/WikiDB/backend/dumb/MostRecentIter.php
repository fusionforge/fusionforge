<?php
/**
 * Copyright © 2001 Jeff Dairiki
 * Copyright © 2002 Lawrence Akka
 * Copyright © 2004-2007 Reini Urban
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
 * An inefficient but general most_recent iterator.
 *
 * This iterator will work with any backends.
 */

class WikiDB_backend_dumb_MostRecentIter
    extends WikiDB_backend_iterator
{
    function __construct($backend, $pages, $params)
    {
        $limit = false;
        extract($params);
        if ($exclude_major_revisions)
            $include_minor_revisions = true;

        $reverse = $limit < 0;
        if ($reverse) {
            $limit = -$limit;
        }
        $this->_revisions = array();
        while ($page = $pages->next()) {
            $revs = $backend->get_all_revisions($page['pagename']);
            while ($revision = $revs->next()) {
                $vdata = &$revision['versiondata'];
                if (!$vdata) continue;
                assert(is_array($vdata));
                if (empty($vdata['mtime'])) {
                    $vdata['mtime'] = 0;
                }
                if (!empty($vdata['is_minor_edit'])) {
                    if (!$include_minor_revisions)
                        continue;
                } else {
                    if ($exclude_major_revisions)
                        continue;
                }
                if (!empty($since) && $vdata['mtime'] < $since)
                    break;

                $this->_revisions[] = $revision;

                if (!$include_all_revisions)
                    break;
            }
            $revs->free();
        }
        if ($reverse) {
            usort($this->_revisions, 'WikiDB_backend_dumb_MostRecentIter_sortf_rev');
        } else {
            usort($this->_revisions, 'WikiDB_backend_dumb_MostRecentIter_sortf');
        }
        if (!empty($limit) && $limit < count($this->_revisions)) {
            array_splice($this->_revisions, $limit);
        }
    }

    function next()
    {
        return array_shift($this->_revisions);
    }

    function free()
    {
        unset($this->_revisions);
    }
}

function WikiDB_backend_dumb_MostRecentIter_sortf($a, $b)
{
    $acreated = $a['versiondata']['mtime'];
    $bcreated = $b['versiondata']['mtime'];
    return $bcreated - $acreated;
}

function WikiDB_backend_dumb_MostRecentIter_sortf_rev($a, $b)
{
    $acreated = $a['versiondata']['mtime'];
    $bcreated = $b['versiondata']['mtime'];
    return $acreated - $bcreated;
}
