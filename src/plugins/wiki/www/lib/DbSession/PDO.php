<?php
/*
 * Copyright 2005 $ThePhpWikiProgrammingTeam
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
 * Db sessions for PDO, based on pear DB Sessions.
 *
 * @author: Reini Urban
 */
class DbSession_PDO
    extends DbSession
{
    public $_backend_type = "PDO";

    function __construct($dbh, $table)
    {
        $this->_dbh = $dbh;
        $this->_table = $table;

        ini_set('session.save_handler', 'user');
        session_module_name('user'); // new style
        session_set_save_handler(array(&$this, 'open'),
            array(&$this, 'close'),
            array(&$this, 'read'),
            array(&$this, 'write'),
            array(&$this, 'destroy'),
            array(&$this, 'gc'));
    }

    function & _connect()
    {
        $dbh = &$this->_dbh;
        if (!$dbh or !is_object($dbh)) {
            global $DBParams;
            $db = new WikiDB_backend_PDO($DBParams);
            $this->_dbh =& $db->_dbh;
            $this->_backend =& $db;
        }
        return $dbh;
    }

    function query($sql)
    {
        return $this->_backend->query($sql);
    }

    // adds surrounding quotes
    function quote($string)
    {
        return $this->_backend->quote($string);
    }

    function _disconnect()
    {
        if (0 and $this->_dbh)
            unset($this->_dbh);
    }

    /**
     * Opens a session.
     *
     * Actually this function is a fake for session_set_save_handle.
     * @param  string  $save_path    a path to stored files
     * @param  string  $session_name a name of the concrete file
     * @return boolean true just a variable to notify PHP that everything
     * is good.
     */
    public function open($save_path, $session_name)
    {
        //$this->log("_open($save_path, $session_name)");
        return true;
    }

    /**
     * Closes a session.
     *
     * This function is called just after <i>write</i> call.
     *
     * @return boolean true just a variable to notify PHP that everything
     * is good.
     */
    public function close()
    {
        //$this->log("_close()");
        return true;
    }

    /**
     * Reads the session data from DB.
     *
     * @param  string $id an id of current session
     * @return string
     */
    public function read($id)
    {
        $dbh = $this->_connect();
        $table = $this->_table;
        $sth = $dbh->prepare("SELECT sess_data FROM $table WHERE sess_id=?");
        $sth->bindParam(1, $id, PDO::PARAM_STR, 32);
        if ($sth->execute()) {
            $res = $sth->fetchColumn();
        } else {
            $res = '';
        }
        $this->_disconnect();
        if (!empty($res) and is_a($dbh, 'ADODB_postgres64')) {
            $res = base64_decode($res);
        }
        if (strlen($res) > 4000) {
            // trigger_error("Overlarge session data! ".strlen($res). " gt. 4000", E_USER_WARNING);
            $res = preg_replace('/s:6:"_cache";O:12:"WikiDB_cache".+}$/', "", $res);
            $res = preg_replace('/s:12:"_cached_html";s:.+",s:4:"hits"/', 's:4:"hits"', $res);
            if (strlen($res) > 4000) {
                $res = '';
            }
        }
        return $res;
    }

    /**
     * Saves the session data into DB.
     *
     * Just  a  comment:       The  "write"  handler  is  not
     * executed until after the output stream is closed. Thus,
     * output from debugging statements in the "write" handler
     * will  never be seen in the browser. If debugging output
     * is  necessary, it is suggested that the debug output be
     * written to a file instead.
     *
     * @param  string  $id
     * @param  string  $sess_data
     * @return boolean true if data saved successfully  and false
     * otherwise.
     */
    public function write($id, $sess_data)
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        if (defined("WIKI_XMLRPC") or defined("WIKI_SOAP")) return false;

        $dbh = $this->_connect();
        $table = $this->_table;
        $time = time();

        // postgres can't handle binary data in a TEXT field.
        if (is_a($dbh, 'ADODB_postgres64'))
            $sess_data = base64_encode($sess_data);

        /* AffectedRows with sessions seems to be unstable on certain platforms.
         * Enable the safe and slow USE_SAFE_DBSESSION then.
         */
        if (USE_SAFE_DBSESSION) {
            $this->_backend->beginTransaction();
            $rs = $this->query("DELETE FROM $table"
                . " WHERE sess_id=$id");
            $sth = $dbh->prepare("INSERT INTO $table"
                . " (sess_id, sess_data, sess_date, sess_ip)"
                . " VALUES (?, ?, ?, ?)");
            $sth->bindParam(1, $id, PDO::PARAM_STR, 32);
            $sth->bindParam(2, $sess_data, PDO::PARAM_LOB);
            $sth->bindParam(3, $time, PDO::PARAM_INT);
            $sth->bindParam(4, $request->get('REMOTE_ADDR'), PDO::PARAM_STR, 15);
            if ($result = $sth->execute()) {
                $this->_backend->commit();
            } else {
                $this->_backend->rollBack();
            }
        } else {
            $sth = $dbh->prepare("UPDATE $table"
                . " SET sess_data=?, sess_date=?, sess_ip=?"
                . " WHERE sess_id=?");
            $sth->bindParam(1, $sess_data, PDO::PARAM_LOB);
            $sth->bindParam(2, $time, PDO::PARAM_INT);
            $sth->bindParam(3, $request->get('REMOTE_ADDR'), PDO::PARAM_STR, 15);
            $sth->bindParam(4, $id, PDO::PARAM_STR, 32);
            $result = $sth->execute(); // implicit affected rows
            if ($result === false or $result < 1) { // false or int > 0
                $sth = $dbh->prepare("INSERT INTO $table"
                    . " (sess_id, sess_data, sess_date, sess_ip)"
                    . " VALUES (?, ?, ?, ?)");
                $sth->bindParam(1, $id, PDO::PARAM_STR, 32);
                $sth->bindParam(2, $sess_data, PDO::PARAM_LOB);
                $sth->bindParam(3, $time, PDO::PARAM_INT);
                $sth->bindParam(4, $request->get('REMOTE_ADDR'), PDO::PARAM_STR, 15);
                $result = $sth->execute();
            }
        }
        $this->_disconnect();
        return $result;
    }

    /**
     * Destroys a session.
     *
     * Removes a session from the table.
     *
     * @param  string  $id
     * @return boolean true
     */
    public function destroy($id)
    {
        $table = $this->_table;
        $dbh = $this->_connect();
        $sth = $dbh->prepare("DELETE FROM $table WHERE sess_id=?");
        $sth->bindParam(1, $id, PDO::PARAM_STR, 32);
        $sth->execute();
        $this->_disconnect();
        return true;
    }

    /**
     * Cleans out all expired sessions.
     *
     * @param  int     $maxlifetime session's time to live.
     * @return boolean true
     */
    public function gc($maxlifetime)
    {
        $table = $this->_table;
        $threshold = time() - $maxlifetime;
        $dbh = $this->_connect();
        $sth = $dbh->prepare("DELETE FROM $table WHERE sess_date < ?");
        $sth->bindParam(1, $threshold, PDO::PARAM_INT);
        $sth->execute();
        $this->_disconnect();
        return true;
    }

    // WhoIsOnline support
    // TODO: ip-accesstime dynamic blocking API
    function currentSessions()
    {
        $sessions = array();
        $table = $this->_table;
        $dbh = $this->_connect();
        $sth = $dbh->prepare("SELECT sess_data,sess_date,sess_ip FROM $table ORDER BY sess_date DESC");
        if (!$sth->execute()) {
            return $sessions;
        }
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $data = $row[0];
            $date = $row[1];
            $ip = $row[2];
            if (preg_match('|^[a-zA-Z0-9/+=]+$|', $data))
                $data = base64_decode($data);
            if ($date < 908437560 or $date > 1588437560)
                $date = 0;
            // session_data contains the <variable name> + "|" + <packed string>
            // we need just the wiki_user object (might be array as well)
            $user = strstr($data, "wiki_user|");
            $sessions[] = array('wiki_user' => substr($user, 10), // from "O:" onwards
                'date' => $date,
                'ip' => $ip);
        }
        $this->_disconnect();
        return $sessions;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
