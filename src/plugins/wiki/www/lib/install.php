<?php

/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * Loaded when config/config.ini was not found.
 * So we have no main loop and no request object yet.
 */

function init_install()
{
    // prevent from recursion
    static $already = 0;
    // setup default settings
    if (!$already)
        IniConfig(dirname(__FILE__) . "/../config/config-dist.ini");
    $already = 1;
}

/**
 * Display a screen of various settings:
 * 1. convert from older index.php configuration [TODO]
 * 2. database and admin_user setup based on configurator.php
 * 3. dump the current settings to config/config.ini.
 */
function run_install($part = '')
{
    static $already = 0;
    if ($part) {
        if (empty($_GET)) $_GET =& $GLOBALS['HTTP_GET_VARS'];
        $_GET['show'] = $part;
    }
    // setup default settings
    if (!$already and !defined("_PHPWIKI_INSTALL_RUNNING")) {
        define("_PHPWIKI_INSTALL_RUNNING", true);
        include(dirname(__FILE__) . "/../configurator.php");
    }
    $already = 1;
}

init_install();

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
