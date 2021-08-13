<?php
/**
 * Copyright © 2005 Reini Urban
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

/**
 * WikiDB layer for PDO, the new php5 abstraction layer, with support for
 * prepared statements and transactions.
 *
 * "The PHP Data Objects (PDO) extension defines a lightweight,
 * consistent interface for accessing databases in PHP. Each database
 * driver that implements the PDO interface can expose
 * database-specific features as regular extension functions. Note
 * that you cannot perform any database functions using the PDO
 * extension by itself; you must use a database-specific PDO driver to
 * access a database server."
 *
 * @author: Reini Urban
 */

class WikiDB_PDO extends WikiDB
{
    function __construct($dbparams)
    {
        if (is_array($dbparams['dsn']))
            $backend = $dbparams['dsn']['phptype'];
        elseif (preg_match('/^(\w+):/', $dbparams['dsn'], $m))
            $backend = $m[1];
        // Do we have a override? Currently none: mysql, sqlite, oci, mssql
        if (findFile("lib/WikiDB/backend/PDO_$backend.php", true)) {
            $backend = 'PDO_' . $backend;
        } else {
            $backend = 'PDO';
        }
        include_once 'lib/WikiDB/backend/' . $backend . '.php';
        $backend_class = "WikiDB_backend_" . $backend;
        $backend = new $backend_class($dbparams);
        parent::__construct($backend, $dbparams);
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
        if (!array_key_exists($pagename, $this->_cache->_id_cache)) {
            $this->_cache->_id_cache[$pagename] = $this->_backend->is_wiki_page($pagename);
        }
        return $this->_cache->_id_cache[$pagename];
    }

    // With PDO we should really use native quoting using prepared statements with ?
    // Add surrounding quotes '' if string
    public function quote($s)
    {
        if (is_int($s) || is_double($s)) {
            return $s;
        } elseif (is_bool($s)) {
            return $s ? 1 : 0;
        } elseif (is_null($s)) {
            return 'NULL';
        } else {
            return $this->qstr($s);
        }
    }

    // Don't add surrounding quotes '', same as in PearDB
    // PDO-0.2.1 added now ::quote()
    public function qstr($in)
    {
        $in = str_replace(array('\\', "\0"), array('\\\\', "\\\0"), $in);
        return str_replace("'", "\'", $in);
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
        return is_object($this->_backend->_dbh);
    }

    // SQL result: for simple select or create/update queries
    // returns the database specific resource type
    public function genericSqlQuery($sql, $args = array())
    {
        try {
            $sth = $this->_backend->_dbh->prepare($sql);
            if ($args) {
                foreach ($args as $key => $val) {
                    $sth->bindParam($key, $val);
                }
            }
            if ($sth->execute())
                $result = $sth->fetch(PDO::FETCH_BOTH);
            else
                return false;
        } catch (PDOException $e) {
            trigger_error("SQL Error: " . $e->getMessage(), E_USER_WARNING);
            return false;
        }
        return $result;
    }

    // SQL iter: for simple select or create/update queries
    // returns the generic iterator object (count, next)
    public function genericSqlIter($sql, $field_list = NULL)
    {
        $result = $this->genericSqlQuery($sql);
        return new WikiDB_backend_PDO_generic_iter($this->_backend, $result, $field_list);
    }

}
