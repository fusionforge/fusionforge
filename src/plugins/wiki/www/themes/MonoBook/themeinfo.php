<?php
// Avoid direct call to this file.
// PHPWIKI_VERSION is defined in lib/prepend.php
if (!defined('PHPWIKI_VERSION')) {
    header("Location: /");
    exit;
}

// rcs_id('$Id: themeinfo.php 7690 2010-09-17 08:46:20Z vargenau $');
/**
 * The new mediawiki (Wikipedia.org) default style.
 * Mediawiki 'monobook' style sheet for CSS2-capable browsers.
 * Copyright Gabriel Wicke - http://www.aulinx.de/
 * See main.css for more.
 *
 * Problems with IE: signin is at the left.
 *
 * We don't (yet) support all mediawiki UI options, but we try to.
 * Besides that, maybe the mediawiki folks will see how much better phpwiki
 * will scale, esp. with a true database, not just mysql.
 * Technically phpwiki has about 2-3 years advantage and our plugins
 * cannot destroy the layout.
 * Anyway, the WikiParser perl module (and our php version) will be able to import
 * and convert back and forth.
 */
require_once('lib/WikiTheme.php');
require_once('themes/wikilens/themeinfo.php');

function ActionButton ($action, $label = false, $page_or_rev = false, $options = false) {
    global $WikiTheme;
    global $request;
    if (is_array($action)) {
        $attr = $action;
        $act = isset($attr['action']) ? $attr['action'] : 'browse';
    } else
        $act = $action;
    $class = is_safe_action($act) ? 'named-wiki' : 'wikiadmin';
    /* if selected action is current then prepend selected */
    $curract = $request->getArg("action");
    if ($curract == $act and $curract != 'browse')
        $class = "selected $class";
    if (!empty($options['class'])) {
        if ($curract == 'browse')
            $class = "$class ".$options['class'];
        else
            $class = $options['class'];
    }
    return HTML::li(array('class' => $class),
                    $WikiTheme->makeActionButton($action, $label, $page_or_rev, $options));
}

class WikiTheme_MonoBook extends WikiTheme_Wikilens
{
  
    /* this adds selected to the class */
    function makeActionButton ($action, $label=false, $page_or_rev=false, $options=false) {
        extract($this->_get_name_and_rev($page_or_rev));

        if (is_array($action)) {
            $attr = $action;
            $action = isset($attr['action']) ? $attr['action'] : 'browse';
        }
        else
            $attr['action'] = $action;

        $class = is_safe_action($action) ? /*'named-wiki'*/'new' : 'wikiadmin';
        /* if selected action is current then prepend selected */
        global $request;
        if ($request->getArg("action") == $action)
            $class = "selected $class";
            //$class = "selected";
        if (!empty($options['class']))
            $class = $options['class'];
        if (!$label)
            $label = $this->_labelForAction($action);

        if ($version)
            $attr['version'] = $version;

        if ($action == 'browse')
            unset($attr['action']);

        $options = $this->fixAccesskey($options);
        return $this->makeButton($label, WikiURL($pagename, $attr), $class, $options);
    }

    function load() {
	$this->addMoreHeaders(JavaScript("var ta;\nvar skin = '".$this->_name."';\n"));
	$this->addMoreHeaders(JavaScript('',array('src' => $this->_findData("wikibits.js"))));
	if (isBrowserIE()) {
	    $ver = browserVersion();
	    if ($ver > 5.1 and $ver < 5.9)
		$this->addMoreHeaders($this->_CSSlink(0,$this->_findFile('IE55Fixes.css'),'all'));
	    elseif ($ver > 5.5 and $ver < 7.0)
		$this->addMoreHeaders($this->_CSSlink(0,$this->_findFile('IE60Fixes.css'),'all'));
	    elseif ($ver >= 7.0)
		$this->addMoreHeaders($this->_CSSlink(0,$this->_findFile('IE70Fixes.css'),'all'));
	    else
		$this->addMoreHeaders($this->_CSSlink(0,$this->_findFile('IE50Fixes.css'),'all'));
	    unset($ver);
	    $this->addMoreHeaders("\n");
	    $this->addMoreHeaders(JavaScript('',array('src' => $this->_findData("IEFixes.js"))));
	    $this->addMoreHeaders("\n");
	    $this->addMoreHeaders(HTML::Raw('<meta http-equiv="imagetoolbar" content="no" />'));
	}
	$this->addMoreAttr('body', "class-ns-0", HTML::Raw('class="ns-0"'));

	// CSS file defines fonts, colors and background images for this
	// style.  The companion '*-heavy.css' file isn't defined, it's just
	// expected to be in the same directory that the base style is in.

	// This should result in phpwiki-printer.css being used when
	// printing or print-previewing with style "PhpWiki" or "MacOSX" selected.
	$this->setDefaultCSS('PhpWiki',
			     array(''      => 'monobook.css',
				   'print' => 'commonPrint.css'));

	// This allows one to manually select "Printer" style (when browsing page)
	// to see what the printer style looks like.
	$this->addAlternateCSS(_("Printer"), 'commonPrint.css', 'print, screen');
	$this->addAlternateCSS(_("Top & bottom toolbars"), 'phpwiki-topbottombars.css');
	$this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');

	/**
	 * The logo image appears on every page and links to the HomePage.
	 */
	$this->addImageAlias('logo', 'MonoBook-Logo.png');
	//$this->addImageAlias('logo', WIKI_NAME . 'Logo.png');

	/**
	 * The Signature image is shown after saving an edited page. If this
	 * is set to false then the "Thank you for editing..." screen will
	 * be omitted.
	 */

	$this->addImageAlias('signature', "Signature.png");
	// Uncomment this next line to disable the signature.
	$this->addImageAlias('signature', false);

	/*
	 * Link icons.
	 */
	/*
	  $this->setLinkIcon('http');
	  $this->setLinkIcon('https');
	  $this->setLinkIcon('ftp');
	  $this->setLinkIcon('mailto');
	  //$this->setLinkIcon('interwiki');
	  */
	$this->setLinkIcon('wikiuser');
	//$this->setLinkIcon('*', 'url');
	// front or after
	//$this->setLinkIconAttr('after');

	//$this->setButtonSeparator("\n | ");

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

$WikiTheme = new WikiTheme_MonoBook('MonoBook');

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End: 
?>
