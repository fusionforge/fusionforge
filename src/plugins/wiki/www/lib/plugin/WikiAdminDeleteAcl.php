<?php

/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
 * Copyright 2009-2010 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Delete individual PagePermissions
 *
 * Usage:   <<WikiAdminAclDelete >> or called via WikiAdminSelect
 * Author:  Marc-Etienne Vargenau, Alcatel-Lucent
 *
 */

require_once 'lib/PageList.php';
require_once 'lib/plugin/WikiAdminSelect.php';

class WikiPlugin_WikiAdminDeleteAcl
    extends WikiPlugin_WikiAdminSelect
{
    function getDescription()
    {
        return _("Delete page permissions.");
    }

    function deleteaclPages(&$request, $pages)
    {
        $result = HTML::div();
        $count = 0;
        $dbi =& $request->_dbi;
        $perm = new PagePermission('');
        $perm->sanify();
        foreach ($pages as $pagename) {
            // check if unchanged? we need a deep array_equal
            $page = $dbi->getPage($pagename);
            setPagePermissions($page, $perm);
            $result->setAttr('class', 'feedback');
            $result->pushContent(HTML::p(fmt("ACL deleted for page “%s”", $pagename)));
            $current = $page->getCurrentRevision();
            $version = $current->getVersion();
            $meta = $current->_data;
            $text = $current->getPackedContent();
            $meta['summary'] = sprintf(_("ACL deleted for page “%s”"), $pagename);
            $meta['is_minor_edit'] = 1;
            $meta['author'] = $request->_user->UserName();
            unset($meta['mtime']); // force new date
            $page->save($text, $version + 1, $meta);
            $count++;
        }
        if ($count) {
            $dbi->touch();
            $result->setAttr('class', 'feedback');
            if ($count > 1) {
                $result->pushContent(HTML::p(fmt("%d pages have been changed.", $count)));
            }
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(_("No pages changed.")));
        }
        return $result;
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        if ($request->getArg('action') != 'browse') {
            if ($request->getArg('action') != __("PhpWikiAdministration")."/".__("AdminDeleteAcl")) {
                return $this->disabled(_("Plugin not run: not in browse mode"));
            }
        }
        if (!ENABLE_PAGEPERM) {
            return $this->disabled("ENABLE_PAGEPERM = false");
        }

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        $post_args = $request->getArg('admin_deleteacl');
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        elseif ($this->_list)
            $pages = $this->_list;
        $header = HTML::fieldset();
        if ($p && $request->isPost()) {
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            return $this->deleteaclPages($request, array_keys($p));
        }
        if (empty($pages)) {
            // List all pages to select from.
            $pages = $this->collectPages($pages, $dbi, $args['sortby'], $args['limit'], $args['exclude']);
        }
        $pagelist = new PageList_Selectable($args['info'],
            $args['exclude'],
            array('types' => array(
                'acl'
                => new _PageList_Column_acl('acl', _("ACL")))));

        $pagelist->addPageList($pages);
        $button_label_delete_acl = _("Delete ACL");
        $header = $this->deleteaclForm($header, $pages);
        $header->pushContent(HTML::legend(_("Select the pages where to delete access rights")));

        $buttons = HTML::p(Button('submit:admin_deleteacl', $button_label_delete_acl, 'wikiadmin'));
        $header->pushContent($buttons);

        return HTML::form(array('action' => $request->getPostURL(),
                'method' => 'post'),
            $header,
            $pagelist->getContent(),
            HiddenInputs($request->getArgs(),
                false,
                array('admin_deleteacl')),
            ENABLE_PAGEPERM
                ? ''
                : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    function deleteaclForm(&$header, $pagehash)
    {

        $pages = array();
        foreach ($pagehash as $name => $checked) {
            if ($checked) $pages[] = $name;
        }

        $header->pushContent(HTML::strong(_("Selected Pages: ")), HTML::tt(join(', ', $pages)), HTML::br());
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
