<?php
/**
 * Copyright © 2004,2006-2007 Reini Urban
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

require_once 'lib/WikiDB/backend/ADODB.php';

/**
 * WikiDB layer for ADODB-sqlite, called by lib/WikiDB/ADODB.php.
 * Just to create a not existing database.
 *
 * @author: Reini Urban
 */
class WikiDB_backend_ADODB_sqlite
    extends WikiDB_backend_ADODB
{
    function __construct($dbparams)
    {
        $parsed = parseDSN($dbparams['dsn']);
        if (!file_exists($parsed['database'])) {
            // creating the empty database
            $db = $parsed['database'];
            $schema = findFile("schemas/sqlite-initialize.sql");
            `sqlite $db < $schema`;
            `echo "CREATE USER wikiuser" | sqlite $db`;
        }
        parent::__construct($dbparams);
    }

    function _get_pageid($pagename, $create_if_missing = false)
    {
        $dbh = &$this->_dbh;
        $page_tbl = $this->_table_names['page_tbl'];
        $query = sprintf("SELECT id FROM $page_tbl WHERE pagename=%s",
            $dbh->qstr($pagename));
        if (!$create_if_missing) {
            $row = $dbh->GetRow($query);
            return $row ? $row[0] : false;
        }
        // attributes play this game.
        if ($pagename === '') return 0;

        $row = $dbh->GetRow($query);
        if (!$row) {
            // atomic version
            // TODO: we have auto-increment since sqlite-2.3.4
            //   http://www.sqlite.org/faq.html#q1
            $dbh->Execute(sprintf("INSERT INTO $page_tbl"
                    . " (id,pagename)"
                    . " VALUES((SELECT max(id) FROM $page_tbl)+1, %s)",
                $dbh->qstr($pagename)));
            $id = $dbh->_insertid();
        } else {
            $id = $row[0];
        }
        return $id;
    }
}
