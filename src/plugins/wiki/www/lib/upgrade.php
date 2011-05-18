<?php //-*-php-*-
// $Id: upgrade.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright 2004,2005,2006,2007 $ThePhpWikiProgrammingTeam
 * Copyright 2008 Marc-Etienne Vargenau, Alcatel-Lucent
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
 */

/**
 * Upgrade existing WikiDB and config settings after installing a new PhpWiki sofwtare version.
 * Status: almost no queries for verification.
 *         simple merge conflict resolution, or Overwrite All.
 *
 * Installation on an existing PhpWiki database needs some
 * additional worksteps. Each step will require multiple pages.
 *
 * This is the plan:
 *  1. Check for new or changed database schema and update it
 *     according to some predefined upgrade tables. (medium, complete)
 *  2. Check for new or changed (localized) pgsrc/ pages and ask
 *     for upgrading these. Check timestamps, upgrade silently or
 *     show diffs if existing. Overwrite or merge (easy, complete)
 *  3. Check for new or changed or deprecated index.php/config.ini settings
 *     and help in upgrading these. (for newer changes since 1.3.11, not yet)
 *   3a. Convert old-style index.php into config/config.ini. (easy, not yet)
 *  4. Check for changed plugin invocation arguments. (medium, done)
 *  5. Check for changed theme variables. (hard, not yet)
 *  6. Convert the single-request upgrade to a class-based multi-page
 *     version. (hard)

 * Done: overwrite=1 link on edit conflicts at first occurence "Overwrite all".
 *
 * @author: Reini Urban
 */
require_once("lib/loadsave.php");

class Upgrade {

    function Upgrade (&$request) {
    $this->request =& $request;
    $this->dbi =& $request->_dbi; // no reference for dbadmin ?
    $this->phpwiki_version = $this->current_db_version = phpwiki_version();
    //$this->current_db_version = 1030.13; // should be stored in the db. should be phpwiki_version

    $this->db_version = $this->dbi->get_db_version();
    $this->isSQL = $this->dbi->_backend->isSQL();
    }

    /**
     * TODO: check for the pgsrc_version number, not the revision mtime only
     */
    function doPgsrcUpdate($pagename, $path, $filename) {
    // don't ever update the HomePage
    if ((defined(HOME_PAGE) and ($pagename == HOME_PAGE))
        or ($pagename == _("HomePage"))
        or ($pagename == "HomePage")) {
        echo "$path/$pagename: ",_("always skip the HomePage."),
        _(" Skipped"),".<br />\n";
            return;
    }

    $page = $this->dbi->getPage($pagename);
    if ($page->exists()) {
        // check mtime: update automatically if pgsrc is newer
        $rev = $page->getCurrentRevision();
        $page_mtime = $rev->get('mtime');
        $data  = implode("", file($path."/".$filename));
        if (($parts = ParseMimeifiedPages($data))) {
        usort($parts, 'SortByPageVersion');
        reset($parts);
        $pageinfo = $parts[0];
        $stat  = stat($path."/".$filename);
        $new_mtime = 0;
        if (isset($pageinfo['versiondata']['mtime']))
            $new_mtime = $pageinfo['versiondata']['mtime'];
        if (!$new_mtime and isset($pageinfo['versiondata']['lastmodified']))
            $new_mtime = $pageinfo['versiondata']['lastmodified'];
        if (!$new_mtime and isset($pageinfo['pagedata']['date']))
            $new_mtime = $pageinfo['pagedata']['date'];
        if (!$new_mtime)
            $new_mtime = $stat[9];
        if ($new_mtime > $page_mtime) {
            echo "$path/$pagename: ",_("newer than the existing page."),
            _(" replace "),"($new_mtime &gt; $page_mtime)","<br />\n";
            LoadAny($this->request, $path."/".$filename);
            echo "<br />\n";
        } else {
            echo "$path/$pagename: ",_("older than the existing page."),
            _(" Skipped"),".<br />\n";
        }
        } else {
        echo "$path/$pagename: ",("unknown format."),
                    _(" Skipped"),".<br />\n";
        }
    } else {
        echo sprintf(_("%s does not exist"),$pagename),"<br />\n";
        LoadAny($this->request, $path."/".$filename);
        echo "<br />\n";
    }
    }

    function CheckActionPageUpdate() {
    echo "<h3>",sprintf(_("Check for necessary %s updates"),
                _("ActionPage")),"</h3>\n";
    // 1.3.13 before we pull in all missing pages, we rename existing ones
    $this->_rename_page_helper(_("_AuthInfo"), _("DebugAuthInfo"));
    // this is in some templates. so we keep the old name
    //$this->_rename_page_helper($this->dbi, _("DebugInfo"), _("DebugBackendInfo"));
    $this->_rename_page_helper(_("_GroupInfo"), _("GroupAuthInfo")); //never officially existed
    $this->_rename_page_helper("InterWikiKarte", "InterWikiListe"); // german only

    $path = FindFile('pgsrc');
    $pgsrc = new fileSet($path);
    // most actionpages have the same name as the plugin
    $loc_path = FindLocalizedFile('pgsrc');
    foreach ($pgsrc->getFiles() as $filename) {
        if (substr($filename,-1,1) == '~') continue;
        if (substr($filename,-5,5) == '.orig') continue;
        $pagename = urldecode($filename);
        if (isActionPage($pagename)) {
        $translation = gettext($pagename);
        if ($translation == $pagename)
            $this->doPgsrcUpdate($pagename, $path, $filename);
        elseif (FindLocalizedFile('pgsrc/'.urlencode($translation),1))
            $this->doPgsrcUpdate($translation, $loc_path, urlencode($translation));
        else
            $this->doPgsrcUpdate($pagename, $path, $filename);
        }
    }
    }

    // see loadsave.php for saving new pages.
    function CheckPgsrcUpdate() {
        // Check some theme specific pgsrc files (blog, wikilens, fusionforge, custom).
        // We check theme specific pgsrc first in case the page is present in both
        // theme specific and global pgsrc
        global $WikiTheme;
        $path = $WikiTheme->file("pgsrc");
        // TBD: the call to fileSet prints a warning:
        // Notice: Unable to open directory 'themes/MonoBook/pgsrc' for reading
        $pgsrc = new fileSet($path);
        if ($pgsrc->getFiles()) {
            echo "<h3>",sprintf(_("Check for necessary theme %s updates"),
                                "pgsrc"),"</h3>\n";
            foreach ($pgsrc->getFiles() as $filename) {
                if (substr($filename,-1,1) == '~') continue;
                if (substr($filename,-5,5) == '.orig') continue;
                $pagename = urldecode($filename);
                $this->doPgsrcUpdate($pagename,$path,$filename);
            }
        }

        echo "<h3>",sprintf(_("Check for necessary %s updates"),
                            "pgsrc"),"</h3>\n";
        if ($this->db_version < 1030.12200612) {
            echo "<h4>",_("rename to Help: pages"),"</h4>\n";
        }
        $path = FindLocalizedFile(WIKI_PGSRC);
        $pgsrc = new fileSet($path);
        // fixme: verification, ...
        foreach ($pgsrc->getFiles() as $filename) {
            if (substr($filename,-1,1) == '~') continue;
            if (substr($filename,-5,5) == '.orig') continue;
            $pagename = urldecode($filename);
            if (!isActionPage($filename)) {
                // There're a lot of now unneeded pages around.
                // At first rename the BlaPlugin pages to Help/<pagename> and then to the update.
                if ($this->db_version < 1030.12200612) {
                    $this->_rename_to_help_page($pagename);
                }
                $this->doPgsrcUpdate($pagename,$path,$filename);
            }
        }
    }

