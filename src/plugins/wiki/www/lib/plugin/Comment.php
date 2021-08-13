<?php
/**
 * Copyright © 2003 Martin Geisler
 * Copyright © 2003-2004 $ThePhpWikiProgrammingTeam
 * Copyright © 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * A WikiPlugin for putting comments in WikiPages
 *
 * Usage:
 * <<Comment
 *
 * == My Secret Text
 *
 * This is some WikiText that won't show up on the page.
 *
 * >>
 */

class WikiPlugin_Comment
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Embed hidden comments in WikiPages.");
    }

    // No arguments here.
    function getDefaultArguments()
    {
        return array();
    }

    /**
     * @param WikiDB $dbi
     * @param string $argstr
     * @param WikiRequest $request
     * @param string $basepage
     * @return mixed
     */
    function run($dbi, $argstr, &$request, $basepage)
    {
        return HTML::raw('');
    }
}
