<?php // -*-php-*-
// rcs_id('$Id: WikiAdminSearchReplace.php 7925 2011-02-01 10:08:52Z vargenau $');
/*
 * Copyright 2004,2007 $ThePhpWikiProgrammingTeam
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Usage:   <<WikiAdminSearchReplace >> or called via WikiAdminSelect
 * Author:  Reini Urban <rurban@x-ray.at>
 *
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminSearchReplace
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminSearchReplace");
    }

    function getDescription() {
        return _("Search and replace text in selected wiki pages.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             WikiPlugin_WikiAdminSelect::getDefaultArguments(),
             array(
                   /* Columns to include in listing */
                   'info'     => 'some',
                   ));
    }

    function replaceHelper(&$dbi, &$request, $pagename, $from, $to, $case_exact=true, $regex=false) {
        $page = $dbi->getPage($pagename);
        if ($page->exists()) {// don't replace default contents
            $current = $page->getCurrentRevision();
            $version = $current->getVersion();
            $text = $current->getPackedContent();
            if ($regex) {
                $newtext = preg_replace("/".$from."/".($case_exact?'':'i'), $to, $text);
            } else {
                if ($case_exact) {
                    $newtext = str_replace($from, $to, $text);
                } else {
                    //not all PHP have this enabled. use a workaround
                    if (function_exists('str_ireplace'))
                        $newtext = str_ireplace($from, $to, $text);
                    else { // see eof
                        $newtext = stri_replace($from, $to, $text);
                    }
                }
            }
            if ($text != $newtext) {
                $meta = $current->_data;
                $meta['summary'] = sprintf(_("Replace '%s' by '%s'"), $from, $to);
                $meta['is_minor_edit'] = 0;
                $meta['author'] =  $request->_user->UserName();
                unset($meta['mtime']); // force new date
                return $page->save($newtext, $version + 1, $meta);
            }
        }
        return false;
    }

    function searchReplacePages(&$dbi, &$request, $pages, $from, $to) {
        if (empty($from)) return HTML::p(HTML::strong(fmt("Error: Empty search string.")));
        $result = HTML::div();
        $ul = HTML::ul();
        $count = 0;
        $post_args = $request->getArg('admin_replace');
        $case_exact = !empty($post_args['case_exact']);
        $regex = !empty($post_args['regex']);
        foreach ($pages as $pagename) {
            if (!mayAccessPage('edit', $pagename)) {
                $ul->pushContent(HTML::li(fmt("Access denied to change page '%s'.",$pagename)));
            } elseif ($this->replaceHelper($dbi, $request, $pagename, $from, $to, $case_exact, $regex)) {
                $ul->pushContent(HTML::li(fmt("Replaced '%s' with '%s' in page '%s'.",
                                              $from, $to, WikiLink($pagename))));
                $count++;
            }
        }
        if ($count) {
            $dbi->touch();
            $result->setAttr('class', 'feedback');
            if ($count == 1) {
                $result->pushContent(HTML::p(_("One page has been permanently changed:")));
            } else {
                $result->pushContent(HTML::p(fmt("%s pages have been permanently changed:", $count)));
            }
            $result->pushContent($ul);
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(_("No pages changed.")));
        }
        return $result;
    }

    function run($dbi, $argstr, &$request, $basepage) {
            // no action=replace support yet
        if ($request->getArg('action') != 'browse')
            return $this->disabled("(action != 'browse')");

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;

        //TODO: support p from <!plugin-list !>
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_replace');
        $next_action = 'select';
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        if ($p && $request->isPost() &&
            empty($post_args['cancel'])) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }

            if ($post_args['action'] == 'verify' and !empty($post_args['from'])) {
                // Real action
                return $this->searchReplacePages($dbi, $request, array_keys($p),
                                                 $post_args['from'], $post_args['to']);
            }
            if ($post_args['action'] == 'select') {
                if (!empty($post_args['from']))
                    $next_action = 'verify';
                foreach ($p as $name => $c) {
                    $pages[$name] = 1;
                }
            }
        }
        if ($next_action == 'select' and empty($pages)) {
            // List all pages to select from.
            //TODO: check for permissions and list only the allowed
            $pages = $this->collectPages($pages, $dbi, $args['sortby'],
                                         $args['limit'], $args['exclude']);
        }

        if ($next_action == 'verify') {
            $args['info'] = "checkbox,pagename";
        } else {
            $args['info'] = "checkbox,pagename,hi_content,mtime,author";
        }
        $pagelist = new PageList_Selectable
            ($args['info'], $args['exclude'],
             array_merge
             (
              $args,
              array('types' => array
                    (
                     'hi_content' // with highlighted search for SearchReplace
                     => new _PageList_Column_content('rev:hi_content', _("Content"))))));

        $pagelist->addPageList($pages);

        $header = HTML::fieldset();
        if (empty($post_args['from']))
            $header->pushContent(
              HTML::p(HTML::em(_("Warning: The search string cannot be empty!"))));
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header->pushContent(
              HTML::p(HTML::strong(
                                   _("Are you sure you want to permanently replace text in the selected files?"))));
            $this->replaceForm($header, $post_args);
        } else {
            $button_label = _("Search & Replace");
            $this->replaceForm($header, $post_args);
            $header->pushContent(HTML::legend(_("Select the pages to search and replace")));
        }

        $buttons = HTML::p(Button('submit:admin_replace[replace]', $button_label, 'wikiadmin'),
                           Button('submit:admin_replace[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_replace')),
                          HiddenInputs(array('admin_replace[action]' => $next_action)),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    function checkBox (&$post_args, $name, $msg) {
            $id = 'admin_replace-'.$name;
            $checkbox = HTML::input(array('type' => 'checkbox',
                                      'name' => 'admin_replace['.$name.']',
                                      'id'   => $id,
                                      'value' => 1));
        if (!empty($post_args[$name]))
            $checkbox->setAttr('checked', 'checked');
        return HTML::div($checkbox, ' ', HTML::label(array('for' => $id), $msg));
    }

    function replaceForm(&$header, $post_args) {
        $header->pushContent(HTML::div(array('class'=>'hint'),
                                       _("Replace all occurences of the given string in the content of all pages.")),
                             HTML::br());
        $table = HTML::table();
        $this->_tablePush($table, _("Replace")._(": "),
                          HTML::input(array('name' => 'admin_replace[from]',
                                            'size' => 90,
                                            'value' => $post_args['from'])));
        $this->_tablePush($table, _("by")._(": "),
                          HTML::input(array('name' => 'admin_replace[to]',
                                            'size' => 90,
                                            'value' => $post_args['to'])));
        $this->_tablePush($table, '', $this->checkBox($post_args, 'case_exact', _("Case exact?")));
        $this->_tablePush($table, '', $this->checkBox($post_args, 'regex', _("Regex?")));
        $header->pushContent($table);
        return $header;
    }
}

function stri_replace($find,$replace,$string) {
    if (!is_array($find)) $find = array($find);
    if (!is_array($replace))  {
        if (!is_array($find))
            $replace = array($replace);
        else {
            // this will duplicate the string into an array the size of $find
            $c = count($find);
            $rString = $replace;
            unset($replace);
            for ($i = 0; $i < $c; $i++) {
                $replace[$i] = $rString;
            }
        }
    }
    foreach ($find as $fKey => $fItem) {
        $between = explode(strtolower($fItem),strtolower($string));
        $pos = 0;
        foreach($between as $bKey => $bItem) {
            $between[$bKey] = substr($string,$pos,strlen($bItem));
            $pos += strlen($bItem) + strlen($fItem);
        }
        $string = implode($replace[$fKey],$between);
    }
    return $string;
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
