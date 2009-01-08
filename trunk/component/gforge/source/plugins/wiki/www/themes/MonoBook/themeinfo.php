<?php
rcs_id('$Id: themeinfo.php,v 1.5 2005/09/15 05:59:45 rurban Exp $');
/**
 * The new mediawiki (Wikipedia.org) default style.
 * Mediawiki 'monobook' style sheet for CSS2-capable browsers.
 * Copyright Gabriel Wicke - http://www.aulinx.de/
 * See main.css for more.
 *
 * Problems: IE: buttons, comment textarea, signin is at the left.
 *
 * We don't (yet) support all mediawiki UI options, but we try to.
 * Besides that, maybe the mediawiki folks will see how much better phpwiki 
 * will scale, esp. with a true database, not just mysql.
 * Technically phpwiki has about 2-3 years advantage.
 * Anyway, the WikiParser perl module (and our php version) will be able to import
 * and convert back and forth.
 */
require_once('lib/Theme.php');
if (!defined("ENABLE_MARKUP_TEMPLATE"))
    define("ENABLE_MARKUP_TEMPLATE", true);

class Theme_MonoBook extends WikiTheme {

    function makeActionButton ($action, $label = false, $page_or_rev = false) {
        extract($this->_get_name_and_rev($page_or_rev));

        if (is_array($action)) {
            $attr = $action;
            $action = isset($attr['action']) ? $attr['action'] : 'browse';
        }
        else
            $attr['action'] = $action;

        $class = is_safe_action($action) ? 'named-wiki' : 'wikiadmin';
        if (!$label)
            $label = $this->_labelForAction($action);

        if ($version)
            $attr['version'] = $version;

        if ($action == 'browse')
            unset($attr['action']);

        return $this->makeButton($label, WikiURL($pagename, $attr), $class);
    }
}

$WikiTheme = new Theme_MonoBook('MonoBook');
$WikiTheme->addMoreHeaders(JavaScript('',array('src' => $WikiTheme->_findData("wikibits.js"))));
if (isBrowserIE()) {
    $WikiTheme->addMoreHeaders($WikiTheme->_CSSlink(0,
        $WikiTheme->_findFile('IEFixes.css'),'all'));
    $WikiTheme->addMoreHeaders("\n");
    $WikiTheme->addMoreHeaders(JavaScript('',array('src' => $WikiTheme->_findData("IEFixes.js"))));
    $WikiTheme->addMoreHeaders("\n");
    $WikiTheme->addMoreHeaders(HTML::Raw('<meta http-equiv="imagetoolbar" content="no" />'));
}
$WikiTheme->addMoreAttr('body', "class-ns-0", HTML::Raw('class="ns-0"'));

// CSS file defines fonts, colors and background images for this
// style.  The companion '*-heavy.css' file isn't defined, it's just
// expected to be in the same directory that the base style is in.

// This should result in phpwiki-printer.css being used when
// printing or print-previewing with style "PhpWiki" or "MacOSX" selected.
$WikiTheme->setDefaultCSS('PhpWiki',
                       array(''      => 'monobook.css',
                             'print' => 'commonPrint.css'));

// This allows one to manually select "Printer" style (when browsing page)
// to see what the printer style looks like.
$WikiTheme->addAlternateCSS(_("Printer"), 'commonPrint.css', 'print, screen');
$WikiTheme->addAlternateCSS(_("Top & bottom toolbars"), 'phpwiki-topbottombars.css');
$WikiTheme->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');

/**
 * The logo image appears on every page and links to the HomePage.
 */
$WikiTheme->addImageAlias('logo', 'MonoBook-Logo.png');
//$WikiTheme->addImageAlias('logo', WIKI_NAME . 'Logo.png');

/**
 * The Signature image is shown after saving an edited page. If this
 * is set to false then the "Thank you for editing..." screen will
 * be omitted.
 */

$WikiTheme->addImageAlias('signature', "Signature.png");
// Uncomment this next line to disable the signature.
$WikiTheme->addImageAlias('signature', false);

/*
 * Link icons.
 */
/*
$WikiTheme->setLinkIcon('http');
$WikiTheme->setLinkIcon('https');
$WikiTheme->setLinkIcon('ftp');
$WikiTheme->setLinkIcon('mailto');
//$WikiTheme->setLinkIcon('interwiki');
*/
$WikiTheme->setLinkIcon('wikiuser');
//$WikiTheme->setLinkIcon('*', 'url');
// front or after
//$WikiTheme->setLinkIconAttr('after');

//$WikiTheme->setButtonSeparator("\n | ");

/**
 * WikiWords can automatically be split by inserting spaces between
 * the words. The default is to leave WordsSmashedTogetherLikeSo.
 */
//$WikiTheme->setAutosplitWikiWords(false);

/**
 * Layout improvement with dangling links for mostly closed wiki's:
 * If false, only users with edit permissions will be presented the 
 * special wikiunknown class with "?" and Tooltip.
 * If true (default), any user will see the ?, but will be presented 
 * the PrintLoginForm on a click.
 */
$WikiTheme->setAnonEditUnknownLinks(false);

/*
 * You may adjust the formats used for formatting dates and times
 * below.  (These examples give the default formats.)
 * Formats are given as format strings to PHP strftime() function See
 * http://www.php.net/manual/en/function.strftime.php for details.
 * Do not include the server's zone (%Z), times are converted to the
 * user's time zone.
 */
$WikiTheme->setDateFormat("%B %d, %Y");
$WikiTheme->setTimeFormat("%H:%M");

/*
 * To suppress times in the "Last edited on" messages, give a
 * give a second argument of false:
 */
//$WikiTheme->setDateFormat("%B %d, %Y", false); 

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
