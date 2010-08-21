<?php
// rcs_id('$Id: AccessLog.php 7639 2010-08-11 12:15:16Z vargenau $');
/*
 * Copyright 2005, 2007 Reini Urban
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * Read and write file and SQL accesslog. Write sequentially.
 *
 * Read from file per pagename: Hits
 *
 */

/**
 * Create NCSA "combined" log entry for current request.
 * Also needed for advanced spam prevention.
 * global object holding global state (sql or file, entries, to dump)
 */
class Request_AccessLog {
    /**
     * @param $logfile string  Log file name.
     */
    function Request_AccessLog ($logfile, $do_sql = false) {
        //global $request; // request not yet initialized!

        $this->logfile = $logfile;
        if ($logfile and !is_writeable($logfile)) {
            trigger_error
                (sprintf(_("%s is not writable."), _("The PhpWiki access log file"))
                 . "\n"
                 . sprintf(_("Please ensure that %s is writable, or redefine %s in config/config.ini."),
                           sprintf(_("the file '%s'"), ACCESS_LOG),
                           'ACCESS_LOG')
                 , E_USER_NOTICE);
        }
        //$request->_accesslog =& $this;
        //if (empty($request->_accesslog->entries))
        register_shutdown_function("Request_AccessLogEntry_shutdown_function");

        if ($do_sql) {
            if (!$request->_dbi->isSQL()) {
                trigger_error("Unsupported database backend for ACCESS_LOG_SQL.\nNeed DATABASE_TYPE=SQL or ADODB or PDO");
            } else {
            	global $DBParams;
                //$this->_dbi =& $request->_dbi;
                $this->logtable = (!empty($DBParams['prefix']) ? $DBParams['prefix'] : '')."accesslog";
            }
        }
        $this->entries = array();
        $this->entries[] = new Request_AccessLogEntry($this);
    }

    function _do($cmd, &$arg) {
        if ($this->entries)
            for ($i=0; $i < count($this->entries);$i++)
                $this->entries[$i]->$cmd($arg);
    }
    function push(&$request)   { $this->_do('push',$request); }
    function setSize($arg)     { $this->_do('setSize',$arg); }
    function setStatus($arg)   { $this->_do('setStatus',$arg); }
    function setDuration($arg) { $this->_do('setDuration',$arg); }

    /**
     * Read sequentially all previous entries from the beginning.
     * while ($logentry = Request_AccessLogEntry::read()) ;
     * For internal log analyzers: RecentReferrers, WikiAccessRestrictions
     */
    function read() {
        return $this->logtable ? $this->read_sql() : $this->read_file();
    }

    /**
     * Return iterator of referer items reverse sorted (latest first).
     */
    function get_referer($limit=15, $external_only=false) {
        if ($external_only) { // see stdlin.php:isExternalReferrer()
            $base = SERVER_URL;
            $blen = strlen($base);
        }
        if (!empty($this->_dbi)) {
            // check same hosts in referer and request and remove them
            $ext_where = " AND LEFT(referer,$blen) <> ".$this->_dbi->quote($base)
                ." AND LEFT(referer,$blen) <> LEFT(CONCAT(".$this->_dbi->quote(SERVER_URL).",request_uri),$blen)";
            return $this->_read_sql_query("(referer <>'' AND NOT(ISNULL(referer)))"
                                          .($external_only ? $ext_where : '')
                                          ." ORDER BY time_stamp DESC"
                                          .($limit ? " LIMIT $limit" : ""));
        } else {
            $iter = new WikiDB_Array_generic_iter(0);
            $logs =& $iter->_array;
            while ($logentry = $this->read_file()) {
                if (!empty($logentry->referer)
                    and (!$external_only or (substr($logentry->referer,0,$blen) != $base)))
                {
                    $iter->_array[] = $logentry;
                    if ($limit and count($logs) > $limit)
                        array_shift($logs);
                }
            }
            $logs = array_reverse($logs);
            $logs = array_slice($logs,0,min($limit,count($logs)));
            return $iter;
        }
    }

