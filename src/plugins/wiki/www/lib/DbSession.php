<?php
/**
 * Copyright © 2002,2004-2005 Reini Urban
 * Copyright © 2003 Jeff Dairiki
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
 */
class DbSession
{
    /**
     * @param mixed $dbh
     * DB handle, or WikiDB object (from which the DB handle will be extracted.
     *
     * @param string $table
     * Name of SQL table containing session data.
     */
    function __construct($dbh, $table = 'session')
    {
        // Check for existing DbSession handler
        $db_type = $dbh->getParam('dbtype');
        if (is_a($dbh, 'WikiDB')) {
            @include_once("lib/DbSession/" . $db_type . ".php");

            $class = "DbSession_" . $db_type;
            if (class_exists($class)) {
                // dba has no ->_dbh, so this is used for the session link
                $this->_backend = new $class($dbh->_backend->_dbh, $table);
                return;
            }
        }
        //Fixme: E_USER_WARNING ignored!
        trigger_error(sprintf(_("Your WikiDB DB backend “%s” cannot be used for DbSession.") . " " .
                _("Set USE_DB_SESSION to false."),
            $db_type), E_USER_WARNING);
    }

    function currentSessions()
    {
        return $this->_backend->currentSessions();
    }

    function query($sql)
    {
        return $this->_backend->query($sql);
    }

    function quote($string)
    {
        return $string;
    }
}
