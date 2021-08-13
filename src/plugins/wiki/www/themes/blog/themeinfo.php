<?php
/**
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

/**
 * This file defines a blog theme for PhpWiki,
 * based on Rui Carmo's excellent http://the.taoofmac.com/space/
 * which is based on the Kubrick theme: http://binarybonsai.com/kubrick/
 * The layout was designed and built by Michael Heilemann,
 * whose blog you will find at http://binarybonsai.com/
 *
 * [Stanley Kubrick]"Good afternoon, gentlemen. I am a HAL 9000
 * computer. I became operational at the H.A.L. plant in Urbana,
 * Illinois on the 12th of January 1992. My instructor was
 * Mr. Langley, and he taught me to sing a song. If you'd like to hear
 * it I can sing it for you."
 *
 * The CSS, XHTML and design is released under GPL:
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * Default is a one-person (ADMIN_USER) blog (at the BlogHomePage), but
 * other blogs are also enabled for every authenticated user.
 *
 * Actionbar: Edit, Home, About, Archives, News, ..., Info  [ Search ]
 * PageTrail: > .. > ..
 * Right sidebar boxes: Archives, Syndication, Links, GoogleAds
 *
 * Happy blogging.
 */

require_once 'lib/WikiTheme.php';

class WikiTheme_blog extends WikiTheme
{
    function __construct($theme_name = 'blog')
    {
        parent::__construct($theme_name);
        $this->calendarInit();
    }

    /* Display up/down button with persistent state */
    /* persistent state per block in cookie for 30 days */
    function folderArrow($id, $init = 'Open')
    {
        global $request;
        if ($cookie = $request->cookies->get("folder_" . $id)) {
            $init = $cookie;
        }
        if ($init == 'Open' or $init == 'Closed')
            $png = $this->_findData('images/folderArrow' . $init . '.png');
        else
            $png = $this->_findData('images/folderArrowOpen.png');
        return HTML::img(array('id' => $id . '-img',
            'src' => $png,
            //'align' => 'right',
            'onclick' => "showHideFolder('$id')",
            'alt' => _("Click to hide/show"),
            'title' => _("Click to hide/show")));
    }

    protected function _labelForAction($action)
    {
        switch ($action) {
            case 'edit':
                return _("Edit");
            case 'diff':
                return _("Diff");
            case 'logout':
                return _("Sign Out");
            case 'login':
                return _("Sign In");
            case 'lock':
                return _("Lock");
            case 'unlock':
                return _("Unlock");
            case 'remove':
                return _("Remove");
            default:
                return gettext(ucfirst($action));
        }
    }

    function getRecentChangesFormatter($format)
    {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false; // use default
        if ($format == 'box')
            return '_blog_RecentChanges_BoxFormatter';
        return '_blog_RecentChanges_Formatter';
    }

    /* TODO: use the blog summary as label instead of the pagename */
    function linkExistingWikiWord($wikiword, $linktext = '', $version = false)
    {
        if ($version !== false and !$this->HTML_DUMP_SUFFIX)
            $url = WikiURL($wikiword, array('version' => $version));
        else
            $url = WikiURL($wikiword);

        // Extra steps for dumping page to an html file.
        if ($this->HTML_DUMP_SUFFIX) {
            $url = preg_replace('/^\./', '%2e', $url); // dot pages
        }

        $link = HTML::a(array('href' => $url));

        if (is_a($wikiword, 'WikiPageName'))
            $default_text = $wikiword->shortName;
        else
            $default_text = $wikiword;

        if (!empty($linktext)) {
            $link->pushContent($linktext);
            $link->setAttr('class', 'named-wiki');
            $link->setAttr('title', $this->maybeSplitWikiWord($default_text));
        } else {
            //TODO: check if wikiblog
            $link->pushContent($this->maybeSplitWikiWord($default_text));
            $link->setAttr('class', 'wiki');
        }
        return $link;
    }

    function load()
    {
        // CSS file defines fonts, colors and background images for this
        // style.

        // override sidebar definitions:
        $this->setDefaultCSS(_("blog"), 'Kubrick.css');
        $this->addButtonAlias(_("(diff)"), "[diff]");
        $this->addButtonAlias("...", "alltime");

        $this->setButtonSeparator("");

        /**
         * WikiWords can automatically be split by inserting spaces between
         * the words. The default is to leave WordsSmashedTogetherLikeSo.
         */
        $this->setAutosplitWikiWords(false);

        /**
         * If true (default) show create '?' buttons on not existing pages, even if the
         * user is not signed in.
         * If false, anon users get no links and it looks cleaner, but then they
         * cannot easily fix missing pages.
         */
        $this->setAnonEditUnknownLinks(false);

        /*
         * You may adjust the formats used for formatting dates and times
         * below.  (These examples give the default formats.)
         * Formats are given as format strings to PHP strftime() function See
         * https://www.php.net/manual/en/function.strftime.php for details.
         * Do not include the server's zone (%Z), times are converted to the
         * user's time zone.
         */
        //$this->setDateFormat("%d %B %Y");
        $this->setDateFormat("%A, %e %B %Y"); // must not contain time
        $this->setTimeFormat("%H:%M:%S");
    }
}

$WikiTheme = new WikiTheme_blog('blog');
define("PAGETRAIL_ARROW", " » ");
