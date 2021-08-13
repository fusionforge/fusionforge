<?php
/**
 * Copyright © 2002-2003 Carsten Klapp
 * Copyright © 2002-2003 Jeff Dairiki
 * Copyright © 2004-2005,2007 Reini Urban
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
 * This file defines an appearance ("theme") of PhpWiki similar to the Portland Pattern Repository.
 */

require_once 'lib/WikiTheme.php';

class WikiTheme_Portland extends WikiTheme
{

    function linkUnknownWikiWord($wikiword, $linktext = '')
    {
        if (is_a($wikiword, 'WikiPageName')) {
            $default_text = $wikiword->shortName;
            $wikiword = $wikiword->name;
        } else {
            $default_text = $wikiword;
        }

        $url = WikiURL($wikiword, array('action' => 'create'));
        $link = HTML::span(HTML::a(array('href' => $url, 'rel' => 'nofollow'), '?'));

        if (!empty($linktext)) {
            $link->unshiftContent(HTML::raw($linktext));
            $link->setAttr('class', 'named-wikiunknown');
        } else {
            $link->unshiftContent(HTML::raw($this->maybeSplitWikiWord($default_text)));
            $link->setAttr('class', 'wikiunknown');
        }

        return $link;
    }

    function getRecentChangesFormatter($format)
    {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false; // use default
        return '_Portland_RecentChanges_Formatter';
    }

    function getPageHistoryFormatter($format)
    {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false; // use default
        return '_Portland_PageHistory_Formatter';
    }

    function load()
    {
        // CSS file defines fonts, colors and background images for this
        // style.  The companion '*-heavy.css' file isn't defined, it's just
        // expected to be in the same directory that the base style is in.

        $this->setDefaultCSS('Portland', 'portland.css');
        $this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css');
        $this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');
        $this->addAlternateCSS('PhpWiki', 'phpwiki.css');

        /**
         * The logo image appears on every page and links to the HomePage.
         */
        $this->addImageAlias('logo', WIKI_NAME . 'logo.png');

        /**
         * The Signature image is shown after saving an edited page. If this
         * is set to false then the "Thank you for editing..." screen will
         * be omitted.
         */
        $this->addImageAlias('signature', WIKI_NAME . "Signature.png");
        // Uncomment this next line to disable the signature.
        //$this->addImageAlias('signature', false);

        /*
         * Link icons.
         */

        $this->setButtonSeparator(' ');

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
        $this->setDateFormat("%d %B %Y", false);

    }
}

$WikiTheme = new WikiTheme_Portland('Portland');
