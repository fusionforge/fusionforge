<?php
/**
 * Copyright © 2001,2003 Jeff Dairiki
 * Copyright © 2004-2005,2007,2009 Reini Urban
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

require_once 'lib/WikiDB.php';

class WikiDB_SQL extends WikiDB
{
    function __construct($dbparams)
    {
        $backend = 'PearDB';
        if (is_array($dbparams['dsn']))
            $backend = $dbparams['dsn']['phptype'];
        elseif (preg_match('/^(\w+):/', $dbparams['dsn'], $m))
            $backend = $m[1];
        if ($backend == 'postgres7') { // ADODB cross-compatibility hack (for unit testing)
            $backend = 'pgsql';
            if (is_string($dbparams['dsn']))
                $dbparams['dsn'] = $backend . ':' . substr($dbparams['dsn'], 10);
        }
        if ($backend == 'mysql') {
            $backend = 'mysqli';
        }

        include_once 'lib/WikiDB/backend/PearDB_' . $backend . '.php';
        $backend_class = "WikiDB_backend_PearDB_" . $backend;
        $backend = new $backend_class($dbparams);
        if (DB::isError($backend->_dbh)) return;
        parent::__construct($backend, $dbparams);
    }

    public static function view_dsn($dsn = false)
    {
        if (!$dsn)
            $dsninfo = DB::parseDSN($GLOBALS['DBParams']['dsn']);
        else
            $dsninfo = DB::parseDSN($dsn);
        return sprintf("%s://%s:<not displayed>@%s/%s",
            $dsninfo['phptype'],
            $dsninfo['username'],
            $dsninfo['hostspec'],
            $dsninfo['database']
        );
    }

    /*
     * Determine whether page exists (in non-default form).
     * @see WikiDB::isWikiPage for the slow generic version
     */
    public function isWikiPage($pagename)
    {
        $pagename = (string)$pagename;
        if ($pagename === '') {
            return false;
        }
        if (empty($this->_cache->id_cache[$pagename])) {
            $this->_cache->_id_cache[$pagename] = $this->_backend->is_wiki_page($pagename);
        }
        return $this->_cache->_id_cache[$pagename];
    }

    // adds surrounding quotes
    public function quote($s)
    {
        return $this->_backend->_dbh->quoteSmart($s);
    }

    // no surrounding quotes because we know it's a string
    public function qstr($s)
    {
        return $this->_backend->_dbh->escapeSimple($s);
    }

    public function isOpen()
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        if (!$request->_dbi) {
            return false;
        }
        return is_resource($this->_backend->connection());
    }

    // SQL result: for simple select or create/update queries
    // returns the database specific resource type
    public function genericSqlQuery($sql, $args = array())
    {
        if ($args)
            $result = $this->_backend->_dbh->query($sql, $args);
        else
            $result = $this->_backend->_dbh->query($sql);
        if (DB::isError($result)) {
            $msg = $result->getMessage();
            trigger_error("SQL Error: " . DB::errorMessage($result), E_USER_WARNING);
            return false;
        } else {
            return $result;
        }
    }

    // SQL iter: for simple select or create/update queries
    // returns the generic iterator object (count, next)
    public function genericSqlIter($sql, $field_list = NULL)
    {
        $result = $this->genericSqlQuery($sql);
        return new WikiDB_backend_PearDB_generic_iter($this->_backend, $result);
    }

}
