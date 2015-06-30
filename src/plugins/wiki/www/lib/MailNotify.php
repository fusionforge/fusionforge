<?php

/* Copyright (C) 2006-2007,2009 Reini Urban
 * Copyright (C) 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Handle the pagelist pref[notifyPages] logic for users
 * and notify => hash ( page => (userid => userhash) ) for pages.
 * Generate notification emails.
 *
 * We add WikiDB handlers and register ourself there:
 *   onChangePage, onDeletePage, onRenamePage
 * Administrative actions:
 *   [Watch] WatchPage - add a page, or delete watch handlers into the users
 *                       pref[notifyPages] slot.
 *   My WatchList      - view or edit list/regex of pref[notifyPages].
 *
 * Helper functions:
 *   getPageChangeEmails
 *   MailAdmin
 *   ? handle emailed confirmation links (EmailSignup, ModeratedPage)
 *
 * @package MailNotify
 * @author  Reini Urban
 */

if (!defined("MAILER_LOG")) {
    if (isWindows()) {
        define("MAILER_LOG", 'c:/wikimail.log');
    } else {
        define("MAILER_LOG", '/var/log/wikimail.log');
    }
}

class MailNotify
{

    function __construct($pagename)
    {
        $this->pagename = $pagename; /* which page */
        $this->emails = array(); /* to which addresses */
        $this->userids = array(); /* corresponding array of displayed names,
                                        don't display the email addresses */
        /* From: from whom the mail appears to be */
        $this->from = $this->fromId();
    }

    private function fromId()
    {
        global $request;
        if (defined('FUSIONFORGE') && FUSIONFORGE) {
            return $request->_user->getId();
        } else {
            return $request->_user->getId() . '@' . $request->get('REMOTE_HOST');
        }
    }

    private function fromEmail()
    {
        global $request;
        return $this->userEmail($request->_user->getId(), false);
    }

    private function userEmail($userid, $doverify = true)
    {
        global $request;

        // Disable verification of emails for corporate env.
        if (defined('FUSIONFORGE') && FUSIONFORGE) {
            $doverify = false;
        }

        $u = $request->getUser();
        if ($u->UserName() == $userid) { // lucky: current user
            $prefs = $u->getPreferences();
            $email = $prefs->get('email');
            // do a dynamic emailVerified check update
            if ($doverify and !$request->_prefs->get('emailVerified')) {
                $email = '';
            }
        } else { // not current user
            $u = WikiUser($userid);
            $u->getPreferences();
            $prefs = &$u->_prefs;
            $email = $prefs->get('email');
            if ($doverify and !$prefs->get('emailVerified')) {
                $email = '';
            }
        }
        return $email;
    }

    /**
     * getPageChangeEmails
     * @param  array $notify  hash ( page => (userid => userhash) )
     * @return array          unique array of ($emails, $userids)
     */
    public function getPageChangeEmails($notify)
    {
        /**
         * @var WikiRequest $request
         */
        global $request;

        $emails = array();
        $userids = array();
        foreach ($notify as $page => $users) {
            if (glob_match($page, $this->pagename)) {
                $curuser = $request->getUser();
                $curuserprefs = $curuser->getPreferences();
                $curuserprefsemail = $curuserprefs->get('email');
                $ownModifications = $curuserprefs->get('ownModifications');
                $majorModificationsOnly = $curuserprefs->get('majorModificationsOnly');

                foreach ($users as $userid => $user) {

                    $usermail = $user['email'];

                    if (($usermail == $curuserprefsemail)
                        and ($ownModifications)
                    ) {
                        // It's my own modification
                        // and I do not want to receive it
                        continue;
                    }

                    if ($majorModificationsOnly) {
                        $backend = &$request->_dbi->_backend;
                        $version = $backend->get_latest_version($this->pagename);
                        $versiondata = $backend->get_versiondata($this->pagename, $version, true);
                        if ($versiondata['is_minor_edit']) {
                            // It's a minor modification
                            // and I do not want to receive it
                            continue;
                        }
                    }

                    if (!$user) { // handle the case for ModeratePage:
                        // no prefs, just userid's.
                        $emails[] = $this->userEmail($userid, false);
                        $userids[] = $userid;
                    } else {
                        if (!empty($user['verified']) and !empty($user['email'])) {
                            $emails[] = $user['email'];
                            $userids[] = $userid;
                        } elseif (!empty($user['email'])) {
                            // do a dynamic emailVerified check update
                            $email = $this->userEmail($userid, true);
                            if ($email) {
                                $notify[$page][$userid]['verified'] = 1;
                                $request->_dbi->set('notify', $notify);
                                $emails[] = $email;
                                $userids[] = $userid;
                            }
                        }
                    }
                }
            }
        }
        $this->emails = array_unique($emails);
        $this->userids = array_unique($userids);
        return array($this->emails, $this->userids);
    }

