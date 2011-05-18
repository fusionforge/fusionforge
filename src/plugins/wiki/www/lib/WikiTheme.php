<?php // $Id: WikiTheme.php 7964 2011-03-05 17:05:30Z vargenau $
/* Copyright (C) 2002,2004,2005,2006,2008,2009,2010 $ThePhpWikiProgrammingTeam
 *
 * This file is part of PhpWiki.
 *
 * PhpWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PhpWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Customize output by themes: templates, css, special links functions,
 * and more formatting.
 */

/**
 * Make a link to a wiki page (in this wiki).
 *
 * This is a convenience function.
 *
 * @param mixed $page_or_rev
 * Can be:<dl>
 * <dt>A string</dt><dd>The page to link to.</dd>
 * <dt>A WikiDB_Page object</dt><dd>The page to link to.</dd>
 * <dt>A WikiDB_PageRevision object</dt><dd>A specific version of the page to link to.</dd>
 * </dl>
 *
 * @param string $type
 * One of:<dl>
 * <dt>'unknown'</dt><dd>Make link appropriate for a non-existant page.</dd>
 * <dt>'known'</dt><dd>Make link appropriate for an existing page.</dd>
 * <dt>'auto'</dt><dd>Either 'unknown' or 'known' as appropriate.</dd>
 * <dt>'button'</dt><dd>Make a button-style link.</dd>
 * <dt>'if_known'</dt><dd>Only linkify if page exists.</dd>
 * </dl>
 * Unless $type of of the latter form, the link will be of class 'wiki', 'wikiunknown',
 * 'named-wiki', or 'named-wikiunknown', as appropriate.
 *
 * @param mixed $label (string or XmlContent object)
 * Label for the link.  If not given, defaults to the page name.
 *
 * @return XmlContent The link
 */
function WikiLink ($page_or_rev, $type = 'known', $label = false) {
    global $WikiTheme, $request;

    if ($type == 'button') {
        return $WikiTheme->makeLinkButton($page_or_rev, $label);
    }

    $version = false;

    if (isa($page_or_rev, 'WikiDB_PageRevision')) {
        $version = $page_or_rev->getVersion();
        if ($page_or_rev->isCurrent())
            $version = false;
        $page = $page_or_rev->getPage();
        $pagename = $page->getName();
        $wikipage = $pagename;
        $exists = true;
    }
    elseif (isa($page_or_rev, 'WikiDB_Page')) {
        $page = $page_or_rev;
        $pagename = $page->getName();
        $wikipage = $pagename;
    }
    elseif (isa($page_or_rev, 'WikiPageName')) {
        $wikipage = $page_or_rev;
        $pagename = $wikipage->name;
        if (!$wikipage->isValid('strict'))
            return $WikiTheme->linkBadWikiWord($wikipage, $label);
    }
    else {
        $wikipage = new WikiPageName($page_or_rev, $request->getPage());
        $pagename = $wikipage->name;
        if (!$wikipage->isValid('strict'))
            return $WikiTheme->linkBadWikiWord($wikipage, $label);
    }

    if ($type == 'auto' or $type == 'if_known') {
        if (isset($page)) {
            $exists = $page->exists();
        }
        else {
        $dbi =& $request->_dbi;
            $exists = $dbi->isWikiPage($wikipage->name);
        }
    }
    elseif ($type == 'unknown') {
        $exists = false;
    }
    else {
        $exists = true;
    }

    // FIXME: this should be somewhere else, if really needed.
    // WikiLink makes A link, not a string of fancy ones.
    // (I think that the fancy split links are just confusing.)
    // Todo: test external ImageLinks http://some/images/next.gif
    if (isa($wikipage, 'WikiPageName') and
        ! $label and
        strchr(substr($wikipage->shortName,1), SUBPAGE_SEPARATOR))
    {
        $parts = explode(SUBPAGE_SEPARATOR, $wikipage->shortName);
        $last_part = array_pop($parts);
        $sep = '';
        $link = HTML::span();
        foreach ($parts as $part) {
            $path[] = $part;
            $parent = join(SUBPAGE_SEPARATOR, $path);
            if ($WikiTheme->_autosplitWikiWords)
                $part = " " . $part;
            if ($part)
                $link->pushContent($WikiTheme->linkExistingWikiWord($parent, $sep . $part));
            $sep = $WikiTheme->_autosplitWikiWords
                   ? ' ' . SUBPAGE_SEPARATOR : SUBPAGE_SEPARATOR;
        }
        if ($exists)
            $link->pushContent($WikiTheme->linkExistingWikiWord($wikipage, $sep . $last_part,
                                                                $version));
        else
            $link->pushContent($WikiTheme->linkUnknownWikiWord($wikipage, $sep . $last_part));
        return $link;
    }

    if ($exists) {
        return $WikiTheme->linkExistingWikiWord($wikipage, $label, $version);
    }
    elseif ($type == 'if_known') {
        if (!$label && isa($wikipage, 'WikiPageName'))
            $label = $wikipage->shortName;
        return HTML($label ? $label : $pagename);
    }
    else {
        return $WikiTheme->linkUnknownWikiWord($wikipage, $label);
    }
}



/**
 * Make a button.
 *
 * This is a convenience function.
 *
 * @param $action string
 * One of <dl>
 * <dt>[action]</dt><dd>Perform action (e.g. 'edit') on the selected page.</dd>
 * <dt>[ActionPage]</dt><dd>Run the actionpage (e.g. 'BackLinks') on the selected page.</dd>
 * <dt>'submit:'[name]</dt><dd>Make a form submission button with the given name.
 *      ([name] can be blank for a nameless submit button.)</dd>
 * <dt>a hash</dt><dd>Query args for the action. E.g.<pre>
 *      array('action' => 'diff', 'previous' => 'author')
 * </pre></dd>
 * </dl>
 *
 * @param $label string
 * A label for the button.  If ommited, a suitable default (based on the valued of $action)
 * will be picked.
 *
 * @param $page_or_rev mixed
 * Which page (& version) to perform the action on.
 * Can be one of:<dl>
 * <dt>A string</dt><dd>The pagename.</dd>
 * <dt>A WikiDB_Page object</dt><dd>The page.</dd>
 * <dt>A WikiDB_PageRevision object</dt><dd>A specific version of the page.</dd>
 * </dl>
 * ($Page_or_rev is ignored for submit buttons.)
 */
function Button ($action, $label = false, $page_or_rev = false, $options = false) {
    global $WikiTheme;

    if (!is_array($action) && preg_match('/^submit:(.*)/', $action, $m))
        return $WikiTheme->makeSubmitButton($label, $m[1], $page_or_rev, $options);
    else
        return $WikiTheme->makeActionButton($action, $label, $page_or_rev, $options);
}

class WikiTheme {
    var $HTML_DUMP_SUFFIX = '';
    var $DUMP_MODE = false, $dumped_images, $dumped_css;

    /**
     * noinit: Do not initialize unnecessary items in default_theme fallback twice.
     */
    function WikiTheme ($theme_name = 'default', $noinit = false) {
        $this->_name = $theme_name;
        $this->_themes_dir = NormalizeLocalFileName("themes");
        $this->_path  = defined('PHPWIKI_DIR') ? NormalizeLocalFileName("") : "";
        $this->_theme = "themes/$theme_name";
        $this->_parents = array();

        if ($theme_name != 'default') {
            $parent = $this;
            /* derived classes should search all parent classes */
            while ($parent = get_parent_class($parent)) {
                if (strtolower($parent) == 'wikitheme') {
                    $this->_default_theme = new WikiTheme('default', true);
                    $this->_parents[] = $this->_default_theme;
                } elseif ($parent) {
                    $this->_parents[] = new WikiTheme
                      (preg_replace("/^WikiTheme_/i", "", $parent), true);
                }
            }
        }
        if ($noinit) return;
        $this->_css = array();

        // on derived classes do not add headers twice
        if (count($this->_parents) > 1) {
            return;
        }
        $this->addMoreHeaders(JavaScript('',array('src' => $this->_findData("wikicommon.js"))));
        if (!FUSIONFORGE) {
            // FusionForge already loads this
            $this->addMoreHeaders(JavaScript('',array('src' => $this->_findData("sortable.js"))));
        }
        // by pixels
        if ((is_object($GLOBALS['request']) // guard against unittests
             and $GLOBALS['request']->getPref('doubleClickEdit'))
            or ENABLE_DOUBLECLICKEDIT)
            $this->initDoubleClickEdit();

        // will be replaced by acDropDown
        if (ENABLE_LIVESEARCH) { // by bitflux.ch
            $this->initLiveSearch();
        }
        // replaces external LiveSearch
        // enable ENABLE_AJAX for DynamicIncludePage
        if (ENABLE_ACDROPDOWN or ENABLE_AJAX) {
            $this->initMoAcDropDown();
            if (ENABLE_AJAX and DEBUG) // minified all together
                $this->addMoreHeaders(JavaScript('',array('src' => $this->_findData("ajax.js"))));
        }
    }

