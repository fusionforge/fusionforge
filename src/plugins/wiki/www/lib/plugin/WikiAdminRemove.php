<?php // -*-php-*-
// $Id: WikiAdminRemove.php 8005 2011-03-31 08:45:20Z vargenau $
/*
 * Copyright 2002,2004 $ThePhpWikiProgrammingTeam
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
 * Usage:   <<WikiAdminRemove>>
 * Author:  Reini Urban <rurban@x-ray.at>
 *
 * KNOWN ISSUES:
 * Currently we must be Admin.
 * Future versions will support PagePermissions.
 */
// maybe display more attributes with this class...
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminRemove
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminRemove");
    }

    function getDescription() {
        return _("Permanently remove all selected pages.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             WikiPlugin_WikiAdminSelect::getDefaultArguments(),
             array(
                     /*
                      * Show only pages which have been 'deleted' this
                      * long (in days).  (negative or non-numeric
                      * means show all pages, even non-deleted ones.)
                      *
                      * FIXME: could use a better name.
                      */
                     'min_age' => 0,

                     /*
                      * Automatically check the checkboxes for files
                      * which have been 'deleted' this long (in days).
                      *
                      * FIXME: could use a better name.
                      */
                     'max_age' => 31,
                     /* Columns to include in listing */
                     'info'     => 'most',
                   ));
    }

    function collectPages(&$list, &$dbi, $sortby, $limit=0) {
        extract($this->_args);

        $now = time();

        $allPages = $dbi->getAllPages('include_empty',$sortby,$limit);
        while ($pagehandle = $allPages->next()) {
            $pagename = $pagehandle->getName();
            $current = $pagehandle->getCurrentRevision();
            if ($current->getVersion() < 1)
                continue;       // No versions in database

            $empty = $current->hasDefaultContents();
            if ($empty) {
                $age = ($now - $current->get('mtime')) / (24 * 3600.0);
                $checked = $age >= $max_age;
            }
            else {
                $age = 0;
                $checked = false;
            }

            if ($age >= $min_age) {
                if (empty($list[$pagename]))
                    $list[$pagename] = $checked;
            }
        }
        return $list;
    }

    function removePages(&$request, $pages) {
        $result = HTML::div();
        $ul = HTML::ul();
        $dbi = $request->getDbh(); $count = 0;
        foreach ($pages as $name) {
            $name = str_replace(array('%5B','%5D'),array('[',']'),$name);
            if (mayAccessPage('remove',$name)) {
                $dbi->deletePage($name);
                $ul->pushContent(HTML::li(fmt("Removed page '%s' successfully.", $name)));
                $count++;
            } else {
                    $ul->pushContent(HTML::li(fmt("Didn't remove page '%s'. Access denied.", $name)));
            }
        }
        if ($count) {
            $dbi->touch();
            $result->setAttr('class', 'feedback');
            if ($count == 1) {
                $result->pushContent(HTML::p(_("One page has been removed:")));
            } else {
                $result->pushContent(HTML::p(fmt("%d pages have been removed:", $count)));
            }
            $result->pushContent($ul);
            return $result;
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(_("No pages removed.")));
            return $result;
        }
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if ($request->getArg('action') != 'browse')
            if ($request->getArg('action') != _("PhpWikiAdministration/Remove"))
                return $this->disabled("(action != 'browse')");

        $args = $this->getArgs($argstr, $request);
        if (!is_numeric($args['min_age']))
            $args['min_age'] = -1;
        $this->_args =& $args;
        /*if (!empty($args['exclude']))
            $exclude = explodePageList($args['exclude']);
        else
        $exclude = false;*/
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_remove');

        $next_action = 'select';
        $pages = array();
        if ($p && $request->isPost() &&
            !empty($post_args['remove']) && empty($post_args['cancel'])) {

            // check individual PagePermissions
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            if ($post_args['action'] == 'verify') {
                // Real delete.
                return $this->removePages($request, array_keys($p));
            }

            if ($post_args['action'] == 'select') {
                $next_action = 'verify';
                foreach ($p as $name => $c) {
                    $name = str_replace(array('%5B','%5D'),array('[',']'),$name);
                    $pages[$name] = $c;
                }
            }
        } elseif ($p && is_array($p) && !$request->isPost()) { // from WikiAdminSelect
            $next_action = 'verify';
            foreach ($p as $name => $c) {
                $name = str_replace(array('%5B','%5D'),array('[',']'),$name);
                $pages[$name] = $c;
            }
            $request->setArg('p',false);
        }
        if ($next_action == 'select') {
            // List all pages to select from.
            $pages = $this->collectPages($pages, $dbi, $args['sortby'], $args['limit'], $args['exclude']);
        }
        $pagelist = new PageList_Selectable($args['info'], $args['exclude'],
                                            array('types' =>
                                                  array('remove'
                                                        => new _PageList_Column_remove('remove', _("Remove")))));
        $pagelist->addPageList($pages);

        $header = HTML::fieldset();
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header->pushContent(HTML::p(HTML::strong(
                _("Are you sure you want to remove the selected files?"))));
        }
        else {
            $button_label = _("Remove selected pages");
            $header->pushContent(HTML::legend(_("Select the files to remove")));
            if ($args['min_age'] > 0) {
                $header->pushContent(
                    fmt("Also pages which have been deleted at least %s days.",
                        $args['min_age']));
            }

            if ($args['max_age'] > 0) {
                $header->pushContent(
                    " ",
                    fmt("Pages which have been deleted at least %s days are already checked.",
                        $args['max_age']));
            }
        }

        $buttons = HTML::p(Button('submit:admin_remove[remove]', $button_label, 'wikiadmin'),
                           Button('submit:admin_remove[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        // TODO: quick select by regex javascript?
        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_remove')),
                          HiddenInputs(array('admin_remove[action]' => $next_action,
                                             'require_authority_for_post' => WIKIAUTH_ADMIN)));
    }
}

class _PageList_Column_remove extends _PageList_Column {
    function _getValue ($page_handle, &$revision_handle) {
        return Button(array('action' => 'remove'), _("Remove"),
                      $page_handle->getName());
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
