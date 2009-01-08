<?php // -*-php-*-

rcs_id('$Id: themeinfo.php,v 1.5 2005/01/26 21:11:54 uckelman Exp $');

/**
 * This theme is by design completely css-based so unfortunately it
 * doesn't render properly or even the same across different browsers.
 * Mozilla 0.98 was used for testing, it is the only Mac browser so
 * far which correctly renders most of the css used here.
 * A preview screen snapshot is included for comparison testing.
 *
 * The reverse coloring of this theme was chosen to provide an extreme
 * example of a heavily customized PhpWiki, through which any
 * potential visual problems can be identified and to eliminate any
 * remaining non-structural html elements from the html templates.
 */

require_once('lib/Theme.php');

class Theme_SpaceWiki extends Theme {
    function getRecentChangesFormatter ($format) {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false;       // use default
        return '_SpaceWiki_RecentChanges_Formatter';
    }

    function getPageHistoryFormatter ($format) {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false;       // use default
        return '_SpaceWiki_PageHistory_Formatter';
    }
}

$WikiTheme = new Theme_SpaceWiki('SpaceWiki');

// CSS file defines fonts, colors and background images for this
// style.  The companion '*-heavy.css' file isn't defined, it's just
// expected to be in the same directory that the base style is in.

$WikiTheme->setDefaultCSS('SpaceWiki', 'SpaceWiki.css');
$WikiTheme->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
$WikiTheme->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');
$WikiTheme->addAlternateCSS('PhpWiki', 'phpwiki.css');

/**
 * The logo image appears on every page and links to the HomePage.
 */
//$WikiTheme->addImageAlias('logo', 'logo.png');
$WikiTheme->addImageAlias('logo', 'Ufp-logo.jpg');
$WikiTheme->addImageAlias('logo', WIKI_NAME . 'Logo.png');

/**
 * The Signature image is shown after saving an edited page. If this
 * is set to false then the "Thank you for editing..." screen will
 * be omitted.
 */
$WikiTheme->addImageAlias('signature', 'lights.png');
$WikiTheme->addImageAlias('signature', WIKI_NAME . "Signature.png");
// Uncomment this next line to disable the signature.
//$WikiTheme->addImageAlias('signature', false);

$WikiTheme->addImageAlias('hr', 'hr.png');

$WikiTheme->setButtonSeparator(" ");

/**
 * WikiWords can automatically be split by inserting spaces between
 * the words. The default is to leave WordsSmashedTogetherLikeSo.
 */
//$WikiTheme->setAutosplitWikiWords(false);

/**
 * The "stardate" format here is really just metricdate.24hourtime. A
 * "real" date2startdate conversion function might be fun but not very
 * useful on a wiki.
 */
$WikiTheme->setTimeFormat("%H%M%S");
$WikiTheme->setDateFormat("%Y%m%d"); // must not contain time


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// (c-file-style: "gnu")
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:   
?>
