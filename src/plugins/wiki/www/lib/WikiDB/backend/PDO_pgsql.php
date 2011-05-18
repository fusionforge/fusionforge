<?php // -*-php-*-
// $Id: PDO_pgsql.php 8071 2011-05-18 14:56:14Z vargenau $

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
 * @author: Reini Urban
 */
require_once('lib/WikiDB/backend/PDO.php');

class WikiDB_backend_PDO_pgsql
extends WikiDB_backend_PDO
{

    /*
     * convert from,count to SQL "LIMIT $count OFFSET $from"
     */
    function _limit_sql($limit = false) {
        if ($limit) {
            list($offset, $count) = $this->limit($limit);
            if ($offset)
                $limit = " LIMIT $count OFFSET $from";
            else
                $limit = " LIMIT $count";
        } else
            $limit = '';
        return $limit;
    }

    function backendType() {
        return 'pgsql';
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
