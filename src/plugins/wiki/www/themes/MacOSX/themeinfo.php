<?php
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

/**
 * A PhpWiki theme inspired by the Aqua appearance of Mac OS X.
 *
 * The images used with this theme depend on the PNG alpha channel to
 * blend in with whatever background color or texture is on the page.
 * When viewed with an older browser, the images may be incorrectly
 * rendered with a thick solid black border. When viewed with a modern
 * browser, the images will display with nice edges and blended
 * shadows.
 *
 * The defaut link icons I want to move into this theme, and come up
 * with some new linkicons for the default look. (Any ideas,
 * feedback?)
 *
 * Do you like the icons used in the buttons?
 *
 * See buttons/README for more info on the buttons.
 *
 * The background image is a subtle brushed paper texture or stucco
 * effect very close to white. If your monitor isn't calibrated well
 * you may not see it.
 * */

require_once 'lib/WikiTheme.php';

class WikiTheme_MacOSX extends WikiTheme
{
    function getCSS()
    {
        // FIXME: this is a hack which will not be needed once
        //        we have dynamic CSS.
        $css = WikiTheme::getCSS();
        $css->pushcontent(HTML::style(array('type' => 'text/css'),
            new RawXml(sprintf("<!--\nbody {background-image: url(%s);}\n-->\n",
                $this->getImageURL('bgpaper8')))));
        //for non-browse pages, like former editpage, message etc.
        //$this->getImageURL('bggranular')));
        return $css;
    }

    function getRecentChangesFormatter($format)
    {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false; // use default
        return '_MacOSX_RecentChanges_Formatter';
    }

    function getPageHistoryFormatter($format)
    {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false; // use default
        return '_MacOSX_PageHistory_Formatter';
    }

    function linkUnknownWikiWord($wikiword, $linktext = '')
    {
        // Get rid of anchors on unknown wikiwords
        if (isa($wikiword, 'WikiPageName')) {
            $default_text = $wikiword->shortName;
            $wikiword = $wikiword->name;
        } else {
            $default_text = $wikiword;
        }

        $url = WikiURL($wikiword, array('action' => 'create'));
        //$link = HTML::span(HTML::a(array('href' => $url), '?'));
        $button = $this->makeButton('?', $url);
        $button->addTooltip(sprintf(_("Create: %s"), $wikiword));
        $link = HTML::span($button);


        if (!empty($linktext)) {
            $link->unshiftContent(HTML::u($linktext));
            $link->setAttr('class', 'named-wikiunknown');
        } else {
            $link->unshiftContent(HTML::u($this->maybeSplitWikiWord($default_text)));
            $link->setAttr('class', 'wikiunknown');
        }

        return $link;
    }

    function load()
    {
        // CSS file defines fonts, colors and background images for this
        // style.  The companion '*-heavy.css' file isn't defined, it's just
        // expected to be in the same directory that the base style is in.

        // This should result in phpwiki-printer.css being used when
        // printing or print-previewing with style "PhpWiki" or "MacOSX" selected.
        $this->setDefaultCSS('MacOSX', array('' => 'MacOSX.css', 'print' => 'phpwiki-printer.css'));

        // This allows one to manually select "Printer" style (when browsing page)
        // to see what the printer style looks like.
        $this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
        $this->addAlternateCSS(_("Top & bottom toolbars"), 'MacOSX-topbottombars.css');

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
        //$this->addImageAlias('signature', false);

        /*
         * Link icons.
         */
        $this->setLinkIcon('http');
        $this->setLinkIcon('https');
        $this->setLinkIcon('ftp');
        $this->setLinkIcon('mailto');
        $this->setLinkIcon('interwiki');
        $this->setLinkIcon('wikiuser');
        $this->setLinkIcon('*', 'url');

        $this->setButtonSeparator(""); //use no separator instead of default

        $this->addButtonAlias('?', 'uww');
        $this->addButtonAlias(_("Lock Page"), "Lock Page");
        $this->addButtonAlias(_("Unlock Page"), "Unlock Page");
        $this->addButtonAlias(_("Page Locked"), "Page Locked");
        $this->addButtonAlias("...", "alltime");

        /**
         * WikiWords can automatically be split by inserting spaces between
         * the words. The default is to leave WordsSmashedTogetherLikeSo.
         */
        //$this->setAutosplitWikiWords(false);

        /*
         * You may adjust the formats used for formatting dates and times
         * below.  (These examples give the default formats.)
         * Formats are given as format strings to PHP strftime() function See
         * http://www.php.net/manual/en/function.strftime.php for details.
         * Do not include the server's zone (%Z), times are converted to the
         * user's time zone.
         */
        $this->setDateFormat("%A, %B %d, %Y"); // must not contain time
        $this->setTimeFormat("%I:%M:%S %p");
    }
}

$WikiTheme = new WikiTheme_MacOSX('MacOSX');

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
