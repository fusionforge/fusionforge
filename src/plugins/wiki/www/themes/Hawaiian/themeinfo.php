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
 * WikiWiki Hawaiian theme for PhpWiki.
 */

require_once 'lib/WikiTheme.php';

class WikiTheme_Hawaiian extends WikiTheme
{
    function getCSS()
    {
        // FIXME: this is a hack which will not be needed once
        //        we have dynamic CSS.
        $css = WikiTheme::getCSS();
        $css->pushContent(HTML::style(array('type' => 'text/css'),
            new RawXml(sprintf("<!--\nbody {background-image: url(%s);}\n-->",
                $this->getImageURL('uhhbackground.jpg')))));
        return $css;
    }

    function load()
    {
        // CSS file defines fonts, colors and background images for this
        // style.  The companion '*-heavy.css' file isn't defined, it's just
        // expected to be in the same directory that the base style is in.

        $this->setDefaultCSS('Hawaiian', 'Hawaiian.css');
        $this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css');
        $this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');
        $this->addAlternateCSS('PhpWiki', 'phpwiki.css');

        /**
         * The logo image appears on every page and links to the HomePage.
         */
        $this->addImageAlias('logo', 'PalmBeach.jpg');
        $this->addImageAlias('logo', WIKI_NAME . 'Logo.png');

        /**
         * The Signature image is shown after saving an edited page. If this
         * is set to false then the "Thank you for editing..." screen will
         * be omitted.
         */
        //$this->addImageAlias('signature', 'SubmersiblePiscesV.jpg');
        $this->addImageAlias('signature', 'WaterFall.jpg');
        $this->addImageAlias('signature', WIKI_NAME . "Signature.png");
        // Uncomment this next line to disable the signature.
        //$this->addImageAlias('signature', false);

        // If you want to see more than just the waterfall let a random
        // picture be chosen for the signature image:
        //include_once($this->file('lib/random.php'));
        include_once("themes/$this->_name/lib/random.php");
        $imgSet = new randomImage($this->file("images/pictures"));
        $imgFile = "pictures/" . $imgSet->filename;
        $this->addImageAlias('signature', $imgFile);

        //To test out the randomization just use logo instead of signature
        //$this->addImageAlias('logo', $imgFile);

        /*
         * Link Icons
         */
        $this->setLinkIcon('interwiki');
        $this->setLinkIcon('*', 'flower.png');

        $this->setButtonSeparator(' ');

        /**
         * WikiWords can automatically be split by inserting spaces between
         * the words. The default is to leave WordsSmashedTogetherLikeSo.
         */
        $this->setAutosplitWikiWords(true);

        /*
         * You may adjust the formats used for formatting dates and times
         * below.  (These examples give the default formats.)
         * Formats are given as format strings to PHP strftime() function See
         * https://www.php.net/manual/en/function.strftime.php for details.
         * Do not include the server's zone (%Z), times are converted to the
         * user's time zone.
         */
        //$this->setDateFormat("%d %B %Y");       // must not contain time

    }
}

$WikiTheme = new WikiTheme_Hawaiian('Hawaiian');
