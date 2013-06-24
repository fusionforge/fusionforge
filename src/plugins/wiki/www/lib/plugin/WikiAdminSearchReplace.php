<?php

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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Usage:   <<WikiAdminSearchReplace >> or called via WikiAdminSelect
 * Author:  Reini Urban <rurban@x-ray.at>
 *
 */
require_once 'lib/PageList.php';
require_once 'lib/plugin/WikiAdminSelect.php';

class WikiPlugin_WikiAdminSearchReplace
    extends WikiPlugin_WikiAdminSelect
{
    function getDescription()
    {
        return _("Search and replace text in selected wiki pages.");
    }

    function getDefaultArguments()
    {
        return array_merge
        (
            WikiPlugin_WikiAdminSelect::getDefaultArguments(),
            array(
                /* Columns to include in listing */
                'info' => 'some',
            ));
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        // no action=replace support yet
        if ($request->getArg('action') != 'browse') {
            return $this->disabled(_("Plugin not run: not in browse mode"));
        }

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;

        //TODO: support p from <!plugin-list !>
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) {
            $p = $this->_list;
        }
        $post_args = $request->getArg('admin_replace');
        $next_action = 'select';
        $pages = array();
        if ($p && !$request->isPost()) {
            $pages = $p;
        }
        if ($p && $request->isPost() && empty($post_args['cancel'])) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled(_("You must be an administrator to use this plugin."));
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
        $result = HTML::div();
        if ($request->isPost() and empty($post_args['from'])) {
            $result->pushContent(HTML::p(array('class' => 'warning'),
                                         _("Warning: The search string cannot be empty!")));
        }
        if ($next_action == 'select' and empty($pages)) {
            // List all pages to select from.
            //TODO: check for permissions and list only the allowed
            $pages = $this->collectPages($pages, $dbi, $args['sortby'],
                $args['limit'], $args['exclude']);
        }

        $args['info'] = "checkbox,pagename,mtime,author";
        if ($next_action == 'select') {
            $columns = $args;
        } else {
           $columns = array_merge($args,
                                  // with highlighted search for SearchReplace
                                  array('types' => array('hi_content' 
                    => new _PageList_Column_content('rev:hi_content', _("Content")))));
        }
        $pagelist = new PageList_Selectable($args['info'], $args['exclude'], $columns);
        $pagelist->addPageList($pages);

        $header = HTML::fieldset();
        $header->pushContent(HTML::legend(_("Select the pages to search and replace")));
        if ($next_action == 'verify') {
            $button_label = _("Replace");
            $header->pushContent(
                HTML::p(HTML::strong(
                    _("Are you sure you want to replace text in the selected files?"))));
            $this->replaceForm($header, $post_args);
        } else {
            $button_label = _("Search");
            $this->replaceForm($header, $post_args);
        }

        $buttons = HTML::p(Button('submit:admin_replace[replace]', $button_label, 'wikiadmin'),
            Button('submit:admin_replace[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        $result->pushContent(HTML::form(array('action' => $request->getPostURL(),
                'method' => 'post'),
            $header,
            $pagelist->getContent(),
            HiddenInputs($request->getArgs(),
                false,
                array('admin_replace')),
            HiddenInputs(array('admin_replace[action]' => $next_action)),
            ENABLE_PAGEPERM
                ? ''
                : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN))));
        return $result;
    }

    private function replaceHelper(&$dbi, &$request, $pagename, $from, $to, $case_exact = true, $regex = false)
    {
        $page = $dbi->getPage($pagename);
        if ($page->exists()) { // don't replace default contents
            $current = $page->getCurrentRevision();
            $version = $current->getVersion();
            $text = $current->getPackedContent();
            if ($regex) {
                $newtext = preg_replace("/" . $from . "/" . ($case_exact ? '' : 'i'), $to, $text);
            } else {
                if ($case_exact) {
                    $newtext = str_replace($from, $to, $text);
                } else {
                    $newtext = str_ireplace($from, $to, $text);
                }
            }
            if ($text != $newtext) {
                $meta = $current->_data;
                $meta['summary'] = sprintf(_("Replace “%s” by “%s”"), $from, $to);
                $meta['is_minor_edit'] = 0;
                $meta['author'] = $request->_user->UserName();
                unset($meta['mtime']); // force new date
                return $page->save($newtext, $version + 1, $meta);
            }
        }
        return false;
    }

    private function searchReplacePages(&$dbi, &$request, $pages, $from, $to)
    {
        $result = HTML::div();
        $ul = HTML::ul();
        $count = 0;
        $post_args = $request->getArg('admin_replace');
        $case_exact = !empty($post_args['case_exact']);
        $regex = !empty($post_args['regex']);
        foreach ($pages as $pagename) {
            if (!mayAccessPage('edit', $pagename)) {
                $ul->pushContent(HTML::li(fmt("Access denied to change page “%s”.", $pagename)));
            } elseif ($this->replaceHelper($dbi, $request, $pagename, $from, $to, $case_exact, $regex)) {
                $ul->pushContent(HTML::li(fmt("Replaced “%s” with “%s” in page “%s”.",
                    $from, $to, WikiLink($pagename))));
                $count++;
            }
        }
        if ($count) {
            $dbi->touch();
            $result->setAttr('class', 'feedback');
            if ($count == 1) {
                $result->pushContent(HTML::p(_("One page has been changed:")));
            } else {
                $result->pushContent(HTML::p(fmt("%d pages have been changed:", $count)));
            }
            $result->pushContent($ul);
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(_("No pages changed.")));
        }
        return $result;
    }

    private function checkBox(&$post_args, $name, $msg)
    {
        $id = 'admin_replace-' . $name;
        $checkbox = HTML::input(array('type' => 'checkbox',
            'name' => 'admin_replace[' . $name . ']',
            'id' => $id,
            'value' => 1));
        if (!empty($post_args[$name])) {
            $checkbox->setAttr('checked', 'checked');
        }
        return HTML::div($checkbox, ' ', HTML::label(array('for' => $id), $msg));
    }

    private function replaceForm(&$header, $post_args)
    {
        $header->pushContent(HTML::p(array('class' => 'hint'),
                _("Replace all occurences of the given string in the content of all selected pages.")));
        $table = HTML::table();
        $this->tablePush($table, _("Replace") . _(": "),
            HTML::input(array('name' => 'admin_replace[from]',
                'size' => 90,
                'value' => $post_args['from'])));
        $this->tablePush($table, _("by") . _(": "),
            HTML::input(array('name' => 'admin_replace[to]',
                'size' => 90,
                'value' => $post_args['to'])));
        $this->tablePush($table, '', $this->checkBox($post_args, 'case_exact', _("Case exact?")));
        $this->tablePush($table, '', $this->checkBox($post_args, 'regex', _("Regex?")));
        $header->pushContent($table);
        return $header;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
