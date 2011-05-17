<?php // -*-php-*-
// rcs_id('$Id: RecentChanges.php 7664 2010-08-31 15:42:34Z vargenau $');
/**
 * Copyright 1999,2000,2001,2002,2007 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2009 Marc-Etienne Vargenau, Alcatel-Lucent
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

include_once("lib/WikiPlugin.php");

class _RecentChanges_Formatter
{
    var $_absurls = false;
    var $action = "RecentChanges";

    function _RecentChanges_Formatter ($rc_args) {
        $this->_args = $rc_args;
        $this->_diffargs = array('action' => 'diff');

        if ($rc_args['show_minor'] || !$rc_args['show_major'])
            $this->_diffargs['previous'] = 'minor';

        // PageHistoryPlugin doesn't have a 'daylist' arg.
        if (!isset($this->_args['daylist']))
            $this->_args['daylist'] = false;
    }

    function title () {
        global $request;
        extract($this->_args);
        if ($author) {
            $title = $author;
            if ($title == '[]') {
                $title = $request->_user->getID();
            }
            $title = _("UserContribs").": $title";
        } elseif ($owner) {
            $title = $owner;
            if ($title == '[]') {
                $title = $request->_user->getID();
            }
            $title = _("UserContribs").": $title";
        } elseif ($only_new) {
            $title = _("RecentNewPages");
        } elseif ($show_minor) {
            $title = _("RecentEdits");
        } else $title = _("RecentChanges");

        if (!empty($category))
            $title = $category;
        elseif (!empty($pagematch))
            $title .= ":$pagematch";
        return $title;
    }

    function include_versions_in_URLs() {
        return (bool) $this->_args['show_all'];
    }

    function date ($rev) {
        global $WikiTheme;
        return $WikiTheme->getDay($rev->get('mtime'));
    }

    function time ($rev) {
        global $WikiTheme;
        return $WikiTheme->formatTime($rev->get('mtime'));
    }

    function diffURL ($rev) {
        $args = $this->_diffargs;
        if ($this->include_versions_in_URLs())
            $args['version'] = $rev->getVersion();
        $page = $rev->getPage();
        return WikiURL($page->getName(), $args, $this->_absurls);
    }

    function historyURL ($rev) {
        $page = $rev->getPage();
        return WikiURL($page, array('action' => _("PageHistory")),
                       $this->_absurls);
    }

    function pageURL ($rev) {
        return WikiURL($this->include_versions_in_URLs() ? $rev : $rev->getPage(),
                       '', $this->_absurls);
    }

    function authorHasPage ($author) {
        global $WikiNameRegexp, $request;
        $dbi = $request->getDbh();
        return isWikiWord($author) && $dbi->isWikiPage($author);
    }

    function authorURL ($author) {
        return $this->authorHasPage() ? WikiURL($author) : false;
    }


    function status ($rev) {
        if ($rev->hasDefaultContents())
            return 'deleted';
        $page = $rev->getPage();
        $prev = $page->getRevisionBefore($rev->getVersion());
        if ($prev->hasDefaultContents())
            return 'new';
        return 'updated';
    }

    function importance ($rev) {
        return $rev->get('is_minor_edit') ? 'minor' : 'major';
    }

    function summary($rev) {
        if ( ($summary = $rev->get('summary')) )
            return $summary;

        switch ($this->status($rev)) {
            case 'deleted':
                return _("Deleted");
            case 'new':
                return _("New page");
            default:
                return '';
        }
    }

    function setValidators($most_recent_rev) {
        $rev = $most_recent_rev;
        $validators = array('RecentChanges-top' =>
                            array($rev->getPageName(), $rev->getVersion()),
                            '%mtime' => $rev->get('mtime'));
        global $request;
        $request->appendValidators($validators);
    }
}

class _RecentChanges_HtmlFormatter
extends _RecentChanges_Formatter
{
    function diffLink ($rev) {
        global $WikiTheme;
        $button = $WikiTheme->makeButton(_("diff"), $this->diffURL($rev), 'wiki-rc-action');
        $button->setAttr('rel', 'nofollow');
        return HTML("(",$button,")");
    }

    /* deletions: red, additions: green */
    function diffSummary ($rev) {
        $html = $this->diffURL($rev);
        return '';
    }

    function historyLink ($rev) {
        global $WikiTheme;
        $button = $WikiTheme->makeButton(_("hist"), $this->historyURL($rev), 'wiki-rc-action');
        $button->setAttr('rel', 'nofollow');
        return HTML("(",$button,")");
    }

    function pageLink ($rev, $link_text=false) {

        return WikiLink($this->include_versions_in_URLs() ? $rev : $rev->getPage(),
                        'auto', $link_text);
        /*
        $page = $rev->getPage();
        global $WikiTheme;
        if ($this->include_versions_in_URLs()) {
            $version = $rev->getVersion();
            if ($rev->isCurrent())
                $version = false;
            $exists = !$rev->hasDefaultContents();
        }
        else {
            $version = false;
            $cur = $page->getCurrentRevision();
            $exists = !$cur->hasDefaultContents();
        }
        if ($exists)
            return $WikiTheme->linkExistingWikiWord($page->getName(), $link_text, $version);
        else
            return $WikiTheme->linkUnknownWikiWord($page->getName(), $link_text);
        */
    }

    function authorLink ($rev) {
        return WikiLink($rev->get('author'), 'if_known');
    }

    /* Link to all users contributions (contribs and owns) */
    function authorContribs ($rev) {
        $author = $rev->get('author');
        if (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $author)) return '';
        return HTML('(',
                    Button(array('action' => _("RecentChanges"),
                                 'format' => 'contribs',
                                 'author' => $author,
                                 'days' => 360),
                           _("contribs"),
                           $author),
                    ' | ',
                    Button(array('action' => _("RecentChanges"),
                                 'format' => 'contribs',
                                 'owner' => $author,
                                 'days' => 360),
                           _("new pages"),
                           $author),
                    ')');
    }

    function summaryAsHTML ($rev) {
        if ( !($summary = $this->summary($rev)) )
            return '';
        return  HTML::span( array('class' => 'wiki-summary'),
                            "(",
                            // TransformLinks($summary, $rev->get('markup'), $rev->getPageName()),
                            // We do parse the summary:
                            // 1) if the summary contains {{foo}}, the template must no be
                            //    expanded
                            // 2) if the summary contains camel case, and DISABLE_MARKUP_WIKIWORD
                            //    is true, the camel case must not be linked.
                            // Side-effect: brackets are not linked. TBD.
                            $summary,
                            ")");
    }

    function format_icon ($format, $filter = array()) {
        global $request, $WikiTheme;
        $args = $this->_args;
        // remove links not used for those formats
        unset($args['daylist']);
        unset($args['difflinks']);
        unset($args['historylinks']);
        $rss_url = $request->getURLtoSelf
                (array_merge($args,
                             array('action' => $this->action, 'format' => $format),
                             $filter));
        return $WikiTheme->makeButton($format, $rss_url, 'rssicon');
    }

    function rss_icon ($args=array())  { return $this->format_icon("rss", $args); }
    function rss2_icon ($args=array()) { return $this->format_icon("rss2", $args); }
    function atom_icon ($args=array()) { return $this->format_icon("atom", $args); }
    function rdf_icon ($args=array())  { return DEBUG ? $this->format_icon("rdf", $args) : ''; }
    function rdfs_icon ($args=array()) { return DEBUG ? $this->format_icon("rdfs", $args) : ''; }
    function owl_icon ($args=array())  { return DEBUG ? $this->format_icon("owl", $args) : ''; }

    function grazr_icon ($args = array()) {
        global $request, $WikiTheme;
        if (is_localhost()) return '';
        if (SERVER_PROTOCOL == "https") return '';
        $our_url = WikiURL($request->getArg('pagename'),
                    array_merge(array('action' => $this->action, 'format' => 'rss2'), $args),
                    true);
        $rss_url = 'http://grazr.com/gzpanel.html?' . $our_url;
        return $WikiTheme->makeButton("grazr", $rss_url, 'rssicon');
    }

    function pre_description () {
        extract($this->_args);
        // FIXME: say something about show_all.
        if ($show_major && $show_minor)
            $edits = _("edits");
        elseif ($show_major)
            $edits = _("major edits");
        else
            $edits = _("minor edits");
        if (isset($caption) and $caption == _("Recent Comments"))
            $edits = _("comments");
        if (!empty($only_new)) {
            $edits = _("created new pages");
        }
        if (!empty($author)) {
            global $request;
            if ($author == '[]')
                $author = $request->_user->getID();
            $edits .= sprintf(_(" for pages changed by %s"), $author);
        }
        if (!empty($owner)) {
            global $request;
            if ($owner == '[]')
                $owner = $request->_user->getID();
            $edits .= sprintf(_(" for pages owned by %s"), $owner);
        }
        if (!empty($category)) {
            $edits .= sprintf(_(" for all pages linking to %s"), $category);
        }
        if (!empty($pagematch)) {
            $edits .= sprintf(_(" for all pages matching '%s'"), $pagematch);
        }
        if ($timespan = $days > 0) {
            if (intval($days) != $days)
                $days = sprintf("%.1f", $days);
        }
        $lmt = abs($limit);
        /**
         * Depending how this text is split up it can be tricky or
         * impossible to translate with good grammar. So the seperate
         * strings for 1 day and %s days are necessary in this case
         * for translating to multiple languages, due to differing
         * overlapping ideal word cutting points.
         *
         * en: day/days "The %d most recent %s [during (the past] day) are listed below."
         * de: 1 Tag    "Die %d jüngste %s [innerhalb (von des letzten] Tages) sind unten aufgelistet."
         * de: %s days  "Die %d jüngste %s [innerhalb (von] %s Tagen) sind unten aufgelistet."
         *
         * en: day/days "The %d most recent %s during [the past] (day) are listed below."
         * fr: 1 jour   "Les %d %s les plus récentes pendant [le dernier (d'une] jour) sont énumérées ci-dessous."
         * fr: %s jours "Les %d %s les plus récentes pendant [les derniers (%s] jours) sont énumérées ci-dessous."
         */
        if ($limit > 0) {
            if ($timespan) {
                if (intval($days) == 1)
                    $desc = fmt("The %d most recent %s during the past day are listed below.",
                                $limit, $edits);
                else
                    $desc = fmt("The %d most recent %s during the past %s days are listed below.",
                                $limit, $edits, $days);
            } else
                $desc = fmt("The %d most recent %s are listed below.",
                            $limit, $edits);
        }
        elseif ($limit < 0) {  //$limit < 0 means we want oldest pages
            if ($timespan) {
                if (intval($days) == 1)
                    $desc = fmt("The %d oldest %s during the past day are listed below.",
                                $lmt, $edits);
                else
                    $desc = fmt("The %d oldest %s during the past %s days are listed below.",
                                $lmt, $edits, $days);
            } else
                $desc = fmt("The %d oldest %s are listed below.",
                            $lmt, $edits);
        }

        else {
            if ($timespan) {
                if (intval($days) == 1)
                    $desc = fmt("The most recent %s during the past day are listed below.",
                                $edits);
                else
                    $desc = fmt("The most recent %s during the past %s days are listed below.",
                                $edits, $days);
            } else
                $desc = fmt("All %s are listed below.", $edits);
        }
        return $desc;
    }

    function description() {
        return HTML::p(false, $this->pre_description());
    }

    /* was title */
    function headline () {
        extract($this->_args);
        return array($this->title(),
                     ' ',
                     $this->rss_icon(),
                     $this->rss2_icon(),
                     $this->atom_icon(),
                     $this->rdf_icon(),
                     /*$this->rdfs_icon(),
                       $this->owl_icon(),*/
                     $this->grazr_icon(),
                     $this->sidebar_link());
    }

    function empty_message() {
        if (isset($this->_args['caption']) and $this->_args['caption'] == _("Recent Comments"))
            return _("No comments found");
        else
            return _("No changes found");
    }

    function sidebar_link() {
        extract($this->_args);
        $pagetitle = $show_minor ? _("RecentEdits") : _("RecentChanges");

        global $request;
        $sidebarurl = WikiURL($pagetitle, array('format' => 'sidebar'), 'absurl');

        $addsidebarjsfunc =
            "function addPanel() {\n"
            ."    window.sidebar.addPanel (\"" . sprintf("%s - %s", WIKI_NAME, $pagetitle) . "\",\n"
            ."       \"$sidebarurl\",\"\");\n"
            ."}\n";
        $jsf = JavaScript($addsidebarjsfunc);

        global $WikiTheme;
        $sidebar_button = $WikiTheme->makeButton("sidebar", 'javascript:addPanel();', 'sidebaricon',
                                                 array('title' => _("Click to add this feed to your sidebar"),
                                                       'style' => 'font-size:9pt;font-weight:normal; vertical-align:middle;'));
        $addsidebarjsclick = asXML($sidebar_button);
        $jsc = JavaScript("if ((typeof window.sidebar == 'object') &&\n"
                                ."    (typeof window.sidebar.addPanel == 'function'))\n"
                                ."   {\n"
                                ."       document.write('$addsidebarjsclick');\n"
                                ."   }\n"
                                );
        return HTML(new RawXML("\n"), $jsf, new RawXML("\n"), $jsc);
    }

    function format ($changes) {
        include_once('lib/InlineParser.php');

        $html = HTML(HTML::h2(false, $this->headline()));
        if (($desc = $this->description()))
            $html->pushContent($desc);

        if ($this->_args['daylist']) {
            $html->pushContent(new OptionsButtonBars($this->_args));
        }

        $last_date = '';
        $lines = false;
        $first = true;

        while ($rev = $changes->next()) {
            if (($date = $this->date($rev)) != $last_date) {
                if ($lines)
                    $html->pushContent($lines);
                // for user contributions no extra date line
                $html->pushContent(HTML::h3($date));
                $lines = HTML::ul();
                $last_date = $date;

            }
            // enforce view permission
            if (mayAccessPage('view', $rev->_pagename)) {
                $lines->pushContent($this->format_revision($rev));
                if ($first)
                    $this->setValidators($rev);
                $first = false;
            }
        }
        if ($lines)
            $html->pushContent($lines);
        if ($first) {
            if ($this->_args['daylist'])
                $html->pushContent // force display of OptionsButtonBars
                    (JavaScript
                     ("document.getElementById('rc-action-body').style.display='block';"));
            $html->pushContent(HTML::p(array('class' => 'rc-empty'),
                                       $this->empty_message()));
        }

        return $html;
    }

    function format_revision ($rev) {
        global $WikiTheme;
        $args = &$this->_args;

        $class = 'rc-' . $this->importance($rev);

        $time = $this->time($rev);
        if ($rev->get('is_minor_edit')) {
            $minor_flag = HTML(" ",
                               HTML::span(array('class' => 'pageinfo-minoredit'),
                                          "(" . _("minor edit") . ")"));
        } else {
            $time = HTML::span(array('class' => 'pageinfo-majoredit'), $time);
            $minor_flag = '';
        }

        $line = HTML::li(array('class' => $class));

        if ($args['difflinks'])
            $line->pushContent($this->diffLink($rev), ' ');

        if ($args['historylinks'])
            $line->pushContent($this->historyLink($rev), ' ');

        // Do not display a link for a deleted page, just the page name
        if ($rev->hasDefaultContents()) {
            $linkorname = $rev->_pagename;
        } else {
            $linkorname = $this->pageLink($rev);
        }

        if ((isa($WikiTheme, 'WikiTheme_MonoBook')) or (isa($WikiTheme, 'WikiTheme_fusionforge'))) {
            $line->pushContent(
                               $args['historylinks'] ? '' : $this->historyLink($rev),
                               ' . . ', $linkorname, '; ',
                               $time, ' . . ',
                               $this->authorLink($rev),' ',
                               $this->authorContribs($rev),' ',
                               $this->summaryAsHTML($rev),' ',
                               $minor_flag);
        } else {
            $line->pushContent($linkorname, ' ',
                               $time, ' ',
                               $this->summaryAsHTML($rev),
                               ' ... ',
                               $this->authorLink($rev));
        }
        return $line;
    }

}

