<?php // -*-php-*-
// $Id: WikiAdminPurge.php 8005 2011-03-31 08:45:20Z vargenau $
/*
 * Copyright 2002,2004 $ThePhpWikiProgrammingTeam
 * Copyright 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Usage:   <<WikiAdminPurge>>
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminPurge
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminPurge");
    }

    function getDescription() {
        return _("Permanently purge all selected pages.");
    }

    /* getDefaultArguments() is inherited from WikiAdminSelect class */

    function collectPages(&$list, &$dbi, $sortby, $limit=0) {

        $allPages = $dbi->getAllPages('include_empty',$sortby,$limit);
        while ($pagehandle = $allPages->next()) {
            $pagename = $pagehandle->getName();
            $current = $pagehandle->getCurrentRevision();
            if ($current->getVersion() < 1) {
                continue;       // No versions in database
            }
            if (empty($list[$pagename])) {
                $list[$pagename] = false;
            }
        }
        return $list;
    }

    function purgePages(&$request, $pages) {
        $result = HTML::div();
        $ul = HTML::ul();
        $dbi = $request->getDbh(); $count = 0;
        foreach ($pages as $name) {
            $name = str_replace(array('%5B','%5D'),array('[',']'),$name);
            if (mayAccessPage('purge',$name)) {
                $dbi->purgePage($name);
                $ul->pushContent(HTML::li(fmt("Purged page '%s' successfully.", $name)));
                $count++;
            } else {
                    $ul->pushContent(HTML::li(fmt("Didn't purge page '%s'. Access denied.", $name)));
            }
        }
        if ($count) {
            $dbi->touch();
            $result->setAttr('class', 'feedback');
            if ($count == 1) {
                $result->pushContent(HTML::p(_("One page has been permanently purged:")));
            } else {
                $result->pushContent(HTML::p(fmt("%d pages have been permanently purged:", $count)));
            }
            $result->pushContent($ul);
            return $result;
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(_("No pages purged.")));
            return $result;
        }
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if ($request->getArg('action') != 'browse')
            if ($request->getArg('action') != _("PhpWikiAdministration/Purge"))
                return $this->disabled("(action != 'browse')");

        $args = $this->getArgs($argstr, $request);
        $this->_args =& $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        if (!$p) $p = $this->_list;
        $post_args = $request->getArg('admin_purge');

        $next_action = 'select';
        $pages = array();
        if ($p && $request->isPost() &&
            !empty($post_args['purge']) && empty($post_args['cancel'])) {

            // check individual PagePermissions
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            if ($post_args['action'] == 'verify') {
                // Real purge.
                return $this->purgePages($request, array_keys($p));
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
        $pagelist = new PageList_Selectable($args['info'], $args['exclude'], array());
        $pagelist->addPageList($pages);

        $header = HTML::fieldset();
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header->pushContent(HTML::p(HTML::strong(
                _("Are you sure you want to permanently purge the following files?"))));
        }
        else {
            $button_label = _("Permanently purge selected pages");
            $header->pushContent(HTML::legend(_("Select the files to purge")));
        }

        $buttons = HTML::p(Button('submit:admin_purge[purge]', $button_label, 'wikiadmin'),
                           Button('submit:admin_purge[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        // TODO: quick select by regex javascript?
        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_purge')),
                          HiddenInputs(array('admin_purge[action]' => $next_action,
                                             'require_authority_for_post' => WIKIAUTH_ADMIN)));
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