    function file ($file) {
        return $this->_path . "$this->_theme/$file";
   }

    function _findFile ($file, $missing_okay = false) {
        if (file_exists($this->file($file)))
            return "$this->_theme/$file";

        // FIXME: this is a short-term hack.  Delete this after all files
        // get moved into themes/...
        // Needed for button paths in parent themes
        if (file_exists($this->_path . $file))
            return $file;

        /* Derived classes should search all parent classes */
        foreach ($this->_parents as $parent) {
        $path = $parent->_findFile($file, 1);
            if ($path) {
                return $path;
            } elseif (0 and DEBUG & (_DEBUG_VERBOSE + _DEBUG_REMOTE)) {
                trigger_error("$parent->_theme/$file: not found", E_USER_NOTICE);
            }
        }
        if (isset($this->_default_theme)) {
            return $this->_default_theme->_findFile($file, $missing_okay);
        }
        else if (!$missing_okay) {
            trigger_error("$this->_theme/$file: not found", E_USER_NOTICE);
            if ((DEBUG & _DEBUG_TRACE) && function_exists('debug_backtrace')) { // >= 4.3.0
                echo "<pre>", printSimpleTrace(debug_backtrace()), "</pre>\n";
            }
        }
        return false;
    }

    function _findData ($file, $missing_okay = false) {
        if (!string_starts_with($file, "themes")) { // common case
            $path = $this->_findFile($file, $missing_okay);
        } else {
            // _findButton only
            if (file_exists($file)) {
                $path = $file;
            } elseif (defined('DATA_PATH')
                      and file_exists(DATA_PATH . "/$file")) {
                $path = $file;
            } else { // fallback for buttons in parent themes
                $path = $this->_findFile($file, $missing_okay);
            }
        }
        if (!$path)
            return false;
        if (!DEBUG) {
            $min = preg_replace("/\.(css|js)$/", "-min.\\1", $file);
            if ($min and ($x = $this->_findFile($min, true))) $path = $x;
        }

        if (defined('DATA_PATH'))
            return DATA_PATH . "/$path";
        return $path;
    }

    ////////////////////////////////////////////////////////////////
    //
    // Date and Time formatting
    //
    ////////////////////////////////////////////////////////////////

    // Note:  Windows' implementation of strftime does not include certain
    // format specifiers, such as %e (for date without leading zeros).  In
    // general, see:
    // http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vclib/html/_crt_strftime.2c_.wcsftime.asp
    // As a result, we have to use %d, and strip out leading zeros ourselves.

    var $_dateFormat = "%B %d, %Y";
    var $_timeFormat = "%I:%M %p";

    var $_showModTime = true;

    /**
     * Set format string used for dates.
     *
     * @param $fs string Format string for dates.
     *
     * @param $show_mod_time bool If true (default) then times
     * are included in the messages generated by getLastModifiedMessage(),
     * otherwise, only the date of last modification will be shown.
     */
    function setDateFormat ($fs, $show_mod_time = true) {
        $this->_dateFormat = $fs;
        $this->_showModTime = $show_mod_time;
    }

    /**
     * Set format string used for times.
     *
     * @param $fs string Format string for times.
     */
    function setTimeFormat ($fs) {
        $this->_timeFormat = $fs;
    }

    /**
     * Format a date.
     *
     * Any time zone offset specified in the users preferences is
     * taken into account by this method.
     *
     * @param $time_t integer Unix-style time.
     *
     * @return string The date.
     */
    function formatDate ($time_t) {
        global $request;

        $offset_time = $time_t + 3600 * $request->getPref('timeOffset');
        // strip leading zeros from date elements (ie space followed by zero
        // or leading 0 as in French "09 mai 2009")
        return preg_replace('/ 0/', ' ', preg_replace('/^0/', ' ',
                            strftime($this->_dateFormat, $offset_time)));
    }

    /**
     * Format a date.
     *
     * Any time zone offset specified in the users preferences is
     * taken into account by this method.
     *
     * @param $time_t integer Unix-style time.
     *
     * @return string The time.
     */
    function formatTime ($time_t) {
        //FIXME: make 24-hour mode configurable?
        global $request;
        $offset_time = $time_t + 3600 * $request->getPref('timeOffset');
        return preg_replace('/^0/', ' ',
                            strtolower(strftime($this->_timeFormat, $offset_time)));
    }

    /**
     * Format a date and time.
     *
     * Any time zone offset specified in the users preferences is
     * taken into account by this method.
     *
     * @param $time_t integer Unix-style time.
     *
     * @return string The date and time.
     */
    function formatDateTime ($time_t) {
        if ($time_t == 0) {
            // Do not display "01 January 1970 1:00" for nonexistent pages
            return "";
        } else {
            return $this->formatDate($time_t) . ' ' . $this->formatTime($time_t);
        }
    }

    /**
     * Format a (possibly relative) date.
     *
     * If enabled in the users preferences, this method might
     * return a relative day (e.g. 'Today', 'Yesterday').
     *
     * Any time zone offset specified in the users preferences is
     * taken into account by this method.
     *
     * @param $time_t integer Unix-style time.
     *
     * @return string The day.
     */
    function getDay ($time_t) {
        global $request;

        if ($request->getPref('relativeDates') && ($date = $this->_relativeDay($time_t))) {
            return ucfirst($date);
        }
        return $this->formatDate($time_t);
    }

    /**
     * Format the "last modified" message for a page revision.
     *
     * @param $revision object A WikiDB_PageRevision object.
     *
     * @param $show_version bool Should the page version number
     * be included in the message.  (If this argument is omitted,
     * then the version number will be shown only iff the revision
     * is not the current one.
     *
     * @return string The "last modified" message.
     */
    function getLastModifiedMessage ($revision, $show_version = 'auto') {
        global $request;
        if (!$revision) return '';

        // dates >= this are considered invalid.
        if (! defined('EPOCH'))
            define('EPOCH', 0); // seconds since ~ January 1 1970

        $mtime = $revision->get('mtime');
        if ($mtime <= EPOCH)
            return fmt("Never edited");

        if ($show_version == 'auto')
            $show_version = !$revision->isCurrent();

        if ($request->getPref('relativeDates') && ($date = $this->_relativeDay($mtime))) {
            if ($this->_showModTime)
                $date =  sprintf(_("%s at %s"),
                                 $date, $this->formatTime($mtime));

            if ($show_version)
                return fmt("Version %s, saved on %s", $revision->getVersion(), $date);
            else
                return fmt("Last edited %s", $date);
        }

        if ($this->_showModTime)
            $date = $this->formatDateTime($mtime);
        else
            $date = $this->formatDate($mtime);

        if ($show_version)
            return fmt("Version %s, saved on %s", $revision->getVersion(), $date);
        else
            return fmt("Last edited on %s", $date);
    }

