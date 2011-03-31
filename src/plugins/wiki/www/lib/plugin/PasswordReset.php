<?php // -*-php-*-
// $Id: PasswordReset.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright (C) 2006 $ThePhpWikiProgrammingTeam
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
 * 1. User forgot password but has email in the prefs.
 *    => action=email&user=username will send the password per email in plaintext.
 *
 *    If no email is stored, because user might not exist,
 *    => "No email stored for user %s.
 *        You need to ask an Administrator to reset this password."
 *       Problem: How to contact Admin? Present a link to ADMIN_USER
 *
 *    If no email exists but is not verified,
 *    => "Warning: This users email address is unverified!"
 *
 * 2. Admin may reset any users password, with verification.
 *    => action=reset&user=username
 */
class WikiPlugin_PasswordReset
extends WikiPlugin
{
    function getName () {
        return _("PasswordReset");
    }

    function getDescription () {
        return _("Allow admin to reset any users password, allow user to request his password by e-mail.");
    }

    function getDefaultArguments() {
        return array('user' => '');
    }

    /* reset password, verified */
    function doReset($userid) {

        $user = WikiUser($userid);
        $prefs = $user->getPreferences();
        $prefs->set('passwd','');
        if ($user->setPreferences($prefs)) {
            $alert = new Alert(_("Message"),
                               fmt("The password for user %s has been deleted.", $userid));
        } else {
            $alert = new Alert(_("Error"),
                               fmt("The password for user %s could not be deleted.", $userid));
        }
        $alert->show();
    }

    function doEmail(&$request, $userid) {

        $thisuser = WikiUser($userid);
        $prefs = $thisuser->getPreferences();
        $email = $prefs->get('email');
        $passwd = $prefs->get('passwd'); // plain?
        $from = $request->_user->getId() . '@' .  $request->get('REMOTE_HOST');
        if (mail($email,
                 "[".WIKI_NAME."] PasswortReset",
                 "PasswortReset requested by $from\r\n".
                 "Password for ".WIKI_NAME.": $passwd",
                 "From: $from"))
            $alert = new Alert(_("Message"),
                               fmt("Email sent to the stored email address for user %s", $userid));
        else
            $alert = new Alert(_("Error"),
                               fmt("Error sending email with password for user %s.", $userid));
        $alert->show();
    }

    function doForm(&$request, $userid='', $header = '', $footer = '') {
        $post_args = $request->getArg('admin_reset');
        if (!$header) {
            $header = HTML::p(_("Reset password of user: "),
                              HTML::Raw('&nbsp;'),
                              HTML::input(array('type' => 'text',
                                                'name' => "user",
                                                'value' => $userid))
                              );
        }
        if (!$footer) {
            $isadmin = $request->_user->isAdmin();
            $footer = HTML::p(Button('submit:admin_reset[reset]',
                                      $isadmin ? _("Yes") : _("Send email"),
                                      $isadmin ? 'wikiadmin' : 'button'),
                               HTML::Raw('&nbsp;'),
                               Button('submit:admin_reset[cancel]', _("Cancel"), 'button'));
        }
        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          HiddenInputs($request->getArgs(), false, array('admin_reset', 'user')),
                          ENABLE_PAGEPERM ? '' : HiddenInputs(array('require_authority_for_post' => WIKIAUTH_ADMIN)),
                          $footer );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        if (isa($request,'MockRequest'))
            return '';

        $user =& $request->_user;
        $post_args = $request->getArg('admin_reset');
        $userid = $args['user'];
        if (!$userid) $userid = $request->getArg('user');
        $isadmin = $user->isAdmin();
        if ($request->isPost()) {
            @$reset = $post_args['reset'];
            if (empty($reset))
                return $this->doForm($request, $userid);
            if (!$userid) {
                $alert = new Alert(_("Warning:"),
                                   _("You need to specify the userid!"));
                $alert->show();
                return $this->doForm($request);
            }
            if ($userid and !empty($post_args['verify'])) {
                if ($user->isAdmin()) {
                    return $this->doReset($userid);
                } else {
                    return $this->doEmail($request, $userid);
                }
            } elseif (empty($post_args['verify'])) {
                //TODO: verify should check if the user exists, his prefs can be read/safed
                //      and the email is verified, even if admin.
                $buttons = HTML::p(Button('submit:admin_reset[reset]',
                                          $isadmin ? _("Yes") : _("Send email"),
                                          $isadmin ? 'wikiadmin' : 'button'),
                                   HTML::Raw('&nbsp;'),
                                   Button('submit:admin_reset[cancel]', _("Cancel"), 'button'));
                $header = HTML::strong("Verify");
                if (!$user->isAdmin()) {
                    // check for email
                    if ($userid == $user->UserName() and $user->isAuthenticated()) {
                        $alert = new Alert(_("Already logged in"),
                                           HTML(fmt("Changing passwords is done at "), WikiLink(_("UserPreferences"))));
                        $alert->show();
                        return;
                    }
                    $thisuser = WikiUser($userid);
                    $prefs = $thisuser->getPreferences();
                    $email = $prefs->get('email');
                    if (!$email) {
                        $alert = new Alert(_("Error"),
                                           HTML(fmt("No email stored for user %s.", $userid),
                                                HTML::br(),
                                                fmt("You need to ask an Administrator to reset this password. See below: "),
                                                HTML::br(), WikiLink(ADMIN_USER)));
                        $alert->show();
                        return;
                    }
                    $verified = $thisuser->_prefs->_prefs['email']->getraw('emailVerified');
                    if (!$verified)
                        $header->pushContent(HTML::br(), "Warning: This users email address is unverified!");
                }
                return $this->doForm($request, $userid,
                                     $header,
                                     HTML(HTML::hr(),
                                          fmt("Do you really want to reset the password of user %s?", $userid),
                                          $isadmin ? '' : _("An email will be sent."),
                                          HiddenInputs(array('admin_reset[verify]' => 1, 'user' => $userid)),
                                          $buttons));
            } else { // verify ok, but no userid
                return $this->doForm($request, $userid);
            }
        } else {
            return $this->doForm($request, $userid);
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
