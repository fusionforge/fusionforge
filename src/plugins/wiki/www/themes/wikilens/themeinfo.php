<?php
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

/**
 * The wikilens theme is just a normal WikiTheme (can be based on any, here based on default),
 * which additionally loads some wikilens libraries.
 * And of course it has it's own set of icons for the ratingwidget.
 * http://www.wikilens.org/wiki/
 */
require_once 'lib/WikiTheme.php';

class WikiTheme_Wikilens extends WikiTheme
{

    function load()
    {
        // CSS file defines fonts, colors and background images for this
        // style.  The companion '*-heavy.css' file isn't defined, it's just
        // expected to be in the same directory that the base style is in.

        // This should result in phpwiki-printer.css being used when
        // printing or print-previewing with style "PhpWiki" or "MacOSX" selected.
        $this->setDefaultCSS('PhpWiki', array('' => 'wikilens.css', 'print' => 'phpwiki-printer.css'));

        // This allows one to manually select "Printer" style (when browsing page)
        // to see what the printer style looks like.
        $this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
        $this->addAlternateCSS(_("Top & bottom toolbars"), 'phpwiki-topbottombars.css');
        $this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');

        /**
         * The logo image appears on every page and links to the HomePage.
         */
        $this->addImageAlias('logo', WIKI_NAME . 'Logo.png');

        /**
         * The Signature image is shown after saving an edited page. If this
         * is set to false then the "Thank you for editing..." screen will
         * be omitted.
         */

        $this->addImageAlias('signature', WIKI_NAME . "Signature.png");
        // Uncomment this next line to disable the signature.
        $this->addImageAlias('signature', false);

        /*
         * Link icons.
         */
        //$this->setLinkIcon('http');
        $this->setLinkIcon('https');
        $this->setLinkIcon('ftp');
        $this->setLinkIcon('mailto');
        //$this->setLinkIcon('interwiki');
        $this->setLinkIcon('wikiuser');
        //$this->setLinkIcon('*', 'url');

        /**
         * WikiWords can automatically be split by inserting spaces between
         * the words. The default is to leave WordsSmashedTogetherLikeSo.
         */
        //$this->setAutosplitWikiWords(false);

        /**
         * Layout improvement with dangling links for mostly closed wiki's:
         * If false, only users with edit permissions will be presented the
         * special wikiunknown class with "?" and Tooltip.
         * If true (default), any user will see the ?, but will be presented
         * the PrintLoginForm on a click.
         */
        $this->setAnonEditUnknownLinks(false);

        /*
         * You may adjust the formats used for formatting dates and times
         * below.  (These examples give the default formats.)
         * Formats are given as format strings to PHP strftime() function See
         * http://www.php.net/manual/en/function.strftime.php for details.
         * Do not include the server's zone (%Z), times are converted to the
         * user's time zone.
         */
        $this->setDateFormat("%B %d, %Y");
        $this->setTimeFormat("%H:%M");

        /*
         * To suppress times in the "Last edited on" messages, give a
         * give a second argument of false:
         */
        //$this->setDateFormat("%B %d, %Y", false);

    }
}

$WikiTheme = new WikiTheme_Wikilens('wikilens');
require_once 'lib/wikilens/CustomPrefs.php';
require_once 'lib/wikilens/PageListColumns.php';

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
