<?php // -*-php-*-
// $Id: PageHistory.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 1999, 2000, 2001, 2002, 2007 $ThePhpWikiProgrammingTeam
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once("lib/plugin/RecentChanges.php");

class _PageHistory_PageRevisionIter
extends WikiDB_PageRevisionIterator
{
    function _PageHistory_PageRevisionIter($rev_iter, $params) {

        $this->_iter = $rev_iter;

        extract($params);

        if (isset($since))
            $this->_since = $since;

        $this->_include_major = empty($exclude_major_revisions);
        if (! $this->_include_major)
            $this->_include_minor = true;
        else
            $this->_include_minor = !empty($include_minor_revisions);

        if (empty($include_all_revisions))
            $this->_limit = 1;
        else if (isset($limit))
            $this->_limit = $limit;
    }

    function next() {
        if (!$this->_iter)
            return false;

        if (isset($this->_limit)) {
            if ($this->_limit <= 0) {
                $this->free();
                return false;
            }
            $this->_limit--;
        }

        while ( ($rev = $this->_iter->next()) ) {
            if (isset($this->_since) && $rev->get('mtime') < $this->_since) {
                $this->free();
                return false;
            }
            if ($rev->get('is_minor_edit') ? $this->_include_minor : $this->_include_major)
                return $rev;
        }
        return false;
    }


    function free() {
        if ($this->_iter)
            $this->_iter->free();
        $this->_iter = false;
    }
}


