<?php
/**
 * Copyright © 2004 Arnaud Fontaine and Laurent Lunati
 * Copyright © 2007 Reini Urban
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

// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

/*
 * This file defines the Crao theme of PhpWiki.
 */

require_once 'lib/WikiTheme.php';

class WikiTheme_Crao extends WikiTheme
{

    function load()
    {
        // CSS file defines fonts, colors and background images for this
        // style.  The companion '*-heavy.css' file isn't defined, it's just
        // expected to be in the same directory that the base style is in.

        // This should result in phpwiki-printer.css being used when
        // printing or print-previewing with style "PhpWiki" selected.
        $this->setDefaultCSS('Crao', array('' => 'crao.css', 'print' => ''));

        /**
         * The Signature image is shown after saving an edited page. If this
         * is not set, any signature defined in index.php will be used. If it
         * is not defined by index.php or in here then the "Thank you for
         * editing..." screen will be omitted.
         */

        // Comment this next line out to enable signature.
        $this->addImageAlias('signature', false);

        /*
         * Link icons.
         */
        $this->setLinkIcon('http');
        $this->setLinkIcon('https');
        $this->setLinkIcon('ftp');
        $this->setLinkIcon('mailto');
        $this->setLinkIcon('interwiki');
        $this->setLinkIcon('*', 'url');

        $this->setButtonSeparator(HTML::raw("&nbsp;|&nbsp;"));

        /**
         * WikiWords can automatically be split by inserting spaces between
         * the words. The default is to leave WordsSmashedTogetherLikeSo.
         */
        $this->setAutosplitWikiWords(false);

        /*
         * You may adjust the formats used for formatting dates and times
         * below.  (These examples give the default formats.)
         * Formats are given as format strings to PHP strftime() function See
         * https://www.php.net/manual/en/function.strftime.php for details.
         * Do not include the server's zone (%Z), times are converted to the
         * user's time zone.
         */
        //$this->setDateFormat("%d %B %Y");
        //$this->setTimeFormat("%H:%M");

        /*
         * To suppress times in the "Last edited on" messages, give a
         * give a second argument of false:
         */
        //$this->setDateFormat("%d %B %Y", false);
        $this->setDateFormat("%A %e %B %Y"); // must not contain time
        //$this->setDateFormat("%x"); // must not contain time
        $this->setTimeFormat("%H:%M:%S");
        //$this->setTimeFormat("%X");

    }
}

$WikiTheme = new WikiTheme_Crao('Crao');