    function _relativeDay ($time_t) {
        global $request;

        if (is_numeric($request->getPref('timeOffset')))
          $offset = 3600 * $request->getPref('timeOffset');
        else
          $offset = 0;

        $now = time() + $offset;
        $today = localtime($now, true);
        $time = localtime($time_t + $offset, true);

        if ($time['tm_yday'] == $today['tm_yday'] && $time['tm_year'] == $today['tm_year'])
            return _("today");

        // Note that due to daylight savings chages (and leap seconds), $now minus
        // 24 hours is not guaranteed to be yesterday.
        $yesterday = localtime($now - (12 + $today['tm_hour']) * 3600, true);
        if ($time['tm_yday'] == $yesterday['tm_yday']
            and $time['tm_year'] == $yesterday['tm_year'])
            return _("yesterday");

        return false;
    }

    /**
     * Format the "Author" and "Owner" messages for a page revision.
     */
    function getOwnerMessage ($page) {
        if (!ENABLE_PAGEPERM or !class_exists("PagePermission"))
            return '';
        $dbi =& $GLOBALS['request']->_dbi;
        $owner = $page->getOwner();
        if ($owner) {
            /*
            if ( mayAccessPage('change',$page->getName()) )
                return fmt("Owner: %s", $this->makeActionButton(array('action'=>_("chown"),
                                                                      's' => $page->getName()),
                                                                $owner, $page));
            */
            if ( $dbi->isWikiPage($owner) )
                return fmt("Owner: %s", WikiLink($owner));
            else
                return fmt("Owner: %s", '"'.$owner.'"');
        }
    }

    /* New behaviour: (by Matt Brown)
       Prefer author (name) over internal author_id (IP) */
    function getAuthorMessage ($revision) {
        if (!$revision) return '';
        $dbi =& $GLOBALS['request']->_dbi;
        $author = $revision->get('author');
        if (!$author) $author = $revision->get('author_id');
            if (!$author) return '';
        if ( $dbi->isWikiPage($author) ) {
                return fmt("by %s", WikiLink($author));
        } else {
                return fmt("by %s", '"'.$author.'"');
        }
    }

    ////////////////////////////////////////////////////////////////
    //
    // Hooks for other formatting
    //
    ////////////////////////////////////////////////////////////////

    //FIXME: PHP 4.1 Warnings
    //lib/WikiTheme.php:84: Notice[8]: The call_user_method() function is deprecated,
    //use the call_user_func variety with the array(&$obj, "method") syntax instead

    function getFormatter ($type, $format) {
        $method = strtolower("get${type}Formatter");
        if (method_exists($this, $method))
            return $this->{$method}($format);
        return false;
    }

    ////////////////////////////////////////////////////////////////
    //
    // Links
    //
    ////////////////////////////////////////////////////////////////

    var $_autosplitWikiWords = false;
    function setAutosplitWikiWords($autosplit=true) {
        $this->_autosplitWikiWords = $autosplit ? true : false;
    }

    function maybeSplitWikiWord ($wikiword) {
        if ($this->_autosplitWikiWords)
            return SplitPagename($wikiword);
        else
            return $wikiword;
    }

