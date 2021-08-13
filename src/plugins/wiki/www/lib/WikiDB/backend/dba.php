<?php
/**
 * Copyright © 2001-2002 Jeff Dairiki
 * Copyright © 2001-2002 Carsten Klapp
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

require_once 'lib/WikiDB/backend/dbaBase.php';
require_once 'lib/DbaDatabase.php';

class WikiDB_backend_dba
    extends WikiDB_backend_dbaBase
{
    function __construct($dbparams)
    {
        $directory = '/tmp';
        $prefix = 'wiki_';
        $dba_handler = 'gdbm';
        $timeout = 20;
        extract($dbparams);
        if ($directory) $directory .= "/";
        $dbfile = $directory . $prefix . 'pagedb' . '.' . $dba_handler;

        // FIXME: error checking.
        $db = new DbaDatabase($dbfile, false, $dba_handler);
        $db->set_timeout($timeout);

        // Workaround for BDB 4.1 bugs
        if (file_exists($dbfile)) {
            $mode = 'w';
        } else {
            $mode = 'c';
        }
        if (!$db->open($mode)) {
            trigger_error(sprintf(_("%s: Can't open dba database"), $dbfile), E_USER_ERROR);
            global $request;
            $request->finish(fmt("%s: Can't open dba database", $dbfile));
        }

        parent::__construct($db);
    }

    function lock($tables = array(), $write_lock = true)
    {
    }

    function unlock($tables = array(), $force = false)
    {
    }
}
