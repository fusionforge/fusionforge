<?php
/**
 * Copyright © 1999, 2000, 2001, 2002, 2003 $ThePhpWikiProgrammingTeam
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

require_once 'lib/WikiDB.php';
require_once 'lib/WikiDB/backend/file.php';

/**
 * Wrapper class for the file backend.
 *
 * Authors: Gerrit Riessen, gerrit.riessen@open-source-consultants.de
 *          Jochen Kalmbach <Jochen@Kalmbachnet.de>
 */

class WikiDB_file extends WikiDB
{
    function __construct($dbparams)
    {
        $backend = new WikiDB_backend_file($dbparams);
        parent::__construct($backend, $dbparams);

        if (empty($dbparams['directory'])
            || preg_match('@^/tmp\b@', $dbparams['directory'])
        )
            trigger_error(sprintf(_("The %s files are in the %s directory. Please read the INSTALL file and move the database to a permanent location or risk losing all the pages!"),
                "Page", "/tmp"), E_USER_WARNING);
    }
}