/* format=contribs: no seperation into extra dates
 * 14:41, 3 December 2006 (hist) (diff) Talk:PhpWiki (added diff link)  (top)
 */
class _RecentChanges_UserContribsFormatter
extends _RecentChanges_HtmlFormatter
{
    function headline () {
        global $request;
        extract($this->_args);
        if ($author == '[]') $author = $request->_user->getID();
        if ($owner  == '[]') $owner = $request->_user->getID();
        $author_args = $owner
            ? array('owner' => $owner)
            : array('author' => $author);
        return array(_("UserContribs"),":",$owner ? $owner : $author,
                     ' ',
                     $this->rss_icon($author_args),
                     $this->rss2_icon($author_args),
                     $this->atom_icon($author_args),
                     $this->rdf_icon($author_args),
                     $this->grazr_icon($author_args));
    }

    function format ($changes) {
        include_once('lib/InlineParser.php');

        $html = HTML(HTML::h2(false, $this->headline()));
        $lines = HTML::ol();
        $first = true; $count = 0;
        while ($rev = $changes->next()) {
            if (mayAccessPage('view', $rev->_pagename)) {
                $lines->pushContent($this->format_revision($rev));
                if ($first)
                    $this->setValidators($rev);
                $first = false;
            }
            $count++;
        }
        $this->_args['limit'] = $count;
        if (($desc = $this->description()))
            $html->pushContent($desc);
        if ($this->_args['daylist']) {
            $html->pushContent(new OptionsButtonBars($this->_args));
        }
        if ($first)
            $html->pushContent(HTML::p(array('class' => 'rc-empty'),
                                       $this->empty_message()));
        else
            $html->pushContent($lines);

        return $html;
    }

    function format_revision ($rev) {
        $args = &$this->_args;
        $class = 'rc-' . $this->importance($rev);
        $time = $this->time($rev);
        if (! $rev->get('is_minor_edit'))
            $time = HTML::span(array('class' => 'pageinfo-majoredit'), $time);

        $line = HTML::li(array('class' => $class));

        $line->pushContent($this->time($rev),", ");
        $line->pushContent($this->date($rev)," ");
        $line->pushContent($this->diffLink($rev), ' ');
        $line->pushContent($this->historyLink($rev), ' ');
        $line->pushContent($this->pageLink($rev), ' ',
                           $this->summaryAsHTML($rev));
        return $line;
    }
}

