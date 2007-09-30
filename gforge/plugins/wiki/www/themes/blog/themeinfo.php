<?php
rcs_id('$Id: themeinfo.php,v 1.5 2005/02/03 05:19:48 rurban Exp $');

/**
 * This file defines a blog theme for PhpWiki, 
 * based on Rui Carmo's excellent http://the.taoofmac.com/space/ 
 * which is based on the Kubrick theme: http://binarybonsai.com/kubrick/
 * The layout was designed and built by Michael Heilemann,
 * whose blog you will find at http://binarybonsai.com/
 *
 * [Stanley Kubrick]"Good afternoon, gentlemen. I am a HAL 9000
 * computer. I became operational at the H.A.L. plant in Urbana,
 * Illinois on the 12th of January 1992. My instructor was
 * Mr. Langley, and he taught me to sing a song. If you'd like to hear
 * it I can sing it for you."
 *
 * The CSS, XHTML and design is released under GPL:
 * http://www.opensource.org/licenses/gpl-license.php 
 *
 * Default is a one-person (ADMIN_USER) blog (at the BlogHomePage), but 
 * other blogs are also enabled for every authenticated user.
 *
 * Actionbar: Edit, Home, About, Archives, News, ..., Info  [ Search ]
 * PageTrail: > .. > ..
 * Right sidebar boxes: Archives, Syndication, Links, GoogleAds
 *
 * For the livesearch feature (autodropdown of the results while you tip) 
 * you'll have to copy livesearch.js from http://blog.bitflux.ch/wiki/LiveSearch
 * to themes/default/, change the liveSearchReq.open line to:
liveSearchReq.open("GET", liveSearchURI + "?format=livesearch&paging=none&limit=25&s=" + document.forms.searchform.s.value);
 * and define ENABLE_LIVESEARCH in config.ini to true.
 * 
 * Better autodropdown's are in consideration:
 *   http://momche.net/publish/article.php?page=acdropdown)
 *
 * Happy blogging.
 */

require_once('lib/Theme.php');
require_once('themes/Sidebar/themeinfo.php');

class Theme_blog extends Theme_Sidebar {
    function _findFile ($file, $missing_okay=false) {
        if (file_exists($this->_path . "themes/".$this->_name."/$file"))
            return "themes/".$this->_name."/$file";
        if (file_exists($this->_path . "themes/Sidebar/$file"))
            return "themes/Sidebar/$file";
        return parent::_findFile($file, $missing_okay);
    }

    function _labelForAction ($action) {
        switch ($action) {
            case 'edit':   return _("Edit");
            case 'diff':   return _("Diff");
            case 'logout': return _("SignOut");
            case 'login':  return _("SignIn");
            case 'lock':   return _("Lock");
            case 'unlock': return _("Unlock");
            case 'remove': return _("Remove");
            default:
                return gettext(ucfirst($action));
        }
    }

    function getRecentChangesFormatter ($format) {
        include_once($this->file('lib/RecentChanges.php'));
        if (preg_match('/^rss|^sidebar/', $format))
            return false;       // use default
        if ($format == 'box')
            return '_blog_RecentChanges_BoxFormatter';
        return '_blog_RecentChanges_Formatter';
    }
    
    /* TODO: use the blog summary as label instead of the pagename */
    function linkExistingWikiWord($wikiword, $linktext = '', $version = false) {
        global $request;
        if ($version !== false and !$this->HTML_DUMP_SUFFIX)
            $url = WikiURL($wikiword, array('version' => $version));
        else
            $url = WikiURL($wikiword);

        // Extra steps for dumping page to an html file.
        if ($this->HTML_DUMP_SUFFIX) {
            $url = preg_replace('/^\./', '%2e', $url); // dot pages
        }

        $link = HTML::a(array('href' => $url));

        if (isa($wikiword, 'WikiPageName'))
             $default_text = $wikiword->shortName;
         else
             $default_text = $wikiword;
         
        if (!empty($linktext)) {
            $link->pushContent($linktext);
            $link->setAttr('class', 'named-wiki');
            $link->setAttr('title', $this->maybeSplitWikiWord($default_text));
        }
        else {
            //TODO: check if wikiblog
            $link->pushContent($this->maybeSplitWikiWord($default_text));
            $link->setAttr('class', 'wiki');
        }
        if ($request->getArg('frame'))
            $link->setAttr('target', '_top');
        return $link;
    }
}

$WikiTheme = new Theme_blog('blog');
define("PAGETRAIL_ARROW", " � ");

// CSS file defines fonts, colors and background images for this
// style.

// override sidebar definitions:
$WikiTheme->setDefaultCSS(_("blog"), 'Kubrick.css');

$WikiTheme->addButtonAlias(_("(diff)"), "[diff]" );
$WikiTheme->addButtonAlias("...", "alltime");

$WikiTheme->setButtonSeparator("");

/**
 * WikiWords can automatically be split by inserting spaces between
 * the words. The default is to leave WordsSmashedTogetherLikeSo.
 */
$WikiTheme->setAutosplitWikiWords(false);

/**
 * If true (default) show create '?' buttons on not existing pages, even if the 
 * user is not signed in.
 * If false, anon users get no links and it looks cleaner, but then they 
 * cannot easily fix missing pages.
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
//$WikiTheme->setDateFormat("%B %d, %Y");
$WikiTheme->setDateFormat("%A, %B %e, %Y"); // must not contain time
$WikiTheme->setTimeFormat("%H:%M:%S");

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