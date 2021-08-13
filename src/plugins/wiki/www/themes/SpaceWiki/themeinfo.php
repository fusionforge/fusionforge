<?php
/**
 * Copyright © 2002-2003 Carsten Klapp
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

/**
 * This theme is by design completely css-based so unfortunately it
 * doesn't render properly or even the same across different browsers.
 * A preview screen snapshot is included for comparison testing.
 *
 * The reverse coloring of this theme was chosen to provide an extreme
 * example of a heavily customized PhpWiki, through which any
 * potential visual problems can be identified and to eliminate any
 * remaining non-structural html elements from the html templates.
 */

require_once 'lib/WikiTheme.php';

class WikiTheme_SpaceWiki extends WikiTheme
{

    function getRecentChangesFormatter($format)
    {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false; // use default
        return '_SpaceWiki_RecentChanges_Formatter';
    }

    function getPageHistoryFormatter($format)
    {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false; // use default
        return '_SpaceWiki_PageHistory_Formatter';
    }

    function load()
    {
        // CSS file defines fonts, colors and background images for this
        // style.  The companion '*-heavy.css' file isn't defined, it's just
        // expected to be in the same directory that the base style is in.

        $this->setDefaultCSS('SpaceWiki', 'SpaceWiki.css');
        $this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css');
        $this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');
        $this->addAlternateCSS('PhpWiki', 'phpwiki.css');

        /**
         * The logo image appears on every page and links to the HomePage.
         */
        //$this->addImageAlias('logo', 'logo.png');
        $this->addImageAlias('logo', 'Ufp-logo.jpg');
        $this->addImageAlias('logo', WIKI_NAME . 'Logo.png');

        /**
         * The Signature image is shown after saving an edited page. If this
         * is set to false then the "Thank you for editing..." screen will
         * be omitted.
         */
        $this->addImageAlias('signature', 'lights.png');
        $this->addImageAlias('signature', WIKI_NAME . "Signature.png");
        // Uncomment this next line to disable the signature.
        //$this->addImageAlias('signature', false);

        $this->addImageAlias('hr', 'hr.png');

        $this->setButtonSeparator(" ");

        /**
         * WikiWords can automatically be split by inserting spaces between
         * the words. The default is to leave WordsSmashedTogetherLikeSo.
         */
        //$this->setAutosplitWikiWords(false);

        /**
         * The "stardate" format here is really just metricdate.24hourtime. A
         * "real" date2startdate conversion function might be fun but not very
         * useful on a wiki.
         */
        $this->setTimeFormat("%H%M%S");
        $this->setDateFormat("%Y%m%d"); // must not contain time

    }
}

$WikiTheme = new WikiTheme_SpaceWiki('SpaceWiki');
