<?php

require_once 'lib/WikiDB/backend/PearDB.php';

// See http://sql-info.de/mysql/gotchas.html for mysql specific quirks.

// The slowest function overall is mysql_connect with [680ms]
// 2nd is db_mysql::simpleQuery with [257ms]
class WikiDB_backend_PearDB_mysql
    extends WikiDB_backend_PearDB
{
    function __construct($dbparams)
    {
        parent::__construct($dbparams);
        if (DB::isError($this->_dbh)) return;
        //$this->_serverinfo = $this->_dbh->ServerInfo();
        $row = $this->_dbh->GetOne("SELECT version()");
        if (!DB::isError($row) and !empty($row)) {
            $arr = explode('.', $row);
            $this->_serverinfo['version'] = (string)(($arr[0] * 100) + $arr[1]) .
                "." . (integer)$arr[2];
            if ($this->_serverinfo['version'] < 323.0) {
                // Older MySQL's don't have CASE WHEN ... END
                $this->_expressions['maxmajor'] = "MAX(IF(minor_edit=0,version,0))";
                $this->_expressions['maxminor'] = "MAX(IF(minor_edit<>0,version,0))";
            }
            // esp. needed for utf databases
            if ($this->_serverinfo['version'] > 401.0) {
                mysql_query("SET NAMES 'UTF-8'");
            }
        }
    }

    /**
     * Kill timed out processes. ( so far only called on about every 50-th save. )
     */
    function _timeout()
    {
        if (empty($this->_dbparams['timeout'])) return;
        $result = mysql_query("SHOW processlist");
        while ($row = mysql_fetch_array($result)) {
            if ($row["db"] == $this->_dbh->dsn['database']
                and $row["User"] == $this->_dbh->dsn['username']
                    and $row["Time"] > $this->_dbparams['timeout']
                        and $row["Command"] == "Sleep"
            ) {
                $process_id = $row["Id"];
                mysql_query("KILL $process_id");
            }
        }
    }

    /*
     * Create a new revision of a page.
     */
    function set_versiondata($pagename, $version, $data)
    {
        $dbh = &$this->_dbh;
        $version_tbl = $this->_table_names['version_tbl'];

        $minor_edit = (int)!empty($data['is_minor_edit']);
        unset($data['is_minor_edit']);

        $mtime = (int)$data['mtime'];
        unset($data['mtime']);
        assert(!empty($mtime));

        @$content = (string)$data['%content'];
        unset($data['%content']);
        unset($data['%pagedata']);

        $this->lock();
        $id = $this->_get_pageid($pagename, true);
        // requires PRIMARY KEY (id,version)!
        // VALUES supported since mysql-3.22.5
        $dbh->query(sprintf("REPLACE INTO $version_tbl"
                . " (id,version,mtime,minor_edit,content,versiondata)"
                . " VALUES(%d,%d,%d,%d,'%s','%s')",
            $id, $version, $mtime, $minor_edit,
            $dbh->escapeSimple($content),
            $dbh->escapeSimple($this->_serialize($data))
        ));
        // real binding (prepare,execute) only since mysqli + PHP5
        $this->_update_recent_table($id);
        $this->_update_nonempty_table($id);
        $this->unlock();
    }

    function _update_recent_table($pageid = false)
    {
        $dbh = &$this->_dbh;
        extract($this->_table_names);
        extract($this->_expressions);

        $pageid = (int)$pageid;

        // optimized: mysql can do this with one REPLACE INTO.
        // supported in every (?) mysql version
        // requires PRIMARY KEY (id)!
        $dbh->query("REPLACE INTO $recent_tbl"
            . " (id, latestversion, latestmajor, latestminor)"
            . " SELECT id, $maxversion, $maxmajor, $maxminor"
            . " FROM $version_tbl"
            . ($pageid ? " WHERE id=$pageid" : "")
            . " GROUP BY id");
    }

    /*
     * Find referenced empty pages.
     */
    function wanted_pages($exclude_from = '', $exclude = '', $sortby = '', $limit = '')
    {
        $dbh = &$this->_dbh;
        extract($this->_table_names);
        if ($orderby = $this->sortby($sortby, 'db', array('pagename', 'wantedfrom')))
            $orderby = 'ORDER BY ' . $orderby;

        if ($exclude_from) // array of pagenames
            $exclude_from = " AND pp.pagename NOT IN " . $this->_sql_set($exclude_from);
        if ($exclude) // array of pagenames
            $exclude = " AND p.pagename NOT IN " . $this->_sql_set($exclude);

    /* ISNULL is mysql specific */
        $sql = "SELECT p.pagename, pp.pagename AS wantedfrom"
            . " FROM $page_tbl p, $link_tbl linked"
            . " LEFT JOIN $page_tbl pp ON (linked.linkto = pp.id)"
            . " LEFT JOIN $nonempty_tbl ne ON (linked.linkto = ne.id)"
            . " WHERE ISNULL(ne.id)"
            . " AND p.id = linked.linkfrom"
            . $exclude_from
            . $exclude
            . $orderby;
        if ($limit) {
            list($from, $count) = $this->limit($limit);
            $result = $dbh->limitQuery($sql, $from, $count * 3);
        } else {
            $result = $dbh->query($sql);
        }
        return new WikiDB_backend_PearDB_generic_iter($this, $result);
    }

    /* // REPLACE will not delete empy pages, so it was removed --ru
    function _update_nonempty_table($pageid = false) {
        $dbh = &$this->_dbh;
        extract($this->_table_names);

        $pageid = (int)$pageid;

        // Optimized: mysql can do this with one REPLACE INTO.
        // supported in every (?) mysql version
        // requires PRIMARY KEY (id)
        $dbh->query("REPLACE INTO $nonempty_tbl (id)"
                    . " SELECT $recent_tbl.id"
                    . " FROM $recent_tbl, $version_tbl"
                    . " WHERE $recent_tbl.id=$version_tbl.id"
                    . "       AND version=latestversion"
                    . "  AND content<>''"
                    . ( $pageid ? " AND $recent_tbl.id=$pageid" : ""));
    }
    */

    /**
     * Pack tables.
     */
    function optimize()
    {
        $dbh = &$this->_dbh;
        $this->_timeout();
        foreach ($this->_table_names as $table) {
            $dbh->query("OPTIMIZE TABLE $table");
        }
        return true;
    }

    /*
     * Lock tables.
     */
    protected function _lock_tables($write_lock = true)
    {
        $lock_type = $write_lock ? "WRITE" : "READ";
        $tables = array();
        foreach ($this->_table_names as $table) {
            $tables[] = "$table $lock_type";
        }
        $this->_dbh->query("LOCK TABLES " . join(",", $tables));
    }

    /*
     * Release all locks.
     */
    protected function _unlock_tables()
    {
        $this->_dbh->query("UNLOCK TABLES");
    }

    function increaseHitCount($pagename)
    {
        $dbh = &$this->_dbh;
        // Hits is the only thing we can update in a fast manner.
        // Note that this will fail silently if the page does not
        // have a record in the page table.  Since it's just the
        // hit count, who cares?
        // LIMIT since 3.23
        $dbh->query(sprintf("UPDATE LOW_PRIORITY %s SET hits=hits+1 WHERE pagename='%s' %s",
            $this->_table_names['page_tbl'],
            $dbh->escapeSimple($pagename),
            ($this->_serverinfo['version'] >= 323.0) ? "LIMIT 1" : ""));
    }

}

class WikiDB_backend_PearDB_mysql_search
    extends WikiDB_backend_PearDB_search
{
    function _pagename_match_clause($node)
    {
        $word = $node->sql();
        $dbh = &$this->_dbh;
        $word = $dbh->escapeSimple($word);
        if ($node->op == 'REGEX') { // posix regex extensions
            return "pagename REGEXP '$word'";
        } else {
            return ($this->_case_exact
                ? "pagename LIKE '$word'"
                : "LOWER(pagename) LIKE '$word'");
        }
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