class _PageHistory_HtmlFormatter
extends _RecentChanges_HtmlFormatter
{
    function include_versions_in_URLs() {
        return true;
    }

    function headline() {
        return HTML(fmt("PageHistory for %s",
                         WikiLink($this->_args['page'])),
                     "\n",
                     $this->rss_icon(),
                     $this->rss2_icon(),
                     $this->atom_icon(),
                     $this->rdf_icon());
    }

    function title() {
        return "PageHistory:".$this->_args['page'];
    }

    function empty_message () {
        return _("No revisions found");
    }

    function description() {
        $button = HTML::input(array('type'  => 'submit',
                                    'value' => _("compare revisions"),
                                    'class' => 'wikiaction'));

        $js_desc = $no_js_desc = _RecentChanges_HtmlFormatter::description();

        $js_desc->pushContent("\n", _("Check any two boxes to compare revisions."));
        $no_js_desc->pushContent("\n", fmt("Check any two boxes then %s.", $button));

        return IfJavaScript($js_desc, $no_js_desc);
    }


    function format ($changes) {
        $this->_itemcount = 0;

        $pagename = $this->_args['page'];

        $fmt = _RecentChanges_HtmlFormatter::format($changes);
        $fmt->action = _("PageHistory");
        $html[] = $fmt;

        $html[] = HTML::input(array('type'  => 'hidden',
                                    'name'  => 'action',
                                    'value' => 'diff'));
        if (USE_PATH_INFO) {
            $action = WikiURL($pagename);
        }
        else {
            $action = SCRIPT_NAME;
            $html[] = HTML::input(array('type'  => 'hidden',
                                        'name'  => 'pagename',
                                        'value' => $pagename));
        }

        return HTML(HTML::form(array('method' => 'get',
                                     'action' => $action,
                                     'id'     => 'diff-select'),
                               $html),
                    "\n",
                    JavaScript('
        var diffCkBoxes = document.forms["diff-select"].elements["versions[]"];

        function diffCkBox_onclick() {
            var nchecked = 0, box = diffCkBoxes;
            for (i = 0; i < box.length; i++)
                if (box[i].checked) nchecked++;
            if (nchecked == 2)
                this.form.submit();
            else if (nchecked > 2) {
                for (i = 0; i < box.length; i++)
                    if (box[i] != this) box[i].checked = 0;
            }
        }

        for (i = 0; i < diffCkBoxes.length; i++)
            diffCkBoxes[i].onclick = diffCkBox_onclick;'));
    }

    function diffLink ($rev) {
        return HTML::input(array('type'  => 'checkbox',
                                 'name'  => 'versions[]',
                                 'value' => $rev->getVersion()));
    }

    function pageLink ($rev) {
        $text = fmt("Version %d", $rev->getVersion());
        return _RecentChanges_HtmlFormatter::pageLink($rev, $text);
    }

    function format_revision ($rev) {
        global $WikiTheme;
        $class = 'rc-' . $this->importance($rev);

        $time = $this->time($rev);
        if ($rev->get('is_minor_edit')) {
            $minor_flag = HTML(" ",
                               HTML::span(array('class' => 'pageinfo-minoredit'),
                                          "(" . _("minor edit") . ")"));
        }
        else {
            $time = HTML::span(array('class' => 'pageinfo-majoredit'), $time);
            $minor_flag = '';
        }
        $line = HTML::li(array('class' => $class));
        if (isa($WikiTheme,'WikiTheme_MonoBook')) {
            $line->pushContent(
                               $this->diffLink($rev), ' ',
                               $this->pageLink($rev), ' ',
                               $time,' ',$this->date($rev), ' . . ',
                               $this->authorLink($rev),' ',
                               $this->authorContribs($rev),' ',
                               $this->summaryAsHTML($rev),' ',
                               $minor_flag);
        } else {
            $line->pushContent(
                               $this->diffLink($rev), ' ',
                               $this->pageLink($rev), ' ',
                               $time, ' ',
                               $this->summaryAsHTML($rev),
                               ' ... ',
                               $this->authorLink($rev),
                               $minor_flag);
        }
        return $line;
    }
}


class _PageHistory_RssFormatter
extends _RecentChanges_RssFormatter
{
    function include_versions_in_URLs() {
        return true;
    }

    function image_properties () {
        return false;
    }

    function textinput_properties () {
        return false;
    }

    function channel_properties () {
        global $request;

        $rc_url = WikiURL($request->getArg('pagename'), false, 'absurl');

        $title = sprintf(_("%s: %s"),
                         WIKI_NAME,
                         SplitPagename($this->_args['page']));

        return array('title'          => $title,
                     'dc:description' => _("History of changes."),
                     'link'           => $rc_url,
                     'dc:date'        => Iso8601DateTime(time()));
    }


    function item_properties ($rev) {
        if (!($title = $this->summary($rev)))
            $title = sprintf(_("Version %d"), $rev->getVersion());

        return array( 'title'           => $title,
                      'link'            => $this->pageURL($rev),
                      'dc:date'         => $this->time($rev),
                      'dc:contributor'  => $rev->get('author'),
                      'wiki:version'    => $rev->getVersion(),
                      'wiki:importance' => $this->importance($rev),
                      'wiki:status'     => $this->status($rev),
                      'wiki:diff'       => $this->diffURL($rev),
                      );
    }
}

class WikiPlugin_PageHistory
extends WikiPlugin_RecentChanges
{
    function getName () {
        return _("PageHistory");
    }

    function getDescription () {
        return sprintf(_("List PageHistory for %s"),'[pagename]');
    }

    function getDefaultArguments() {
        return array('days'         => false,
                     'show_minor'   => true,
                     'show_major'   => true,
                     'limit'        => false,
                     'page'         => '[pagename]',
                     'format'       => false);
    }

    function getDefaultFormArguments() {
        $dflts = WikiPlugin_RecentChanges::getDefaultFormArguments();
        $dflts['textinput'] = 'page';
        return $dflts;
    }

    function getMostRecentParams ($args) {
        $params = WikiPlugin_RecentChanges::getMostRecentParams($args);
        $params['include_all_revisions'] = true;
        return $params;
    }

    function getChanges ($dbi, $args) {
        $page = $dbi->getPage($args['page']);
        $iter = $page->getAllRevisions();
        $params = $this->getMostRecentParams($args);
        if (empty($args['days'])) unset($params['since']);
        return new _PageHistory_PageRevisionIter($iter, $params);
    }

    function format ($changes, $args) {
        global $WikiTheme;
        $format = $args['format'];

        $fmt_class = $WikiTheme->getFormatter('PageHistory', $format);
        if (!$fmt_class) {
            if ($format == 'rss')
                $fmt_class = '_PageHistory_RssFormatter';
            else
                $fmt_class = '_PageHistory_HtmlFormatter';
        }

        $fmt = new $fmt_class($args);
        $fmt->action = _("PageHistory");
        return $fmt->format($changes);
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        $pagename = $args['page'];
        if (empty($pagename))
            return $this->makeForm("", $request);

        $page = $dbi->getPage($pagename);
        $current = $page->getCurrentRevision();
        if ($current->getVersion() < 1) {
            return HTML(HTML::p(fmt("I'm sorry, there is no such page as %s.",
                                    WikiLink($pagename, 'unknown'))),
                        $this->makeForm("", $request));
        }
        // Hack alert: format() is a NORETURN for rss formatters.
        return $this->format($this->getChanges($dbi, $args), $args);
    }
};

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
