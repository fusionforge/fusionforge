<?php // -*-php-*-
// $Id: WatchPage.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright (C) 2006 $ThePhpWikiProgrammingTeam
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
 * Plugin to manage notifications emails per page. action=WatchPage
 * mode = add or edit
 * pagename = pagename to be added
 *
 * Prefs are stored in metadata in the current session,
 *  within the user's home page or in a database.
 */
class WikiPlugin_WatchPage
extends WikiPlugin
{
    function getName () {
        return _("WatchPage");
    }

    function getDescription () {
        return _("Manage notifications emails per page.");
    }

    function getDefaultArguments() {
        return array('page' => '[pagename]',
                     'mode'   => 'add',
                     );
    }

    function contains($pagelist, $page) {
        if (!isset($this->_explodePageList))
            $this->_explodePageList = explodePageList($pagelist);
        return in_array($page, $this->_explodePageList);
    }

    // This could be expanded as in Mediawiki to a list of each page with a remove button.
    function showWatchList($pagelist) {
        return HTML::strong(HTML::tt(empty($pagelist) ? _("<empty>") : $pagelist));
    }

    function addpagelist($page, $pagelist) {
        if (!empty($pagelist)) {
            if ($this->contains($pagelist, $page))
                return "$pagelist";
            else
                return "$pagelist, $page";
        } else
            return "$page";
    }

    function showNotify(&$request, $messages, $page, $pagelist, $verified) {
        $isNecessary = ! $this->contains($pagelist, $page);
        $form = HTML::form(array('action' => $request->getPostURL(),
                                 'method' => 'post'),
             HiddenInputs(array('verify' => 1)),
             HiddenInputs($request->getArgs(),false,array('verify')),
             $messages,
             HTML::p(_("Your current watchlist: "), $this->showWatchList($pagelist)));
        if ($isNecessary) {
            $form->pushContent(HTML::p(_("New watchlist: "),
                                       $this->showWatchList($this->addpagelist($page, $pagelist))),
                               HTML::p(sprintf(_("Do you %s want to add this page \"%s\" to your WatchList?"),
                                               ($verified ? _("really") : ""), $page)),
                               HTML::p(Button('submit:add', _("Yes")),
                                       HTML::Raw('&nbsp;'),
                                       Button('submit:cancel', _("Cancel"))));
        } else {
            $form->pushContent(HTML::p(fmt("The page %s is already watched!", $page)),
                               HTML::p(Button('submit:edit', _("Edit")),
                                       HTML::Raw('&nbsp;'),
                                       Button('submit:cancel', _("Cancel"))));
        }
        $fieldset = HTML::fieldset(HTML::legend(_("Watch Page")), $form);
        return $fieldset;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;

        $args = $this->getArgs($argstr, $request);
        if (isa($request,'MockRequest'))
            return '';
        $user =& $request->_user;
        $userid = $user->UserName();
        $page = $args['page'];
        if (!$user->isAuthenticated() or empty($userid)) {
            // wrong or unauthenticated user
            if (defined('FUSIONFORGE') and FUSIONFORGE) {
                // No login banner for FusionForge
                return HTML::div(array('class' => 'error'),
                                 HTML::p(_("You must sign in to watch pages.")));
            }
            return $request->_notAuthorized(WIKIAUTH_BOGO);
        } else {
            $pref = &$request->_prefs;
            $messages = "";
            if (!defined('FUSIONFORGE') or !FUSIONFORGE) {
                $email = $pref->get("email");
                if (empty($email)) {
                    return HTML::p(
                             array('class' => 'error'),
                             _("ERROR: No email defined! You need to do this in your "),
                             WikiLink(_("UserPreferences")));
                }
                $emailVerified = $pref->get("emailVerified");
                if (empty($emailVerified)) {
                    $messages = HTML::div(array('class' => 'mw-warning'),
                                HTML::p("WARNING! Your email address was not verifed yet!"),
                                HTML::p("EmailNotifications currently disabled. <TODO>"));
                }
            }
            $pagelist = $pref->get("notifyPages");
            if (! $request->isPost() ) {
                return $this->showNotify($request, $messages, $page, $pagelist, false);
            } else { // POST
                    $errmsg = '';
                if ($request->getArg('cancel')) {
                    $request->redirect(WikiURL($request->getArg('pagename'),
                                               false, 'absolute_url')); // noreturn
                    return;
                }
                if ($request->getArg('edit')) {
                    $request->redirect(WikiURL(_("UserPreferences"),
                                               false, 'absolute_url')); // noreturn
                    return;
                }
                $add = $request->getArg('add');
                if ($add and !$request->getArg('verify')) {
                    return $this->showNotify($request, $messages, $page, $pagelist, true);
                }
                elseif ($add and $request->getArg('verify')) { // this is not executed so far.
                    // add page to watchlist, verified
                    $rp = clone($user->getPreferences());
                    $rp->set('notifyPages', $this->addpagelist($page, $pagelist));
                    $user->setPreferences($rp);
                    $request->_setUser($user);
                    $request->setArg("verify",false);
                    $request->setArg("add",false);
                    $errmsg .= _("E-Mail Notification for the current page successfully stored in your preferences.");
                    $args['errmsg'] = HTML::div(array('class' => 'feedback'), HTML::p($errmsg));
                    return Template('userprefs', $args);
                }
            }
        }
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
