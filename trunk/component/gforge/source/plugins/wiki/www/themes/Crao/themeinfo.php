<?php
rcs_id('$Id: themeinfo.php,v 1.3 2005/09/18 13:04:37 rurban Exp $');

/*
 * This file defines the default appearance ("theme") of PhpWiki.
 */

require_once('lib/Theme.php');

$WikiTheme = new WikiTheme('Crao');

// CSS file defines fonts, colors and background images for this
// style.  The companion '*-heavy.css' file isn't defined, it's just
// expected to be in the same directory that the base style is in.

// This should result in phpwiki-printer.css being used when
// printing or print-previewing with style "PhpWiki" selected.
$WikiTheme->setDefaultCSS('Crao',
                      array(''		=> 'crao.css',
                            'print'	=> ''));

// This allows one to manually select "Printer" style (when browsing page)
// to see what the printer style looks like.
//$WikiTheme->addAlternateCSS(_("Printer"), 'phpwiki-printer.css');
//$WikiTheme->addAlternateCSS(_("Top & bottom toolbars"), 'phpwiki-topbottombars.css');
//$WikiTheme->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');


/**
 * The logo image appears on every page and links to the HomePage.
 */
//$WikiTheme->addImageAlias('logo', 'logo.png');

/**
 * The Signature image is shown after saving an edited page. If this
 * is not set, any signature defined in index.php will be used. If it
 * is not defined by index.php or in here then the "Thank you for
 * editing..." screen will be omitted.
 */

// Comment this next line out to enable signature.
$WikiTheme->addImageAlias('signature', false);

/*
 * Link icons.
 */
$WikiTheme->setLinkIcon('http');
$WikiTheme->setLinkIcon('https');
$WikiTheme->setLinkIcon('ftp');
$WikiTheme->setLinkIcon('mailto');
$WikiTheme->setLinkIcon('interwiki');
$WikiTheme->setLinkIcon('*', 'url');

$WikiTheme->setButtonSeparator(HTML::raw("&nbsp;|&nbsp;"));

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
//$WikiTheme->setDateFormat("%B %d, %Y");
//$WikiTheme->setTimeFormat("%I:%M %p");

/*
 * To suppress times in the "Last edited on" messages, give a
 * give a second argument of false:
 */
//$WikiTheme->setDateFormat("%B %d, %Y", false); 
$WikiTheme->setDateFormat("%A %e %B %Y"); // must not contain time
//$WikiTheme->setDateFormat("%x"); // must not contain time
$WikiTheme->setTimeFormat("%H:%M:%S");
//$WikiTheme->setTimeFormat("%X");



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
