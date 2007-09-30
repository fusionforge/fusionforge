<?php
rcs_id('$Id: themeinfo.php,v 1.12 2005/01/20 10:09:27 rurban Exp $');

/*
 * This file defines an appearance ("theme") of PhpWiki similar to the Portland Pattern Repository.
 */

require_once('lib/Theme.php');

class Theme_Portland extends Theme {
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
        if ($request->getArg('frame'))
            $link->setAttr('target', '_top');

        return $link;
    }
    function getRecentChangesFormatter ($format) {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false;       // use default
        return '_Portland_RecentChanges_Formatter';
    }

    function getPageHistoryFormatter ($format) {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false;       // use default
        return '_Portland_PageHistory_Formatter';
    }
}

$WikiTheme = new Theme_Portland('Portland');

// CSS file defines fonts, colors and background images for this
// style.  The companion '*-heavy.css' file isn't defined, it's just
// expected to be in the same directory that the base style is in.

$WikiTheme->setDefaultCSS('Portland', 'portland.css');
$WikiTheme->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
$WikiTheme->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');
$WikiTheme->addAlternateCSS('PhpWiki', 'phpwiki.css');

/**
 * The logo image appears on every page and links to the HomePage.
 */
$WikiTheme->addImageAlias('logo', WIKI_NAME . 'logo.png');

/**
 * The Signature image is shown after saving an edited page. If this
 * is set to false then the "Thank you for editing..." screen will
 * be omitted.
 */
$WikiTheme->addImageAlias('signature', WIKI_NAME . "Signature.png");
// Uncomment this next line to disable the signature.
//$WikiTheme->addImageAlias('signature', false);

/*
 * Link icons.
 */
//$WikiTheme->setLinkIcon('http');
//$WikiTheme->setLinkIcon('https');
//$WikiTheme->setLinkIcon('ftp');
//$WikiTheme->setLinkIcon('mailto');
//$WikiTheme->setLinkIcon('interwiki');
//$WikiTheme->setLinkIcon('*', 'url');

$WikiTheme->setButtonSeparator(' ');

/**
 * WikiWords can automatically be split by inserting spaces between
 * the words. The default is to leave WordsSmashedTogetherLikeSo.
 */
$WikiTheme->setAutosplitWikiWords(false);

/*
 * You may adjust the formats used for formatting dates and times
 * below.  (These examples give the default formats.)
 * Formats are given as format strings to PHP strftime() function See
 * http://www.php.net/manual/en/function.strftime.php for details.
 * Do not include the server's zone (%Z), times are converted to the
 * user's time zone.
 */
$WikiTheme->setDateFormat("%B %d, %Y", false);


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
