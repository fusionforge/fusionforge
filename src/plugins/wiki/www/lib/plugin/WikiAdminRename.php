<?php

/*
 * Copyright 2004,2005,2007 $ThePhpWikiProgrammingTeam
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
 * Usage:   <<WikiAdminRename >> or called via WikiAdminSelect
 * @author:  Reini Urban <rurban@x-ray.at>
 *
 */
require_once 'lib/PageList.php';
require_once 'lib/plugin/WikiAdminSelect.php';

class WikiPlugin_WikiAdminRename
    extends WikiPlugin_WikiAdminSelect
{
    function getDescription()
    {
        return _("Rename selected pages.");
    }

    function getDefaultArguments()
    {
        return array_merge
        (
            WikiPlugin_WikiAdminSelect::getDefaultArguments(),
            array(
                /* Columns to include in listing */
                'info' => 'pagename,mtime',
                'updatelinks' => 0,
                'createredirect' => 0
            ));
    }

    public function renameHelper($name, $from, $to, $options = false)
    {
        if (isset($options['regex'])) {
            return preg_replace('/' . $from . '/' . (isset($options['icase']) ? 'i' : ''), $to, $name);
        } elseif (isset($options['icase'])) {
            return str_ireplace($from, $to, $name);
        } else {
            return str_replace($from, $to, $name);
        }
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        $action = $request->getArg('action');
        if ($action != 'browse' and $action != 'rename'
            and $action != __("PhpWikiAdministration"). "/". __("Rename")
        ) {
            return $this->disabled(_("Plugin not run: not in browse mode"));
        }

        if ($action == 'rename') {
            // We rename a single page.
            // No need to display "Regex?" and "Case insensitive?" boxes
            // No need to confirm
            $singlepage = true;
        } else {
            $singlepage = false;
        }

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_rename');
        $next_action = 'select';
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        if ($p && $request->isPost() &&
            !empty($post_args['rename']) && empty($post_args['cancel'])
        ) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            // DONE: error message if not allowed.
            if ($post_args['action'] == 'verify') {
                // Real action
                return $this->renamePages($dbi, $request, array_keys($p),
                    $post_args['from'], $post_args['to'],
                    !empty($post_args['updatelinks']),
                    !empty($post_args['createredirect']));
            }
        }
        if ($post_args['action'] == 'select') {
            if (!empty($post_args['from']))
                $next_action = 'verify';
            foreach ($p as $name => $c) {
                $pages[$name] = 1;
            }
        }
        if ($next_action == 'select' and empty($pages)) {
            // List all pages to select from.
            $pages = $this->collectPages($pages, $dbi, $args['sortby'],
                $args['limit'], $args['exclude']);
        }
        /*if ($next_action == 'verify') {
            $args['info'] = "checkbox,pagename,renamed_pagename";
        }*/
        $pagelist = new PageList_Selectable
        (
            $args['info'], $args['exclude'],
            array('types' =>
            array('renamed_pagename'
            => new _PageList_Column_renamed_pagename('rename', _("Rename to")),
            )));
        $pagelist->addPageList($pages);

        $header = HTML::div();
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header->pushContent(
                HTML::p(HTML::strong(
                    _("Are you sure you want to rename the selected pages?"))));
            $header = $this->renameForm($header, $post_args, $singlepage);
        } else {
            if ($singlepage === true) {
                $button_label = _("Rename Page");
            } else {
                $button_label = _("Rename selected pages");
            }
            if (!$post_args and count($pages) == 1) {
                list($post_args['from'],) = array_keys($pages);
                $post_args['to'] = $post_args['from'];
            }
            $header = $this->renameForm($header, $post_args, $singlepage);
            if ($singlepage === false) {
                $header->pushContent(HTML::p(_("Select the pages to rename:")));
            }
        }

        $buttons = HTML::p
        (Button('submit:admin_rename[rename]', $button_label, 'wikiadmin'),
            Button('submit:admin_rename[cancel]', _("Cancel"), 'button'));

        if ($singlepage === false) {
            $list = $pagelist->getContent();
        } else {
            $list = "";
        }
        return HTML::form(array('action' => $request->getPostURL(),
                'method' => 'post'),
            HTML::fieldset(
                HTML::legend(_("Rename Page")),
                $header,
                $buttons,
                $list,
                HiddenInputs($request->getArgs(),
                    false,
                    array('admin_rename')),
                HiddenInputs(array('admin_rename[action]' => $next_action)),
                ENABLE_PAGEPERM
                    ? ''
                    : HiddenInputs(array('require_authority_for_post'
                => WIKIAUTH_ADMIN))));
    }

    private function checkBox(&$post_args, $name, $msg)
    {
        $id = 'admin_rename-' . $name;
        $checkbox = HTML::input(array('type' => 'checkbox',
            'name' => 'admin_rename[' . $name . ']',
            'id' => $id,
            'value' => 1));
        if (!empty($post_args[$name]))
            $checkbox->setAttr('checked', 'checked');
        return HTML::div($checkbox, ' ', HTML::label(array('for' => $id), $msg));
    }

    private function renamePages(&$dbi, &$request, $pages, $from, $to, $updatelinks = false,
                                 $createredirect = false)
    {
        $result = HTML::div();
        $ul = HTML::ul();
        $count = 0;
        $post_args = $request->getArg('admin_rename');
        $options =
            array('regex' => isset($post_args['regex']) ? $post_args['regex'] : null,
                'icase' => isset($post_args['icase']) ? $post_args['icase'] : null);
        foreach ($pages as $name) {
            if (($newname = $this->renameHelper($name, $from, $to, $options))
                and $newname != $name
            ) {
                if (strlen($newname) > MAX_PAGENAME_LENGTH)
                    $ul->pushContent(HTML::li(_("Cannot rename. New page name too long.")));
                elseif ($dbi->isWikiPage($newname))
                    $ul->pushContent(HTML::li(fmt("Page “%s” already exists. Ignored.",
                        WikiLink($newname)))); elseif (!mayAccessPage('edit', $name))
                    $ul->pushContent(HTML::li(fmt("Access denied to rename page “%s”.",
                        WikiLink($name)))); elseif ($dbi->renamePage($name, $newname, $updatelinks)) {
                    /* not yet implemented for all backends */
                    $page = $dbi->getPage($newname);
                    $current = $page->getCurrentRevision();
                    $version = $current->getVersion();
                    $meta = $current->_data;
                    $text = $current->getPackedContent();
                    $meta['summary'] = sprintf(_("Renamed page from “%s” to “%s”."), $name, $newname);
                    $meta['is_minor_edit'] = 1;
                    $meta['author'] = $request->_user->UserName();
                    unset($meta['mtime']); // force new date
                    $page->save($text, $version + 1, $meta);
                    if ($createredirect) {
                        $page = $dbi->getPage($name);
                        $text = "<<RedirectTo page=\"" . $newname . "\">>";
                        $meta['summary'] =
                            sprintf(_("Renaming created redirect page from “%s” to “%s”"),
                                $name, $newname);
                        $meta['is_minor_edit'] = 0;
                        $meta['author'] = $request->_user->UserName();
                        $page->save($text, 1, $meta);
                    }
                    $ul->pushContent(HTML::li(fmt("Renamed page from “%s” to “%s”.",
                        $name, WikiLink($newname))));
                    $count++;
                } else {
                    $ul->pushContent(HTML::li(fmt("Couldn't rename page “%s” to “%s”.",
                        $name, $newname)));
                }
            } else {
                $ul->pushContent(HTML::li(fmt("Couldn't rename page “%s” to “%s”.",
                    $name, $newname)));
            }
        }
        if ($count) {
            $dbi->touch();
            $result->setAttr('class', 'feedback');
            if ($count == 1) {
                $result->pushContent(HTML::p(
                    _("One page has been renamed:")));
            } else {
                $result->pushContent(HTML::p(
                    fmt("%d pages have been renamed:", $count)));
            }
            $result->pushContent($ul);
            return $result;
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(fmt("No pages renamed.")));
            $result->pushContent($ul);
            return $result;
        }
    }

    private function renameForm(&$header, $post_args, $singlepage)
    {
        $table = HTML::table();
        $this->tablePush($table, _("Rename") . " " . _("from") . _(": "),
            HTML::input(array('name' => 'admin_rename[from]',
                'size' => MAX_PAGENAME_LENGTH,
                'maxlength' => MAX_PAGENAME_LENGTH,
                'readonly' => 'readonly',
                'value' => $post_args['from'])));
        $this->tablePush($table, _("to") . _(": "),
            HTML::input(array('name' => 'admin_rename[to]',
                'size' => MAX_PAGENAME_LENGTH,
                'maxlength' => MAX_PAGENAME_LENGTH,
                'value' => $post_args['to'])));
        if ($singlepage === false) {
            $this->tablePush($table, '',
                $this->checkBox($post_args, 'regex', _("Regex?")));
            $this->tablePush($table, '',
                $this->checkBox($post_args, 'icase', _("Case insensitive?")));
        }
        if (defined('EXPERIMENTAL') and EXPERIMENTAL) // not yet stable
            $this->tablePush($table, '',
                $this->checkBox($post_args, 'updatelinks',
                    _("Change pagename in all linked pages also?")));
        $this->tablePush($table, '',
            $this->checkBox($post_args, 'createredirect',
                _("Create redirect from old to new name?")));
        $header->pushContent($table);
        return $header;
    }
}

// TODO: grey out unchangeble pages, even in the initial list also?
// TODO: autoselect by matching name javascript in admin_rename[from]
// TODO: update rename[] fields when case-sensitive and regex is changed

// moved from lib/PageList.php
class _PageList_Column_renamed_pagename extends _PageList_Column
{
    function _getValue($page_handle, &$revision_handle)
    {
        global $request;
        $post_args = $request->getArg('admin_rename');
        $options =
            array('regex' => isset($post_args['regex']) ? $post_args['regex'] : null,
                'icase' => isset($post_args['icase']) ? $post_args['icase'] : null);
        $value = $post_args
            ? WikiPlugin_WikiAdminRename::renameHelper
            ($page_handle->getName(),
                $post_args['from'], $post_args['to'],
                $options)
            : $page_handle->getName();
        $div = HTML::div(" => ", HTML::input(array('type' => 'text',
            'name' => 'rename[]',
            'value' => $value)));
        $new_page = $request->getPage($value);
        return $div;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