    /**
     * Return iterator of matching host items reverse sorted (latest first).
     */
    function get_host($host, $since_minutes=20) {
        if ($this->logtable) {
            // mysql specific only:
            return $this->read_sql("request_host=".$this->_dbi->quote($host)
				   ." AND time_stamp > ". (time()-$since_minutes*60)
				   ." ORDER BY time_stamp DESC");
        } else {
            $iter = new WikiDB_Array_generic_iter();
            $logs =& $iter->_array;
            $logentry = new Request_AccessLogEntry($this);
            while ($logentry->read_file()) {
                if (!empty($logentry->referer)) {
                    $iter->_array[] = $logentry;
                    if ($limit and count($logs) > $limit)
                        array_shift($logs);
                    $logentry = new Request_AccessLogEntry($this);
                }
            }
            $logs = array_reverse($logs);
            $logs = array_slice($logs,0,min($limit,count($logs)));
            return $iter;
        }
    }

    /**
     * Read sequentially backwards all previous entries from log file.
     * FIXME!
     */
    function read_file() {
        global $request;
        if ($this->logfile) $this->logfile = ACCESS_LOG; // support Request_AccessLog::read

        if (empty($this->reader))       // start at the beginning
            $this->reader = fopen($this->logfile, "r");
        if ($s = fgets($this->reader)) {
            $entry = new Request_AccessLogEntry($this);
            $re = '/^(\S+)\s(\S+)\s(\S+)\s\[(.+?)\] "([^"]+)" (\d+) (\d+) "([^"]*)" "([^"]*)"$/';
            if (preg_match($re, $s, $m)) {
            	list(,$entry->host, $entry->ident, $entry->user, $entry->time,
                     $entry->request, $entry->status, $entry->size,
                     $entry->referer, $entry->user_agent) = $m;
            }
            return $entry;
        } else { // until the end
            fclose($this->reader);
            return false;
        }
    }
    function read_sql($where='') {
        if (empty($this->sqliter))
            $this->sqliter = $this->_read_sql_query($where);
        return $this->sqliter->next();
    }
    function _read_sql_query($where='') {
    	global $request;
    	$dbh =& $request->_dbi;
        $log_tbl =& $this->logtable;
        return $dbh->genericSqlIter("SELECT *,request_uri as request,request_time as time,remote_user as user,"
                                    ."remote_host as host,agent as user_agent"
                                    ." FROM $log_tbl"
                                    . ($where ? " WHERE $where" : ""));
    }

    /* done in request->finish() before the db is closed */
    function write_sql() {
    	global $request;
    	$dbh =& $request->_dbi;
        if (isset($this->entries) and $dbh and $dbh->isOpen())
            foreach ($this->entries as $entry) {
                $entry->write_sql();
            }
    }
    /* done in the shutdown callback */
    function write_file() {
        if (isset($this->entries) and $this->logfile)
            foreach ($this->entries as $entry) {
                $entry->write_file();
            }
        unset($this->entries);
    }
    /* in an ideal world... */
    function write() {
        if ($this->logfile) $this->write_file();
        if ($this->logtable) $this->write_sql();
        unset($this->entries);
    }
}

class Request_AccessLogEntry
{
    /**
     * Constructor.
     *
     * The log entry will be automatically appended to the log file or
     * SQL table when the current request terminates.
     *
     * If you want to modify a Request_AccessLogEntry before it gets
     * written (e.g. via the setStatus and setSize methods) you should
     * use an '&' on the constructor, so that you're working with the
     * original (rather than a copy) object.
     *
     * <pre>
     *    $log_entry = & new Request_AccessLogEntry("/tmp/wiki_access_log");
     *    $log_entry->setStatus(401);
     *    $log_entry->push($request);
     * </pre>
     *
     *
     */
    function Request_AccessLogEntry (&$accesslog) {
        $this->_accesslog = $accesslog;
        $this->logfile = $accesslog->logfile;
        $this->time = time();
        $this->status = 200;    // see setStatus()
        $this->size = 0;	// see setSize()
    }

    /**
     * @param $request object  Request object for current request.
     */
    function push(&$request) {
        $this->host  = $request->get('REMOTE_HOST');
        $this->ident = $request->get('REMOTE_IDENT');
        if (!$this->ident)
            $this->ident = '-';
        $user = $request->getUser();
        if ($user->isAuthenticated())
            $this->user = $user->UserName();
        else
            $this->user = '-';
        $this->request = join(' ', array($request->get('REQUEST_METHOD'),
                                         $request->get('REQUEST_URI'),
                                         $request->get('SERVER_PROTOCOL')));
        $this->referer = (string) $request->get('HTTP_REFERER');
        $this->user_agent = (string) $request->get('HTTP_USER_AGENT');
    }

