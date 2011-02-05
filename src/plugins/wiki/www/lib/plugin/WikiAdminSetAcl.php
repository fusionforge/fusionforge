<?php // -*-php-*-
// rcs_id('$Id: WikiAdminSetAcl.php 7850 2011-01-21 09:41:05Z vargenau $');
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * Set individual PagePermissions
 *
 * Usage:   <<WikiAdminSetAcl >> or called via WikiAdminSelect
 * Author:  Reini Urban <rurban@x-ray.at>
 *
 * TODO: UI to add custom group/username.
 * Currently it's easier to dump a page, fix it manually and
 * import it, than use Setacl
 */
require_once('lib/PageList.php');
require_once('lib/plugin/WikiAdminSelect.php');

class WikiPlugin_WikiAdminSetAcl
extends WikiPlugin_WikiAdminSelect
{
    function getName() {
        return _("WikiAdminSetAcl");
    }

    function getDescription() {
        return _("Set individual page permissions.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             WikiPlugin_WikiAdminSelect::getDefaultArguments(),
             array(
                     'p'        => "[]",  // list of pages
                     /* Columns to include in listing */
                     'info'     => 'pagename,perm,mtime,owner,author',
                     ));
    }

    function setaclPages(&$request, $pages, $acl) {
        $result = HTML::div();
        $count = 0;
        $dbi =& $request->_dbi;
        // check new_group and new_perm
        if (isset($acl['_add_group'])) {
            //add groups with perm
            foreach ($acl['_add_group'] as $access => $dummy) {
                $group = $acl['_new_group'][$access];
                $acl[$access][$group] = isset($acl['_new_perm'][$access]) ? 1 : 0;
            }
            unset($acl['_add_group']);
        }
        unset($acl['_new_group']); unset($acl['_new_perm']);
        if (isset($acl['_del_group'])) {
            //del groups with perm
            foreach ($acl['_del_group'] as $access => $del) {
                while (list($group,$dummy) = each($del))
                    unset($acl[$access][$group]);
            }
            unset($acl['_del_group']);
        }
        if ($perm = new PagePermission($acl)) {
            $perm->sanify();
            foreach ($pages as $pagename) {
                    // check if unchanged? we need a deep array_equal
                    $page = $dbi->getPage($pagename);
                    $oldperm = getPagePermissions($page);
                if ($oldperm)
                    $oldperm->sanify();
                    if ($oldperm and $perm->equal($oldperm->perm)) {
                    $result->setAttr('class', 'error');
                    $result->pushContent(HTML::p(fmt("ACL not changed for page '%s'.",$pagename)));
                } elseif (mayAccessPage('change', $pagename)) {
                    setPagePermissions ($page, $perm);
                    $result->setAttr('class', 'feedback');
                    $result->pushContent(HTML::p(fmt("ACL changed for page '%s'",
                                                     $pagename)));
                    $result->pushContent(HTML::p(fmt("from '%s'",
                                                     $oldperm ? $oldperm->asAclLines() : "None")));
                    $result->pushContent(HTML::p(fmt("to '%s'.",
                                                     $perm->asAclLines())));

                    // Create new revision so that ACL change appears in history.
                    $current = $page->getCurrentRevision();
                    $version = $current->getVersion();
                    $meta = $current->_data;
                    $text = $current->getPackedContent();
                    $meta['summary'] = sprintf(_("ACL changed for page '%s' from '%s' to '%s'."),
                                               $pagename,
                                               $oldperm ? $oldperm->asAclLines() : "None",
                                               $perm->asAclLines());
                    $meta['is_minor_edit'] = 1;
                    $meta['author'] =  $request->_user->UserName();
                    unset($meta['mtime']); // force new date
                    $page->save($text, $version + 1, $meta);

                    $count++;
                } else {
                    $result->setAttr('class', 'error');
                    $result->pushContent(HTML::p(fmt("Access denied to change page '%s'.",$pagename)));
                }
            }
        } else {
            $result->pushContent(HTML::p(fmt("Invalid ACL")));
        }
        if ($count) {
            $dbi->touch();
            $result->setAttr('class', 'feedback');
            if ($count > 1) {
                $result->pushContent(HTML::p(fmt("%s pages have been changed.",$count)));
            }
        } else {
            $result->setAttr('class', 'error');
            $result->pushContent(HTML::p(fmt("No pages changed.")));
        }
        return $result;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if ($request->getArg('action') != 'browse')
            if ($request->getArg('action') != _("PhpWikiAdministration/SetAcl"))
                return $this->disabled("(action != 'browse')");
        if (!ENABLE_PAGEPERM)
            return $this->disabled("ENABLE_PAGEPERM = false");

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        $post_args = $request->getArg('admin_setacl');
        $next_action = 'select';
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        elseif ($this->_list)
            $pages = $this->_list;
        $header = HTML::fieldset();
        if ($p && $request->isPost() &&
            !empty($post_args['acl']) && empty($post_args['cancel'])) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            if ($post_args['action'] == 'verify') {
                // Real action
                return $this->setaclPages($request, array_keys($p), $request->getArg('acl'));
            }
            if ($post_args['action'] == 'select') {
                if (!empty($post_args['acl']))
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
            $args['info'] = "checkbox,pagename,perm,mtime,owner,author";
        }
        $pagelist = new PageList_Selectable($args['info'],
                                            $args['exclude'],
                                            array('types' => array(
                                                  'perm'
                                                  => new _PageList_Column_perm('perm', _("Permission")),
                                                  'acl'
                                                  => new _PageList_Column_acl('acl', _("ACL")))));

        $pagelist->addPageList($pages);
        if ($next_action == 'verify') {
            $button_label = _("Yes");
            $header = $this->setaclForm($header, $post_args, $pages);
            $header->pushContent(
              HTML::p(HTML::strong(
                  _("Are you sure you want to permanently change access rights to the selected files?"))));
        }
        else {
            $button_label = _("Change Access Rights");
            $header = $this->setaclForm($header, $post_args, $pages);
            $header->pushContent(HTML::legend(_("Select the pages where to change access rights")));
        }

        $buttons = HTML::p(Button('submit:admin_setacl[acl]', $button_label, 'wikiadmin'),
                           Button('submit:admin_setacl[cancel]', _("Cancel"), 'button'));
        $header->pushContent($buttons);

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_setacl')),
                          HiddenInputs(array('admin_setacl[action]' => $next_action)),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    function setaclForm(&$header, $post_args, $pagehash) {
        $acl = $post_args['acl'];

        //FIXME: find intersection of all pages perms, not just from the last pagename
        $pages = array();
        foreach ($pagehash as $name => $checked) {
           if ($checked) $pages[] = $name;
        }
        $perm_tree = pagePermissions($name);
        $table = pagePermissionsAclFormat($perm_tree, !empty($pages));
        $header->pushContent(HTML::strong(_("Selected Pages: ")), HTML::tt(join(', ',$pages)), HTML::br());
        $first_page = $GLOBALS['request']->_dbi->getPage($name);
        $owner = $first_page->getOwner();
        list($type, $perm) = pagePermissionsAcl($perm_tree[0], $perm_tree);
        //if (DEBUG) $header->pushContent(HTML::pre("Permission tree for $name:\n",print_r($perm_tree,true)));
        if ($type == 'inherited')
            $type = sprintf(_("page permission inherited from %s"), $perm_tree[1][0]);
        elseif ($type == 'page')
            $type = _("individual page permission");
        elseif ($type == 'default')
            $type = _("default page permission");
        $header->pushContent(HTML::strong(_("Type")._(": ")), HTML::tt($type),HTML::br());
        $header->pushContent(HTML::strong(_("ACL")._(": ")), HTML::tt($perm->asAclLines()),HTML::br());

        $header->pushContent(HTML::p(HTML::strong(_("Description")._(": ")),
                                     _("Selected Grant checkboxes allow access, unselected checkboxes deny access."),
                                     _("To ignore delete the line."),
                                     _("To add check 'Add' near the dropdown list.")
                                     ));
        $header->pushContent($table);
        //
        // display array of checkboxes for existing perms
        // and a dropdown for user/group to add perms.
        // disabled if inherited,
        // checkbox to disable inheritance,
        // another checkbox to progate new permissions to all childs (if there exist some)
        //Todo:
        // warn if more pages are selected and they have different perms
        //$header->pushContent(HTML::input(array('name' => 'admin_setacl[acl]',
        //                                       'value' => $post_args['acl'])));
        $header->pushContent(HTML::br());
        if (!empty($pages) and defined('EXPERIMENTAL') and EXPERIMENTAL) {
          $checkbox = HTML::input(array('type' => 'checkbox',
                                        'name' => 'admin_setacl[updatechildren]',
                                        'value' => 1));
          if (!empty($post_args['updatechildren']))  $checkbox->setAttr('checked','checked');
          $header->pushContent($checkbox,
                    _("Propagate new permissions to all subpages?"),
                  HTML::raw("&nbsp;&nbsp;"),
                  HTML::em(_("(disable individual page permissions, enable inheritance)?")),
                  HTML::br(),HTML::em(_("(Currently not working)"))
                               );
        }
        $header->pushContent(HTML::hr());
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
