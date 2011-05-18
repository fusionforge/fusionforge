<?php // -*-php-*-
// $Id: JabberPresence.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright (C) 2004 $ThePhpWikiProgrammingTeam
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
 * A simple Jabber presence WikiPlugin.
 * http://wiki.crao.net/index.php/JabberPr%E9sence/Source
 * http://edgar.netflint.net/howto.php
 *
 * Usage:
 *  <<JabberPresence scripturl=http://edgar.netflint.net/status.php
 *                          jid=yourid@jabberserver type=html iconset=phpbb >>
 *
 * @author: Arnaud Fontaine
 */

if (!defined('MY_JABBER_ID'))
    define('MY_JABBER_ID', $GLOBALS['request']->_user->UserName()."@jabber.com"); // or "@netflint.net"

class WikiPlugin_JabberPresence
extends WikiPlugin
{
    function getName () {
        return _("JabberPresence");
    }

    function getDescription () {
        return _("Simple jabber presence plugin");
    }

    // Establish default values for each of this plugin's arguments.
    function getDefaultArguments() {
        return array('scripturl' => "http://edgar.netflint.net/status.php",
                     'jid'       => MY_JABBER_ID,
                     'type'      => 'image',
                     'iconset'   => "gabber");
    }

    function run($dbi, $argstr, $request) {
        extract($this->getArgs($argstr, $request));
        // Any text that is returned will not be further transformed,
        // so use html where necessary.
        if (empty($jid))
            $html = HTML();
        else
          $html = HTML::img(array('src' => urlencode($scripturl).
                                  '&jid='.urlencode($jid).
                                  '&type='.urlencode($type).
                                  '&iconset='.($iconset),
                                  'alt' =>""));
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