class _RecentChanges_SideBarFormatter
extends _RecentChanges_HtmlFormatter
{
    function rss_icon () {
        //omit rssicon
    }
    function rss2_icon () { }
    function headline () {
        //title click opens the normal RC or RE page in the main browser frame
        extract($this->_args);
        $titlelink = WikiLink($this->title());
        $titlelink->setAttr('target', '_content');
        return HTML($this->logo(), $titlelink);
    }
    function logo () {
        //logo click opens the HomePage in the main browser frame
        global $WikiTheme;
        $img = HTML::img(array('src' => $WikiTheme->getImageURL('logo'),
                               'align' => 'right',
                               'style' => 'height:2.5ex'
                               ));
        $linkurl = WikiLink(HOME_PAGE, false, $img);
        $linkurl->setAttr('target', '_content');
        return $linkurl;
    }

    function authorLink ($rev) {
        $author = $rev->get('author');
        if ( $this->authorHasPage($author) ) {
            $linkurl = WikiLink($author);
            $linkurl->setAttr('target', '_content'); // way to do this using parent::authorLink ??
            return $linkurl;
        } else
            return $author;
    }

    function diffLink ($rev) {
        $linkurl = parent::diffLink($rev);
        $linkurl->setAttr('target', '_content');
        $linkurl->setAttr('rel', 'nofollow');
        // FIXME: Smelly hack to get smaller diff buttons in sidebar
        $linkurl = new RawXML(str_replace('<img ', '<img style="height:2ex" ', asXML($linkurl)));
        return $linkurl;
    }
    function historyLink ($rev) {
        $linkurl = parent::historyLink($rev);
        $linkurl->setAttr('target', '_content');
        // FIXME: Smelly hack to get smaller history buttons in sidebar
        $linkurl = new RawXML(str_replace('<img ', '<img style="height:2ex" ', asXML($linkurl)));
        return $linkurl;
    }
    function pageLink ($rev) {
        $linkurl = parent::pageLink($rev);
        $linkurl->setAttr('target', '_content');
        return $linkurl;
    }
    // Overriding summaryAsHTML, because there is no way yet to
    // return summary as transformed text with
    // links setAttr('target', '_content') in Mozilla sidebar.
    // So for now don't create clickable links inside summary
    // in the sidebar, or else they target the sidebar and not the
    // main content window.
    function summaryAsHTML ($rev) {
        if ( !($summary = $this->summary($rev)) )
            return '';
        return HTML::span(array('class' => 'wiki-summary'),
                          "[",
                          /*TransformLinks(*/$summary,/* $rev->get('markup')),*/
                          "]");
    }


    function format ($changes) {
        $this->_args['daylist'] = false; //don't show day buttons in Mozilla sidebar
        $html = _RecentChanges_HtmlFormatter::format ($changes);
        $html = HTML::div(array('class' => 'wikitext'), $html);
        global $request;
        $request->discardOutput();

        printf("<?xml version=\"1.0\" encoding=\"%s\"?>\n", $GLOBALS['charset']);
        printf('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"');
        printf('  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
        printf('<html xmlns="http://www.w3.org/1999/xhtml">');

        printf("<head>\n");
        extract($this->_args);
        if (!empty($category))
            $title = $category;
        elseif (!empty($pagematch))
            $title = $pagematch;
        else
            $title = WIKI_NAME . $show_minor ? _("RecentEdits") : _("RecentChanges");
        printf("<title>" . $title . "</title>\n");
        global $WikiTheme;
        $css = $WikiTheme->getCSS();
        $css->PrintXML();
        printf("</head>\n");

        printf("<body class=\"sidebar\">\n");
        $html->PrintXML();
        echo '<a href="http://www.feedvalidator.org/check.cgi?url=http://phpwiki.org/RecentChanges?format=rss"><img src="themes/default/buttons/valid-rss.png" alt="[Valid RSS]" title="Validate the RSS feed" width="44" height="15" /></a>';
        printf("\n</body>\n");
        printf("</html>\n");

        $request->finish(); // cut rest of page processing short
    }
}

class _RecentChanges_BoxFormatter
extends _RecentChanges_HtmlFormatter
{
    function rss_icon () {
    }
    function rss2_icon () {
    }
    function headline () {
    }
    function authorLink ($rev) {
    }
    function diffLink ($rev) {
    }
    function historyLink ($rev) {
    }
    function summaryAsHTML ($rev) {
    }
    function description () {
    }
    function format ($changes) {
        include_once('lib/InlineParser.php');
        $last_date = '';
        $first = true;
        $html = HTML();
        $counter = 1;
        $sp = HTML::Raw("\n&nbsp;&middot;&nbsp;");
        while ($rev = $changes->next()) {
            // enforce view permission
            if (mayAccessPage('view',$rev->_pagename)) {
                    if ($link = $this->pageLink($rev)) // some entries may be empty
                                                       // (/Blog/.. interim pages)
                    $html->pushContent($sp, $link, HTML::br());
                if ($first)
                    $this->setValidators($rev);
                $first = false;
            }
        }
        if ($first)
            $html->pushContent(HTML::p(array('class' => 'rc-empty'),
                                       $this->empty_message()));
        return $html;
    }
}

class _RecentChanges_RssFormatter
extends _RecentChanges_Formatter
{
    var $_absurls = true;

    function time ($rev) {
        return Iso8601DateTime($rev->get('mtime'));
    }

    function pageURI ($rev) {
        return WikiURL($rev, '', 'absurl');
    }

    function format ($changes) {

        include_once('lib/RssWriter.php');
        $rss = new RssWriter;
        $rss->channel($this->channel_properties());

        if (($props = $this->image_properties()))
            $rss->image($props);
        if (($props = $this->textinput_properties()))
            $rss->textinput($props);

        $first = true;
        while ($rev = $changes->next()) {
            // enforce view permission
            if (mayAccessPage('view', $rev->_pagename)) {
                $rss->addItem($this->item_properties($rev),
                              $this->pageURI($rev));
                if ($first)
                    $this->setValidators($rev);
                $first = false;
            }
        }

        global $request;
        $request->discardOutput();
        $rss->finish();
        //header("Content-Type: application/rss+xml; charset=" . $GLOBALS['charset']);
        printf("\n<!-- Generated by PhpWiki-%s -->\n", PHPWIKI_VERSION);

        // Flush errors in comment, otherwise it's invalid XML.
        global $ErrorManager;
        if (($errors = $ErrorManager->getPostponedErrorsAsHTML()))
            printf("\n<!-- PHP Warnings:\n%s-->\n", AsXML($errors));

        $request->finish();     // NORETURN!!!!
    }

    function image_properties () {
        global $WikiTheme;

        $img_url = AbsoluteURL($WikiTheme->getImageURL('logo'));
        if (!$img_url)
            return false;

        return array('title' => WIKI_NAME,
                     'link' => WikiURL(HOME_PAGE, false, 'absurl'),
                     'url' => $img_url);
    }

    function textinput_properties () {
        return array('title' => _("Search"),
                     'description' => _("Title Search"),
                     'name' => 's',
                     'link' => WikiURL(_("TitleSearch"), false, 'absurl'));
    }

    function channel_properties () {
        global $request;

        $rc_url = WikiURL($request->getArg('pagename'), false, 'absurl');
        extract($this->_args);
        $title = WIKI_NAME;
        $description = $this->title();
        if ($category)
            $title = $category;
        elseif ($pagematch)
            $title = $pagematch;
        return array('title' => $title,
                     'link' => $rc_url,
                     'description' => $description,
                     'dc:date' => Iso8601DateTime(time()),
                     'dc:language' => $GLOBALS['LANG']);

        /* FIXME: other things one might like in <channel>:
         * sy:updateFrequency
         * sy:updatePeriod
         * sy:updateBase
         * dc:subject
         * dc:publisher
         * dc:language
         * dc:rights
         * rss091:language
         * rss091:managingEditor
         * rss091:webmaster
         * rss091:lastBuildDate
         * rss091:copyright
         */
    }

    function item_properties ($rev) {
        $page = $rev->getPage();
        $pagename = $page->getName();

        return array( 'title'           => SplitPagename($pagename),
                      'description'     => $this->summary($rev),
                      'link'            => $this->pageURL($rev),
                      'dc:date'         => $this->time($rev),
                      'dc:contributor'  => $rev->get('author'),
                      'wiki:version'    => $rev->getVersion(),
                      'wiki:importance' => $this->importance($rev),
                      'wiki:status'     => $this->status($rev),
                      'wiki:diff'       => $this->diffURL($rev),
                      'wiki:history'    => $this->historyURL($rev)
                      );
    }
}

/** explicit application/rss+xml Content-Type,
 * simplified xml structure (no namespace),
 * support for xml-rpc cloud registerProcedure (not yet)
 */
class _RecentChanges_Rss2Formatter
extends _RecentChanges_RssFormatter {

    function format ($changes) {
        include_once('lib/RssWriter2.php');
        $rss = new RssWriter2;

        $rss->channel($this->channel_properties());
        if (($props = $this->cloud_properties()))
            $rss->cloud($props);
        if (($props = $this->image_properties()))
            $rss->image($props);
        if (($props = $this->textinput_properties()))
            $rss->textinput($props);
        $first = true;
        while ($rev = $changes->next()) {
            // enforce view permission
            if (mayAccessPage('view', $rev->_pagename)) {
                $rss->addItem($this->item_properties($rev),
                              $this->pageURI($rev));
                if ($first)
                    $this->setValidators($rev);
                $first = false;
            }
        }

        global $request;
        $request->discardOutput();
        $rss->finish();
        //header("Content-Type: application/rss+xml; charset=" . $GLOBALS['charset']);
        printf("\n<!-- Generated by PhpWiki-%s -->\n", PHPWIKI_VERSION);
        // Flush errors in comment, otherwise it's invalid XML.
        global $ErrorManager;
        if (($errors = $ErrorManager->getPostponedErrorsAsHTML()))
            printf("\n<!-- PHP Warnings:\n%s-->\n", AsXML($errors));

        $request->finish();     // NORETURN!!!!
    }

    function channel_properties () {
        $chann_10 = parent::channel_properties();
        return array_merge($chann_10,
                           array('generator' => 'PhpWiki-'.PHPWIKI_VERSION,
                                 //<pubDate>Tue, 10 Jun 2003 04:00:00 GMT</pubDate>
                                 //<lastBuildDate>Tue, 10 Jun 2003 09:41:01 GMT</lastBuildDate>
                                 //<docs>http://blogs.law.harvard.edu/tech/rss</docs>
                                 'copyright' => COPYRIGHTPAGE_URL
                                 ));
    }

    // xml-rpc registerProcedure not yet implemented
    function cloud_properties () { return false; }
    function cloud_properties_test () {
        return array('protocol' => 'xml-rpc', // xml-rpc or soap or http-post
                     'registerProcedure' => 'wiki.rssPleaseNotify',
                     'path' => DATA_PATH.'/RPC2.php',
                     'port' => !SERVER_PORT ? '80' : (SERVER_PROTOCOL == 'https' ? '443' : '80'),
                     'domain' => SERVER_NAME);
    }
}

/** Explicit application/atom+xml Content-Type
 *  A weird, questionable format
 */
class _RecentChanges_AtomFormatter
extends _RecentChanges_RssFormatter {

    function format ($changes) {
        global $request;
        include_once('lib/RssWriter.php');
        $rss = new AtomFeed;

        // "channel" is called "feed" in atom
        $rc_url = WikiURL($request->getArg('pagename'), false, 'absurl');
        extract($this->_args);
        $title = WIKI_NAME;
        $description = $this->title();
        if ($category)
            $title = $category;
        elseif ($pagematch)
            $title = $pagematch;
        $feed_props = array('title' => $description,
                            'link' => array('rel'=>"alternate",
                                                'type'=>"text/html",
                                            'href' => $rc_url),
                            'id' => md5($rc_url),
                            'modified' => Iso8601DateTime(time()),
                            'generator' => 'PhpWiki-'.PHPWIKI_VERSION,
                            'tagline' => '');
        $rss->feed($feed_props);
        $first = true;
        while ($rev = $changes->next()) {
            // enforce view permission
            if (mayAccessPage('view', $rev->_pagename)) {
                $props = $this->item_properties($rev);
                $rss->addItem($props,
                              false,
                              $this->pageURI($rev));
                if ($first)
                    $this->setValidators($rev);
                $first = false;
            }
        }

        $request->discardOutput();
        $rss->finish();
        //header("Content-Type: application/atom; charset=" . $GLOBALS['charset']);
        printf("\n<!-- Generated by PhpWiki-%s -->\n", PHPWIKI_VERSION);
        // Flush errors in comment, otherwise it's invalid XML.
        global $ErrorManager;
        if (($errors = $ErrorManager->getPostponedErrorsAsHTML()))
            printf("\n<!-- PHP Warnings:\n%s-->\n", AsXML($errors));

        $request->finish();     // NORETURN!!!!
    }

    function item_properties ($rev) {
        $page = $rev->getPage();
        $pagename = $page->getName();
        return array( 'title'           => $pagename,
                      'link'            => array('rel' => 'alternate',
                                                 'type' => 'text/html',
                                                 'href' => $this->pageURL($rev)),
                      'summary'         => $this->summary($rev),
                      'modified'        => $this->time($rev)."Z",
                      'issued'          => $this->time($rev),
                      'created'         => $this->time($rev)."Z",
                      'author'          => new XmlElement('author', new XmlElement('name', $rev->get('author')))
                      );
    }
}

/**
 * Filter by non-empty
 */
class NonDeletedRevisionIterator extends WikiDB_PageRevisionIterator
{
    /** Constructor
     *
     * @param $revisions object a WikiDB_PageRevisionIterator.
     */
    function NonDeletedRevisionIterator ($revisions, $check_current_revision = true) {
        $this->_revisions = $revisions;
        $this->_check_current_revision = $check_current_revision;
    }

    function next () {
        while (($rev = $this->_revisions->next())) {
            if ($this->_check_current_revision) {
                $page = $rev->getPage();
                $check_rev = $page->getCurrentRevision();
            }
            else {
                $check_rev = $rev;
            }
            if (! $check_rev->hasDefaultContents())
                return $rev;
        }
        $this->free();
        return false;
    }

}

/**
 * Filter by only_new.
 * Only new created pages
 */
class NewPageRevisionIterator extends WikiDB_PageRevisionIterator
{
    /** Constructor
     *
     * @param $revisions object a WikiDB_PageRevisionIterator.
     */
    function NewPageRevisionIterator ($revisions) {
        $this->_revisions = $revisions;
    }

    function next () {
        while (($rev = $this->_revisions->next())) {
            if ($rev->getVersion() == 1)
                return $rev;
        }
        $this->free();
        return false;
    }
}

/**
 * Only pages with links to a certain category
 */
class LinkRevisionIterator extends WikiDB_PageRevisionIterator
{
    function LinkRevisionIterator ($revisions, $category) {
        $this->_revisions = $revisions;
        if (preg_match("/[\?\.\*]/", $category)) {
          $backlinkiter = $this->_revisions->_wikidb->linkSearch
            (new TextSearchQuery("*", true),
             new TextSearchQuery($category, true),
             "linkfrom");
        } else {
          $basepage = $GLOBALS['request']->getPage($category);
          $backlinkiter = $basepage->getBackLinks(true);
        }
        $this->links = array();
        foreach ($backlinkiter->asArray() as $p) {
            if (is_object($p)) $this->links[] = $p->getName();
            elseif (is_array($p)) $this->links[] = $p['pagename'];
            else $this->links[] = $p;
        }
        $backlinkiter->free();
        sort($this->links);
    }

    function next () {
        while (($rev = $this->_revisions->next())) {
            if (binary_search($rev->getName(), $this->links) != false)
                return $rev;
        }
        $this->free();
        return false;
    }

    function free () {
        unset ($this->links);
    }
}

class PageMatchRevisionIterator extends WikiDB_PageRevisionIterator
{
    function PageMatchRevisionIterator ($revisions, $match) {
        $this->_revisions = $revisions;
        $this->search = new TextSearchQuery($match, true);
    }

    function next () {
        while (($rev = $this->_revisions->next())) {
            if ($this->search->match($rev->getName()))
                return $rev;
        }
        $this->free();
        return false;
    }

    function free () {
        unset ($this->search);
    }
}

/**
 * Filter by author
 */
class AuthorPageRevisionIterator extends WikiDB_PageRevisionIterator
{
    function AuthorPageRevisionIterator ($revisions, $author) {
        $this->_revisions = $revisions;
        $this->_author = $author;
    }

    function next () {
        while (($rev = $this->_revisions->next())) {
            if ($rev->get('author_id') == $this->_author)
                return $rev;
        }
        $this->free();
        return false;
    }
}

/**
 * Filter by owner
 */
class OwnerPageRevisionIterator extends WikiDB_PageRevisionIterator
{
    function OwnerPageRevisionIterator ($revisions, $owner) {
        $this->_revisions = $revisions;
        $this->_owner = $owner;
    }

    function next () {
        while (($rev = $this->_revisions->next())) {
            $page = $rev->getPage();
            if ($page->getOwner() == $this->_owner)
                return $rev;
        }
        $this->free();
        return false;
    }
}

class WikiPlugin_RecentChanges
extends WikiPlugin
{
    function getName () {
        return _("RecentChanges");
    }

    function getDescription () {
        return _("List all recent changes in this wiki.");
    }

    function managesValidators() {
        // Note that this is a bit of a fig.
        // We set validators based on the most recently changed page,
        // but this fails when the most-recent page is deleted.
        // (Consider that the Last-Modified time will decrease
        // when this happens.)

        // We might be better off, leaving this as false (and junking
        // the validator logic above) and just falling back to the
        // default behavior (handled by WikiPlugin) of just using
        // the WikiDB global timestamp as the mtime.

        // Nevertheless, for now, I leave this here, mostly as an
        // example for how to use appendValidators() and managesValidators().

        return true;
    }

    function getDefaultArguments() {
        return array('days'         => 2,
                     'show_minor'   => false,
                     'show_major'   => true,
                     'show_all'     => false,
                     'show_deleted' => 'sometimes',
                     'only_new'     => false,
                     'author'       => false,
                     'owner'        => false,
                     'limit'        => false,
                     'format'       => false,
                     'daylist'      => false,
                     'difflinks'    => true,
                     'historylinks' => false,
                     'caption'      => '',
                     'category'     => '',
                     'pagematch'    => ''
                     );
    }

    function getArgs ($argstr, $request, $defaults = false) {
            if (!$defaults) $defaults = $this->getDefaultArguments();
        $args = WikiPlugin::getArgs($argstr, $request, $defaults);

        $action = $request->getArg('action');
        if ($action != 'browse' && !isActionPage($action))
            $args['format'] = false; // default -> HTML

        if ($args['format'] == 'rss' && empty($args['limit']))
            $args['limit'] = 15; // Fix default value for RSS.
        if ($args['format'] == 'rss2' && empty($args['limit']))
            $args['limit'] = 15; // Fix default value for RSS2.

        if ($args['format'] == 'sidebar' && empty($args['limit']))
            $args['limit'] = 10; // Fix default value for sidebar.

        return $args;
    }

    function getMostRecentParams (&$args) {
            $show_all = false; $show_minor = false; $show_major = false;
            $limit = false;
        extract($args);

        $params = array('include_minor_revisions' => $show_minor,
                        'exclude_major_revisions' => !$show_major,
                        'include_all_revisions' => !empty($show_all));
        if ($limit != 0)
            $params['limit'] = $limit;
        if (!empty($args['author'])) {
            global $request;
            if ($args['author'] == '[]')
                $args['author'] = $request->_user->getID();
            $params['author'] = $args['author'];
        }
        if (!empty($args['owner'])) {
            global $request;
            if ($args['owner'] == '[]')
                $args['owner'] = $request->_user->getID();
            $params['owner'] = $args['owner'];
        }
        if (!empty($days)) {
          if ($days > 0.0)
            $params['since'] = time() - 24 * 3600 * $days;
          elseif ($days < 0.0)
            $params['since'] = 24 * 3600 * $days - time();
        }

        return $params;
    }

    function getChanges ($dbi, $args) {
        $changes = $dbi->mostRecent($this->getMostRecentParams($args));

        $show_deleted = @$args['show_deleted'];
        $show_all = @$args['show_all'];
        if ($show_deleted == 'sometimes')
            $show_deleted = @$args['show_minor'];

        // only pages (e.g. PageHistory of subpages)
        if (!empty($args['pagematch'])) {
            require_once("lib/TextSearchQuery.php");
            $changes = new PageMatchRevisionIterator($changes, $args['pagematch']);
        }
        if (!empty($args['category'])) {
            require_once("lib/TextSearchQuery.php");
            $changes = new LinkRevisionIterator($changes, $args['category']);
        }
        if (!empty($args['only_new']))
            $changes = new NewPageRevisionIterator($changes);
        if (!empty($args['author']))
            $changes = new AuthorPageRevisionIterator($changes, $args['author']);
        if (!empty($args['owner']))
            $changes = new OwnerPageRevisionIterator($changes, $args['owner']);
        if (!$show_deleted)
            $changes = new NonDeletedRevisionIterator($changes, !$show_all);

        return $changes;
    }

    function format ($changes, $args) {
        global $WikiTheme;
        $format = $args['format'];

        $fmt_class = $WikiTheme->getFormatter('RecentChanges', $format);
        if (!$fmt_class) {
            if ($format == 'rss')
                $fmt_class = '_RecentChanges_RssFormatter';
            elseif ($format == 'rss2')
                $fmt_class = '_RecentChanges_Rss2Formatter';
            elseif ($format == 'atom')
                $fmt_class = '_RecentChanges_AtomFormatter';
            elseif ($format == 'rss091') {
                include_once "lib/RSSWriter091.php";
                $fmt_class = '_RecentChanges_RssFormatter091';
            }
            elseif ($format == 'sidebar')
                $fmt_class = '_RecentChanges_SideBarFormatter';
            elseif ($format == 'box')
                $fmt_class = '_RecentChanges_BoxFormatter';
            elseif ($format == 'contribs')
                $fmt_class = '_RecentChanges_UserContribsFormatter';
            else
                $fmt_class = '_RecentChanges_HtmlFormatter';
        }

        $fmt = new $fmt_class($args);
        return $fmt->format($changes);
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);

        // HACKish: fix for SF bug #622784  (1000 years of RecentChanges ought
        // to be enough for anyone.)
        $args['days'] = min($args['days'], 365000);

        // Within Categories just display Category Backlinks
        if (empty($args['category']) and empty($args['pagematch'])
            and preg_match("/^Category/", $request->getArg('pagename')))
        {
            $args['category'] = $request->getArg('pagename');
        }

        // Hack alert: format() is a NORETURN for rss formatters.
        return $this->format($this->getChanges($dbi, $args), $args);
    }

    // box is used to display a fixed-width, narrow version with common header.
    // just a numbered list of limit pagenames, without date.
    function box($args = false, $request = false, $basepage = false) {
        if (!$request) $request =& $GLOBALS['request'];
        if (!isset($args['limit'])) $args['limit'] = 15;
        $args['format'] = 'box';
        $args['show_minor'] = false;
        $args['show_major'] = true;
        $args['show_deleted'] = 'sometimes';
        $args['show_all'] = false;
        $args['days'] = 90;
        return $this->makeBox(WikiLink($this->getName(),'',
                                       SplitPagename($this->getName())),
                              $this->format
                              ($this->getChanges($request->_dbi, $args), $args));
    }

};

