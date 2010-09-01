<?php // -*-php-*-
// rcs_id('$Id: WikiAdminChmod.php 7647 2010-08-23 15:10:10Z vargenau $');
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
 * Copyright 2008 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Set individual PagePermissions, simplifying effective ACLs to
 * unix-like rwxr--r--+ permissions. (as in cygwin)
 *
 * Usage:   <<WikiAdminChmod >> or called via WikiAdminSelect
 * Author:  Reini Urban <rurban@x-ray.at>
 *
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminChmod
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminChmod");
    }

    function getDescription() {
        return _("Set individual page permissions.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array(
                   's'                 => false,
                   'perm'         => false,
                   /* Columns to include in listing */
                   'info'     => 'pagename,perm,mtime,author',
                   ));
    }

    // todo: change permstring to some kind of default ACL hash.
    // See PagePermission class
    function chmodHelper($permstring) {
        $perm = array();
        return $perm;
    }

    function chmodPages(&$dbi, &$request, $pages, $permstring) {
        $ul = HTML::ul();
        $count = 0;
        $acl = chmodHelper($permstring);
        if ($perm = new PagePermission($acl)) {
            foreach ($pages as $name) {
                if ( $perm->store($dbi->getPage($name)) ) {
                    $ul->pushContent(HTML::li(fmt("chmod page '%s' to '%s'.",$name, $permstring)));
                    $count++;
                } else {
                    $ul->pushContent(HTML::li(fmt("Couldn't chmod page '%s' to '%s'.", $name, $permstring)));
                }
            }
        } else {
            $ul->pushContent(HTML::li(fmt("Invalid chmod string")));
        }
        if ($count) {
            $dbi->touch();
            return HTML($ul,
                        HTML::p(fmt("%s pages have been changed.",$count)));
        } else {
            return HTML($ul,
                        HTML::p(fmt("No pages changed.")));
        }
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if (!DEBUG)
            return $this->disabled("WikiAdminChmod not yet enabled. Set DEBUG to try it.");

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_chmod');
        $next_action = 'select';
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        if ($p && $request->isPost() &&
            !empty($post_args['chmod']) && empty($post_args['cancel'])) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }

            if ($post_args['action'] == 'verify') {
                // Real action
                return $this->chmodPages($dbi, $request, array_keys($p),
                                          $post_args['perm']);
            }
            if ($post_args['action'] == 'select') {
                if (!empty($post_args['perm']))
                    $next_action = 'verify';
                foreach ($p as $name => $c) {
                    $pages[$name] = 1;
                }
            }
        }
        if ($next_action == 'select' and empty($pages)) {
            // List all pages to select from.
            $pages = $this->collectPages($pages, $dbi, $args['sortby'], $args['limit'], $args['exclude']);
        }
        if ($next_action == 'verify') {
            $args['info'] = "checkbox,pagename,perm,author,mtime";
        }
        $args['types'] = array('perm' => new _PageList_Column_chmod_perm('perm', _("Permission")));
        $pagelist = new PageList_Selectable($args['info'], $args['exclude'], $args);
        $pagelist->addPageList($pages);

        $header = HTML::p();
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header = $this->chmodForm($header, $post_args);
            $header->pushContent(
              HTML::p(HTML::strong(
                _("Are you sure you want to permanently change the selected files?"))));
        }
        else {
            $button_label = _("Chmod");
            $header = $this->chmodForm($header, $post_args);
            $header->pushContent(HTML::p(_("Select the pages to change:")));
        }

        $buttons = HTML::p(Button('submit:admin_chmod[chmod]', $button_label, 'wikiadmin'),
                           Button('submit:admin_chmod[cancel]', _("Cancel"), 'button'));

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $buttons,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_chmod')),
                          HiddenInputs(array('admin_chmod[action]' => $next_action)),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    function chmodForm(&$header, $post_args) {
        $header->pushContent(
            HTML::p(HTML::em(
               _("This plugin is currently under development and does not work!"))));
        $header->pushContent(_("Chmod to permission:"));
        $header->pushContent(HTML::input(array('name' => 'admin_chmod[perm]',
                                               'value' => $post_args['perm'])));
        $header->pushContent(' '._("(ugo : rwx)"));
        $header->pushContent(HTML::p());
        $checkbox = HTML::input(array('type' => 'checkbox',
                                      'name' => 'admin_chmod[updatechildren]',
                                      'value' => 1));
        if (!empty($post_args['updatechildren']))  $checkbox->setAttr('checked','checked');
        $header->pushContent($checkbox, HTML::raw("&nbsp;"),
                _("Propagate new permissions to all subpages?"),
                HTML::raw("&nbsp;&nbsp;"),
                HTML::em(_("(disable individual page permissions, enable inheritance)?")));
        $header->pushContent(HTML::hr(),HTML::p());
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