    function _rename_page_helper($oldname, $pagename) {
    echo sprintf(_("rename %s to %s"), $oldname, $pagename)," ...";
    if ($this->dbi->isWikiPage($oldname) and !$this->dbi->isWikiPage($pagename)) {
        if ($this->dbi->_backend->rename_page($oldname, $pagename))
        echo _("OK")," <br />\n";
        else
        echo " <b><font color=\"red\">", _("FAILED"), "</font></b>",
            " <br />\n";
    } else {
        echo _(" Skipped")," <br />\n";
    }
    }

    function _rename_to_help_page($pagename) {
    $newprefix = _("Help") . "/";
    if (substr($pagename,0,strlen($newprefix)) != $newprefix) return;
    $oldname = substr($pagename,strlen($newprefix));
    $this->_rename_page_helper($oldname, $pagename);
    }

    /**
     * TODO: Search table definition in appropriate schema
     *       and create it.
     * Supported: mysql and generic SQL, for ADODB and PearDB.
     */
    function installTable($table, $backend_type) {
    global $DBParams;
    if (!$this->isSQL) return;
    echo _("MISSING")," ... \n";
    $backend = &$this->dbi->_backend->_dbh;
    /*
      $schema = findFile("schemas/${backend_type}.sql");
      if (!$schema) {
      echo "  ",_("FAILED"),": ",sprintf(_("no schema %s found"),
      "schemas/${backend_type}.sql")," ... <br />\n";
      return false;
      }
    */
    extract($this->dbi->_backend->_table_names);
    $prefix = isset($DBParams['prefix']) ? $DBParams['prefix'] : '';
    switch ($table) {
    case 'session':
        assert($session_tbl);
        if ($backend_type == 'mysql') {
        $this->dbi->genericSqlQuery("
CREATE TABLE $session_tbl (
        sess_id     CHAR(32) NOT NULL DEFAULT '',
        sess_data     BLOB NOT NULL,
        sess_date     INT UNSIGNED NOT NULL,
        sess_ip     CHAR(15) NOT NULL,
        PRIMARY KEY (sess_id),
    INDEX (sess_date)
)");
        } else {
        $this->dbi->genericSqlQuery("
CREATE TABLE $session_tbl (
    sess_id     CHAR(32) NOT NULL DEFAULT '',
        sess_data     ".($backend_type == 'pgsql'?'TEXT':'BLOB')." NOT NULL,
        sess_date     INT,
        sess_ip     CHAR(15) NOT NULL
)");
        $this->dbi->genericSqlQuery("CREATE UNIQUE INDEX sess_id ON $session_tbl (sess_id)");
        }
        $this->dbi->genericSqlQuery("CREATE INDEX sess_date on session (sess_date)");
        echo "  ",_("CREATED");
        break;
    case 'pref':
        $pref_tbl = $prefix.'pref';
        if ($backend_type == 'mysql') {
        $this->dbi->genericSqlQuery("
CREATE TABLE $pref_tbl (
      userid     CHAR(48) BINARY NOT NULL UNIQUE,
      prefs      TEXT NULL DEFAULT '',
      PRIMARY KEY (userid)
)");
        } else {
        $this->dbi->genericSqlQuery("
CREATE TABLE $pref_tbl (
      userid     CHAR(48) NOT NULL,
      prefs      TEXT NULL DEFAULT ''
)");
        $this->dbi->genericSqlQuery("CREATE UNIQUE INDEX userid ON $pref_tbl (userid)");
        }
        echo "  ",_("CREATED");
        break;
    case 'member':
        $member_tbl = $prefix.'member';
        if ($backend_type == 'mysql') {
        $this->dbi->genericSqlQuery("
CREATE TABLE $member_tbl (
    userid    CHAR(48) BINARY NOT NULL,
       groupname CHAR(48) BINARY NOT NULL DEFAULT 'users',
       INDEX (userid),
       INDEX (groupname)
)");
        } else {
        $this->dbi->genericSqlQuery("
CREATE TABLE $member_tbl (
    userid    CHAR(48) NOT NULL,
       groupname CHAR(48) NOT NULL DEFAULT 'users'
)");
        $this->dbi->genericSqlQuery("CREATE INDEX userid ON $member_tbl (userid)");
        $this->dbi->genericSqlQuery("CREATE INDEX groupname ON $member_tbl (groupname)");
        }
        echo "  ",_("CREATED");
        break;
    case 'rating':
        $rating_tbl = $prefix.'rating';
        if ($backend_type == 'mysql') {
        $this->dbi->genericSqlQuery("
CREATE TABLE $rating_tbl (
        dimension INT(4) NOT NULL,
        raterpage INT(11) NOT NULL,
        rateepage INT(11) NOT NULL,
        ratingvalue FLOAT NOT NULL,
        rateeversion INT(11) NOT NULL,
        tstamp TIMESTAMP(14) NOT NULL,
        PRIMARY KEY (dimension, raterpage, rateepage)
)");
        } else {
        $this->dbi->genericSqlQuery("
CREATE TABLE $rating_tbl (
        dimension INT(4) NOT NULL,
        raterpage INT(11) NOT NULL,
        rateepage INT(11) NOT NULL,
        ratingvalue FLOAT NOT NULL,
        rateeversion INT(11) NOT NULL,
        tstamp TIMESTAMP(14) NOT NULL
)");
        $this->dbi->genericSqlQuery("CREATE UNIQUE INDEX rating"
                      ." ON $rating_tbl (dimension, raterpage, rateepage)");
        }
        echo "  ",_("CREATED");
        break;
    case 'accesslog':
        $log_tbl = $prefix.'accesslog';
        // fields according to http://www.outoforder.cc/projects/apache/mod_log_sql/docs-2.0/#id2756178
        /*
          A    User Agent agent    varchar(255)    Mozilla/4.0 (compat; MSIE 6.0; Windows)
          a    CGi request arguments    request_args    varchar(255)    user=Smith&cart=1231&item=532
          b    Bytes transfered    bytes_sent    int unsigned    32561
          c???    Text of cookie    cookie    varchar(255)    Apache=sdyn.fooonline.net 1300102700823
          f    Local filename requested    request_file    varchar(255)    /var/www/html/books-cycroad.html
          H    HTTP request_protocol    request_protocol    varchar(10)    HTTP/1.1
          h    Name of remote host    remote_host    varchar(50)    blah.foobar.com
          I    Request ID (from modd_unique_id)    id    char(19)    POlFcUBRH30AAALdBG8
          l    Ident user info    remote_logname    varcgar(50)    bobby
          M    Machine ID???    machine_id    varchar(25)    web01
          m    HTTP request method    request_method    varchar(10)    GET
          P    httpd cchild PID    child_pid    smallint unsigned    3215
          p    http port    server_port    smallint unsigned    80
          R    Referer    referer    varchar(255)    http://www.biglinks4u.com/linkpage.html
          r    Request in full form    request_line    varchar(255)    GET /books-cycroad.html HTTP/1.1
          S    Time of request in UNIX time_t format    time_stamp    int unsigned    1005598029
          T    Seconds to service request    request_duration    smallint unsigned    2
          t    Time of request in human format    request_time    char(28)    [02/Dec/2001:15:01:26 -0800]
          U    Request in simple form    request_uri    varchar(255)    /books-cycroad.html
          u    User info from HTTP auth    remote_user    varchar(50)    bobby
          v    Virtual host servicing the request    virtual_host    varchar(255)
        */
        $this->dbi->genericSqlQuery("
CREATE TABLE $log_tbl (
        time_stamp    int unsigned,
    remote_host   varchar(100),
    remote_user   varchar(50),
        request_method varchar(10),
    request_line  varchar(255),
    request_args  varchar(255),
    request_uri   varchar(255),
    request_time  char(28),
    status           smallint unsigned,
    bytes_sent    smallint unsigned,
        referer       varchar(255),
    agent         varchar(255),
    request_duration float
)");
        $this->dbi->genericSqlQuery("CREATE INDEX log_time ON $log_tbl (time_stamp)");
        $this->dbi->genericSqlQuery("CREATE INDEX log_host ON $log_tbl (remote_host)");
        echo "  ",_("CREATED");
        break;
    }
    echo "<br />\n";
    }

    /**
     * Update from ~1.3.4 to current.
     * tables: Only session, user, pref and member
     * jeffs-hacks database api (around 1.3.2) later:
     *   people should export/import their pages if using that old versions.
     */
    function CheckDatabaseUpdate() {
    global $DBAuthParams, $DBParams;

    echo "<h3>",sprintf(_("Check for necessary %s updates"),
                _("database")),
        " - ", DATABASE_TYPE,"</h3>\n";
    $dbadmin = $this->request->getArg('dbadmin');
    if ($this->isSQL) {
        $this->_db_init();
        if (isset($dbadmin['cancel'])) {
        echo _("CANCEL")," <br />\n";
        return;
        }
    }
        echo "db version: we want ", $this->current_db_version, "\n<br />";
        echo "db version: we have ", $this->db_version, "\n<br />";
        if ($this->db_version >= $this->current_db_version) {
            echo _("OK"), "<br />\n";
            return;
        }

    $backend_type = $this->dbi->_backend->backendType();
    if ($this->isSQL) {
        echo "<h4>",_("Backend type: "),$backend_type,"</h4>\n";
        $prefix = isset($DBParams['prefix']) ? $DBParams['prefix'] : '';
        $tables = $this->dbi->_backend->listOfTables();
        foreach (explode(':','session:pref:member') as $table) {
        echo sprintf(_("Check for table %s"), $table)," ...";
        if (!in_array($prefix.$table, $tables)) {
            $this->installTable($table, $backend_type);
        } else {
            echo _("OK")," <br />\n";
        }
        }
    }

    if ($this->phpwiki_version >= 1030.12200612 and $this->db_version < 1030.13) {
        if ($this->isSQL and preg_match("/(pgsql|postgres)/", $backend_type)) {
        trigger_error("You need to upgrade to schema/psql-initialize.sql manually!",
                  E_USER_WARNING);
            // $this->_upgrade_psql_tsearch2();
        }
        $this->_upgrade_relation_links();
    }

    if (ACCESS_LOG_SQL and $this->isSQL) {
        $table = "accesslog";
        echo sprintf(_("Check for table %s"), $table)," ...";
        if (!in_array($prefix.$table, $tables)) {
        $this->installTable($table, $backend_type);
        } else {
        echo _("OK")," <br />\n";
        }
    }
    if ($this->isSQL and (class_exists("RatingsUserFactory") or $this->dbi->isWikiPage(_("RateIt")))) {
        $table = "rating";
        echo sprintf(_("Check for table %s"), $table)," ...";
        if (!in_array($prefix.$table, $tables)) {
        $this->installTable($table, $backend_type);
        } else {
        echo _("OK")," <br />\n";
        }
    }
    $backend = &$this->dbi->_backend->_dbh;
    if ($this->isSQL)
        extract($this->dbi->_backend->_table_names);

    // 1.3.8 added session.sess_ip
    if ($this->isSQL and $this->phpwiki_version >= 1030.08 and USE_DB_SESSION
        and isset($this->request->_dbsession))
    {
        echo _("Check for new session.sess_ip column")," ... ";
        $database = $this->dbi->_backend->database();
        assert(!empty($DBParams['db_session_table']));
        $session_tbl = $prefix . $DBParams['db_session_table'];
        $sess_fields = $this->dbi->_backend->listOfFields($database, $session_tbl);
        if (!$sess_fields) {
        echo _("SKIP");
        } elseif (!strstr(strtolower(join(':', $sess_fields)), "sess_ip")) {
        // TODO: postgres test (should be able to add columns at the end, but not in between)
        echo "<b>",_("ADDING"),"</b>"," ... ";
        $this->dbi->genericSqlQuery("ALTER TABLE $session_tbl ADD sess_ip CHAR(15) NOT NULL");
        $this->dbi->genericSqlQuery("CREATE INDEX sess_date ON $session_tbl (sess_date)");
        } else {
        echo _("OK");
        }
        echo "<br />\n";
        if (substr($backend_type,0,5) == 'mysql') {
        // upgrade to 4.1.8 destroyed my session table:
        // sess_id => varchar(10), sess_data => varchar(5). For others obviously also.
        echo _("Check for mysql session.sess_id sanity")," ... ";
        $result = $this->dbi->genericSqlQuery("DESCRIBE $session_tbl");
        if (DATABASE_TYPE == 'SQL') {
            $iter = new WikiDB_backend_PearDB_generic_iter($backend, $result);
        } elseif (DATABASE_TYPE == 'ADODB') {
            $iter = new WikiDB_backend_ADODB_generic_iter($backend, $result,
                                  array("Field", "Type", "Null", "Key", "Default", "Extra"));
        } elseif (DATABASE_TYPE == 'PDO') {
            $iter = new WikiDB_backend_PDO_generic_iter($backend, $result);
        }
        while ($col = $iter->next()) {
            if ($col["Field"] == 'sess_id' and !strstr(strtolower($col["Type"]), 'char(32)')) {
            $this->dbi->genericSqlQuery("ALTER TABLE $session_tbl CHANGE sess_id"
                          ." sess_id CHAR(32) NOT NULL");
            echo "sess_id ", $col["Type"], " ", _("fixed"), " =&gt; CHAR(32) ";
            }
            if ($col["Field"] == 'sess_ip' and !strstr(strtolower($col["Type"]), 'char(15)')) {
            $this->dbi->genericSqlQuery("ALTER TABLE $session_tbl CHANGE sess_ip"
                          ." sess_ip CHAR(15) NOT NULL");
            echo "sess_ip ", $col["Type"], " ", _("fixed"), " =&gt; CHAR(15) ";
            }
        }
        echo _("OK"), "<br />\n";
        }
    }

    /* TODO:
       ALTER TABLE link ADD relation INT DEFAULT 0;
       CREATE INDEX linkrelation ON link (relation);
    */

    // mysql >= 4.0.4 requires LOCK TABLE privileges
    if (substr($backend_type,0,5) == 'mysql') {
        echo _("Check for mysql LOCK TABLE privilege")," ...";
        $mysql_version = $this->dbi->_backend->_serverinfo['version'];
        if ($mysql_version > 400.40) {
        if (!empty($this->dbi->_backend->_parsedDSN))
            $parseDSN = $this->dbi->_backend->_parsedDSN;
        elseif (function_exists('parseDSN')) // ADODB or PDO
            $parseDSN = parseDSN($DBParams['dsn']);
        else                  // pear
            $parseDSN = DB::parseDSN($DBParams['dsn']);
        $username = $this->dbi->_backend->qstr($parseDSN['username']);
        // on db level
        $query = "SELECT lock_tables_priv FROM mysql.db WHERE user='$username'";
        //mysql_select_db("mysql", $this->dbi->_backend->connection());
        $db_fields = $this->dbi->_backend->listOfFields("mysql", "db");
        if (!strstr(strtolower(join(':', $db_fields)), "lock_tables_priv")) {
            echo join(':', $db_fields);
            die("lock_tables_priv missing. The DB Admin must run mysql_fix_privilege_tables");
        }
        $row = $this->dbi->_backend->getRow($query);
        if (isset($row[0]) and $row[0] == 'N') {
            $this->dbi->genericSqlQuery("UPDATE mysql.db SET lock_tables_priv='Y'"
                      ." WHERE mysql.user='$username'");
            $this->dbi->genericSqlQuery("FLUSH PRIVILEGES");
            echo "mysql.db user='$username'", _("fixed"), "<br />\n";
        } elseif (!$row) {
            // or on user level
            $query = "SELECT lock_tables_priv FROM mysql.user WHERE user='$username'";
            $row = $this->dbi->_backend->getRow($query);
            if ($row and $row[0] == 'N') {
            $this->dbi->genericSqlQuery("UPDATE mysql.user SET lock_tables_priv='Y'"
                          ." WHERE mysql.user='$username'");
            $this->dbi->genericSqlQuery("FLUSH PRIVILEGES");
            echo "mysql.user user='$username'", _("fixed"), "<br />\n";
            } elseif (!$row) {
            echo " <b><font color=\"red\">", _("FAILED"), "</font></b>: ",
                "Neither mysql.db nor mysql.user has a user='$username'"
                ." or the lock_tables_priv field",
                "<br />\n";
            } else {
            echo _("OK"), "<br />\n";
            }
        } else {
            echo _("OK"), "<br />\n";
        }
        //mysql_select_db($this->dbi->_backend->database(), $this->dbi->_backend->connection());
        } else {
        echo sprintf(_("version <em>%s</em> not affected"), $mysql_version),"<br />\n";
        }
    }

    // 1.3.10 mysql requires page.id auto_increment
    // mysql, mysqli or mysqlt
    if ($this->phpwiki_version >= 1030.099 and substr($backend_type,0,5) == 'mysql'
        and DATABASE_TYPE != 'PDO')
        {
        echo _("Check for mysql page.id auto_increment flag")," ...";
        assert(!empty($page_tbl));
        $database = $this->dbi->_backend->database();
        $fields = mysql_list_fields($database, $page_tbl, $this->dbi->_backend->connection());
        $columns = mysql_num_fields($fields);
        for ($i = 0; $i < $columns; $i++) {
        if (mysql_field_name($fields, $i) == 'id') {
            $flags = mysql_field_flags($fields, $i);
            //DONE: something was wrong with ADODB here.
            if (!strstr(strtolower($flags), "auto_increment")) {
            echo "<b>",_("ADDING"),"</b>"," ... ";
            // MODIFY col_def valid since mysql 3.22.16,
            // older mysql's need CHANGE old_col col_def
            $this->dbi->genericSqlQuery("ALTER TABLE $page_tbl CHANGE id"
                            ." id INT NOT NULL AUTO_INCREMENT");
            $fields = mysql_list_fields($database, $page_tbl);
            if (!strstr(strtolower(mysql_field_flags($fields, $i)), "auto_increment"))
                echo " <b><font color=\"red\">", _("FAILED"), "</font></b><br />\n";
            else
                echo _("OK"), "<br />\n";
            } else {
            echo _("OK"), "<br />\n";
            }
            break;
        }
        }
        mysql_free_result($fields);
    }

    // Check for mysql 4.1.x/5.0.0a binary search problem.
    //   http://bugs.mysql.com/bug.php?id=4398
    // "select * from page where LOWER(pagename) like '%search%'" does not apply LOWER!
    // Confirmed for 4.1.0alpha,4.1.3-beta,5.0.0a; not yet tested for 4.1.2alpha,
    // On windows only, though utf8 would be useful elsewhere also.
    // Illegal mix of collations (latin1_bin,IMPLICIT) and
    // (utf8_general_ci, COERCIBLE) for operation '='])
    if (isWindows() and substr($backend_type,0,5) == 'mysql') {
        echo _("Check for mysql 4.1.x/5.0.0 binary search on windows problem")," ...";
        $mysql_version = $this->dbi->_backend->_serverinfo['version'];
        if ($mysql_version < 401.0) {
        echo sprintf(_("version <em>%s</em>"), $mysql_version)," ",
            _("not affected"),"<br />\n";
        } elseif ($mysql_version >= 401.6) { // FIXME: since which version?
        $row = $this->dbi->_backend->getRow("SHOW CREATE TABLE $page_tbl");
        $result = join(" ", $row);
        if (strstr(strtolower($result), "character set")
            and strstr(strtolower($result), "collate"))
            {
            echo _("OK"), "<br />\n";
            } else {
            //SET CHARACTER SET latin1
            $charset = CHARSET;
            if ($charset == 'iso-8859-1') $charset = 'latin1';
            $this->dbi->genericSqlQuery("ALTER TABLE $page_tbl CHANGE pagename "
                      ."pagename VARCHAR(100) "
                      ."CHARACTER SET '$charset' COLLATE '$charset"."_bin' NOT NULL");
            echo sprintf(_("version <em>%s</em>"), $mysql_version),
            " <b>",_("FIXED"),"</b>",
            "<br />\n";
        }
        } elseif (DATABASE_TYPE != 'PDO') {
        // check if already fixed
        extract($this->dbi->_backend->_table_names);
        assert(!empty($page_tbl));
        $database = $this->dbi->_backend->database();
        $fields = mysql_list_fields($database, $page_tbl, $this->dbi->_backend->connection());
        $columns = mysql_num_fields($fields);
        for ($i = 0; $i < $columns; $i++) {
            if (mysql_field_name($fields, $i) == 'pagename') {
            $flags = mysql_field_flags($fields, $i);
            // I think it was fixed with 4.1.6, but I tested it only with 4.1.8
            if ($mysql_version > 401.0 and $mysql_version < 401.6) {
                // remove the binary flag
                if (strstr(strtolower($flags), "binary")) {
                // FIXME: on duplicate pagenames this will fail!
                $this->dbi->genericSqlQuery("ALTER TABLE $page_tbl CHANGE pagename"
                              ." pagename VARCHAR(100) NOT NULL");
                echo sprintf(_("version <em>%s</em>"), $mysql_version),
                    "<b>",_("FIXED"),"</b>"
                    ,"<br />\n";
                }
            }
            break;
            }
        }
        }
    }
    if ($this->isSQL and ACCESS_LOG_SQL & 2) {
        echo _("Check for ACCESS_LOG_SQL passwords in POST requests")," ...";
        // Don't display passwords in POST requests (up to 2005-02-04 12:03:20)
        $res = $this->dbi->genericSqlIter("SELECT time_stamp, remote_host, " .
                    "request_args FROM ${prefix}accesslog WHERE request_args LIKE " .
                    "'%s:6:\"passwd\"%' AND request_args NOT LIKE '%s:6:\"passwd\";" .
                    "s:15:\"<not displayed>\"%'");
        $count = 0;
        while ($row = $res->next()) {
        $args = preg_replace("/(s:6:\"passwd\";s:15:\").*(\")/",
                     "$1<not displayed>$2", $row["request_args"]);
        $ts = $row["time_stamp"];
        $rh = $row["remote_host"];
        $this->dbi->genericSqlQuery("UPDATE ${prefix}accesslog SET " .
                      "request_args='$args' WHERE time_stamp=$ts AND " .
                      "remote_host='$rh'");
        $count++;
        }
        if ($count > 0)
        echo "<b>",_("FIXED"),"</b>", "<br />\n";
        else
        echo _("OK"),"<br />\n";

        if ($this->phpwiki_version >= 1030.13) {
        echo _("Check for ACCESS_LOG_SQL remote_host varchar(50)")," ...";
        $database = $this->dbi->_backend->database();
        $accesslog_tbl = $prefix . 'accesslog';
        $fields = $this->dbi->_backend->listOfFields($database, $accesslog_tbl);
        if (!$fields) {
            echo _("SKIP");
        } elseif (strstr(strtolower(join(':', $sess_fields)), "remote_host")) {
            // TODO: how to check size, already done?
            echo "<b>",_("FIXING"),"remote_host</b>"," ... ";
            $this->dbi->genericSqlQuery("ALTER TABLE $accesslog_tbl CHANGE remote_host VARCHAR(100)");
        } else {
            echo _("FAIL");
        }
        echo "<br />\n";
        }
    }
    $this->_upgrade_cached_html();

    if ($this->db_version < $this->current_db_version) {
        $this->dbi->set_db_version($this->current_db_version);
        $this->db_version = $this->dbi->get_db_version();
            echo "db version: upgrade to ", $this->db_version,"  ";
            echo _("OK"), "<br />\n";
            flush();
    }

    return;
    }

    /**
     * Filter SQL missing permissions errors.
     *
     * A wrong DBADMIN user will not be able to connect
     * @see _is_false_error, ErrorManager
     * @access private
     */
    function _dbpermission_filter($err) {
    if  ( $err->isWarning() ) {
        global $ErrorManager;
        $this->error_caught = 1;
        $ErrorManager->_postponed_errors[] = $err;
        return true;
    }
    return false;
    }

    function _try_dbadmin_user ($user, $passwd) {
    global $DBParams, $DBAuthParams;
    $AdminParams = $DBParams;
    if (DATABASE_TYPE == 'SQL')
        $dsn = DB::parseDSN($AdminParams['dsn']);
    else {
        $dsn = parseDSN($AdminParams['dsn']);
    }
    $AdminParams['dsn'] = sprintf("%s://%s:%s@%s/%s",
                      $dsn['phptype'],
                      $user,
                      $passwd,
                      $dsn['hostspec'],
                      $dsn['database']);
    $AdminParams['_tryroot_from_upgrade'] = 1;
    // add error handler to warn about missing permissions for DBADMIN_USER
    global $ErrorManager;
    $ErrorManager->pushErrorHandler(new WikiMethodCb($this, '_dbpermission_filter'));
    $this->error_caught = 0;
    $this->dbi = WikiDB::open($AdminParams);
    if (!$this->error_caught) return true;
    // FAILED: redo our connection with the wikiuser
    $this->dbi = WikiDB::open($DBParams);
    $ErrorManager->flushPostponedErrors();
    $ErrorManager->popErrorHandler();
    return false;
    }

    function _db_init () {
    if (!$this->isSQL) return;

    /* SQLite never needs admin params */
    $backend_type = $this->dbi->_backend->backendType();
    if (substr($backend_type,0,6)=="sqlite") {
        return;
    }
    $dbadmin_user = 'root';
    if ($dbadmin = $this->request->getArg('dbadmin')) {
        $dbadmin_user = $dbadmin['user'];
        if (isset($dbadmin['cancel'])) {
        return;
        } elseif (!empty($dbadmin_user)) {
        if ($this->_try_dbadmin_user($dbadmin['user'], $dbadmin['passwd']))
            return;
        }
    } elseif (DBADMIN_USER) {
        if ($this->_try_dbadmin_user(DBADMIN_USER, DBADMIN_PASSWD))
        return true;
    }
    // Check if the privileges are enough. Need CREATE and ALTER perms.
    // And on windows: SELECT FROM mysql, possibly: UPDATE mysql.
    $form = HTML::form(array("method" => "post",
                 "action" => $this->request->getPostURL(),
                 "accept-charset"=>$GLOBALS['charset']),
               HTML::p(_("Upgrade requires database privileges to CREATE and ALTER the phpwiki database."),
                   HTML::br(),
                   _("And on windows at least the privilege to SELECT FROM mysql, and possibly UPDATE mysql")),
               HiddenInputs(array('action' => 'upgrade',
                          'overwrite' => $this->request->getArg('overwrite'))),
               HTML::table(array("cellspacing"=>4),
                       HTML::tr(HTML::td(array('align'=>'right'),
                             _("DB admin user:")),
                        HTML::td(HTML::input(array('name'=>"dbadmin[user]",
                                       'size'=>12,
                                       'maxlength'=>256,
                                       'value'=>$dbadmin_user)))),
                       HTML::tr(HTML::td(array('align'=>'right'),
                             _("DB admin password:")),
                        HTML::td(HTML::input(array('name'=>"dbadmin[passwd]",
                                       'type'=>'password',
                                       'size'=>12,
                                       'maxlength'=>256)))),
                       HTML::tr(HTML::td(array('align'=>'center', 'colspan' => 2),
                             Button("submit:", _("Submit"), 'wikiaction'),
                             HTML::raw('&nbsp;'),
                             Button("submit:dbadmin[cancel]", _("Cancel"),
                                'button')))));
    $form->printXml();
    echo "</div><!-- content -->\n";
    echo asXML(Template("bottom"));
    echo "</body></html>\n";
    $this->request->finish();
    exit();
    }

    /**
     * if page.cached_html does not exists:
     *   put _cached_html from pagedata into a new seperate blob,
     *   not into the huge serialized string.
     *
     * It is only rarelely needed: for current page only, if-not-modified,
     * but was extracetd for every simple page iteration.
     */
    function _upgrade_cached_html ( $verbose=true ) {
    global $DBParams;
    if (!$this->isSQL) return;
    $count = 0;
    if ($this->phpwiki_version >= 1030.10) {
        if ($verbose)
        echo _("Check for extra page.cached_html column")," ... ";
        $database = $this->dbi->_backend->database();
        extract($this->dbi->_backend->_table_names);
        $fields = $this->dbi->_backend->listOfFields($database, $page_tbl);
        if (!$fields) {
        echo _("SKIP"), "<br />\n";
        return 0;
        }
        if (!strstr(strtolower(join(':', $fields)), "cached_html")) {
        if ($verbose)
            echo "<b>",_("ADDING"),"</b>"," ... ";
        $backend_type = $this->dbi->_backend->backendType();
        if (substr($backend_type,0,5) == 'mysql')
            $this->dbi->genericSqlQuery("ALTER TABLE $page_tbl ADD cached_html MEDIUMBLOB");
        else
            $this->dbi->genericSqlQuery("ALTER TABLE $page_tbl ADD cached_html BLOB");
        if ($verbose)
            echo "<b>",_("CONVERTING"),"</b>"," ... ";
        $count = _convert_cached_html();
        if ($verbose)
            echo $count, " ", _("OK"), "<br />\n";
        } else {
        if ($verbose)
            echo _("OK"), "<br />\n";
        }
    }
    return $count;
    }

    /**
     * move _cached_html for all pages from pagedata into a new seperate blob.
     * decoupled from action=upgrade, so that it can be used by a WikiAdminUtils button also.
     */
    function _convert_cached_html () {
    global $DBParams;
    if (!$this->isSQL) return;
    //if (!in_array(DATABASE_TYPE, array('SQL','ADODB'))) return;

    $pages = $this->dbi->getAllPages();
    $cache =& $this->dbi->_cache;
    $count = 0;
    extract($this->dbi->_backend->_table_names);
    while ($page = $pages->next()) {
        $pagename = $page->getName();
        $data = $this->dbi->_backend->get_pagedata($pagename);
        if (!empty($data['_cached_html'])) {
        $cached_html = $data['_cached_html'];
        $data['_cached_html'] = '';
        $cache->update_pagedata($pagename, $data);
        // store as blob, not serialized
        $this->dbi->genericSqlQuery("UPDATE $page_tbl SET cached_html=? WHERE pagename=?",
                      array($cached_html, $pagename));
        $count++;
        }
    }
    return $count;
    }

    /**
     * upgrade to 1.3.13 link structure.
     */
    function _upgrade_relation_links ( $verbose=true ) {
        if ($this->phpwiki_version >= 1030.12200610 and $this->isSQL) {
            echo _("Check for relation field in link table")," ...";
            $database = $this->dbi->_backend->database();
            $link_tbl = $prefix . 'link';
            $fields = $this->dbi->_backend->listOfFields($database, $link_tbl);
            if (!$fields) {
                echo _("SKIP");
            } elseif (strstr(strtolower(join(':', $fields)), "link")) {
                echo "<b>",_("ADDING")," relation</b>"," ... ";
                $this->dbi->genericSqlQuery("ALTER TABLE $link_tbl ADD relation INT DEFAULT 0;");
                $this->dbi->genericSqlQuery("CREATE INDEX link_relation ON $link_tbl (relation);");
            } else {
                echo _("FAIL");
            }
            echo "<br />\n";
        }
    if ($this->phpwiki_version >= 1030.12200610) {
        echo _("Rebuild entire database to upgrade relation links")," ... ";
        if (DATABASE_TYPE == 'dba') {
        echo "<b>",_("CONVERTING")," dba linktable</b>","(~2 min, max 4 min) ... ";
            flush();
            longer_timeout(240);
            $this->dbi->_backend->_linkdb->rebuild();
        } else {
            flush();
            longer_timeout(180);
            $this->dbi->_backend->rebuild();
        }
        echo _("OK"), "<br />\n";
    }
    }

    function CheckPluginUpdate() {
        return;

    echo "<h3>",sprintf(_("Check for necessary %s updates"),
                _("plugin argument")),"</h3>\n";

    $this->_configUpdates = array();
    $this->_configUpdates[] = new UpgradePluginEntry
        ($this, array('key' => 'plugin_randompage_numpages',
              'fixed_with' => 1012.0,
              //'header' => _("change RandomPage pages => numpages"),
              //'notice'  =>_("found RandomPage plugin"),
                      'check_args' => array("plugin RandomPage pages",
                                                "/(<\?\s*plugin\s+ RandomPage\s+)pages/",
                                                "\\1numpages")));
    $this->_configUpdates[] = new UpgradePluginEntry
        ($this, array('key' => 'plugin_createtoc_position',
              'fixed_with' => 1013.0,
              //'header' => _("change CreateToc align => position"),
              //'notice'  =>_("found CreateToc plugin"),
                      'check_args' => array("plugin CreateToc align",
                                                "/(<\?\s*plugin\s+ CreateToc[^\?]+)align/",
                                                "\\1position")));

    if (empty($this->_configUpdates)) return;
        foreach ($this->_configUpdates as $update) {
            $pages = $this->dbi->fullSearch($this->check_args[0]);
            while ($page = $allpages->next()) {
                $current = $page->getCurrentRevision();
                $pagetext = $current->getPackedContent();
            $update->check($this->check_args[1], $this->check_args[2], $pagetext, $page, $current);
        }
    }
    free($allpages);
    unset($pagetext);
    unset($current);
    unset($page);
    }

    /**
     * preg_replace over local file.
     * Only line-orientated matches possible.
     */
    function fixLocalFile($match, $replace, $filename) {
        $o_filename = $filename;
        if (!file_exists($filename))
        $filename = FindFile($filename);
        if (!file_exists($filename))
        return array(false, sprintf(_("file %s not found"), $o_filename));
    $found = false;
    if (is_writable($filename)) {
        $in = fopen($filename, "rb");
        $out = fopen($tmp = tempnam(getUploadFilePath(),"cfg"), "wb");
        if (isWindows())
        $tmp = str_replace("/","\\",$tmp);
        // Detect the existing linesep at first line. fgets strips it even if 'rb'.
        // Before we simply assumed \r\n on windows local files.
        $s = fread($in, 1024);
        rewind($in);
        $linesep = (substr_count($s, "\r\n") > substr_count($s, "\n")) ? "\r\n" : "\n";
        //$linesep = isWindows() ? "\r\n" : "\n";
        while ($s = fgets($in)) {
        // =>php-5.0.1 can fill count
        //$new = preg_replace($match, $replace, $s, -1, $count);
        $new = preg_replace($match, $replace, $s);
        if ($new != $s) {
            $s = $new . $linesep;
            $found = true;
        }
        fputs($out, $s);
        }
        fclose($in);
        fclose($out);
        if (!$found) {
        // todo: skip
            $reason = sprintf(_("%s not found in %s"), $match, $filename);
        unlink($out);
        return array($found, $reason);
        } else {
        @unlink("$file.bak");
        @rename($file,"$file.bak");
        if (!rename($tmp, $file))
                return array(false, sprintf(_("couldn't move %s to %s"), $tmp, $filename));
            return true;
        }
    } else {
        return array(false, sprintf(_("file %s is not writable"), $filename));
    }
    }

    function CheckConfigUpdate () {
    echo "<h3>",sprintf(_("Check for necessary %s updates"),
                "config.ini"),"</h3>\n";
    $entry = new UpgradeConfigEntry
        ($this, array('key' => 'cache_control_none',
                      'fixed_with' => 1012.0,
                      'header' => sprintf(_("Check for %s"),"CACHE_CONTROL = NONE"),
                      'applicable_args' => 'CACHE_CONTROL',
                      'notice'  => _("CACHE_CONTROL is set to 'NONE', and must be changed to 'NO_CACHE'"),
                      'check_args' => array("/^\s*CACHE_CONTROL\s*=\s*NONE/", "CACHE_CONTROL = NO_CACHE")));
    $entry->setApplicableCb(new WikiMethodCb($entry, '_applicable_defined_and_empty'));
    $this->_configUpdates[] = $entry;

    $entry = new UpgradeConfigEntry
        ($this, array('key' => 'group_method_none',
              'fixed_with' => 1012.0,
              'header' => sprintf(_("Check for %s"), "GROUP_METHOD = NONE"),
              'applicable_args' => 'GROUP_METHOD',
              'notice'  =>_("GROUP_METHOD is set to NONE, and must be changed to \"NONE\""),
                      'check_args' => array("/^\s*GROUP_METHOD\s*=\s*NONE/", "GROUP_METHOD = \"NONE\"")));
    $entry->setApplicableCb(new WikiMethodCb($entry, '_applicable_defined_and_empty'));
    $this->_configUpdates[] = $entry;

    $entry = new UpgradeConfigEntry
        ($this, array('key' => 'blog_empty_default_prefix',
              'fixed_with' => 1013.0,
              'header' => sprintf(_("Check for %s"), "BLOG_EMPTY_DEFAULT_PREFIX"),
              'applicable_args' => 'BLOG_EMPTY_DEFAULT_PREFIX',
              'notice'  =>_("fix BLOG_EMPTY_DEFAULT_PREFIX into BLOG_DEFAULT_EMPTY_PREFIX"),
                      'check_args' => array("/BLOG_EMPTY_DEFAULT_PREFIX\s*=/","BLOG_DEFAULT_EMPTY_PREFIX =")));
    $entry->setApplicableCb(new WikiMethodCb($entry, '_applicable_defined'));
    $this->_configUpdates[] = $entry;

    // TODO: find extra file updates
    if (empty($this->_configUpdates)) return;
    foreach ($this->_configUpdates as $update) {
        $update->check();
    }
    }

} // class Upgrade

class UpgradeEntry
{
    /**
     * Add an upgrade item to be checked.
     *
     * @param $parent object The parent Upgrade class to inherit the version properties
     * @param $key string    A short unique key to store success in the WikiDB
     * @param $fixed_with double @see phpwiki_version() number
     * @param $header string Optional header to be printed always even if not applicable
     * @param $applicable WikiCallback Optional callback boolean applicable()
     * @param $notice string Description of the check
     * @param $method WikiCallback Optional callback array method(array)
     * //param All other args are passed to $method
     */
    function UpgradeEntry(&$parent, $params) {
    $this->parent =& $parent;           // get the properties db_version
        foreach (array('key' => 'required',
                   // the wikidb stores the version when we actually fixed that.
                       'fixed_with' => 'required',
                       'header' => '',          // always printed
                       'applicable_cb' => null, // method to check if applicable
                       'applicable_args' => array(), // might be the config name
                       'notice' => '',
                       'check_cb' => null,      // method to apply
                       'check_args' => array())
                 as $k => $v)
        {
            if (!isset($params[$k])) { // default
                if ($v == 'required') trigger_error("Required arg $k missing", E_USER_ERROR);
                else $this->{$k} = $v;
            } else {
                $this->{$k} = $params[$k];
            }
        }
    if (!is_array($this->applicable_args)) // single arg convenience shortcut
        $this->applicable_args = array($this->applicable_args);
    if (!is_array($this->check_args))   // single arg convenience shortcut
        $this->check_args = array($this->check_args);
    if ($this->notice === '' and count($this->applicable_args) > 0)
        $this->notice = 'Check for '.join(', ', $this->applicable_args);
    $this->_db_key = "_upgrade";
    $this->upgrade = $this->parent->dbi->get($this->_db_key);
    }
    /* needed ? */
    function setApplicableCb($object) {
    $this->applicable_cb =& $object;
    }
    function _check_if_already_fixed() {
    // not yet fixed?
    if (!isset($this->upgrade['name'])) return false;
    // override with force?
    if ($this->parent->request->getArg('force')) return false;
    // already fixed and with an ok version
    if ($this->upgrade['name'] >= $this->fixed_with) return $this->upgrade['name'];
    // already fixed but with an older version. do it again.
    return false;
    }
    function pass() {
    // store in db no to fix again
    $this->upgrade['name'] = $this->parent->phpwiki_version;
    $this->parent->dbi->set($this->_db_key, $this->upgrade);
    echo "<b>",_("FIXED"),"</b>";
    if (isset($this->reason))
        echo _(": "), $this->reason;
    echo "<br />\n";
    flush();
    return true;
    }
    function fail() {
    echo " <b><font color=\"red\">", _("FAILED"), "</font></b>";
    if (isset($this->reason))
        echo _(": "), $this->reason;
    echo "<br />\n";
    flush();
    return false;
    }
    function skip() { // not applicable
        if (isset($this->silent_skip)) return true;
    echo _(" Skipped"),".<br />\n";
    flush();
    return true;
    }
    function check($args = null) {
    if ($this->header) echo $this->header, ' ... ';
    if ($when = $this->_check_if_already_fixed()) {
        // be totally silent if no header is defined.
        if ($this->header) echo _("fixed with")," ",$when,"<br />\n";
        flush();
        return true;
    }
    if (is_object($this->applicable_cb)) {
        if (!$this->applicable_cb->call_array($this->applicable_args))
            return $this->skip();
    }
    if ($this->notice) {
        if ($this->header)
            echo "<br />\n";
        echo $this->notice," ";
        flush();
    }
    if (!is_null($args)) $this->check_args =& $args;
    if (is_object($this->check_cb))
        $do = $this->method_cb->call_array($this->check_args);
    else
        $do = $this->default_method($this->check_args);
    if (is_array($do)) {
        $this->reason = $do[1];
        $do = $do[0];
    }
    return $do ? $this->pass() : $this->fail();
    }
} // class UpgradeEntry

class UpgradeConfigEntry extends UpgradeEntry {
    function _applicable_defined() {
        return (boolean)defined($this->applicable_args[0]);
    }
    function _applicable_defined_and_empty() {
        $const = $this->applicable_args[0];
        return (boolean)(defined($const) and !constant($const));
    }
    function default_method ($args) {
    $match = $args[0];
    $replace = $args[1];
    return $this->parent->fixLocalFile($match, $replace, "config/config.ini");
    }
} // class UpdateConfigEntry

/* This is different */
class UpgradePluginEntry extends UpgradeEntry {

   /**
     * check all pages for a plugin match
     */
    var $silent_skip = 1;

    function default_method (&$args) {
        $match    =  $args[0];
        $replace  =  $args[1];
        $pagetext =& $args[2];
        $page     =& $args[3];
        $current  =& $args[4];
    if (preg_match($match, $pagetext)) {
        echo $page->getName()," ",$this->notice," ... ";
        if ($newtext = preg_replace($match, $replace, $pagetext)) {
        $meta = $current->_data;
        $meta['summary'] = "upgrade: ".$this->header;
        $page->save($newtext, $current->getVersion() + 1, $meta);
        $this->pass();
        } else {
        $this->fail();
        }
    }
    }
} // class UpdatePluginEntry

/**
 * fix custom themes which are not in our distribution
 * this should be optional
 */
class UpgradeThemeEntry extends UpgradeEntry {

    function default_method (&$args) {
        $match    =  $args[0];
        $replace  =  $args[1];
        $template = $args[2];
    }

    function fixThemeTemplate($match, $new, $template) {
        // for all custom themes
        $ourthemes = explode(":","blog:Crao:default:Hawaiian:MacOSX:MonoBook:Portland:shamino_com:SpaceWiki:wikilens:Wordpress");
    $themedir = NormalizeLocalFileName("themes");
    $dh = opendir($themedir);
    while ($r = readdir($dh)) {
        if (filetype($r) == 'dir' and $r[0] != '.' and !is_array($r, $ourthemes))
        $customthemes[] = $r;
    }
    $success = true;
    $errors = '';
    foreach ($customthemes as $customtheme) {
        $template = FindFile("themes/$customtheme/templates/$template");
        $do = $this->parent->fixLocalFile($match, $new, template);
        if (!$do[0]) {
        $success = false;
        $errors .= $do[1]." ";
        echo $do[1];
        }
    }
    return array($success, $errors);
    }
}

/**
 * TODO:
 *
 * Upgrade: Base class for multipage worksteps
 * identify, validate, display options, next step
 */
/*
*/

// TODO: At which step are we?
// validate and do it again or go on with next step.

/** entry function from lib/main.php
 */
function DoUpgrade(&$request) {

    if (!$request->_user->isAdmin()) {
        $request->_notAuthorized(WIKIAUTH_ADMIN);
        $request->finish(
                         HTML::div(array('class' => 'disabled-plugin'),
                                   fmt("Upgrade disabled: user != isAdmin")));
        return;
    }
    // TODO: StartLoadDump should turn on implicit_flush.
    @ini_set("implicit_flush", true);
    StartLoadDump($request, _("Upgrading this PhpWiki"));
    $upgrade = new Upgrade($request);
    //if (!$request->getArg('noindex'))
    //    CheckOldIndexUpdate($request); // index.php => config.ini to upgrade from < 1.3.10
    if (!$request->getArg('nodb'))
    $upgrade->CheckDatabaseUpdate($request);   // first check cached_html and friends
    if (!$request->getArg('nopgsrc')) {
    $upgrade->CheckPgsrcUpdate($request);
    $upgrade->CheckActionPageUpdate($request);
    }
    if (!$request->getArg('noplugin'))
    $upgrade->CheckPluginUpdate($request);
    if (!$request->getArg('noconfig'))
    $upgrade->CheckConfigUpdate($request);
    // This is optional and should be linked. In EndLoadDump or PhpWikiAdministration?
    //if ($request->getArg('theme'))
    //      $upgrade->CheckThemeUpdate($request);
    EndLoadDump($request);
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
