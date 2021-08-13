<?php
/**
 * Copyright © 2004 Matthew Palmer
 * Copyright © 2004 Reini Urban
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
 * SQLite PearDB backend by Matthew Palmer
 * The SQLite DB will gain popularity with the current MySQL vs PHP license drama.
 * It's in core since PHP-5.0, MySQL not anymore.
 *
 * Initial setup:
 * sqlite -init /tmp/phpwiki-sqlite.db
 * sqlite /tmp/phpwiki-sqlite.db < schemas/sqlite.sql
 */

require_once 'lib/WikiDB/backend/PearDB.php';

//TODO: create tables on virgin wiki
/*
    $db = &new DB_sqlite();
    $db->connect($DBParams['dsn'], array('persistent'=> true) );
    $result = $db->query("CREATE TABLE $table (comment varchar(50),
      datetime varchar(50));");
*/

class WikiDB_backend_PearDB_sqlite
    extends WikiDB_backend_PearDB
{
    /*
     * Pack tables.
     */
    function optimize()
    {
        return true;
        // NOP
    }

    /*
     * Lock tables.
     */
    protected function _lock_tables($write_lock = true)
    {
        // NOP - SQLite does all locking automatically
    }

    /*
     * Release all locks.
     */
    protected function _unlock_tables()
    {
        // NOP
    }

    /*
     * Serialize data
     */
    function _serialize($data)
    {
        if (empty($data))
            return '';
        assert(is_array($data));
        return base64_encode(serialize($data));
    }

    /*
     * Unserialize data
     */
    function _unserialize($data)
    {
        if (empty($data))
            return array();
        // Base64 encoded data does not contain colons.
        //  (only alphanumerics and '+' and '/'.)
        if (substr($data, 0, 2) == 'a:')
            return unserialize($data);
        return unserialize(base64_decode($data));
    }

    // same as DB::getSpecialQuery('tables')
    /*
    function sqlite_list_tables (&$dblink) {
       $tables = array ();
       $sql = "SELECT name FROM sqlite_master WHERE (type = 'table')";
       if ($res = sqlite_query ($dblink, $sql)) {
           while (sqlite_has_more($res)) {
               $tables[] = sqlite_fetch_single($res);
           }
       }
       return $tables;
    }
    */

    function _table_exists(&$dblink, $table)
    {
        $sql = "SELECT count(name) FROM sqlite_master WHERE ((type = 'table') and (name = '$table'))";
        if ($res = sqlite_query($dblink, $sql)) {
            return sqlite_fetch_single($res) > 0;
        } else {
            return false; // or throw exception
        }
    }

}