    /**
     * @param string $subject Subject of the e-mail
     * @param string $content Content of the e-mail
     * @param bool $notice    Message used when triggering error
     * @return bool           Return false in case of error
     */
    public function sendMail($subject, $content, $notice = false)
    {
        // Add WIKI_NAME to Subject
        $subject = "[" . WIKI_NAME . "] " . $subject;
        // Encode $subject if needed
        $encoded_subject = $this->subject_encode($subject);
        $emails = $this->emails;
        // Do not send if modification is from FusionForge admin
        if ((defined('FUSIONFORGE') && FUSIONFORGE) && ($this->fromId() == ADMIN_USER)) {
            return true;
        }
        if (!$notice) {
            $notice = _("PageChange Notification of %s");
        }
        $from = $this->fromEmail();
        $headers = "From: $from\r\n" .
            "Bcc: " . join(',', $emails) . "\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/plain; charset=UTF-8; format=flowed\r\n" .
            "Content-Transfer-Encoding: 8bit";

        $ok = mail(($to = array_shift($emails)),
            $encoded_subject,
            $subject . "\n" . $content,
            $headers
        );
        if (MAILER_LOG and is_writable(MAILER_LOG)) {
            global $ErrorManager;

            $f = fopen(MAILER_LOG, "a");
            fwrite($f, "\n\nX-MailSentOK: " . $ok ? 'OK' : 'FAILED');

            if (!$ok && isset($ErrorManager->_postponed_errors[count($ErrorManager->_postponed_errors) - 1])) {
                // get last error message
                $last_err = $ErrorManager->_postponed_errors[count($ErrorManager->_postponed_errors) - 1];
                fwrite($f, "\nX-MailFailure: " .
                    "errno: " . $last_err->errno . ", " .
                    "errstr: " . $last_err->errstr . ", " .
                    "errfile: " . $last_err->errfile . ", " .
                    "errline: " . $last_err->errline);
            }
            fwrite($f, "\nDate: " . CTime());
            fwrite($f, "\nSubject: $encoded_subject");
            fwrite($f, "\nFrom: $from");
            fwrite($f, "\nTo: $to");
            fwrite($f, "\nBcc: " . join(',', $emails));
            fwrite($f, "\n\n" . $content);
            fclose($f);
        }
        if ($ok) {
            return true;
        } else {
            trigger_error(sprintf($notice, $this->pagename)
                    . " "
                    . sprintf(_("Error: Couldn't send %s to %s"),
                        $subject . "\n" . $content, join(',', $this->userids)),
                E_USER_WARNING);
            return false;
        }
    }