class OptionsButtonBars extends HtmlElement {

    function OptionsButtonBars ($plugin_args) {
        $this->__construct('fieldset', array('class' => 'wiki-rc-action'));

            // Add ShowHideFolder button
        $icon = $GLOBALS['WikiTheme']->_findData('images/folderArrowOpen.png');
        $img = HTML::img(array('id' => 'rc-action-img',
                               'src' => $icon,
                               'onclick' => "showHideFolder('rc-action')",
                               'alt'  => _("Click to hide/show"),
                               'title'  => _("Click to hide/show")));

        // Display selection buttons
        extract($plugin_args);

        // Custom caption
        if (! $caption) {
            $caption = _("Show changes for:");
        }

        $this->pushContent(HTML::legend($caption,' ',$img));
        $table = HTML::table(array('id' => 'rc-action-body',
                                   'style' => 'display:block'));

        $tr = HTML::tr();
        foreach (explode(",", $daylist) as $days_button) {
            $tr->pushContent($this->_makeDayButton($days_button, $days));
        }
        $table->pushContent($tr);

        $tr = HTML::tr();
        $tr->pushContent($this->_makeUsersButton(0));
        $tr->pushContent($this->_makeUsersButton(1));
        $table->pushContent($tr);

        $tr = HTML::tr();
        $tr->pushContent($this->_makePagesButton(0));
        $tr->pushContent($this->_makePagesButton(1));
        $table->pushContent($tr);

        $tr = HTML::tr();
        $tr->pushContent($this->_makeMinorButton(1, $show_minor));
        $tr->pushContent($this->_makeMinorButton(0, $show_minor));
        $table->pushContent($tr);

        $tr = HTML::tr();
        $tr->pushContent($this->_makeShowAllButton(1, $show_all));
        $tr->pushContent($this->_makeShowAllButton(0, $show_all));
        $table->pushContent($tr);

        $tr = HTML::tr();
        $tr->pushContent($this->_makeNewPagesButton(0, $only_new));
        $tr->pushContent($this->_makeNewPagesButton(1, $only_new));
        $table->pushContent($tr);

        $this->pushContent($table);
    }

