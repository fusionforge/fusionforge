<?php // $Id: DbSession.php 7964 2011-03-05 17:05:30Z vargenau $

/**
 * Store sessions data in Pear DB / ADODB / dba / PDO, ....
 *
 * History
 *
 * Originally by Stanislav Shramko <stanis@movingmail.com>
 * Minor rewrite by Reini Urban <rurban@x-ray.at> for Phpwiki.
 * Quasi-major rewrite/decruft/fix by Jeff Dairiki <dairiki@dairiki.org>.
 * ADODB, dba and PDO classes by Reini Urban.
 *
 * Warning: Enable USE_SAFE_DBSESSION if you get INSERT duplicate id warnings.
 */
class DbSession
{
    /**
     * Constructor
     *
     * @param mixed $dbh
     * DB handle, or WikiDB object (from which the DB handle will
     * be extracted.
     *
     * @param string $table
     * Name of SQL table containing session data.
     */
    function DbSession(&$dbh, $table = 'session') {
        // Check for existing DbSession handler
        $db_type = $dbh->getParam('dbtype');
        if (isa($dbh, 'WikiDB')) {
            @include_once("lib/DbSession/".$db_type.".php");

            $class = "DbSession_".$db_type;
            if (class_exists($class)) {
                // dba has no ->_dbh, so this is used for the session link
                $this->_backend = new $class($dbh->_backend->_dbh, $table);
                return $this;
            }
        }
        //Fixme: E_USER_WARNING ignored!
        trigger_error(sprintf(_("Your WikiDB DB backend '%s' cannot be used for DbSession.")." ".
                              _("Set USE_DB_SESSION to false."),
                             $db_type), E_USER_WARNING);
        return false;
    }

    function currentSessions() {
        return $this->_backend->currentSessions();
    }
    function query($sql) {
        return $this->_backend->query($sql);
    }
    function quote($string) { return $string; }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