    /**
     * Set result status code.
     *
     * @param $status integer  HTTP status code.
     */
    function setStatus ($status) {
        $this->status = $status;
    }

    /**
     * Set response size.
     *
     * @param $size integer
     */
    function setSize ($size=0) {
        $this->size = (int)$size;
    }
    function setDuration ($seconds) {
        // Pear DB does not correctly quote , in floats using ?. e.g. in european locales.
        // Workaround:
        $this->duration = str_replace(",",".",sprintf("%f",$seconds));
    }

    /**
     * Get time zone offset.
     *
     * This is a static member function.
     *
     * @param $time integer Unix timestamp (defaults to current time).
     * @return string Zone offset, e.g. "-0800" for PST.
     */
    function _zone_offset ($time = false) {
        if (!$time)
            $time = time();
        $offset = date("Z", $time);
        $negoffset = "";
        if ($offset < 0) {
            $negoffset = "-";
            $offset = -$offset;
        }
        $offhours = floor($offset / 3600);
        $offmins  = $offset / 60 - $offhours * 60;
        return sprintf("%s%02d%02d", $negoffset, $offhours, $offmins);
    }

    /**
     * Format time in NCSA format.
     *
     * This is a static member function.
     *
     * @param $time integer Unix timestamp (defaults to current time).
     * @return string Formatted date & time.
     */
    function _ncsa_time($time = false) {
        if (!$time)
            $time = time();
        return date("d/M/Y:H:i:s", $time) .
            " " . $this->_zone_offset();
    }

    function write() {
        if ($this->_accesslog->logfile) $this->write_file();
        if ($this->_accesslog->logtable) $this->write_sql();
    }

    /**
     * Write entry to log file.
     */
    function write_file() {
        $entry = sprintf('%s %s %s [%s] "%s" %d %d "%s" "%s"',
                         $this->host, $this->ident, $this->user,
                         $this->_ncsa_time($this->time),
                         $this->request, $this->status, $this->size,
                         $this->referer, $this->user_agent);
        if (!empty($this->_accesslog->reader)) {
            fclose($this->_accesslog->reader);
            unset($this->_accesslog->reader);
        }
        //Error log doesn't provide locking.
        //error_log("$entry\n", 3, $this->logfile);
        // Alternate method
        if (($fp = fopen($this->logfile, "a"))) {
            flock($fp, LOCK_EX);
            fputs($fp, "$entry\n");
            fclose($fp);
        }
    }

    /* This is better been done by apache mod_log_sql */
    /* If ACCESS_LOG_SQL & 2 we do write it by our own */
    function write_sql() {
    	global $request;

        $dbh =& $request->_dbi;
        if ($dbh and $dbh->isOpen() and $this->_accesslog->logtable) {
            //$log_tbl =& $this->_accesslog->logtable;
            if ($request->get('REQUEST_METHOD') == "POST") {
                // strangely HTTP_POST_VARS doesn't contain all posted vars.
                $args = $_POST; // copy not ref. clone not needed on hashes
                // garble passwords
                if (!empty($args['auth']['passwd']))    $args['auth']['passwd'] = '<not displayed>';
                if (!empty($args['dbadmin']['passwd'])) $args['dbadmin']['passwd'] = '<not displayed>';
                if (!empty($args['pref']['passwd']))    $args['pref']['passwd'] = '<not displayed>';
                if (!empty($args['pref']['passwd2']))   $args['pref']['passwd2'] = '<not displayed>';
                $this->request_args = substr(serialize($args),0,254); // if VARCHAR(255) is used.
            } else {
          	$this->request_args = $request->get('QUERY_STRING');
            }
            $this->request_method = $request->get('REQUEST_METHOD');
            $this->request_uri = $request->get('REQUEST_URI');
            // duration problem: sprintf "%f" might use comma e.g. "100,201" in european locales
            $dbh->_backend->write_accesslog($this);
        }
    }
}

/**
 * Shutdown callback. Ensures that the file is written.
 *
 * @access private
 * @see Request_AccessLogEntry
 */
function Request_AccessLogEntry_shutdown_function () {
    global $request;

    if (isset($request->_accesslog->entries) and $request->_accesslog->logfile)
        foreach ($request->_accesslog->entries as $entry) {
            $entry->write_file();
        }
    unset($request->_accesslog->entries);
}

