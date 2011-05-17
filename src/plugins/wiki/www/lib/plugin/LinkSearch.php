<?php // -*-php-*-
// rcs_id('$Id: LinkSearch.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright 2007 Reini Urban
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

require_once('lib/TextSearchQuery.php');
require_once('lib/PageList.php');

/**
 * Similar to SemanticSearch, just for ordinary in- or outlinks.
 *
 * @author: Reini Urban
 */
class WikiPlugin_LinkSearch
extends WikiPlugin
{
    function getName() {
        return _("LinkSearch");
    }
    function getDescription() {
        return _("Search page and link names");
    }
    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(), // paging and more.
             array(
                   's'          => "", // linkvalue query string
                   'page'       => "*", // which pages (glob allowed), default: all
                   'direction'  => "out", // or in
                   'case_exact' => false,
                   'regex'      => 'auto',
                   'noform'     => false, // don't show form with results.
                   'noheader'   => false  // no caption
                   ));
    }

    function showForm (&$dbi, &$request, $args) {
        $action = $request->getPostURL();
        $hiddenfield = HiddenInputs($request->getArgs(),'',
                                    array('action','page','s','direction'));
        $pagefilter = HTML::input(array('name' => 'page',
                                        'value' => $args['page'],
                                        'title' => _("Search only in these pages. With autocompletion."),
                                        'class' => 'dropdown',
                                        'acdropdown' => 'true',
                                        'autocomplete_complete' => 'true',
                                        'autocomplete_matchsubstring' => 'false',
                                        'autocomplete_list' => 'xmlrpc:wiki.titleSearch ^[S] 4'
                                        ), '');
        $query = HTML::input(array('name' => 's',
                                   'value' => $args['s'],
                                   'title' => _("Filter by this link. These are pagenames. With autocompletion."),
                                   'class' => 'dropdown',
                                   'acdropdown' => 'true',
                                   'autocomplete_complete' => 'true',
                                   'autocomplete_matchsubstring' => 'true',
                                   'autocomplete_list' => 'xmlrpc:wiki.titleSearch ^[S] 4'
                                   ), '');
        $dirsign_switch = JavaScript("
function dirsign_switch() {
  var d = document.getElementById('dirsign')
  d.innerHTML = (d.innerHTML == ' =&gt; ') ? ' &lt;= ' : ' =&gt; '
}
");
        $dirsign = " => ";
        $in = $out = array('name' => 'direction', 'type'=>'radio', 'onChange' => 'dirsign_switch()');
        $out['value'] = 'out';
        $out['id'] = 'dir_out';
        if ($args['direction']=='out') $out['checked'] = 'checked';
        $in['value'] = 'in';
        $in['id'] = 'dir_in';
        if ($args['direction']=='in') {
            $in['checked'] = 'checked';
            $dirsign = " <= ";
        }
        $direction = HTML(HTML::input($out), HTML::label(array('for'=>'dir_out'),_("outgoing")),
                          HTML::input($in), HTML::label(array('for'=>'dir_in'),_("incoming")));
        /*
        $direction = HTML::select(array('name'=>'direction',
                                        'onChange' => 'dirsign_switch()'));
        $out = array('value' => 'out');
        if ($args['direction']=='out') $out['selected'] = 'selected';
        $in = array('value' => 'in');
        if ($args['direction']=='in') {
            $in['selected'] = 'selected';
            $dirsign = " <= ";
        }
        $direction->pushContent(HTML::option($out, _("outgoing")));
        $direction->pushContent(HTML::option($in, _("incoming")));
        */
        $submit = Button('submit:search',  _("LinkSearch"), false);
        $instructions = _("Search in pages for links with the matching name.");
        $form = HTML::form(array('action' => $action,
                                 'method' => 'GET',
                                 'accept-charset' => $GLOBALS['charset']),
                           $dirsign_switch,
                           $hiddenfield,
                           $instructions, HTML::br(),
                           $pagefilter,
                           HTML::strong(HTML::tt(array('id'=>'dirsign'), $dirsign)),
                           $query,
                           HTML::raw('&nbsp;'), $direction,
                           HTML::raw('&nbsp;'), $submit);
        return $form;
    }

    function run ($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;
        $args = $this->getArgs($argstr, $request);

        if (empty($args['page']))
            $args['page'] = "*";
        $form = $this->showForm($dbi, $request, $args);
        extract($args);
        if (empty($s))
            return $form;
        $pagequery = new TextSearchQuery($page, $args['case_exact'], $args['regex']);
        $linkquery = new TextSearchQuery($s, $args['case_exact'], $args['regex']);
        $links = $dbi->linkSearch($pagequery, $linkquery, $direction == 'in' ? 'linkfrom' : 'linkto');
        $pagelist = new PageList($args['info'], $args['exclude'], $args);
        $pagelist->_links = array();
        while ($link = $links->next()) {
            $pagelist->addPage($link['pagename']);
            $pagelist->_links[] = $link;
        }
        $pagelist->addColumnObject
            (new _PageList_Column_LinkSearch_link('link', _("Link"), $pagelist));

        if (!$noheader) {
            // We put the form into the caption just to be able to return one pagelist object,
            // and to still have the convenience form at the top. we could workaround this by
            // putting the form as WikiFormRich into the actionpage. but thid doesnt look as
            // nice as this here.
            $pagelist->setCaption
            (   // on mozilla the form doesn't fit into the caption very well.
                HTML($noform ? '' : HTML($form,HTML::hr()),
                     fmt("LinkSearch result for \"%s\" in pages \"%s\", direction %s", $s, $page, $direction)));
        }
        return $pagelist;
    }
};

// FIXME: sortby errors with this column
class _PageList_Column_LinkSearch_link
extends _PageList_Column
{
    function _PageList_Column_LinkSearch_link ($field, $heading, &$pagelist) {
        $this->_field = $field;
        $this->_heading = $heading;
        $this->_need_rev = false;
        $this->_iscustom = true;
        $this->_pagelist =& $pagelist;
    }
    function _getValue(&$page, $revision_handle) {
        if (is_object($page)) $text = $page->getName();
        else $text = $page;
        $link = $this->_pagelist->_links[$this->current_row];
        return WikiLink($link['linkvalue'],'if_known');
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
