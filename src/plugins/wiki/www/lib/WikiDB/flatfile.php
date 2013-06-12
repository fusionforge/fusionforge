<?php

/**
 * Copyright 1999, 2005 $ThePhpWikiProgrammingTeam
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

require_once 'lib/WikiDB.php';
require_once 'lib/WikiDB/backend/flatfile.php';

/**
 * Wrapper class for the flatfile backend.
 * flatfile has readable (mimified) page_data files, the rest is the
 * same as in the file backend (serialized arrays).
 */
class WikiDB_flatfile extends WikiDB
{
    /**
     * Constructor requires the DB parameters.
     */
    function WikiDB_flatfile($dbparams)
    {
        $backend = new WikiDB_backend_flatfile($dbparams);
        $backend->_wikidb =& $this;
        $this->WikiDB($backend, $dbparams);

        if (empty($dbparams['directory'])
            || preg_match('@^/tmp\b@', $dbparams['directory'])
        )
            trigger_error(sprintf(_("The %s files are in the %s directory. Please read the INSTALL file and move the database to a permanent location or risk losing all the pages!"),
                "Page", "/tmp"), E_USER_WARNING);
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
