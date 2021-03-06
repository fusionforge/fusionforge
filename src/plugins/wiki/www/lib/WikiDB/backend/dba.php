<?php

require_once 'lib/WikiDB/backend/dbaBase.php';
require_once 'lib/DbaDatabase.php';

class WikiDB_backend_dba
    extends WikiDB_backend_dbaBase
{
    function __construct($dbparams)
    {
        $directory = '/tmp';
        $prefix = 'wiki_';
        $dba_handler = 'gdbm';
        $timeout = 20;
        extract($dbparams);
        if ($directory) $directory .= "/";
        $dbfile = $directory . $prefix . 'pagedb' . '.' . $dba_handler;

        // FIXME: error checking.
        $db = new DbaDatabase($dbfile, false, $dba_handler);
        $db->set_timeout($timeout);

        // Workaround for BDB 4.1 bugs
        if (file_exists($dbfile)) {
            $mode = 'w';
        } else {
            $mode = 'c';
        }
        if (!$db->open($mode)) {
            trigger_error(sprintf(_("%s: Can't open dba database"), $dbfile), E_USER_ERROR);
            global $request;
            $request->finish(fmt("%s: Can't open dba database", $dbfile));
        }

        parent::__construct($db);
    }

    function lock($tables = array(), $write_lock = true)
    {
    }

    function unlock($tables = array(), $force = false)
    {
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
