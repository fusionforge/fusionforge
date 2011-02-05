<?php // -*-php-*-
// rcs_id('$Id: WikiAdminChown.php 7925 2011-02-01 10:08:52Z vargenau $');
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * Usage:   <<WikiAdminChown s||=* >> or called via WikiAdminSelect
 * @author:  Reini Urban <rurban@x-ray.at>
 *
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminChown
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminChown");
    }

    function getDescription() {
        return _("Change owner of selected pages.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             WikiPlugin_WikiAdminSelect::getDefaultArguments(),
             array(
                   'user'         => false,
                   /* Columns to include in listing */
                   'info'     => 'pagename,owner,mtime',
                   ));
    }

    function chownPages(&$dbi, &$request, $pages, $newowner) {
        $result = HTML::div();
        $ul = HTML::ul();
        $count = 0;
        foreach ($pages as $name) {
            $page = $dbi->getPage($name);
            $current = $page->getCurrentRevision();
            if ( ($owner = $page->getOwner()) and
                 $newowner != $owner ) {
                if (!mayAccessPage('change', $name)) {
                    $ul->pushContent(HTML::li(fmt("Access denied to change page '%s'.",
                                                  WikiLink($name))));
                } else {
                    $version = $current->getVersion();
                    $meta = $current->_data;
                    $text = $current->getPackedContent();
                    $meta['summary'] = "Change page owner from '".$owner."' to '".$newowner."'";
                    $meta['is_minor_edit'] = 1;
                    $meta['author'] =  $request->_user->UserName();
                    unset($meta['mtime']); // force new date
                    $page->set('owner', $newowner);
                    $page->save($text, $version + 1, $meta);
                    if ($page->get('owner') === $newowner) {
                        $ul->pushContent(HTML::li(fmt("Change owner of page '%s' to '%s'.",
                                                      WikiLink($name), WikiLink($newowner))));
                        $count++;
                    } else {
                        $ul->pushContent(HTML::li(fmt("Could not change owner of page '%s' to '%s'.",
                                                      WikiLink($name), $newowner)));
                    }
                }
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
            return $result;
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(_("No pages changed.")));
            return $result;
        }
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if ($request->getArg('action') != 'browse')
            if (!$request->getArg('action') == _("PhpWikiAdministration/Chown"))
                return $this->disabled("(action != 'browse')");

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        if (empty($args['user']))
            $args['user'] = $request->_user->UserName();
        /*if (!empty($args['exclude']))
            $exclude = explodePageList($args['exclude']);
        else
        $exclude = false;*/
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_chown');
        if (!$request->isPost() and empty($post_args['user']))
            $post_args['user'] = $args['user'];
        $next_action = 'select';
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        if ($p && $request->isPost() &&
            !empty($post_args['chown']) && empty($post_args['cancel'])) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            // DONE: error message if not allowed.
            if ($post_args['action'] == 'verify') {
                // Real action
                return $this->chownPages($dbi, $request, array_keys($p),
                                          trim($post_args['user']));
            }
            if ($post_args['action'] == 'select') {
                if (!empty($post_args['user']))
                    $next_action = 'verify';
                foreach ($p as $name => $c) {
                    $pages[$name] = 1;
                }
            }
        }
        if ($next_action == 'select' and empty($pages)) {
            // List all pages to select from.
            $pages = $this->collectPages($pages, $dbi, $args['sortby'], $args['limit'],
                                         $args['exclude']);
        }
        /* // let the user decide which info
         if ($next_action == 'verify') {
            $args['info'] = "checkbox,pagename,owner,mtime";
        }
        */
        if ($next_action == 'select') {
            $pagelist = new PageList_Selectable($args['info'], $args['exclude'], $args);
        } else {
            $pagelist = new PageList_Unselectable($args['info'], $args['exclude'], $args);
        }
        $pagelist->addPageList($pages);

        $header = HTML::fieldset();
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header->pushContent(
              HTML::p(HTML::strong(
                _("Are you sure you want to permanently change the owner of the selected pages?"))));
            $header = $this->chownForm($header, $post_args);
        }
        else {
            $button_label = _("Change owner of selected pages");
            $header->pushContent(HTML::legend(_("Select the pages to change the owner")));
            $header = $this->chownForm($header, $post_args);
        }

        $buttons = HTML::p(Button('submit:admin_chown[chown]', $button_label, 'wikiadmin'),
                           Button('submit:admin_chown[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_chown')),
                          HiddenInputs(array('admin_chown[action]' => $next_action)),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    function chownForm(&$header, $post_args) {
        $header->pushContent(_("Change owner to: "));
        $header->pushContent(HTML::input(array('name' => 'admin_chown[user]',
                                               'value' => $post_args['user'],
                                               'size' => 40)));
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
?>
