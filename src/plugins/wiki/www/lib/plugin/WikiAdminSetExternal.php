<?php // -*-php-*-
// $Id: WikiAdminSetExternal.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2005 $ThePhpWikiProgrammingTeam
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

/**
 * Usage:   <<WikiAdminSetExternal s||=* >> or called via WikiAdminSelect
 * @author:  Marc-Etienne Vargenau, Alcatel-Lucent
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminSetExternal
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminSetExternal");
    }

    function getDescription() {
        return _("Mark selected pages as external.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             WikiPlugin_WikiAdminSelect::getDefaultArguments(),
             array(
                   'external'         => 1,
                   /* Columns to include in listing */
                   'info'     => 'pagename,external,mtime',
                   ));
    }

    function setExternalPages(&$dbi, &$request, $pages) {
        $result = HTML::div();
        $ul = HTML::ul();
        $count = 0;
        foreach ($pages as $name) {
            $page = $dbi->getPage($name);
            $current = $page->getCurrentRevision();
            $external = $current->get('external');
            if (!$external) $external = 0;
            $external = (bool)$external;
            if (!$external) {
                if (!mayAccessPage('change', $name)) {
                    $result->setAttr('class', 'error');
                    $result->pushContent(HTML::p(fmt("Access denied to change page '%s'.",
                                                  WikiLink($name))));
                } else {
                    $version = $current->getVersion();
                    $page->set('external', (bool)1);
                    $ul->pushContent(HTML::li(fmt("change page '%s' to external.", WikiLink($name))));
                    $count++;
                }
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
            return $result;
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(_("No pages changed.")));
            return $result;
        }
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if ($request->getArg('action') != 'browse')
            if (!$request->getArg('action') == _("PhpWikiAdministration/SetExternal"))
                return $this->disabled("(action != 'browse')");

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_external');
        if (!$request->isPost() and empty($post_args['external']))
            $post_args['external'] = $args['external'];
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        if ($p && $request->isPost() &&
            !empty($post_args['button']) && empty($post_args['cancel'])) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            // Real action
            return $this->setExternalPages($dbi, $request, array_keys($p));
        }
        $pages = $this->collectPages($pages, $dbi, $args['sortby'], $args['limit'], $args['exclude']);
        $pagelist = new PageList_Selectable($args['info'], $args['exclude'], $args);
        $pagelist->addPageList($pages);

        $header = HTML::fieldset();
        $button_label = _("Set pages to external");
        $header->pushContent(HTML::legend(_("Select the pages to set as external")));

        $buttons = HTML::p(Button('submit:admin_external[button]', $button_label, 'wikiadmin'),
                           Button('submit:admin_external[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_external')),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
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
