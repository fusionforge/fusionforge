<?php
rcs_id('$Id: themeinfo.php,v 1.5 2006/02/22 06:51:37 rurban Exp $');
/**
 * tiny actionbar, only Edit (if signed in) and Info => PageInfo,
 *   all other Actionbars buttons in info.tmpl
 * old-style Sign In button
 * navbar also shorter labels and without buttons, just links
 */

require_once('lib/Theme.php');

class Theme_gforge extends WikiTheme {

	function header() {
		global $HTML, $TITLE, $group_id;
		
		$HTML->header(array('title'=> WIKI_NAME . ' - '.  AsString($TITLE) ,
			'pagename'=> $TITLE, 'group' => $group_id, 'toptab' => 'wiki',
			'css' => '/themes/alcatel/phpwiki.css" />'."\n".'	<base href="'.PHPWIKI_BASE_URL));
	}
	
	function footer() {
		global $HTML;
		
		$HTML->footer();
		
	}
}

$WikiTheme = new Theme_gforge('gforge');
// CSS file defines fonts, colors and background images for this
// style.  The companion '*-heavy.css' file isn't defined, it's just
// expected to be in the same directory that the base style is in.

// This should result in phpwiki-printer.css being used when
// printing or print-previewing with style "PhpWiki" or "MacOSX" selected.
$WikiTheme->setDefaultCSS('PhpWiki',
                       array(''      => 'phpwiki.css',
                             'print' => 'phpwiki-printer.css'));

// This allows one to manually select "Printer" style (when browsing page)
// to see what the printer style looks like.
//$WikiTheme->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
//$WikiTheme->addAlternateCSS(_("Top & bottom toolbars"), 'phpwiki-topbottombars.css');
//$WikiTheme->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');

//if (isBrowserIE()) {
//    $WikiTheme->addMoreHeaders($WikiTheme->_CSSlink(0,
//        $WikiTheme->_findFile('IEFixes.css'),'all'));
//    $WikiTheme->addMoreHeaders("\n");
//}

/**
 * The logo image appears on every page and links to the HomePage.
 */
$WikiTheme->addImageAlias('logo', WIKI_NAME . 'Logo.png');

/**
 * The Signature image is shown after saving an edited page. If this
 * is set to false then the "Thank you for editing..." screen will
 * be omitted.
 */

$WikiTheme->addImageAlias('signature', WIKI_NAME . "Signature.png");
// Uncomment this next line to disable the signature.
$WikiTheme->addImageAlias('signature', false);

/*
 * Link icons.
 */
//$WikiTheme->setLinkIcon('http');
$WikiTheme->setLinkIcon('https');
$WikiTheme->setLinkIcon('ftp');
$WikiTheme->setLinkIcon('mailto');
//$WikiTheme->setLinkIcon('interwiki');
//$WikiTheme->setLinkIcon('wikiuser');
//$WikiTheme->setLinkIcon('*', 'url');

$WikiTheme->setButtonSeparator(HTML::Raw("<li>"));

/**
 * WikiWords can automatically be split by inserting spaces between
 * the words. The default is to leave WordsSmashedTogetherLikeSo.
 */
$WikiTheme->setAutosplitWikiWords(false);

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
