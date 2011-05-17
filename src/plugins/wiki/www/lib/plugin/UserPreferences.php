<?php // -*-php-*-
// rcs_id('$Id: UserPreferences.php 7659 2010-08-31 14:55:29Z vargenau $');
/**
 * Copyright (C) 2001,2002,2003,2004,2005 $ThePhpWikiProgrammingTeam
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
 * Plugin to allow any user to adjust his own preferences.
 * This must be used in the page "UserPreferences".
 * Prefs are stored in metadata in the current session,
 *  within the user's home page or in a database.
 *
 * WikiTheme extension: WikiThemes are able to extend the predefined list
 * of preferences.
 */
class WikiPlugin_UserPreferences
extends WikiPlugin
{
    var $bool_args;

    function getName () {
        return _("UserPreferences");
    }

    function getDescription () {
        return _("Allow any user to adjust his own preferences.");
    }

    function getDefaultArguments() {
        global $request;
        $pagename = $request->getArg('pagename');
        $user = $request->getUser();
        if ( isset($user->_prefs) and
             isset($user->_prefs->_prefs) and
             isset($user->_prefs->_method) ) {
            $pref =& $user->_prefs;
        } else {
            $pref = $user->getPreferences();
        }
        $prefs = array();
        //we need a hash of pref => default_value
        foreach ($pref->_prefs as $name => $obj) {
            $prefs[$name] = $obj->default_value;
        }
        return $prefs;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        $user =& $request->_user;
        $user->_request = $request;
        if (isa($request,'MockRequest'))
            return '';
        if (defined('FUSIONFORGE') and FUSIONFORGE) {
            if (!($user->isAuthenticated())) {
                return HTML::div(array('class' => 'errors'),
                                 _("Error: You are not logged in, cannot display UserPreferences."));
            }
        }
        if ((!isActionPage($request->getArg('pagename'))
             and (!isset($user->_prefs->_method)
                  or !in_array($user->_prefs->_method, array('ADODB','SQL','PDO'))))
            or (in_array($request->getArg('action'), array('zip','ziphtml','dumphtml')))
            or (isa($user,'_ForbiddenUser')))
        {
            $no_args = $this->getDefaultArguments();
            $no_args['errmsg'] = HTML::div(array('class' => 'errors'),
                                           _("Error: The user HomePage must be a valid WikiWord. Sorry, UserPreferences cannot be saved."));
            $no_args['isForm'] = false;
            return Template('userprefs', $no_args);
        }
        $userid = $user->UserName();
        if ($user->isAuthenticated() and !empty($userid))
        {
            $pref = &$request->_prefs;
            $args['isForm'] = true;

            if ($request->isPost()) {
                    $errmsg = '';
                $delete = $request->getArg('delete');
                if ($delete and $request->getArg('verify')) {
                    // deleting prefs, verified
                    $default_prefs = $pref->defaultPreferences();
                    $default_prefs['userid'] = $user->UserName();
                    $user->setPreferences($default_prefs);
                    $request->_setUser($user);
                    $request->setArg("verify",false);
                    $request->setArg("delete",false);
                    $errmsg .= _("Your UserPreferences have been successfully reset to default.");
                    $args['errmsg'] = HTML::div(array('class' => 'feedback'), HTML::p($errmsg));
                    return Template('userprefs', $args);
                } elseif ($delete and !$request->getArg('verify')) {
                    return HTML::fieldset(
                                 HTML::form(array('action' => $request->getPostURL(),
                                            'method' => 'post'),
                                       HiddenInputs(array('verify' => 1)),
                                       HiddenInputs($request->getArgs()),
                                       HTML::p(_("Do you really want to reset all your UserPreferences?")),
                                       HTML::p(Button('submit:delete', _("Yes"), 'delete'),
                                               HTML::Raw('&nbsp;'),
                                               Button('cancel', _("Cancel")))
                                       ));
                } elseif ($rp = $request->getArg('pref')) {
                    // replace only changed prefs in $pref with those from request
                    if (!empty($rp['passwd']) and ($rp['passwd2'] != $rp['passwd'])) {
                        $errmsg = _("Wrong password. Try again.");
                    } else {
                        if (empty($rp['passwd'])) unset($rp['passwd']);
                        // fix to set system pulldown's. empty values don't get posted
                        if (empty($rp['theme'])) $rp['theme'] = '';
                        if (empty($rp['lang']))  $rp['lang']  = '';
                        $num = $user->setPreferences($rp);
                        if (!empty($rp['passwd'])) {
                            $passchanged = false;
                            if ($user->mayChangePass()) {
                                if (method_exists($user, 'storePass')) {
                                    $passchanged = $user->storePass($rp['passwd']);
                                }
                                if (!$passchanged and method_exists($user, 'changePass')) {
                                    $passchanged = $user->changePass($rp['passwd']);
                                }
                                if ($passchanged) {
                                    $errmsg = _("Password updated.");
                                } else {
                                    $errmsg = _("Password was not changed.");
                                }
                            } else {
                                $errmsg = _("Password cannot be changed.");
                            }
                        }
                        if (!$num) {
                            $errmsg .= " " ._("No changes.");
                        } else {
                            $request->_setUser($user);
                            $pref = $user->_prefs;
                            if ($num == 1) {
                                $errmsg .= _("One UserPreferences field successfully updated.");
                            } else {
                            $errmsg .= sprintf(_("%d UserPreferences fields successfully updated."), $num);
                        }
                    }
                    }
                    $args['errmsg'] = HTML::div(array('class' => 'feedback'), HTML::p($errmsg));

                }
            }
            $args['available_themes'] = listAvailableThemes();
            $args['available_languages'] = listAvailableLanguages();

            return Template('userprefs', $args);
        } else {
            // wrong or unauthenticated user
            return $request->_notAuthorized(WIKIAUTH_BOGO);
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