// TODO: SQL access methods....
// (c) 2005 Charles Corrigan (the mysql parts)
// (c) 2006 Rein Urban (the postgresql parts)
// from AnalyseAccessLogSql.php
class Request_AccessLog_SQL
{

    /**
     * Build the query string
     *
     * FIXME: some or all of these queries may be MySQL specific / non-portable
     * FIXME: properly quote the string args
     *
     * The column names displayed are generated from the actual query column
     * names, so make sure that each column in the query is given a user
     * friendly name. Note that the column names are passed to _() and so may be
     * translated.
     *
     * If there are query specific where conditions, then the construction
     * "    if ($where_conditions<>'')
     *          $where_conditions = 'WHERE '.$where_conditions.' ';"
     * should be changed to
     * "    if ($where_conditions<>'')
     *          $where_conditions = 'AND '.$where_conditions.' ';"
     * and in the assignment to query have something like
     * "    $query= "SELECT "
     *          ."referer "
     *          ."FROM $accesslog "
     *          ."WHERE referer IS NOT NULL "
     *          .$where_conditions
     */
    function _getQueryString(&$args) {
        // extract any parametrised conditions from the arguments,
        // in particular, how much history to select
        $where_conditions = $this->_getWhereConditions($args);

        // get the correct name for the table
        //FIXME is there a more correct way to do this?
        global $DBParams, $request;
        $accesslog = (!empty($DBParams['prefix']) ? $DBParams['prefix'] : '')."accesslog";

        $query = '';
        $backend_type = $request->_dbi->_backend->backendType();
        switch ($backend_type) {
        case 'mysql':
            $Referring_URL = "left(referer,length(referer)-instr(reverse(referer),'?'))"; break;
        case 'pgsql':
        case 'postgres7':
            $Referring_URL = "substr(referer,0,position('?' in referer))"; break;
        default:
            $Referring_URL = "referer";
        }
        switch ($args['mode']) {
        case 'referring_urls':
            if ($where_conditions<>'')
                $where_conditions = 'WHERE '.$where_conditions.' ';
            $query = "SELECT "
                . "$Referring_URL AS Referring_URL, "
                . "count(*) AS Referral_Count "
                . "FROM $accesslog "
                . $where_conditions
                . "GROUP BY Referring_URL";
            break;
        case 'external_referers':
            $args['local_referrers'] = 'false';
            $where_conditions = $this->_getWhereConditions($args);
            if ($where_conditions<>'')
                $where_conditions = 'WHERE '.$where_conditions.' ';
            $query = "SELECT "
                . "$Referring_URL AS Referring_URL, "
                . "count(*) AS Referral_Count "
                . "FROM $accesslog "
                . $where_conditions
                . "GROUP BY Referring_URL";
            break;
        case 'referring_domains':
            if ($where_conditions<>'')
                $where_conditions = 'WHERE '.$where_conditions.' ';
            switch ($backend_type) {
            case 'mysql':
                $Referring_Domain = "left(referer, if(locate('/', referer, 8) > 0,locate('/', referer, 8) -1, length(referer)))"; break;
            case 'pgsql':
            case 'postgres7':
                $Referring_Domain = "substr(referer,0,8) || regexp_replace(substr(referer,8), '/.*', '')"; break;
            default:
                $Referring_Domain = "referer"; break;
            }
            $query = "SELECT "
                . "$Referring_Domain AS Referring_Domain, "
                . "count(*) AS Referral_Count "
                . "FROM $accesslog "
                . $where_conditions
                . "GROUP BY Referring_Domain";
            break;
        case 'remote_hosts':
            if ($where_conditions<>'')
                $where_conditions = 'WHERE '.$where_conditions.' ';
            $query = "SELECT "
                ."remote_host AS Remote_Host, "
                ."count(*) AS Access_Count "
                ."FROM $accesslog "
                .$where_conditions
                ."GROUP BY Remote_Host";
            break;
        case 'users':
            if ($where_conditions<>'')
                $where_conditions = 'WHERE '.$where_conditions.' ';
            $query = "SELECT "
                ."remote_user AS User, "
                ."count(*) AS Access_Count "
                ."FROM $accesslog "
                .$where_conditions
                ."GROUP BY remote_user";
            break;
        case 'host_users':
            if ($where_conditions<>'')
                $where_conditions = 'WHERE '.$where_conditions.' ';
            $query = "SELECT "
                ."remote_host AS Remote_Host, "
                ."remote_user AS User, "
                ."count(*) AS Access_Count "
                ."FROM $accesslog "
                .$where_conditions
                ."GROUP BY remote_host, remote_user";
            break;
        case "search_bots":
            // This queries for all entries in the SQL access log table that
            // have a dns name that I know to be a web search engine crawler and
            // categorises the results into time buckets as per the list below
            // 0 - 1 minute - 60
            // 1 - 1 hour   - 3600     = 60 * 60
            // 2 - 1 day    - 86400    = 60 * 60 * 24
            // 3 - 1 week   - 604800   = 60 * 60 * 24 * 7
            // 4 - 1 month  - 2629800  = 60 * 60 * 24 * 365.25 / 12
            // 5 - 1 year   - 31557600 = 60 * 60 * 24 * 365.25
            $now = time();
            $query = "SELECT "
                ."CASE WHEN $now-time_stamp<60 THEN '"._("0 - last minute")."' ELSE "
                  ."CASE WHEN $now-time_stamp<3600 THEN '"._("1 - 1 minute to 1 hour")."' ELSE "
                    ."CASE WHEN $now-time_stamp<86400 THEN '"._("2 - 1 hour to 1 day")."' ELSE "
                      ."CASE WHEN $now-time_stamp<604800 THEN '"._("3 - 1 day to 1 week")."' ELSE "
                        ."CASE WHEN $now-time_stamp<2629800 THEN '"._("4 - 1 week to 1 month")."' ELSE "
                          ."CASE WHEN $now-time_stamp<31557600 THEN '"._("5 - 1 month to 1 year")."' ELSE "
                            ."'"._("6 - more than 1 year")."' END END END END END END AS Time_Scale, "
                ."remote_host AS Remote_Host, "
                ."count(*) AS Access_Count "
                ."FROM $accesslog "
                ."WHERE (remote_host LIKE '%googlebot.com' "
                ."OR remote_host LIKE '%alexa.com' "
                ."OR remote_host LIKE '%inktomisearch.com' "
                ."OR remote_host LIKE '%msnbot.msn.com') "
                .($where_conditions ? 'AND '.$where_conditions : '')
                ."GROUP BY Time_Scale, remote_host";
            break;
        case "search_bots_hits":
            // This queries for all entries in the SQL access log table that
            // have a dns name that I know to be a web search engine crawler and
            // displays the URI that was hit.
            // If PHPSESSID appears in the URI, just display the URI to the left of this
            $sessname = session_name();
            switch ($backend_type) {
            case 'mysql':
                $Request_URI = "IF(instr(request_uri, '$sessname')=0, request_uri,left(request_uri, instr(request_uri, '$sessname')-2))";
                break;
            case 'pgsql':
            case 'postgres7':
                $Request_URI = "regexp_replace(request_uri, '$sessname.*', '')"; break;
            default:
                $Request_URI = 'request_uri'; break;
            }
            $now = time();
            $query = "SELECT "
                ."CASE WHEN $now-time_stamp<60 THEN '"._("0 - last minute")."' ELSE "
                  ."CASE WHEN $now-time_stamp<3600 THEN '"._("1 - 1 minute to 1 hour")."' ELSE "
                    ."CASE WHEN $now-time_stamp<86400 THEN '"._("2 - 1 hour to 1 day")."' ELSE "
                      ."CASE WHEN $now-time_stamp<604800 THEN '"._("3 - 1 day to 1 week")."' ELSE "
                        ."CASE WHEN $now-time_stamp<2629800 THEN '"._("4 - 1 week to 1 month")."' ELSE "
                          ."CASE WHEN $now-time_stamp<31557600 THEN '"._("5 - 1 month to 1 year")."' ELSE "
                            ."'"._("6 - more than 1 year")."' END END END END END END AS Time_Scale, "
                ."remote_host AS Remote_Host, "
                ."$Request_URI AS Request_URI "
                ."FROM $accesslog "
                ."WHERE (remote_host LIKE '%googlebot.com' "
                ."OR remote_host LIKE '%alexa.com' "
                ."OR remote_host LIKE '%inktomisearch.com' "
                ."OR remote_host LIKE '%msnbot.msn.com') "
                .($where_conditions ? 'AND '.$where_conditions : '')
                ."ORDER BY time_stamp";
        }
        return $query;
    }