    function _makeDayButton ($days_button, $days) {
        global $request;

        $url = $request->getURLtoSelf(array('action' => $request->getArg('action'), 'days' => $days_button));
        if ($days_button == 1) {
            $label = _("1 day");
        } elseif ($days_button < 1) {
            $label = _("All time");
        } else {
            $label = sprintf(_("%s days"), abs($days_button));
        }
        $selected = HTML::td(array('class'=>'tdselected'), $label);
        $unselected = HTML::td(array('class'=>'tdunselected'),
                      HTML::a(array('href'  => $url, 'class' => 'wiki-rc-action'), $label));
        return ($days_button == $days) ? $selected : $unselected;
    }

    function _makeUsersButton ($users) {
        global $request;

        if ($users == 0) {
            $label = _("All users");
            $author = "";
        } else {
            $label = _("My modifications only");
            $author = "[]";
        }

        $selfurl = $request->getURLtoSelf(array('action' => $request->getArg('action')));
        $url = $request->getURLtoSelf(array('action' => $request->getArg('action'), 'author' => $author));
        if ($url == $selfurl) {
            return HTML::td(array('colspan'=>3, 'class'=>'tdselected'), $label);
        }
        return HTML::td(array('colspan'=>3, 'class'=>'tdunselected'),
                        HTML::a(array('href'  => $url, 'class' => 'wiki-rc-action'), $label));
    }

