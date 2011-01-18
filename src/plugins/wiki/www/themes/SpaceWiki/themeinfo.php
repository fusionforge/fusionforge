<?php // -*-php-*-
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

// rcs_id('$Id: themeinfo.php 7832 2011-01-13 14:57:09Z vargenau $');

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

require_once('lib/WikiTheme.php');

class WikiTheme_SpaceWiki extends WikiTheme {

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
  
    function load() {
	// CSS file defines fonts, colors and background images for this
	// style.  The companion '*-heavy.css' file isn't defined, it's just
	// expected to be in the same directory that the base style is in.

	$this->setDefaultCSS('SpaceWiki', 'SpaceWiki.css');
	$this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
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

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End: 
?>
