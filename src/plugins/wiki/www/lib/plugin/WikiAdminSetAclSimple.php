<?php // -*-php-*-
// rcs_id('$Id: WikiAdminSetAclSimple.php 7644 2010-08-13 13:34:26Z vargenau $');
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * Set simple individual PagePermissions
 *
 * Usage:   <<WikiAdminSetAclSimple >> or called via WikiAdminSelect
 * Author:  Marc-Etienne Vargenau, Alcatel-Lucent
 *
 */

require_once('lib/plugin/WikiAdminSetAcl.php');

class WikiPlugin_WikiAdminSetAclSimple
extends WikiPlugin_WikiAdminSetAcl
{
    function getName() {
        return _("WikiAdminSetAclSimple");
    }

    function getDescription() {
        return _("Set simple individual page permissions.");
    }

    function run($dbi, $argstr, &$request, $basepage) {
        if ($request->getArg('action') != 'browse')
            if ($request->getArg('action') != _("PhpWikiAdministration/SetAclSimple"))
                return $this->disabled("(action != 'browse')");
        if (!ENABLE_PAGEPERM)
            return $this->disabled("ENABLE_PAGEPERM = false");

        $args = $this->getArgs($argstr, $request);
        $this->_args = $args;
        $this->preSelectS($args, $request);

        $p = $request->getArg('p');
        $post_args = $request->getArg('admin_setacl');
        $pages = array();
        if ($p && !$request->isPost())
            $pages = $p;
        elseif ($this->_list)
            $pages = $this->_list;
        $header = HTML::fieldset();
        if ($p && $request->isPost() &&
            (!empty($post_args['aclliberal']) || !empty($post_args['aclrestricted']))) {
            // without individual PagePermissions:
            if (!ENABLE_PAGEPERM and !$request->_user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                $this->disabled("! user->isAdmin");
            }
            if (!empty($post_args['aclliberal'])) {
                return $this->setaclPages($request, array_keys($p), $this->liberalPerms());
            } else if (!empty($post_args['aclrestricted'])) {
                return $this->setaclPages($request, array_keys($p), $this->restrictedPerms());
            }
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
        $button_label_liberal = _("Set Liberal Access Rights");
        $button_label_restrictive = _("Set Restrictive Access Rights");
        $header = $this->setaclForm($header, $pages);
        $header->pushContent(HTML::legend(_("Select the pages where to change access rights")));

        $buttons = HTML::p(Button('submit:admin_setacl[aclliberal]', $button_label_liberal, 'wikiadmin'),
                           Button('submit:admin_setacl[aclrestricted]', $button_label_restrictive, 'wikiadmin'));
        $header->pushContent($buttons);

        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          $pagelist->getContent(),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('admin_setacl')),
                          ENABLE_PAGEPERM
                          ? ''
                          : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)));
    }

    /*
     * acces rights where everyone can edit
     * _EVERY: view edit list create;
     * _ADMIN: remove purge dump change;
     * _OWNER: remove purge dump change;
     */

    function liberalPerms() {

        $perm = array('view'   => array(ACL_EVERY => true),
                      'edit'   => array(ACL_EVERY => true),
                      'create' => array(ACL_EVERY => true),
                      'list'   => array(ACL_EVERY => true),
                      'remove' => array(ACL_ADMIN => true,
                                        ACL_OWNER => true),
                      'purge'  => array(ACL_ADMIN => true,
                                        ACL_OWNER => true),
                      'dump'   => array(ACL_ADMIN => true,
                                        ACL_OWNER => true),
                      'change' => array(ACL_ADMIN => true,
                                        ACL_OWNER => true));
        return $perm;
    }

    /*
     * acces rights where only authenticated users can see pages
     * _AUTHENTICATED: view edit list create;
     * _ADMIN: remove purge dump change;
     * _OWNER: remove purge dump change;
     * _EVERY: -view -edit -list -create;
     */

    function restrictedPerms() {

        $perm = array('view'   => array(ACL_AUTHENTICATED => true,
                                        ACL_EVERY => false),
                      'edit'   => array(ACL_AUTHENTICATED => true,
                                        ACL_EVERY => false),
                      'create' => array(ACL_AUTHENTICATED => true,
                                        ACL_EVERY => false),
                      'list'   => array(ACL_AUTHENTICATED => true,
                                        ACL_EVERY => false),
                      'remove' => array(ACL_ADMIN => true,
                                        ACL_OWNER => true),
                      'purge'  => array(ACL_ADMIN => true,
                                        ACL_OWNER => true),
                      'dump'   => array(ACL_ADMIN => true,
                                        ACL_OWNER => true),
                      'change' => array(ACL_ADMIN => true,
                                        ACL_OWNER => true));
        return $perm;
    }

    function setaclForm(&$header, $pagehash) {

        $pages = array();
        foreach ($pagehash as $name => $checked) {
           if ($checked) $pages[] = $name;
        }

        $header->pushContent(HTML::strong(_("Selected Pages: ")), HTML::tt(join(', ',$pages)), HTML::br());
        return $header;
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
