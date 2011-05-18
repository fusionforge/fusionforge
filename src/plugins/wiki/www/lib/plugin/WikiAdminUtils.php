<?php // -*-php-*-
// $Id: WikiAdminUtils.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 2003,2004,2006 $ThePhpWikiProgrammingTeam
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
  valid actions:
        purge-cache
        purge-bad-pagenames
        purge-empty-pages
        access-restrictions
        email-verification
        convert-cached-html
        db-check
        db-rebuild
 */
class WikiPlugin_WikiAdminUtils
extends WikiPlugin
{
    function getName () {
        return _("WikiAdminUtils");
    }

    function getDescription () {
        return _("Miscellaneous utility functions for the Administrator.");
    }

    function getDefaultArguments() {
        return array('action'           => '',
                     'label'                => '',
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        $args['action'] = strtolower($args['action']);
        extract($args);

        if (!$action)
            $this->error("No action specified");
        if (!($default_label = $this->_getLabel($action))) {
            return HTML::div(array('class' => "error"), fmt("Bad action requested: %s", $action));
        }
        if ($request->getArg('action') != 'browse')
            return $this->disabled("(action != 'browse')");

        $posted = $request->getArg('wikiadminutils');

        if ($request->isPost() and $posted['action'] == $action) { // a different form. we might have multiple
            $user = $request->getUser();
            if (!$user->isAdmin()) {
                $request->_notAuthorized(WIKIAUTH_ADMIN);
                return $this->error(_("You must be an administrator to use this plugin."));
            }
            return $this->do_action($request, $posted);
        }
        if (empty($label))
            $label = $default_label;

        return $this->_makeButton($request, $args, $label);
    }

    function _makeButton(&$request, $args, $label) {
        $args['return_url'] = $request->getURLtoSelf();
        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          HTML::p(Button('submit:', $label, 'wikiadmin')),
                          HiddenInputs($args, 'wikiadminutils'),
                          HiddenInputs(array('require_authority_for_post' =>
                                             WIKIAUTH_ADMIN)),
                          HiddenInputs($request->getArgs(),false,array('action')));
    }

    function do_action(&$request, $args) {
        $method = strtolower('_do_' . str_replace('-', '_', $args['action']));
        if (!method_exists($this, $method))
            return $this->error("Bad action $method");

        $message = call_user_func(array(&$this, $method), $request, $args);

        // display as seperate page or as alert?
        $alert = new Alert(fmt("WikiAdminUtils %s returned:", $args['action']),
                           $message,
                           array(_("Back") => $args['return_url']));
        $alert->show();         // noreturn
    }

    function _getLabel($action) {
        $labels = array('purge-cache'                 => _("Purge Markup Cache"),
                        'purge-bad-pagenames'         => _("Purge all Pages With Invalid Names"),
                        'purge-empty-pages'         => _("Purge all empty, unreferenced Pages"),
                        'access-restrictions'         => _("Access Restrictions"),
                        'email-verification'         => _("Email Verification"),
                        'convert-cached-html'         => _("Convert cached_html"),
                        'db-check'                 => _("DB Check"),
                        'db-rebuild'                 => _("Db Rebuild")
                        );
        return @$labels[$action];
    }

    function _do_purge_cache(&$request, $args) {
        $dbi = $request->getDbh();
        $pages = $dbi->getAllPages('include_empty'); // Do we really want the empty ones too?
        while (($page = $pages->next())) {
            $page->set('_cached_html', false);
        }
        return _("Markup cache purged!");
    }

    function _do_purge_bad_pagenames(&$request, $args) {
        // FIXME: this should be moved into WikiDB::normalize() or something...
        $dbi = $request->getDbh();
        $count = 0;
        $list = HTML::ol(array('align'=>'left'));
        $pages = $dbi->getAllPages('include_empty'); // Do we really want the empty ones too?
        while (($page = $pages->next())) {
            $pagename = $page->getName();
            $wpn = new WikiPageName($pagename);
            if (! $wpn->isValid() ) {
                $dbi->purgePage($pagename);
                $list->pushContent(HTML::li($pagename));
                $count++;
            }
        }
        $pages->free();
        if (!$count)
            return _("No pages with bad names had to be deleted.");
        else {
            return HTML(fmt("Deleted %d pages with invalid names:", $count),
                        HTML::div(array('align'=>'left'), $list));
        }
    }

    /**
     * Purge all non-referenced empty pages. Mainly those created by bad link extraction.
     */
    function _do_purge_empty_pages(&$request, $args) {
        $dbi = $request->getDbh();
        $count = 0; $notpurgable = 0;
        $list = HTML::ol(array('align'=>'left'));
        $pages = $dbi->getAllPages('include_empty');
        while (($page = $pages->next())) {
            if (!$page->exists()
                and ($links = $page->getBackLinks('include_empty'))
                     and !$links->next())
            {
                $pagename = $page->getName();
                if ($pagename == 'global_data' or $pagename == '.') continue;
                if ($dbi->purgePage($pagename))
                    $list->pushContent(HTML::li($pagename.' '._("[purged]")));
                else {
                    $list->pushContent(HTML::li($pagename.' '._("[not purgable]")));
                    $notpurgable++;
                }
                $count++;
            }
        }
        $pages->free();
        if (!$count)
            return _("No empty, unreferenced pages were found.");
        else
            return HTML(fmt("Deleted %d unreferenced pages:", $count),
                        HTML::div(array('align'=>'left'), $list),
                        ($notpurgable ?
        fmt("The %d not-purgable pages/links are links in some page(s). You might want to edit them.",
            $notpurgable)
                                      : ''));
    }


    function _do_convert_cached_html(&$request, $args) {

        require_once("lib/upgrade.php");
        $dbh = $request->_dbi;
        _upgrade_db_init($dbh);

        $count = _upgrade_cached_html($dbh, false);

        if (!$count)
            return _("No old _cached_html pagedata found.");
        else {
            return HTML(fmt("Converted successfully %d pages", $count),
                        HTML::div(array('align'=>'left'), $list));
        }
    }

    function _do_db_check(&$request, $args) {
        longer_timeout(180);
        $dbh = $request->getDbh();
        //FIXME: display result.
        return $dbh->_backend->check($args);
    }

    function _do_db_rebuild(&$request, $args) {
        longer_timeout(240);
        $dbh = $request->getDbh();
        //FIXME: display result.
        return $dbh->_backend->rebuild($args);
    }

    //TODO: We need a seperate plugin for this.
    //      Too many options.
    function _do_access_restrictions(&$request, &$args) {
            return _("Sorry. Access Restrictions not yet implemented");
    }

    // pagelist with enable/disable button
    function _do_email_verification(&$request, &$args) {
        $dbi = $request->getDbh();
        $pagelist = new PageList('pagename',0,$args);
        //$args['return_url'] = 'action=email-verification-verified';
        $email = new _PageList_Column_email('email',_("E-Mail"),'left');
        $emailVerified = new _PageList_Column_emailVerified('emailVerified',
                                                            _("Verification Status"),'center');
        $pagelist->_columns[0]->_heading = _("Username");
        $pagelist->_columns[] = $email;
        $pagelist->_columns[] = $emailVerified;
        //This is the best method to find all users (Db and PersonalPage)
        $current_user = $request->_user;
        if (empty($args['verify'])) {
            $group = $request->getGroup();
            $allusers = $group->_allUsers();
        } else {
            if (!empty($args['user']))
                $allusers = array_keys($args['user']);
            else
                $allusers = array();
        }
        foreach ($allusers as $username) {
            if (ENABLE_USER_NEW)
                $user = WikiUser($username);
            else
                $user = new WikiUser($request, $username);
            $prefs = $user->getPreferences();
            if ($prefs->get('email')) {
                    if (!$prefs->get('userid'))
                        $prefs->set('userid',$username);
                if (!empty($pagelist->_rows))
                    $group = (int)(count($pagelist->_rows) / $pagelist->_group_rows);
                else
                    $group = 0;
                $class = ($group % 2) ? 'oddrow' : 'evenrow';
                $row = HTML::tr(array('class' => $class));
                $page_handle = $dbi->getPage($username);
                $row->pushContent($pagelist->_columns[0]->format($pagelist,
                                                                 $page_handle, $page_handle));
                $row->pushContent($email->format($pagelist, $prefs, $page_handle));
                if (!empty($args['verify'])) {
                    $prefs->_prefs['email']->set('emailVerified',
                                                 empty($args['verified'][$username]) ? 0 : true);
                    $user->setPreferences($prefs);
                }
                $row->pushContent($emailVerified->format($pagelist, $prefs, $args['verify']));
                $pagelist->_rows[] = $row;
            }
        }
        $request->_user = $current_user;
        if (!empty($args['verify']) or empty($pagelist->_rows)) {
            return HTML($pagelist->_generateTable(false));
        } elseif (!empty($pagelist->_rows)) {
            $args['verify'] = 1;
            $args['return_url'] = $request->getURLtoSelf();
            return HTML::form(array('action' => $request->getPostURL(),
                                    'method' => 'post'),
                          HiddenInputs($args, 'wikiadminutils'),
                          HiddenInputs(array('require_authority_for_post' =>
                                             WIKIAUTH_ADMIN)),
                          HiddenInputs($request->getArgs()),
                          $pagelist->_generateTable(false),
                          HTML::p(Button('submit:', _("Change Verification Status"),
                                         'wikiadmin'),
                                  HTML::Raw('&nbsp;'),
                                  Button('cancel', _("Cancel")))
                                 );
        }
    }
};

require_once("lib/PageList.php");

class _PageList_Column_email
extends _PageList_Column {
    function _getValue (&$prefs, $dummy) {
        return $prefs->get('email');
    }
}

class _PageList_Column_emailVerified
extends _PageList_Column {
    function _getValue (&$prefs, $status) {
            $name = $prefs->get('userid');
            $input = HTML::input(array('type' => 'checkbox',
                                       'name' => 'wikiadminutils[verified]['.$name.']',
                                       'value' => 1));
            if ($prefs->get('emailVerified'))
                $input->setAttr('checked','1');
        if ($status)
                $input->setAttr('disabled','1');
            return HTML($input, HTML::input
                    (array('type' => 'hidden',
                           'name' => 'wikiadminutils[user]['.$name.']',
                           'value' => $name)));
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
