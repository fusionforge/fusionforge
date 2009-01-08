<?php

rcs_id('$Id: themeinfo.php,v 1.26 2004/06/14 11:26:48 rurban Exp $');

/**
 * WikiWiki Hawaiian theme for PhpWiki.
 */

require_once('lib/Theme.php');

class Theme_Hawaiian extends Theme {
    function getCSS() {
        // FIXME: this is a hack which will not be needed once
        //        we have dynamic CSS.
        $css = Theme::getCSS();
        $css->pushcontent(HTML::style(array('type' => 'text/css'),
                             new RawXml(sprintf("<!--\nbody {background-image: url(%s);}\n-->",
                                                $this->getImageURL('uhhbackground.jpg')))));
        return $css;
    }
}
$WikiTheme = new Theme_Hawaiian('Hawaiian');

// CSS file defines fonts, colors and background images for this
// style.  The companion '*-heavy.css' file isn't defined, it's just
// expected to be in the same directory that the base style is in.

$WikiTheme->setDefaultCSS('Hawaiian', 'Hawaiian.css');
$WikiTheme->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
$WikiTheme->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');
$WikiTheme->addAlternateCSS('PhpWiki', 'phpwiki.css');

/**
 * The logo image appears on every page and links to the HomePage.
 */
$WikiTheme->addImageAlias('logo', 'PalmBeach.jpg');
$WikiTheme->addImageAlias('logo', WIKI_NAME . 'Logo.png');

/**
 * The Signature image is shown after saving an edited page. If this
 * is set to false then the "Thank you for editing..." screen will
 * be omitted.
 */
//$WikiTheme->addImageAlias('signature', 'SubmersiblePiscesV.jpg');
$WikiTheme->addImageAlias('signature', 'WaterFall.jpg');
$WikiTheme->addImageAlias('signature', WIKI_NAME . "Signature.png");
// Uncomment this next line to disable the signature.
//$WikiTheme->addImageAlias('signature', false);

// If you want to see more than just the waterfall let a random
// picture be chosen for the signature image:
//include_once($WikiTheme->file('lib/random.php'));
include_once("themes/$WikiTheme->_name/lib/random.php");
$imgSet = new randomImage($WikiTheme->file("images/pictures"));
$imgFile = "pictures/" . $imgSet->filename;
$WikiTheme->addImageAlias('signature', $imgFile);

//To test out the randomization just use logo instead of signature
//$WikiTheme->addImageAlias('logo', $imgFile);

/*
 * Link Icons
 */
$WikiTheme->setLinkIcon('interwiki');
$WikiTheme->setLinkIcon('*', 'flower.png');

$WikiTheme->setButtonSeparator(' ');

/**
 * WikiWords can automatically be split by inserting spaces between
 * the words. The default is to leave WordsSmashedTogetherLikeSo.
 */
$WikiTheme->setAutosplitWikiWords(true);

/*
 * You may adjust the formats used for formatting dates and times
 * below.  (These examples give the default formats.)
 * Formats are given as format strings to PHP strftime() function See
 * http://www.php.net/manual/en/function.strftime.php for details.
 * Do not include the server's zone (%Z), times are converted to the
 * user's time zone.
 */
//$WikiTheme->setDateFormat("%B %d, %Y");	    // must not contain time


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