    function _makePagesButton ($pages) {
        global $request;

        if ($pages == 0) {
            $label = _("All pages");
            $owner = "";
        } else {
            $label = _("My pages only");
            $owner = "[]";
        }

        $selfurl = $request->getURLtoSelf(array('action' => $request->getArg('action')));
        $url = $request->getURLtoSelf(array('action' => $request->getArg('action'), 'owner' => $owner));
        if ($url == $selfurl) {
            return HTML::td(array('colspan'=>3, 'class'=>'tdselected'), $label);
        }
        return HTML::td(array('colspan'=>3, 'class'=>'tdunselected'),
                        HTML::a(array('href'  => $url, 'class' => 'wiki-rc-action'), $label));
    }

    function _makeMinorButton ($minor_button, $show_minor) {
        global $request;

        $url = $request->getURLtoSelf(array('action' => $request->getArg('action'), 'show_minor' => $minor_button));
        $label = ($minor_button == 0) ? _("Major modifications only") : _("All modifications");
        $selected = HTML::td(array('colspan'=>3, 'class'=>'tdselected'), $label);
        $unselected = HTML::td(array('colspan'=>3, 'class'=>'tdunselected'),
                      HTML::a(array('href'  => $url, 'class' => 'wiki-rc-action'), $label));
        return ($minor_button == $show_minor) ? $selected : $unselected;
    }

    function _makeShowAllButton ($showall_button, $show_all) {
        global $request;

        $url = $request->getURLtoSelf(array('action' => $request->getArg('action'), 'show_all' => $showall_button));
        $label = ($showall_button == 0) ? _("Page once only") : _("Full changes");
        $selected = HTML::td(array('colspan'=>3, 'class'=>'tdselected'), $label);
        $unselected = HTML::td(array('colspan'=>3, 'class'=>'tdunselected'),
                      HTML::a(array('href'  => $url, 'class' => 'wiki-rc-action'), $label));
        return ($showall_button == $show_all) ? $selected : $unselected;
    }

    function _makeNewPagesButton ($newpages_button, $only_new) {
        global $request;

        $url = $request->getURLtoSelf(array('action' => $request->getArg('action'), 'only_new' => $newpages_button));
        $label = ($newpages_button == 0) ? _("Old and new pages") : _("New pages only");
        $selected = HTML::td(array('colspan'=>3, 'class'=>'tdselected'), $label);
        $unselected = HTML::td(array('colspan'=>3, 'class'=>'tdunselected'),
                      HTML::a(array('href'  => $url, 'class' => 'wiki-rc-action'), $label));
        return ($newpages_button == $only_new) ? $selected : $unselected;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
