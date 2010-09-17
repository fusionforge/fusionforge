<?php
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

// rcs_id('$Id: themeinfo.php 7690 2010-09-17 08:46:20Z vargenau $');

/*
 * This file defines the Crao theme of PhpWiki.
 */

require_once('lib/WikiTheme.php');

class WikiTheme_Crao extends WikiTheme {

    function load() {
	// CSS file defines fonts, colors and background images for this
	// style.  The companion '*-heavy.css' file isn't defined, it's just
	// expected to be in the same directory that the base style is in.

	// This should result in phpwiki-printer.css being used when
	// printing or print-previewing with style "PhpWiki" selected.
	$this->setDefaultCSS('Crao',
				  array(''      => 'crao.css',
					'print'	=> ''));

	// This allows one to manually select "Printer" style (when browsing page)
	// to see what the printer style looks like.
	//$this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css');
	//$this->addAlternateCSS(_("Top & bottom toolbars"), 'phpwiki-topbottombars.css');
	//$this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');


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

	// Comment this next line out to enable signature.
	$this->addImageAlias('signature', false);

	/*
	 * Link icons.
	 */
	$this->setLinkIcon('http');
	$this->setLinkIcon('https');
	$this->setLinkIcon('ftp');
	$this->setLinkIcon('mailto');
	$this->setLinkIcon('interwiki');
	$this->setLinkIcon('*', 'url');

	$this->setButtonSeparator(HTML::raw("&nbsp;|&nbsp;"));

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
	//$this->setDateFormat("%B %d, %Y");
	//$this->setTimeFormat("%I:%M %p");

	/*
	 * To suppress times in the "Last edited on" messages, give a
	 * give a second argument of false:
	 */
	//$this->setDateFormat("%B %d, %Y", false);
	$this->setDateFormat("%A %e %B %Y"); // must not contain time
	//$this->setDateFormat("%x"); // must not contain time
	$this->setTimeFormat("%H:%M:%S");
	//$this->setTimeFormat("%X");

    }
}

$WikiTheme = new WikiTheme_Crao('Crao');

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End: 
?>
