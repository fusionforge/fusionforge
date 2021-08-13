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

/**
 * MS SQL extensions for the ADODB DB backend.
 */

require_once 'lib/WikiDB/backend/ADODB.php';

class WikiDB_backend_ADODB_mssql
    extends WikiDB_backend_ADODB
{
    function __construct($dbparams)
    {
        // Lowercase Assoc arrays
        define('ADODB_ASSOC_CASE', 0);

        // Backend constructor
        parent::__construct($dbparams);

        // Empty strings in MSSQL?  NULLS?
        $this->_expressions['notempty'] = "NOT LIKE ''";
        // TEXT handling
        //$this->_expressions['iscontent'] = "content NOT LIKE ''";

        $this->_prefix = isset($dbparams['prefix']) ? $dbparams['prefix'] : '';
    }

    /**
     * Pack tables.
     */
    function optimize()
    {
        // Do nothing here -- Leave that for the DB
        // Cost Based Optimizer tuning vary from version to version
        return true;
    }

    /*
     * Lock tables.
     *
     * We don't really need to lock exclusive, but I'll relax it when I fully
     * understand phpWiki locking ;-)
     *
     */
    protected function _lock_tables($tables, $write_lock = true)
    {
        if (!$tables) return;

        $dbh = &$this->_dbh;
        if ($write_lock) {
            // Next line is default behaviour, so just skip it
            // $dbh->Execute("SET TRANSACTION READ WRITE");
            foreach ($tables as $table) {
                if ($this->_prefix && !strstr($table, $this->_prefix)) {
                    $table = $this->_prefix . $table;
                }
                $dbh->Execute("LOCK TABLE $table IN EXCLUSIVE MODE");
            }
        } else {
            // Just ensure read consistency
            $dbh->Execute("SET TRANSACTION READ ONLY");
        }
    }

    /*
     * Release the locks.
     */
    protected function _unlock_tables($tables)
    {
        $dbh = &$this->_dbh;
        $dbh->Execute("COMMIT WORK");
    }

    // Search callbacks
    // Page name
    function _sql_match_clause($word)
    {
        $word = preg_replace('/(?=[%_\\\\])/', "\\", $word);
        $word = $this->_dbh->qstr("%$word%");
        return "LOWER(pagename) LIKE $word";
    }

    // Fulltext -- case sensitive :-\
    function _fullsearch_sql_match_clause($word)
    {
        $word = preg_replace('/(?=[%_\\\\])/', "\\", $word);
        $wordq = $this->_dbh->qstr("%$word%");
        return "LOWER(pagename) LIKE $wordq "
            . "OR CHARINDEX(content, '$word') > 0";
    }

    /*
     * Serialize data
     */
    function _serialize($data)
    {
        if (empty($data))
            return '';
        assert(is_array($data));
        return serialize(addslashes($data));
    }

    /*
     * Unserialize data
     */
    function _unserialize($data)
    {
        return empty($data) ? array() : unserialize(stripslashes($data));
    }

}