    /**
     * Send udiff for a changed page to multiple users.
     * See rename and remove methods also
     */
    private function sendPageChangeNotification(&$wikitext, $version, &$meta)
    {

        global $request;

        if (isset($request->_deferredPageChangeNotification)) {
            // collapse multiple changes (loaddir) into one email
            $request->_deferredPageChangeNotification[] =
                array($this->pagename, $this->emails, $this->userids);
            return;
        }
        $backend = &$request->_dbi->_backend;
        $previous = $backend->get_previous_version($this->pagename, $version);
        if (!isset($meta['mtime'])) {
            $meta['mtime'] = time();
        }
        if ($previous) {
            // Page existed, and was modified
            $subject = _("Page change") . ' ' . ($this->pagename);
            $difflink = WikiURL($this->pagename, array('action' => 'diff'), true);
            $cache = &$request->_dbi->_cache;
            $this_content = explode("\n", $wikitext);
            $prevdata = $cache->get_versiondata($this->pagename, $previous, true);
            if (empty($prevdata['%content'])) {
                $prevdata = $backend->get_versiondata($this->pagename, $previous, true);
            }
            $other_content = explode("\n", $prevdata['%content']);

            include_once 'lib/difflib.php';
            $diff2 = new Diff($other_content, $this_content);
            //$context_lines = max(4, count($other_content) + 1,
            //                     count($this_content) + 1);
            $fmt = new UnifiedDiffFormatter( /*$context_lines*/);
            $content = $this->pagename . " " . $previous . " " .
                Iso8601DateTime($prevdata['mtime']) . "\n";
            $content .= $this->pagename . " " . $version . " " .
                Iso8601DateTime($meta['mtime']) . "\n";
            $content .= $fmt->format($diff2);
            $editedby = sprintf(_("Edited by: %s"), $this->fromId());
        } else {
            // Page did not exist, and was created
            $subject = _("Page creation") . ' ' . ($this->pagename);
            $difflink = WikiURL($this->pagename, array(), true);
            $content = $this->pagename . " " . $version . " " .
                Iso8601DateTime($meta['mtime']) . "\n";
            $content .= _("New page");
            $content .= "\n\n";
            $content .= $wikitext;
            $editedby = sprintf(_("Created by: %s"), $this->fromId());
        }
        $summary = sprintf(_("Summary: %s"), $meta['summary']);
        $this->sendMail($subject,
            $editedby . "\n" . $summary . "\n" . $difflink . "\n\n" . $content);
    }

    /**
     * Support mass rename / remove (TBD)
     * @param string $to New page name
     */
    private function sendPageRenameNotification($to)
    {
        $pagename = $this->pagename;
        $editedby = sprintf(_("Renamed by: %s"), $this->fromId());
        $subject = sprintf(_("Page rename %s to %s"), $pagename, $to);
        $link = WikiURL($to, true);
        $this->sendMail($subject, $editedby . "\n" . $link . "\n\n" . $subject);
    }

    /*
     * The handlers:
     */

    /**
     * @param WikiDB $wikidb
     * @param string $wikitext
     * @param string $version
     * @param array $meta
     */
    public function onChangePage(&$wikidb, &$wikitext, $version, &$meta)
    {
        $notify = $wikidb->get('notify');
        /* Generate notification emails? */
        if (!empty($notify) and is_array($notify)) {
            if (empty($this->pagename))
                $this->pagename = $meta['pagename'];
            // TODO: Should be used for ModeratePage and RSS2 Cloud xml-rpc also.
            $this->getPageChangeEmails($notify);
            if (!empty($this->emails)) {
                $this->sendPageChangeNotification($wikitext, $version, $meta);
            }
        }
    }

    /**
     * @param WikiDB $wikidb
     * @param string $pagename
     * @return bool
     */
    public function onDeletePage(&$wikidb, $pagename)
    {
        $result = true;
        /* Generate notification emails? */
        if (!$wikidb->isWikiPage($pagename)) {
            $notify = $wikidb->get('notify');
            if (!empty($notify) and is_array($notify)) {
                //TODO: deferr it (quite a massive load if you remove some pages).
                $this->getPageChangeEmails($notify);
                if (!empty($this->emails)) {
                    $subject = sprintf(_("User %s removed page %s"), $this->fromId(), $pagename);
                    $result = $this->sendMail($subject, $subject . "\n\n");
                }
            }
        }
        return $result;
    }

    /**
     * @param WikiDB $wikidb
     * @param string $oldpage
     * @param string $new_pagename
     */
    public function onRenamePage(&$wikidb, $oldpage, $new_pagename)
    {
        $notify = $wikidb->get('notify');
        if (!empty($notify) and is_array($notify)) {
            $this->getPageChangeEmails($notify);
            if (!empty($this->emails)) {
                $this->pagename = $oldpage;
                $this->sendPageRenameNotification($new_pagename);
            }
        }
    }

    private function subject_encode($subject)
    {
        // We need to encode the subject if it contains non-ASCII characters
        // The page name may contain non-ASCII characters, as well as
        // the translation of the messages, e.g. _("PageChange Notification of %s");

        // If all characters are ASCII, do nothing
        if (isAsciiString($subject)) {
            return $subject;
        }

        // quoted_printable_encode inserts "\r\n" if line is too long, use "\n" only
        return "=?UTF-8?Q?" . str_replace("\r\n", "\n", quoted_printable_encode($subject)) . "?=";
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
