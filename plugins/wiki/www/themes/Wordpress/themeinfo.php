<?php
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

// $Id: themeinfo.php 7968 2011-03-07 13:39:47Z vargenau $

/*
 * This file defines an appearance ("theme") of PhpWiki similar to the
 * Wordpress Pattern Repository.
 * @author: Mike Pickering
 */

require_once('lib/WikiTheme.php');

class WikiTheme_Wordpress extends WikiTheme {

    function linkUnknownWikiWord($wikiword, $linktext = '') {
        global $request;
        if (isa($wikiword, 'WikiPageName')) {
            $default_text = $wikiword->shortName;
            $wikiword = $wikiword->name;
        }
        else {
            $default_text = $wikiword;
        }

        $url = WikiURL($wikiword, array('action' => 'create'));
        $link = HTML::span(HTML::a(array('href' => $url, 'rel' => 'nofollow'), '?'));

        if (!empty($linktext)) {
            $link->unshiftContent(HTML::u($linktext));
            $link->setAttr('class', 'named-wikiunknown');
        }
        else {
            $link->unshiftContent(HTML::u($this->maybeSplitWikiWord($default_text)));
            $link->setAttr('class', 'wikiunknown');
        }

        return $link;
    }
    function getRecentChangesFormatter ($format) {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false;       // use default
        return '_Wordpress_RecentChanges_Formatter';
    }

    function getPageHistoryFormatter ($format) {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false;       // use default
        return '_Wordpress_PageHistory_Formatter';
    }

    function load() {
    // CSS file defines fonts, colors and background images for this
    // style.  The companion '*-heavy.css' file isn't defined, it's just
    // expected to be in the same directory that the base style is in.

    $this->setDefaultCSS('Wordpress', 'Wordpress.css');
    $this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
    $this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');
    $this->addAlternateCSS('PhpWiki', 'phpwiki.css');

    /**
     * The logo image appears on every page and links to the HomePage.
     */
    //$this->addImageAlias('logo', 'logo.png');

    /**
     * The Signature image is shown after saving an edited page. If this
     * is not set, any signature defined in index.php will be used. If it
     * is not defined by index.php or in here then the "Thank you for
     * editing..." screen will be omitted.
     */
    $this->addImageAlias('signature', 'signature.png');

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
     * http://www.php.net/manual/en/function.strftime.php for details.
     * Do not include the server's zone (%Z), times are converted to the
     * user's time zone.
     */
    $this->setDateFormat("%B %d, %Y", false);
    }
}

$WikiTheme = new WikiTheme_Wordpress('Wordpress');

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
