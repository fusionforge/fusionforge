<?php // rcs_id('$Id: dba.php 7417 2010-05-19 12:57:42Z vargenau $');

/** DBA Sessions
 *  session:
 *     Index: session_id
 *     Values: date : IP : data
 *  Already open sessions, e.g. interim xmlrpc requests are
 *  are treated specially. see write(). 
 *  To avoid deadlocks in the session.db3 access,
 *  the db is opened and closed for each access.
 * @author: Reini Urban.
 */
class DbSession_dba
extends DbSession
{
    var $_backend_type = "dba";

    function DbSession_dba (&$dbh, $table) {
        $this->_dbh = $dbh;
        ini_set('session.save_handler','user');
        session_module_name('user'); // new style
        session_set_save_handler(array(&$this, 'open'),
                                 array(&$this, 'close'),
                                 array(&$this, 'read'),
                                 array(&$this, 'write'),
                                 array(&$this, 'destroy'),
                                 array(&$this, 'gc'));
        return $this;
    }

    function quote($str) { return $str; }
    function query($sql) { return false; }

    function & _connect() {
        global $DBParams;
        $dbh = &$this->_dbh;
        if (!$dbh) {
            $directory = '/tmp';
            $prefix = 'wiki_';
            $dba_handler = 'gdbm';
            $timeout = 12;
            extract($DBParams); // overwrite the defaults
            $dbfile = "$directory/$prefix" . 'session' . '.' . $dba_handler;
            $dbh = new DbaDatabase($dbfile, 'c', $dba_handler);
            $this->_dbh = &$dbh;
        }
        return $dbh;
    }

    function _disconnect() {
        if (isset($this->_dbh)) {
            $this->_dbh->close();
            unset($this->_dbh);
        }
    }

    function open ($save_path, $session_name) {
        $dbh = $this->_connect();
        $dbh->open();
    }

    function close() {
        $this->_disconnect();
    }

    function read ($id) {
        $dbh = $this->_connect();
        $result = $dbh->get($id);
        if (!$result) {
            return false;
        }
        list(,,$packed) = explode(':', $result, 3);
        $this->_disconnect();
        if (strlen($packed) > 4000) {
            trigger_error("Overlarge session data!", E_USER_WARNING);
            $packed = '';
            //$res = preg_replace('/s:6:"_cache";O:12:"WikiDB_cache".+}$/',"",$res);
        }
        return $packed;
    }
  
    function write ($id, $sess_data) {
        if (defined("WIKI_XMLRPC") or defined("WIKI_SOAP")) return;

        $dbh = $this->_connect();
        $time = time();
        $ip = $GLOBALS['request']->get('REMOTE_ADDR');
        if (strlen($sess_data) > 4000) {
            trigger_error("Overlarge session data!", E_USER_WARNING);
            $sess_data = '';
        }
        $dbh->set($id, $time.':'.$ip.':'.$sess_data);
        $this->_disconnect();
        return true;
    }

    function destroy ($id) {
        $dbh = $this->_connect();
        $dbh->delete($id);
        $this->_disconnect();
        return true;
    }

    function gc ($maxlifetime) {
        $dbh = $this->_connect();
        $threshold = time() - $maxlifetime;
        for ($id = $dbh->firstkey(); $id !== false; $id = $dbh->nextkey()) {
            $result = $dbh->get($id);
            list($date,,) = explode(':', $result, 3);
            if ($date < $threshold)
                $dbh->delete($id);
        }
        $this->_disconnect();
        return true;
    }

    // WhoIsOnline support. 
    // TODO: ip-accesstime dynamic blocking API
    function currentSessions() {
        $sessions = array();
        $dbh = $this->_connect();
        for ($id = $dbh->firstkey(); $id !== false; $id = $dbh->nextkey()) {
            $result = $dbh->get($id);
            list($date,$ip,$packed) = explode(':', $result, 3);
            if (!$packed) continue;
            // session_data contains the <variable name> + "|" + <packed string>
            // we need just the wiki_user object (might be array as well)
            if ($date < 908437560 or $date > 1588437560)
                $date = 0;
            $user = strstr($packed, "wiki_user|");
            $sessions[] = array('wiki_user' => substr($user,10), // from "O:" onwards
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
?>