    /** Honeypot for xgettext. Those strings are translated dynamically.
     */
    function _locale_dummy() {
        $dummy = array(
                       // mode caption
                       _("referring_urls"),
                       _("external_referers"),
                       _("referring_domains"),
                       _("remote_hosts"),
                       _("users"),
                       _("host_users"),
                       _("search_bots"),
                       _("search_bots_hits"),
                       // period header
                       _("minutes"),
                       _("hours"),
                       _("days"),
                       _("weeks"),
                       );
    }

    function getDefaultArguments() {
        return array(
                     'mode'             => 'referring_domains',
                     // referring_domains, referring_urls, remote_hosts, users, host_users, search_bots, search_bots_hits
                     'caption'          => '',
                     // blank means use the mode as the caption/title for the output
                     'local_referrers'  => 'true',  // only show external referring sites
                     'period'           => '',      // the type of period to report:
                     // may be weeks, days, hours, minutes, or blank for all
                     'count'            => '0'      // the number of periods to report
                     );
    }


    function table_output () {
        $query = $this->_getQueryString($args);

        if ($query=='')
            return HTML::p(sprintf( _("Unrecognised parameter 'mode=%s'"),
                                    $args['mode']));

        // get the data back.
        // Note that this must be done before the final generation ofthe table,
        // otherwise the headers will not be ready
        $tbody = $this->_getQueryResults($query, $dbi);

        return HTML::table(array('border'        => 1,
                                 'cellspacing'   => 1,
                                 'cellpadding'   => 1),
                    HTML::caption(HTML::h1(HTML::br(),$this->_getCaption($args))),
                    HTML::thead($this->_theadrow),
                    $tbody);
    }

