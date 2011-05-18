<?php // $Id: DbaDatabase.php 8034 2011-04-11 09:22:33Z vargenau $

require_once('lib/ErrorManager.php');

if (isWindows())
    define('DBA_DATABASE_DEFAULT_TIMEOUT', 60);
else
    define('DBA_DATABASE_DEFAULT_TIMEOUT', 5);

class DbaDatabase
{
    function DbaDatabase($filename, $mode = false, $handler = 'gdbm') {
        $this->_file = $filename;
        $this->_handler = $handler;
        $this->_timeout = DBA_DATABASE_DEFAULT_TIMEOUT;
        $this->_dbh = false;
        if (!in_array($handler, dba_handlers()))
            $this->_error(
                sprintf(
                    _("The DBA handler %s is unsupported!")."\n".
                        _("Supported handlers are: %s"),
                         $handler, join(",",dba_handlers())));
        $this->readonly = false;
        if ($mode)
            $this->open($mode);
    }

    function set_timeout($timeout) {
        $this->_timeout = $timeout;
    }

    function open($mode = 'w') {
        if ($this->_dbh)
            return;             // already open.

        $watchdog = $this->_timeout;

        global $ErrorManager;
        $this->_dba_open_error = false;
        $ErrorManager->pushErrorHandler(new WikiMethodCb($this, '_dba_open_error_handler'));

        // oops, you don't have DBA support.
        if (!function_exists("dba_open")) {
            echo "You don't seem to have DBA support compiled into PHP.";
        }

        if (READONLY) {
            $mode = 'r';
        }

        if ((strlen($mode) == 1)) {
            // PHP 4.3.x Windows lock bug workaround: http://bugs.php.net/bug.php?id=23975
            if (isWindows()) {
                $mode .= "-";             // suppress locking, or
            } elseif ($this->_handler != 'gdbm') {     // gdbm does it internally
                $mode .= "d";             // else use internal locking
            }
        }
        while (($dbh = dba_open($this->_file, $mode, $this->_handler)) < 1) {
            if ($watchdog <= 0)
                break;
            // "c" failed, try "w" instead.
            if ($mode == "w"
                and file_exists($this->_file)
                and (isWindows() or !is_writable($this->_file)))
            {
                // try to continue with read-only
                if (!defined("READONLY"))
                    define("READONLY", true);
                $GLOBALS['request']->_dbi->readonly = true;
                $this->readonly = true;
                $mode = "r";
            }
            if (substr($mode,0,1) == "c" and file_exists($this->_file) and !READONLY)
                $mode = "w";
            // conflict: wait some random time to unlock (as with ethernet)
            $secs = 0.5 + ((double)rand(1,32767)/32767);
            sleep($secs);
            $watchdog -= $secs;
            if (strlen($mode) == 2) $mode = substr($mode,0,-1);
        }
        $ErrorManager->popErrorHandler();

        if (!$dbh) {
            if ( ($error = $this->_dba_open_error) ) {
                $error->errno = E_USER_ERROR;
                $error->errstr .= "\nfile: " . $this->_file
                               .  "\nmode: " . $mode
                               .  "\nhandler: " . $this->_handler;
                // try to continue with read-only
                if (!defined("READONLY"))
                    define("READONLY", true);
                $GLOBALS['request']->_dbi->readonly = true;
                $this->readonly = true;
                if (!file_exists($this->_file)) {
                    $ErrorManager->handleError($error);
                flush();
                }
            }
            else {
                trigger_error("dba_open failed", E_USER_ERROR);
            }
        }
        $this->_dbh = $dbh;
        return !empty($dbh);
    }

    function close() {
        if ($this->_dbh)
            dba_close($this->_dbh);
        $this->_dbh = false;
    }

    function exists($key) {
        return dba_exists($key, $this->_dbh);
    }

    function fetch($key) {
        $val = dba_fetch($key, $this->_dbh);
        if ($val === false)
            return $this->_error("fetch($key)");
        return $val;
    }

    function insert($key, $val) {
        if (!dba_insert($key, $val, $this->_dbh))
            return $this->_error("insert($key)");
    }

    function replace($key, $val) {
        if (!dba_replace($key, $val, $this->_dbh))
            return $this->_error("replace($key)");
    }


    function firstkey() {
        return dba_firstkey($this->_dbh);
    }

    function nextkey() {
        return dba_nextkey($this->_dbh);
    }

    function delete($key) {
        if ($this->readonly) return;
        if (!dba_delete($key, $this->_dbh))
            return $this->_error("delete($key)");
    }

    function get($key) {
        return dba_fetch($key, $this->_dbh);
    }

    function set($key, $val) {
        $dbh = &$this->_dbh;
        if ($this->readonly) return;
        if (dba_exists($key, $dbh)) {
            if ($val !== false) {
                if (!dba_replace($key, $val, $dbh))
                    return $this->_error("store[replace]($key)");
            }
            else {
                if (!dba_delete($key, $dbh))
                    return $this->_error("store[delete]($key)");
            }
        }
        else {
            if (!dba_insert($key, $val, $dbh))
                return $this->_error("store[insert]($key)");
        }
    }

    function sync() {
        if (!dba_sync($this->_dbh))
            return $this->_error("sync()");
    }

    function optimize() {
        if (!dba_optimize($this->_dbh))
            return $this->_error("optimize()");
        return 1;
    }

    function _error($mes) {
        //trigger_error("DbaDatabase: $mes", E_USER_WARNING);
        //return false;
        trigger_error("$this->_file: dba error: $mes", E_USER_ERROR);
    }

    function _dump() {
        $dbh = &$this->_dbh;
        for ($key = $this->firstkey($dbh); $key; $key = $this->nextkey($dbh))
            printf("%10s: %s\n", $key, $this->fetch($key));
    }

    function _dba_open_error_handler($error) {
        $this->_dba_open_error = $error;
        return true;
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