    var $_anonEditUnknownLinks = true;
    function setAnonEditUnknownLinks($anonedit=true) {
        $this->_anonEditUnknownLinks = $anonedit ? true : false;
    }

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
            $link->pushContent($this->maybeSplitWikiWord($default_text));
            $link->setAttr('class', 'wiki');
        }
        if ($request->getArg('frame'))
            $link->setAttr('target', '_top');
        return $link;
    }

    function linkUnknownWikiWord($wikiword, $linktext = '') {
        global $request;

        // Get rid of anchors on unknown wikiwords
        if (isa($wikiword, 'WikiPageName')) {
            $default_text = $wikiword->shortName;
            $wikiword = $wikiword->name;
        }
        else {
            $default_text = $wikiword;
        }

        if ($this->DUMP_MODE) { // HTML, PDF or XML
            $link = HTML::span( empty($linktext) ? $wikiword : $linktext);
            $link->setAttr('style', 'text-decoration: underline');
            $link->addTooltip(sprintf(_("Empty link to: %s"), $wikiword));
            $link->setAttr('class', empty($linktext) ? 'wikiunknown' : 'named-wikiunknown');
            return $link;
        } else {
            // if AnonEditUnknownLinks show "?" only users which are allowed to edit this page
            if (! $this->_anonEditUnknownLinks and
                ( ! $request->_user->isSignedIn()
                  or ! mayAccessPage('edit', $request->getArg('pagename'))))
            {
                $text = HTML::span( empty($linktext) ? $wikiword : $linktext);
                $text->setAttr('class', empty($linktext) ? 'wikiunknown' : 'named-wikiunknown');
                return $text;
            } else {
                $url = WikiURL($wikiword, array('action' => 'create'));
                $button = $this->makeButton('?', $url);
                $button->addTooltip(sprintf(_("Create: %s"), $wikiword));
            }
        }

        $link = HTML::span();
        if (!empty($linktext)) {
            $link->pushContent(HTML::span($linktext));
            $link->setAttr('style', 'text-decoration: underline');
            $link->setAttr('class', 'named-wikiunknown');
        }
        else {
            $link->pushContent(HTML::span($this->maybeSplitWikiWord($default_text)));
            $link->setAttr('style', 'text-decoration: underline');
            $link->setAttr('class', 'wikiunknown');
        }
        if (!isa($button, "ImageButton"))
            $button->setAttr('rel', 'nofollow');
        $link->pushContent($button);
        if ($request->getPref('googleLink')) {
            $gbutton = $this->makeButton('G', "http://www.google.com/search?q="
                                         . urlencode($wikiword));
            $gbutton->addTooltip(sprintf(_("Google:%s"), $wikiword));
            $link->pushContent($gbutton);
        }
        if ($request->getArg('frame'))
            $link->setAttr('target', '_top');

        return $link;
    }

    function linkBadWikiWord($wikiword, $linktext = '') {
        global $ErrorManager;

        if ($linktext) {
            $text = $linktext;
        }
        elseif (isa($wikiword, 'WikiPageName')) {
            $text = $wikiword->shortName;
        }
        else {
            $text = $wikiword;
        }

        if (isa($wikiword, 'WikiPageName'))
            $message = $wikiword->getWarnings();
        else
            $message = sprintf(_("'%s': Bad page name"), $wikiword);
        $ErrorManager->warning($message);

        return HTML::span(array('class' => 'badwikiword'), $text);
    }

    ////////////////////////////////////////////////////////////////
    //
    // Images and Icons
    //
    ////////////////////////////////////////////////////////////////
    var $_imageAliases = array();

    /**
     *
     * (To disable an image, alias the image to <code>false</code>.
     */
    function addImageAlias ($alias, $image_name) {
        // fall back to the PhpWiki-supplied image if not found
    if ((empty($this->_imageAliases[$alias])
           and $this->_findFile("images/$image_name", true))
        or $image_name === false)
            $this->_imageAliases[$alias] = $image_name;
    }

    function getImageURL ($image) {
        $aliases = &$this->_imageAliases;

        if (isset($aliases[$image])) {
            $image = $aliases[$image];
            if (!$image)
                return false;
        }

        // If not extension, default to .png.
        if (!preg_match('/\.\w+$/', $image))
            $image .= '.png';

        // FIXME: this should probably be made to fall back
        //        automatically to .gif, .jpg.
        //        Also try .gif before .png if browser doesn't like png.

        $path = $this->_findData("images/$image", 'missing okay');
        if (!$path) // search explicit images/ or button/ links also
            $path = $this->_findData("$image", 'missing okay');

        if ($this->DUMP_MODE) {
            if (empty($this->dumped_images)) $this->dumped_images = array();
            $path = "images/". basename($path);
            if (!in_array($path,$this->dumped_images))
                $this->dumped_images[] = $path;
        }
        return $path;
    }

    function setLinkIcon($proto, $image = false) {
        if (!$image)
            $image = $proto;

        $this->_linkIcons[$proto] = $image;
    }

    function getLinkIconURL ($proto) {
        $icons = &$this->_linkIcons;
        if (!empty($icons[$proto]))
            return $this->getImageURL($icons[$proto]);
        elseif (!empty($icons['*']))
            return $this->getImageURL($icons['*']);
        return false;
    }

    var $_linkIcon = 'front'; // or 'after' or 'no'.
    // maybe also 'spanall': there is a scheme currently in effect with front, which
    // spans the icon only to the first, to let the next words wrap on line breaks
    // see stdlib.php:PossiblyGlueIconToText()
    function getLinkIconAttr () {
        return $this->_linkIcon;
    }
    function setLinkIconAttr ($where) {
        $this->_linkIcon = $where;
    }

    function addButtonAlias ($text, $alias = false) {
        $aliases = &$this->_buttonAliases;

        if (is_array($text))
            $aliases = array_merge($aliases, $text);
        elseif ($alias === false)
            unset($aliases[$text]);
        else
            $aliases[$text] = $alias;
    }

    function getButtonURL ($text) {
        $aliases = &$this->_buttonAliases;
        if (isset($aliases[$text]))
            $text = $aliases[$text];

        $qtext = urlencode($text);
        $url = $this->_findButton("$qtext.png");
        if ($url && strstr($url, '%')) {
            $url = preg_replace('|([^/]+)$|e', 'urlencode("\\1")', $url);
        }
        if (!$url) {// Jeff complained about png not supported everywhere.
                    // This was not PC until 2005.
            $url = $this->_findButton("$qtext.gif");
            if ($url && strstr($url, '%')) {
                $url = preg_replace('|([^/]+)$|e', 'urlencode("\\1")', $url);
            }
        }
        if ($url and $this->DUMP_MODE) {
            if (empty($this->dumped_buttons)) $this->dumped_buttons = array();
            $file = $url;
            if (defined('DATA_PATH'))
                $file = substr($url,strlen(DATA_PATH)+1);
            $url = "images/buttons/".basename($file);
            if (!array_key_exists($text, $this->dumped_buttons))
                $this->dumped_buttons[$text] = $file;
        }
        return $url;
    }

    function _findButton ($button_file) {
        if (empty($this->_button_path))
            $this->_button_path = $this->_getButtonPath();

        foreach ($this->_button_path as $dir) {
            if ($path = $this->_findData("$dir/$button_file", 1))
                return $path;
        }
        return false;
    }

    function _getButtonPath () {
        $button_dir = $this->_findFile("buttons");
        $path_dir = $this->_path . $button_dir;
        if (!file_exists($path_dir) || !is_dir($path_dir))
            return array();
        $path = array($button_dir);

        $dir = dir($path_dir);
        while (($subdir = $dir->read()) !== false) {
            if ($subdir[0] == '.')
                continue;
            if ($subdir == 'CVS')
                continue;
            if (is_dir("$path_dir/$subdir"))
                $path[] = "$button_dir/$subdir";
        }
        $dir->close();
        // add default buttons
        $path[] = "themes/default/buttons";
        $path_dir = $this->_path . "themes/default/buttons";
        $dir = dir($path_dir);
        while (($subdir = $dir->read()) !== false) {
            if ($subdir[0] == '.')
                continue;
            if ($subdir == 'CVS')
                continue;
            if (is_dir("$path_dir/$subdir"))
                $path[] = "themes/default/buttons/$subdir";
        }
        $dir->close();

        return $path;
    }

    ////////////////////////////////////////////////////////////////
    //
    // Button style
    //
    ////////////////////////////////////////////////////////////////

    function makeButton ($text, $url, $class = false, $options = false) {
        // FIXME: don't always try for image button?

        // Special case: URLs like 'submit:preview' generate form
        // submission buttons.
        if (preg_match('/^submit:(.*)$/', $url, $m))
            return $this->makeSubmitButton($text, $m[1], $class, $options);

        if (is_string($text))
            $imgurl = $this->getButtonURL($text);
        else
            $imgurl = $text;
        if ($imgurl)
            return new ImageButton($text, $url,
                   in_array($class,array("wikiaction","wikiadmin"))?"wikibutton":$class,
                   $imgurl, $options);
        else
            return new Button($this->maybeSplitWikiWord($text), $url,
                              $class, $options);
    }

    function makeSubmitButton ($text, $name, $class = false, $options = false) {
        $imgurl = $this->getButtonURL($text);

        if ($imgurl)
            return new SubmitImageButton($text, $name, !$class ? "wikibutton" : $class, $imgurl, $options);
        else
            return new SubmitButton($text, $name, $class, $options);
    }

    /**
     * Make button to perform action.
     *
     * This constructs a button which performs an action on the
     * currently selected version of the current page.
     * (Or anotherpage or version, if you want...)
     *
     * @param $action string The action to perform (e.g. 'edit', 'lock').
     * This can also be the name of an "action page" like 'LikePages'.
     * Alternatively you can give a hash of query args to be applied
     * to the page.
     *
     * @param $label string Textual label for the button.  If left empty,
     * a suitable name will be guessed.
     *
     * @param $page_or_rev mixed  The page to link to.  This can be
     * given as a string (the page name), a WikiDB_Page object, or as
     * WikiDB_PageRevision object.  If given as a WikiDB_PageRevision
     * object, the button will link to a specific version of the
     * designated page, otherwise the button links to the most recent
     * version of the page.
     *
     * @return object A Button object.
     */
    function makeActionButton ($action, $label = false,
                               $page_or_rev = false, $options = false)
    {
        extract($this->_get_name_and_rev($page_or_rev));

        if (is_array($action)) {
            $attr = $action;
            $action = isset($attr['action']) ? $attr['action'] : 'browse';
        }
        else
            $attr['action'] = $action;

        $class = is_safe_action($action) ? 'wikiaction' : 'wikiadmin';
        if ( !$label )
            $label = $this->_labelForAction($action);

        if ($version)
            $attr['version'] = $version;

        if ($action == 'browse')
            unset($attr['action']);

        $options = $this->fixAccesskey($options);

        return $this->makeButton($label, WikiURL($pagename, $attr), $class, $options);
    }

    function tooltipAccessKeyPrefix() {
    static $tooltipAccessKeyPrefix = null;
    if ($tooltipAccessKeyPrefix) return $tooltipAccessKeyPrefix;

        $tooltipAccessKeyPrefix = 'alt';
    if (isBrowserOpera()) $tooltipAccessKeyPrefix = 'shift-esc';
    elseif (isBrowserSafari() or browserDetect("Mac") or isBrowserKonqueror())
        $tooltipAccessKeyPrefix = 'ctrl';
    // ff2 win and x11 only
    elseif ((browserDetect("firefox/2") or browserDetect("minefield/3") or browserDetect("SeaMonkey/1.1"))
        and ((browserDetect("windows") or browserDetect("x11"))))
        $tooltipAccessKeyPrefix = 'alt-shift';
    return $tooltipAccessKeyPrefix;
    }

    /** Define the accesskey in the title only, with ending [p] or [alt-p].
     *  This fixes the prefix in the title and sets the accesskey.
     */
    function fixAccesskey($attrs) {
        if (!empty($attrs['title']) and preg_match("/\[(alt-)?(.)\]$/", $attrs['title'], $m))
        {
            if (empty($attrs['accesskey'])) $attrs['accesskey'] = $m[2];
            // firefox 'alt-shift', MSIE: 'alt', ... see wikibits.js
            $attrs['title'] = preg_replace("/\[(alt-)?(.)\]$/", "[".$this->tooltipAccessKeyPrefix()."-\\2]", $attrs['title']);
        }
        return $attrs;
    }

    /**
     * Make a "button" which links to a wiki-page.
     *
     * These are really just regular WikiLinks, possibly
     * disguised (e.g. behind an image button) by the theme.
     *
     * This method should probably only be used for links
     * which appear in page navigation bars, or similar places.
     *
     * Use linkExistingWikiWord, or LinkWikiWord for normal links.
     *
     * @param $page_or_rev mixed The page to link to.  This can be
     * given as a string (the page name), a WikiDB_Page object, or as
     * WikiDB_PageRevision object.  If given as a WikiDB_PageRevision
     * object, the button will link to a specific version of the
     * designated page, otherwise the button links to the most recent
     * version of the page.
     *
     * @return object A Button object.
     */
    function makeLinkButton ($page_or_rev, $label = false, $action = false) {
        extract($this->_get_name_and_rev($page_or_rev));

        $args = $version ? array('version' => $version) : false;
        if ($action) $args['action'] = $action;

        return $this->makeButton($label ? $label : $pagename,
                                 WikiURL($pagename, $args), 'wiki');
    }

    function _get_name_and_rev ($page_or_rev) {
        $version = false;

        if (empty($page_or_rev)) {
            global $request;
            $pagename = $request->getArg("pagename");
            $version = $request->getArg("version");
        }
        elseif (is_object($page_or_rev)) {
            if (isa($page_or_rev, 'WikiDB_PageRevision')) {
                $rev = $page_or_rev;
                $page = $rev->getPage();
                if (!$rev->isCurrent()) $version = $rev->getVersion();
            }
            else {
                $page = $page_or_rev;
            }
            $pagename = $page->getName();
        }
        elseif (is_numeric($page_or_rev)) {
            $version = $page_or_rev;
        }
        else {
            $pagename = (string) $page_or_rev;
        }
        return compact('pagename', 'version');
    }

    function _labelForAction ($action) {
        switch ($action) {
            case 'edit':   return _("Edit");
            case 'diff':   return _("Diff");
            case 'logout': return _("Sign Out");
            case 'login':  return _("Sign In");
            case 'rename': return _("Rename Page");
            case 'lock':   return _("Lock Page");
            case 'unlock': return _("Unlock Page");
            case 'remove': return _("Remove Page");
            case 'purge':  return _("Purge Page");
            default:
                // I don't think the rest of these actually get used.
                // 'setprefs'
                // 'upload' 'dumpserial' 'loadfile' 'zip'
                // 'save' 'browse'
                return gettext(ucfirst($action));
        }
    }

    //----------------------------------------------------------------
    var $_buttonSeparator = "\n | ";

    function setButtonSeparator($separator) {
        $this->_buttonSeparator = $separator;
    }

    function getButtonSeparator() {
        return $this->_buttonSeparator;
    }


    ////////////////////////////////////////////////////////////////
    //
    // CSS
    //
    // Notes:
    //
    // Based on testing with Galeon 1.2.7 (Mozilla 1.2):
    // Automatic media-based style selection (via <link> tags) only
    // seems to work for the default style, not for alternate styles.
    //
    // Doing
    //
    //  <link rel="stylesheet" type="text/css" href="phpwiki.css" />
    //  <link rel="stylesheet" type="text/css" href="phpwiki-printer.css" media="print" />
    //
    // works to make it so that the printer style sheet get used
    // automatically when printing (or print-previewing) a page
    // (but when only when the default style is selected.)
    //
    // Attempts like:
    //
    //  <link rel="alternate stylesheet" title="Modern"
    //        type="text/css" href="phpwiki-modern.css" />
    //  <link rel="alternate stylesheet" title="Modern"
    //        type="text/css" href="phpwiki-printer.css" media="print" />
    //
    // Result in two "Modern" choices when trying to select alternate style.
    // If one selects the first of those choices, one gets phpwiki-modern
    // both when browsing and printing.  If one selects the second "Modern",
    // one gets no CSS when browsing, and phpwiki-printer when printing.
    //
    // The Real Fix?
    // =============
    //
    // We should probably move to doing the media based style
    // switching in the CSS files themselves using, e.g.:
    //
    //  @import url(print.css) print;
    //
    ////////////////////////////////////////////////////////////////

    function _CSSlink($title, $css_file, $media, $is_alt = false) {
        // Don't set title on default style.  This makes it clear to
        // the user which is the default (i.e. most supported) style.
        if ($is_alt and isBrowserKonqueror())
            return HTML();
        $link = HTML::link(array('rel'     => $is_alt ? 'alternate stylesheet' : 'stylesheet',
                                 'type'    => 'text/css',
                                 'charset' => $GLOBALS['charset'],
                                 'href'    => $this->_findData($css_file)));
        if ($is_alt)
            $link->setAttr('title', $title);

        if ($media)
            $link->setAttr('media', $media);
        if ($this->DUMP_MODE) {
            if (empty($this->dumped_css)) $this->dumped_css = array();
            if (!in_array($css_file,$this->dumped_css)) $this->dumped_css[] = $css_file;
            $link->setAttr('href', basename($link->getAttr('href')));
        }

        return $link;
    }

    /** Set default CSS source for this theme.
     *
     * To set styles to be used for different media, pass a
     * hash for the second argument, e.g.
     *
     * $theme->setDefaultCSS('default', array('' => 'normal.css',
     *                                        'print' => 'printer.css'));
     *
     * If you call this more than once, the last one called takes
     * precedence as the default style.
     *
     * @param string $title Name of style (currently ignored, unless
     * you call this more than once, in which case, some of the style
     * will become alternate (rather than default) styles, and then their
     * titles will be used.
     *
     * @param mixed $css_files Name of CSS file, or hash containing a mapping
     * between media types and CSS file names.  Use a key of '' (the empty string)
     * to set the default CSS for non-specified media.  (See above for an example.)
     */
    function setDefaultCSS ($title, $css_files) {
        if (!is_array($css_files))
            $css_files = array('' => $css_files);
        // Add to the front of $this->_css
        unset($this->_css[$title]);
        $this->_css = array_merge(array($title => $css_files), $this->_css);
    }

    /** Set alternate CSS source for this theme.
     *
     * @param string $title Name of style.
     * @param string $css_files Name of CSS file.
     */
    function addAlternateCSS ($title, $css_files) {
        if (!is_array($css_files))
            $css_files = array('' => $css_files);
        $this->_css[$title] = $css_files;
    }

    /**
     * @return string HTML for CSS.
     */
    function getCSS () {
        $css = array();
        $is_alt = false;
        foreach ($this->_css as $title => $css_files) {
            ksort($css_files); // move $css_files[''] to front.
            foreach ($css_files as $media => $css_file) {
        if (!empty($this->DUMP_MODE)) {
            if ($media == 'print')
            $css[] = $this->_CSSlink($title, $css_file, '', $is_alt);
        } else {
            $css[] = $this->_CSSlink($title, $css_file, $media, $is_alt);
        }
                if ($is_alt) break;
            }
            $is_alt = true;
        }
        return HTML($css);
    }

    function findTemplate ($name) {
        if ($tmp = $this->_findFile("templates/$name.tmpl", 1))
            return $this->_path . $tmp;
        else {
            $f1 = $this->file("templates/$name.tmpl");
            foreach ($this->_parents as $parent) {
                if ($tmp = $parent->_findFile("templates/$name.tmpl", 1))
                    return $this->_path . $tmp;
            }
            trigger_error("$f1 not found", E_USER_ERROR);
            return false;
        }
    }

    /**
     * Add a random header element to head
     * TODO: first css, then js. Maybe seperate it into addJSHeaders/addCSSHeaders
     * or use an optional type argument, and seperate it within _MoreHeaders[]
     */
    //$GLOBALS['request']->_MoreHeaders = array();
    function addMoreHeaders ($element) {
        $GLOBALS['request']->_MoreHeaders[] = $element;
        if (!empty($this->_headers_printed) and $this->_headers_printed) {
        trigger_error(_("Some action(page) wanted to add more headers, but they were already printed.")
              ."\n". $element->asXML(),
                           E_USER_NOTICE);
        }
    }

    /**
      * Singleton. Only called once, by the head template. See the warning above.
      */
    function getMoreHeaders () {
        global $request;
        // actionpages cannot add headers, because recursive template expansion
        // already expanded the head template before.
        $this->_headers_printed = 1;
        if (empty($request->_MoreHeaders))
            return '';
        $out = '';
        if (false and ($file = $this->_findData('delayed.js'))) {
            $request->_MoreHeaders[] = JavaScript('
// Add a script element as a child of the body
function downloadJSAtOnload() {
var element = document.createElement("script");
element.src = "' . $file . '";
document.body.appendChild(element);
}
// Check for browser support of event handling capability
if (window.addEventListener)
window.addEventListener("load", downloadJSAtOnload, false);
else if (window.attachEvent)
window.attachEvent("onload", downloadJSAtOnload);
else window.onload = downloadJSAtOnload;');
        }
        //$out = "<!-- More Headers -->\n";
        foreach ($request->_MoreHeaders as $h) {
            if (is_object($h))
                $out .= $h->printXML();
            else
                $out .= "$h\n";
        }
        return $out;
    }

    //$GLOBALS['request']->_MoreAttr = array();
    // new arg: named elements to be able to remove them. such as DoubleClickEdit for htmldumps
    function addMoreAttr ($tag, $name, $element) {
        global $request;
        // protect from duplicate attr (body jscript: themes, prefs, ...)
        static $_attr_cache = array();
        $hash = md5($tag."/".$element);
        if (!empty($_attr_cache[$hash])) return;
        $_attr_cache[$hash] = 1;

        if (empty($request->_MoreAttr) or !is_array($request->_MoreAttr[$tag]))
            $request->_MoreAttr[$tag] = array($name => $element);
        else
            $request->_MoreAttr[$tag][$name] = $element;
    }

    function getMoreAttr ($tag) {
        global $request;
        if (empty($request->_MoreAttr[$tag]))
            return '';
        $out = '';
        foreach ($request->_MoreAttr[$tag] as $name => $element) {
            if (is_object($element))
                $out .= $element->printXML();
            else
                $out .= "$element";
        }
        return $out;
    }

    /**
     * Common Initialisations
     */

    /**
     * The ->load() method replaces the formerly global code in themeinfo.php.
     * This is run only once for the selected theme, and not for the parent themes.
     * Without this you would not be able to derive from other themes.
     */
    function load() {

        $this->initGlobals();

    // CSS file defines fonts, colors and background images for this
    // style.  The companion '*-heavy.css' file isn't defined, it's just
    // expected to be in the same directory that the base style is in.

    // This should result in phpwiki-printer.css being used when
    // printing or print-previewing with style "PhpWiki" or "MacOSX" selected.
    $this->setDefaultCSS('PhpWiki',
                 array(''      => 'phpwiki.css',
                   'print' => 'phpwiki-printer.css'));

    // This allows one to manually select "Printer" style (when browsing page)
    // to see what the printer style looks like.
    $this->addAlternateCSS(_("Printer"), 'phpwiki-printer.css', 'print, screen');
    $this->addAlternateCSS(_("Top & bottom toolbars"), 'phpwiki-topbottombars.css');
    $this->addAlternateCSS(_("Modern"), 'phpwiki-modern.css');

    if (isBrowserIE()) {
        $this->addMoreHeaders($this->_CSSlink(0,
                          $this->_findFile('IEFixes.css'),'all'));
        $this->addMoreHeaders("\n");
    }

    /**
     * The logo image appears on every page and links to the HomePage.
     */
    $this->addImageAlias('logo', WIKI_NAME . 'Logo.png');

    $this->addImageAlias('search', 'search.png');

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

    $this->setButtonSeparator("\n | ");

    /**
     * WikiWords can automatically be split by inserting spaces between
     * the words. The default is to leave WordsSmashedTogetherLikeSo.
     */
    $this->setAutosplitWikiWords(false);

    /**
     * Layout improvement with dangling links for mostly closed wiki's:
     * If false, only users with edit permissions will be presented the
     * special wikiunknown class with "?" and Tooltip.
     * If true (default), any user will see the ?, but will be presented
     * the PrintLoginForm on a click.
     */
    //$this->setAnonEditUnknownLinks(false);

    /*
     * You may adjust the formats used for formatting dates and times
     * below.  (These examples give the default formats.)
     * Formats are given as format strings to PHP strftime() function See
     * http://www.php.net/manual/en/function.strftime.php for details.
     * Do not include the server's zone (%Z), times are converted to the
     * user's time zone.
     *
     * Suggestion for french:
     *   $this->setDateFormat("%A %e %B %Y");
     *   $this->setTimeFormat("%H:%M:%S");
     * Suggestion for capable php versions, using the server locale:
     *   $this->setDateFormat("%x");
     *   $this->setTimeFormat("%X");
     */
    //$this->setDateFormat("%B %d, %Y");
    //$this->setTimeFormat("%I:%M %p");

    /*
     * To suppress times in the "Last edited on" messages, give a
     * give a second argument of false:
     */
    //$this->setDateFormat("%B %d, %Y", false);


    /**
     * Custom UserPreferences:
     * A list of name => _UserPreference class pairs.
     * Rationale: Certain themes should be able to extend the predefined list
     * of preferences. Display/editing is done in the theme specific userprefs.tmpl
     * but storage/sanification/update/... must be extended to the Get/SetPreferences methods.
     * See themes/wikilens/themeinfo.php
     */
    //$this->customUserPreference();

    /**
     * Register custom PageList type and define custom PageList classes.
     * Rationale: Certain themes should be able to extend the predefined list
     * of pagelist types. E.g. certain plugins, like MostPopular might use
     * info=pagename,hits,rating
     * which displays the rating column whenever the wikilens theme is active.
     * See themes/wikilens/themeinfo.php
     */
    //$this->addPageListColumn();

    } // end of load

    /**
     * Custom UserPreferences:
     * A list of name => _UserPreference class pairs.
     * Rationale: Certain themes should be able to extend the predefined list
     * of preferences. Display/editing is done in the theme specific userprefs.tmpl
     * but storage/sanification/update/... must be extended to the Get/SetPreferences methods.
     * These values are just ignored if another theme is used.
     */
    function customUserPreferences($array) {
        global $customUserPreferenceColumns; // FIXME: really a global?
        if (empty($customUserPreferenceColumns)) $customUserPreferenceColumns = array();
        //array('wikilens' => new _UserPreference_wikilens());
        foreach ($array as $field => $prefobj) {
            $customUserPreferenceColumns[$field] = $prefobj;
        }
    }

    /** addPageListColumn(array('rating' => new _PageList_Column_rating('rating', _("Rate"))))
     *  Register custom PageList types for special themes, like
     *  'rating' for wikilens
     */
    function addPageListColumn ($array) {
        global $customPageListColumns;
        if (empty($customPageListColumns)) $customPageListColumns = array();
        foreach ($array as $column => $obj) {
            $customPageListColumns[$column] = $obj;
        }
    }

    function initGlobals() {
        global $request;
    static $already = 0;
        if (!$already) {
            $script_url = deduce_script_name();
            if ((DEBUG & _DEBUG_REMOTE) and isset($_GET['start_debug']))
                $script_url .= ("?start_debug=".$_GET['start_debug']);
            $folderArrowPath = dirname($this->_findData('images/folderArrowLoading.gif'));
            $pagename = $request->getArg('pagename');
            $js = "var data_path = '". javascript_quote_string(DATA_PATH) ."';\n"
                // XSS warning with pagename
                ."var pagename  = '". javascript_quote_string($pagename) ."';\n"
                ."var script_url= '". javascript_quote_string($script_url) ."';\n"
                ."var stylepath = data_path+'/".javascript_quote_string($this->_theme)."/';\n"
                ."var folderArrowPath = '".javascript_quote_string($folderArrowPath)."';\n"
                ."var use_path_info = " . (USE_PATH_INFO ? "true" : "false") .";\n";
            $this->addMoreHeaders(JavaScript($js));
        $already = 1;
        }
    }

    // Works only on action=browse. Patch #970004 by pixels
    // Usage: call $WikiTheme->initDoubleClickEdit() from theme init or
    // define ENABLE_DOUBLECLICKEDIT
    function initDoubleClickEdit() {
        if (!$this->HTML_DUMP_SUFFIX)
            $this->addMoreAttr('body', 'DoubleClickEdit', HTML::Raw(" ondblclick=\"url = document.URL; url2 = url; if (url.indexOf('?') != -1) url2 = url.slice(0, url.indexOf('?')); if ((url.indexOf('action') == -1) || (url.indexOf('action=browse') != -1)) document.location = url2 + '?action=edit';\""));
    }

    // Immediate title search results via XMLHTML(HttpRequest)
    // by Bitflux GmbH, bitflux.ch. You need to install the livesearch.js seperately.
    // Google's or acdropdown is better.
    function initLiveSearch() {
    //subclasses of Sidebar will init this twice
    static $already = 0;
        if (!$this->HTML_DUMP_SUFFIX and !$already) {
            $this->addMoreAttr('body', 'LiveSearch',
                               HTML::Raw(" onload=\"liveSearchInit()"));
            $this->addMoreHeaders(JavaScript('var liveSearchURI="'
                                             .WikiURL(_("TitleSearch"),false,true).'";'));
            $this->addMoreHeaders(JavaScript('', array
                                             ('src' => $this->_findData('livesearch.js'))));
        $already = 1;
        }
    }

    // Immediate title search results via XMLHttpRequest
    // using the shipped moacdropdown js-lib
    function initMoAcDropDown() {
    //subclasses of Sidebar will init this twice
    static $already = 0;
        if (!$this->HTML_DUMP_SUFFIX and !$already) {
            $dir = $this->_findData('moacdropdown');
            if (!DEBUG and ($css = $this->_findFile('moacdropdown/css/dropdown-min.css'))) {
                $this->addMoreHeaders($this->_CSSlink(0, $css, 'all'));
            } else {
                $this->addMoreHeaders(HTML::style(array('type' => 'text/css'), "  @import url( $dir/css/dropdown.css );\n"));
            }
            // if autocomplete_remote is used: (getobject2 also for calc. the showlist width)
        if (DEBUG) {
        foreach (array("mobrowser.js","modomevent3.js","modomt.js",
                   "modomext.js","getobject2.js","xmlextras.js") as $js)
        {
            $this->addMoreHeaders(JavaScript('', array('src' => "$dir/js/$js")));
        }
        $this->addMoreHeaders(JavaScript('', array('src' => "$dir/js/acdropdown.js")));
        } else {
                // already in wikicommon-min.js
        ; //$this->addMoreHeaders(JavaScript('', array('src' => DATA_PATH . "/themes/default/moacdropdown.js")));
        }
        /*
        // for local xmlrpc requests
        $xmlrpc_url = deduce_script_name();
        //if (1 or DATABASE_TYPE == 'dba')
        $xmlrpc_url = DATA_PATH . "/RPC2.php";
        if ((DEBUG & _DEBUG_REMOTE) and isset($_GET['start_debug']))
        $xmlrpc_url .= ("?start_debug=".$_GET['start_debug']);
            $this->addMoreHeaders(JavaScript("var xmlrpc_url = '$xmlrpc_url'"));
        */
        $already = 1;
        }
    }

    function calendarLink($date = false) {
        return $this->calendarBase() . SUBPAGE_SEPARATOR .
               strftime("%Y-%m-%d", $date ? $date : time());
    }

    function calendarBase() {
        static $UserCalPageTitle = false;
        global $request;

        if (!$UserCalPageTitle)
            $UserCalPageTitle = $request->_user->getId() .
                                SUBPAGE_SEPARATOR . _("Calendar");
        if (!$UserCalPageTitle)
            $UserCalPageTitle = (BLOG_EMPTY_DEFAULT_PREFIX ? ''
                                 : ($request->_user->getId() . SUBPAGE_SEPARATOR)) . "Blog";
        return $UserCalPageTitle;
    }

    function calendarInit($force = false) {
        $dbi = $GLOBALS['request']->getDbh();
        // display flat calender dhtml in the sidebar
        if ($force or $dbi->isWikiPage($this->calendarBase())) {
            $jslang = @$GLOBALS['LANG'];
            $this->addMoreHeaders
                (
                 $this->_CSSlink(0,
                                 $this->_findFile('jscalendar/calendar-phpwiki.css'), 'all'));
            $this->addMoreHeaders
                (JavaScript('',
                            array('src' => $this->_findData('jscalendar/calendar'.(DEBUG?'':'_stripped').'.js'))));
            if (!($langfile = $this->_findData("jscalendar/lang/calendar-$jslang.js")))
                $langfile = $this->_findData("jscalendar/lang/calendar-en.js");
            $this->addMoreHeaders(JavaScript('',array('src' => $langfile)));
            $this->addMoreHeaders
                (JavaScript('',
                            array('src' =>
                                  $this->_findData('jscalendar/calendar-setup'.(DEBUG?'':'_stripped').'.js'))));

            // Get existing date entries for the current user
            require_once("lib/TextSearchQuery.php");
            $iter = $dbi->titleSearch(new TextSearchQuery("^".$this->calendarBase().SUBPAGE_SEPARATOR, true, "auto"));
            $existing = array();
            while ($page = $iter->next()) {
                if ($page->exists())
                    $existing[] = basename($page->_pagename);
            }
            if (!empty($existing)) {
                $js_exist = '{"'.join('":1,"',$existing).'":1}';
                //var SPECIAL_DAYS = {"2004-05-11":1,"2004-05-12":1,"2004-06-01":1}
                $this->addMoreHeaders(JavaScript('
/* This table holds the existing calender entries for the current user
 *  calculated from the database
 */

var SPECIAL_DAYS = '.javascript_quote_string($js_exist).';

/* This function returns true if the date exists in SPECIAL_DAYS */
function dateExists(date, y, m, d) {
    var year = date.getFullYear();
    m = m + 1;
    m = m < 10 ? "0" + m : m;  // integer, 0..11
    d = d < 10 ? "0" + d : d;  // integer, 1..31
    var date = year+"-"+m+"-"+d;
    var exists = SPECIAL_DAYS[date];
    if (!exists) return false;
    else return true;
}
// This is the actual date status handler.
// Note that it receives the date object as well as separate
// values of year, month and date.
function dateStatusFunc(date, y, m, d) {
    if (dateExists(date, y, m, d)) return "existing";
    else return false;
}
'));
            }
            else {
                $this->addMoreHeaders(JavaScript('
function dateStatusFunc(date, y, m, d) { return false;}'));
            }
        }
    }

    ////////////////////////////////////////////////////////////////
    //
    // Events
    //
    ////////////////////////////////////////////////////////////////

    /**  CbUserLogin (&$request, $userid)
     * Callback when a user logs in
    */
    function CbUserLogin (&$request, $userid) {
    ; // do nothing
    }

    /** CbNewUserEdit (&$request, $userid)
     * Callback when a new user creates or edits a page
     */
    function CbNewUserEdit (&$request, $userid) {
    ; // i.e. create homepage with Template/UserPage
    }

    /** CbNewUserLogin (&$request, $userid)
     * Callback when a "new user" logs in.
     *  What is new? We only record changes, not logins.
     *  Should we track user actions?
     *  Let's say a new user is a user without homepage.
     */
    function CbNewUserLogin (&$request, $userid) {
    ; // do nothing
    }

    /** CbUserLogout (&$request, $userid)
     * Callback when a user logs out
     */
    function CbUserLogout (&$request, $userid) {
    ; // do nothing
    }

};


/**
 * A class representing a clickable "button".
 *
 * In it's simplest (default) form, a "button" is just a link associated
 * with some sort of wiki-action.
 */
class Button extends HtmlElement {
    /** Constructor
     *
     * @param $text string The text for the button.
     * @param $url string The url (href) for the button.
     * @param $class string The CSS class for the button.
     * @param $options array Additional attributes for the &lt;input&gt; tag.
     */
    function Button ($text, $url, $class=false, $options=false) {
        global $request;
        //php5 workaround
        if (check_php_version(5)) {
            $this->_init('a', array('href' => $url));
        } else {
            $this->__construct('a', array('href' => $url));
        }
        if ($class)
            $this->setAttr('class', $class);
        if ($request->getArg('frame'))
            $this->setAttr('target', '_top');
        if (!empty($options) and is_array($options)) {
            foreach ($options as $key => $val)
                $this->setAttr($key, $val);
        }
        // Google honors this
        if (in_array(strtolower($text), array('edit','create','diff','pdf'))
            and !$request->_user->isAuthenticated())
            $this->setAttr('rel', 'nofollow');
        $this->pushContent($GLOBALS['WikiTheme']->maybeSplitWikiWord($text));
    }

};


/**
 * A clickable image button.
 */
class ImageButton extends Button {
    /** Constructor
     *
     * @param $text string The text for the button.
     * @param $url string The url (href) for the button.
     * @param $class string The CSS class for the button.
     * @param $img_url string URL for button's image.
     * @param $img_attr array Additional attributes for the &lt;img&gt; tag.
     */
    function ImageButton ($text, $url, $class, $img_url, $img_attr=false) {
        $this->__construct('a', array('href' => $url));
        if ($class)
            $this->setAttr('class', $class);
        // Google honors this
        if (in_array(strtolower($text), array('edit','create','diff','pdf'))
            and !$GLOBALS['request']->_user->isAuthenticated())
            $this->setAttr('rel', 'nofollow');

        if (!is_array($img_attr))
            $img_attr = array();
        $img_attr['src'] = $img_url;
        $img_attr['alt'] = $text;
        $img_attr['class'] = 'wiki-button';
        $this->pushContent(HTML::img($img_attr));
    }
};

/**
 * A class representing a form <samp>submit</samp> button.
 */
class SubmitButton extends HtmlElement {
    /** Constructor
     *
     * @param $text string The text for the button.
     * @param $name string The name of the form field.
     * @param $class string The CSS class for the button.
     * @param $options array Additional attributes for the &lt;input&gt; tag.
     */
    function SubmitButton ($text, $name=false, $class=false, $options=false) {
        $this->__construct('input', array('type' => 'submit',
                                          'value' => $text));
        if ($name)
            $this->setAttr('name', $name);
        if ($class)
            $this->setAttr('class', $class);
        if (!empty($options)) {
            foreach ($options as $key => $val)
                $this->setAttr($key, $val);
        }
    }

};


/**
 * A class representing an image form <samp>submit</samp> button.
 */
class SubmitImageButton extends SubmitButton {
    /** Constructor
     *
     * @param $text string The text for the button.
     * @param $name string The name of the form field.
     * @param $class string The CSS class for the button.
     * @param $img_url string URL for button's image.
     * @param $img_attr array Additional attributes for the &lt;img&gt; tag.
     */
    function SubmitImageButton ($text, $name=false, $class=false, $img_url, $img_attr=false) {
        $this->__construct('input', array('type'  => 'image',
                                          'src'   => $img_url,
                                          'value' => $text,
                                          'alt'   => $text));
        if ($name)
            $this->setAttr('name', $name);
        if ($class)
            $this->setAttr('class', $class);
        if (!empty($img_attr)) {
            foreach ($img_attr as $key => $val)
                $this->setAttr($key, $val);
        }
    }

};

/**
 * A sidebar box with title and body, narrow fixed-width.
 * To represent abbrevated content of plugins, links or forms,
 * like "Getting Started", "Search", "Sarch Pagename",
 * "Login", "Menu", "Recent Changes", "Last comments", "Last Blogs"
 * "Calendar"
 * ... See http://tikiwiki.org/
 *
 * Usage:
 * sidebar.tmpl:
 *   $menu = SidebarBox("Menu",HTML::dl(HTML::dt(...))); $menu->format();
 *   $menu = PluginSidebarBox("RecentChanges",array('limit'=>10)); $menu->format();
 */
class SidebarBox {

    function SidebarBox($title, $body) {
        require_once('lib/WikiPlugin.php');
        $this->title = $title;
        $this->body = $body;
    }
    function format() {
        return WikiPlugin::makeBox($this->title, $this->body);
    }
}

/**
 * A sidebar box for plugins.
 * Any plugin may provide a box($args=false, $request=false, $basepage=false)
 * method, with the help of WikiPlugin::makeBox()
 */
class PluginSidebarBox extends SidebarBox {

    var $_plugin, $_args = false, $_basepage = false;

    function PluginSidebarBox($name, $args = false, $basepage = false) {
    require_once("lib/WikiPlugin.php");

        $loader = new WikiPluginLoader();
        $plugin = $loader->getPlugin($name);
        if (!$plugin) {
            return $loader->_error(sprintf(_("Plugin %s: undefined"),
                                          $name));
        }/*
        if (!method_exists($plugin, 'box')) {
            return $loader->_error(sprintf(_("%s: has no box method"),
                                           get_class($plugin)));
        }*/
        $this->_plugin   =& $plugin;
        $this->_args     = $args ? $args : array();
        $this->_basepage = $basepage;
    }

    function format($args = false) {
        return $this->_plugin->box($args ? array_merge($this->_args, $args) : $this->_args,
                                   $GLOBALS['request'],
                                   $this->_basepage);
    }
}

// Various boxes which are no plugins
class RelatedLinksBox extends SidebarBox {
    function RelatedLinksBox($title = false, $body = '', $limit = 20) {
        global $request;
        $this->title = $title ? $title : _("Related Links");
        $this->body = HTML($body);
        $page = $request->getPage($request->getArg('pagename'));
        $revision = $page->getCurrentRevision();
        $page_content = $revision->getTransformedContent();
        //$cache = &$page->_wikidb->_cache;
        $counter = 0;
        $sp = HTML::Raw('&middot; ');
        foreach ($page_content->getWikiPageLinks() as $link) {
            $linkto = $link['linkto'];
            if (!$request->_dbi->isWikiPage($linkto)) continue;
            $this->body->pushContent($sp, WikiLink($linkto), HTML::br());
            $counter++;
            if ($limit and $counter > $limit) continue;
        }
    }
}

class RelatedExternalLinksBox extends SidebarBox {
    function RelatedExternalLinksBox($title = false, $body = '', $limit = 20) {
        global $request;
        $this->title = $title ? $title : _("External Links");
        $this->body = HTML($body);
        $page = $request->getPage($request->getArg('pagename'));
        $cache = &$page->_wikidb->_cache;
        $counter = 0;
        $sp = HTML::Raw('&middot; ');
        foreach ($cache->getWikiPageLinks() as $link) {
            $linkto = $link['linkto'];
            if ($linkto) {
                $this->body->pushContent($sp, WikiLink($linkto), HTML::br());
                $counter++;
                if ($limit and $counter > $limit) continue;
            }
        }
    }
}

function listAvailableThemes() {
    $available_themes = array();
    $dir_root = 'themes';
    if (defined('PHPWIKI_DIR'))
        $dir_root = PHPWIKI_DIR . "/$dir_root";
    $dir = dir($dir_root);
    if ($dir) {
        while($entry = $dir->read()) {
            if (is_dir($dir_root.'/'.$entry)
                and file_exists($dir_root.'/'.$entry.'/themeinfo.php'))
            {
                array_push($available_themes, $entry);
            }
        }
        $dir->close();
    }
    return $available_themes;
}

function listAvailableLanguages() {
    $available_languages = array('en');
    $dir_root = 'locale';
    if (defined('PHPWIKI_DIR'))
        $dir_root = PHPWIKI_DIR . "/$dir_root";
    if ($dir = dir($dir_root)) {
        while($entry = $dir->read()) {
            if (is_dir($dir_root."/".$entry) and is_dir($dir_root.'/'.$entry.'/LC_MESSAGES'))
            {
                array_push($available_languages, $entry);
            }
        }
        $dir->close();
    }
    return $available_languages;
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