    function _getQueryResults($query, &$dbi) {
        $queryResult = $dbi->genericSqlIter($query);
        if (!$queryResult) {
            $tbody = HTML::tbody(HTML::tr(HTML::td(_("<empty>"))));
        } else {
            $tbody = HTML::tbody();
            while ($row = $queryResult->next()) {
                $this->_setHeaders($row);
                $tr = HTML::tr();
                foreach ($row as $value) {
                    // output a '-' for empty values, otherwise the table looks strange
                    $tr->pushContent(HTML::td( empty($value) ? '-' : $value ));
                }
                $tbody->pushContent($tr);
            }
        }
        $queryResult->free();
        return $tbody;
    }

    function _setHeaders($row) {
        if (!$this->_headerSet) {
            foreach ($row as $key => $value) {
                $this->_theadrow->pushContent(HTML::th(_($key)));
            }
            $this->_headerSet = true;
        }
    }

    function _getWhereConditions(&$args) {
        $where_conditions = '';

        if ($args['period']<>'') {
            $since = 0;
            if ($args['period']=='minutes') {
                $since = 60;
            } elseif ($args['period']=='hours') {
                $since = 60 * 60;
            } elseif ($args['period']=='days') {
                $since = 60 * 60 * 24;
            } elseif ($args['period']=='weeks') {
                $since = 60 * 60 * 24 * 7;
            }
            $since = $since * $args['count'];
            if ($since>0) {
                if ($where_conditions<>'')
                    $where_conditions = $where_conditions.' AND ';
                $since = time() - $since;
                $where_conditions = $where_conditions."time_stamp > $since";
            }
        }

        if ($args['local_referrers']<>'true') {
            global $request;
            if ($where_conditions<>'')
                $where_conditions = $where_conditions.' AND ';
            $localhost = SERVER_URL;
            $len = strlen($localhost);
            $backend_type = $request->_dbi->_backend->backendType();
            switch ($backend_type) {
            case 'mysql':
                $ref_localhost = "left(referer,$len)<>'$localhost'"; break;
            case 'pgsql':
            case 'postgres7':
                $ref_localhost = "substr(referer,0,$len)<>'$localhost'"; break;
            default:
                $ref_localhost = "";
            }
            $where_conditions = $where_conditions.$ref_localhost;
        }

        // The assumed contract is that there is a space at the end of the
        // conditions string, so that following SQL clauses (such as GROUP BY)
        // will not cause a syntax error
        if ($where_conditions<>'')
            $where_conditions = $where_conditions.' ';

        return $where_conditions;
    }

    function _getCaption(&$args) {
        $caption = $args['caption'];
        if ($caption=='')
            $caption = gettext($args['mode']);
        if ($args['period']<>'' && $args['count'])
            $caption = $caption." - ".$args['count']." ". gettext($args['period']);
        return $caption;
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
