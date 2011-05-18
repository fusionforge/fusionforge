<?php // -*-php-*-
// $Id: HelloWorld.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * A simple demonstration WikiPlugin.
 *
 * Usage:
 * <<HelloWorld?>
 * <<HelloWorld
 *          salutation="Greetings, "
 *          name=Wikimeister
 * >>
 * <<HelloWorld salutation=Hi >>
 * <<HelloWorld name=WabiSabi >>
 */

// Constants are defined before the class.
if (!defined('THE_END'))
    define('THE_END', "!");

class WikiPlugin_HelloWorld
extends WikiPlugin
{
    // Four required functions in a WikiPlugin.

    function getName () {
        return _("HelloWorld");
    }

    function getDescription () {
        return _("Simple Sample Plugin");

    }

    // Establish default values for each of this plugin's arguments.
    function getDefaultArguments() {
        return array('salutation' => "Hello,",
                     'name'       => "World");
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));

        // Any text that is returned will not be further transformed,
        // so use html where necessary.
        $html = HTML::tt(fmt('%s: %s', $salutation, WikiLink($name, 'auto')),
                         THE_END);
        return $html;
    }
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
