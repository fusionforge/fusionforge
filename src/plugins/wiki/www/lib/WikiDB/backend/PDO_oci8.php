<?php // -*-php-*-
// rcs_id('$Id: PDO_oci8.php 7641 2010-08-11 13:00:46Z vargenau $');

/*
 * Copyright 2007 $ThePhpWikiProgrammingTeam
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * @author: Reini Urban
 */
require_once('lib/WikiDB/backend/PDO.php');

class WikiDB_backend_PDO_oci8
extends WikiDB_backend_PDO
{

    function optimize() {
        // Do nothing here -- Leave that for the DBA
        // Cost Based Optimizer tuning vary from version to version
        return 1;
    }

    /**
     * Lock all tables we might use.
     */
    function _lock_tables($write_lock=true) {
        $dbh = &$this->_dbh;

        // Not sure if we really need to lock tables here, the Oracle row
        // locking mechanism should be more than enough
        // For the time being, lets stay on the safe side and lock...
        if ($write_lock) {
            // Next line is default behaviour, so just skip it
            // $dbh->query("SET TRANSACTION READ WRITE");
            foreach ($this->_table_names as $table) {
                $dbh->exec("LOCK TABLE $table IN EXCLUSIVE MODE");
            }
        } else {
            // Just ensure read consistency
            $dbh->exec("SET TRANSACTION READ ONLY");
        }
    }

    function backendType() {
        return 'oci8';
    }
    function write_accesslog(&$entry) {
        global $request;
        $dbh = &$this->_dbh;
        $log_tbl = $entry->_accesslog->logtable;
        $sth = $dbh->prepare("INSERT INTO $log_tbl"
                             . " (time_stamp,remote_host,remote_user,request_method,request_line,request_args,"
                             .   "request_file,request_uri,request_time,status,bytes_sent,referer,agent,request_duration)"
                             . " VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        // Either use unixtime as %d (long), or the native timestamp format.
        $sth->bindParam(1, date('d-M-Y H:i:s', $entry->time));
        $sth->bindParam(2, $entry->host, PDO_PARAM_STR, 100);
        $sth->bindParam(3, $entry->user, PDO_PARAM_STR, 50);
        $sth->bindParam(4, $entry->request_method, PDO_PARAM_STR, 10);
        $sth->bindParam(5, $entry->request, PDO_PARAM_STR, 255);
        $sth->bindParam(6, $entry->request_args, PDO_PARAM_STR, 255);
        $sth->bindParam(7, $entry->request_uri, PDO_PARAM_STR, 255);
        $sth->bindParam(8, $entry->_ncsa_time($entry->time), PDO_PARAM_STR, 28);
        $sth->bindParam(9, $entry->time, PDO_PARAM_INT);
        $sth->bindParam(10,$entry->status, PDO_PARAM_INT);
        $sth->bindParam(11,$entry->size, PDO_PARAM_INT);
        $sth->bindParam(12,$entry->referer, PDO_PARAM_STR, 255);
        $sth->bindParam(13,$entry->user_agent, PDO_PARAM_STR, 255);
        $sth->bindParam(14,$entry->duration, PDO_PARAM_FLOAT);
        $sth->execute();
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
