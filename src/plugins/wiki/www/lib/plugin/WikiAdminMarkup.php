<?php // -*-php-*-
// $Id: WikiAdminMarkup.php 8071 2011-05-18 14:56:14Z vargenau $
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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Usage:   <<WikiAdminMarkup s||=* >> or called via WikiAdminSelect
 * @author:  Reini Urban <rurban@x-ray.at>
 *
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminMarkup
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminMarkup");
    }

    function getDescription() {
        return _("Change the markup type of selected pages.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             WikiPlugin_WikiAdminSelect::getDefaultArguments(),
             array(
                   'markup'         => 2,
                   /* Columns to include in listing */
                   'info'     => 'pagename,markup,mtime',
                   ));
    }

    function chmarkupPages(&$dbi, &$request, $pages, $newmarkup) {
        $result = HTML::div();
        $ul = HTML::ul();
        $count = 0;
        foreach ($pages as $name) {
            $page = $dbi->getPage($name);
            $current = $page->getCurrentRevision();
            $markup = $current->get('markup');
            if ( !$markup or $newmarkup != $markup ) {
                if (!mayAccessPage('change', $name)) {
                    $result->setAttr('class', 'error');
                    $result->pushContent(HTML::p(fmt("Access denied to change page '%s'.",
                                                  WikiLink($name))));
                } else {
                    $version = $current->getVersion();
                    $meta = $current->_data;
                    $meta['markup'] = $newmarkup;
                    // convert text?
                    $text = $current->getPackedContent();
                    $meta['summary'] = sprintf(_("Change markup type from %s to %s"), $markup, $newmarkup);
                    $meta['is_minor_edit'] = 1;
                    $meta['author'] =  $request->_user->UserName();
                    unset($meta['mtime']); // force new date
                    $page->save($text, $version + 1, $meta);
                    $current = $page->getCurrentRevision();
                    if ($current->get('markup') === $newmarkup) {
                        $ul->pushContent(HTML::li(fmt("change page '%s' to markup type '%s'.",
                                                      WikiLink($name), $newmarkup)));
                        $count++;
                    } else {
                        $ul->pushContent(HTML::li(fmt("Couldn't change page '%s' to markup type '%s'.",
                                                      WikiLink($name), $newmarkup)));
                    }
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
        if ($request->getArg('action') != 'browse') {
            if (!$request->getArg('action') == _("PhpWikiAdministration/Markup")) {
                return $this->disabled(_("Plugin not run: not in browse mode"));
            }
        }

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_markup');
        if (!$request->isPost() and empty($post_args['markup']))
            $post_args['markup'] = $args['markup'];
        $next_action = 'select';
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
            // DONE: error message if not allowed.
            if ($post_args['action'] == 'verify') {
                // Real action
                return $this->chmarkupPages($dbi, $request, array_keys($p),
                                            $post_args['markup']);
            }
            if ($post_args['action'] == 'select') {
                if (!empty($post_args['markup']))
                    $next_action = 'verify';
                foreach ($p as $name => $c) {
                    $pages[$name] = 1;
                }
            }
        }
        if ($next_action == 'select' and empty($pages)) {
            $pages = $this->collectPages($pages, $dbi, $args['sortby'], $args['limit'],
                                         $args['exclude']);
        }

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
                _("Are you sure you want to change the markup type of the selected files?"))));
            $header = $this->chmarkupForm($header, $post_args);
        }
        else {
            $button_label = _("Change markup type");
            $header->pushContent(HTML::legend(_("Select the pages to change the markup type")));
            $header = $this->chmarkupForm($header, $post_args);
        }

        $buttons = HTML::p(Button('submit:admin_markup[button]', $button_label, 'wikiadmin'),
                           Button('submit:admin_markup[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_markup')),
                          HiddenInputs(array('admin_markup[action]' => $next_action)),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    function chmarkupForm(&$header, $post_args) {
        $header->pushContent(_("Change markup to: "));
        $header->pushContent(HTML::input(array('name' => 'admin_markup[markup]',
                                               'value' => $post_args['markup'])));
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
