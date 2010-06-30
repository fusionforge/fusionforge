<?php //-*-php-*-
rcs_id('$Id: MailNotify.php,v 1.11 2007/07/01 09:01:16 rurban Exp $');

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
 *   EMailConfirm methods: send and verify
 *
 * Helper functions:
 *   getPageChangeEmails
 *   MailAdmin
 *   ? handle emailed confirmation links (EmailSignup, ModeratedPage)
 *
 * @package MailNotify
 * @author  Reini Urban
 */

if (!defined("MAILER_LOG"))
    if (isWindows())
        define("MAILER_LOG", 'c:/wikimail.log');
    else
        define("MAILER_LOG", '/var/log/wikimail.log');

class MailNotify {

    function MailNotify($pagename) {
	$this->pagename = $pagename; /* which page */
        $this->emails  = array();    /* to which addresses */
        $this->userids = array();    /* corresponding array of displayed names, 
                                        don't display the email addresses */
        /* From: from whom the mail appears to be */
        $this->from = $this->fromId();
    }

    function fromId() {
        global $request;
        return $request->_user->getId();
    }

    function userEmail($userid, $doverify = true) {
        global $request;

        // Disable verification of emails for corporate env.
        if (defined('GFORGE') and GFORGE) {
            $doverify = false;
        }

        $u = $request->getUser();
        if ($u->UserName() == $userid) { // lucky: current user
            $prefs = $u->getPreferences();
            $email = $prefs->get('email');
            // do a dynamic emailVerified check update
            if ($doverify and !$request->_prefs->get('emailVerified'))
                $email = '';
        } else {  // not current user
            if (ENABLE_USER_NEW) {
                $u = WikiUser($userid);
                $u->getPreferences();
                $prefs = &$u->_prefs;
            } else {
                $u = new WikiUser($request, $userid);
                $prefs = $u->getPreferences();
            }
            $email = $prefs->get('email');
            if ($doverify and !$prefs->get('emailVerified')) {
                $email = '';
            }
        }
        return $email;
    }

    /**
     * getPageChangeEmails($notify)
     * @param  $notify: hash ( page => (userid => userhash) )
     * @return array
     *         unique array of ($emails, $userids)
     */
    function getPageChangeEmails($notify) {
        global $request;
        $emails = array(); $userids = array();
        foreach ($notify as $page => $users) {
            if (glob_match($page, $this->pagename)) {
 
                global $request;
                $curuser = $request->getUser();
                $curusername = $curuser->UserName();
                $curuserprefs = $curuser->getPreferences();
                $curuserprefsemail = $curuserprefs->get('email');
                $ownModifications = $curuserprefs->get('ownModifications');
                // $majorModificationsOnly = $curuserprefs->get('majorModificationsOnly');

                foreach ($users as $userid => $user) {

                    $usermail = $user['email'];
 
                    if (($usermail == $curuserprefsemail)
                        and ($ownModifications)) {
                        // It's my own modification
                        // and I do not want to receive it
                        continue;
                    }

                    // if ($majorModificationsOnly) {
                    //     $backend = &$request->_dbi->_backend;
                    //     $version = $backend->get_latest_version($page);
                    //     $versiondata = $backend->get_versiondata($page, $version, true);
                    //     if ($versiondata['is_minor_edit']) {
                    //         // It's a minor modification
                    //         // and I do not want to receive it
                    //         continue;
                    //     }
                    // }

                    if (!$user) { // handle the case for ModeratePage: 
                        	  // no prefs, just userid's.
                        $emails[] = $this->userEmail($userid, false);
                        $userids[] = $userid;
                    } else {
                        if (!empty($user['verified']) and !empty($user['email'])) {
                            $emails[]  = $user['email'];
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
                        // ignore verification
                        /*
                        if (DEBUG) {
                            if (!in_array($user['email'], $emails))
                                $emails[] = $user['email'];
                        }
                        */
                    }
                }
            }
        }
        $this->emails = array_unique($emails);
        $this->userids = array_unique($userids);
        return array($this->emails, $this->userids);
    }
    
    function sendMail($subject, $content, 
                      $notice = false,
                      $silent = 0)
    {
        global $request;
	if (!DEBUG and $silent === 0)
	    $silent = true;
        $emails = $this->emails;
        $from = $this->from;
        // Do not send if modification is from Gforge admin
        if ($from == "ACOS Forge Administrator") {
            return;
        }
        if (!$notice) $notice = _("PageChange Notification of %s");
        $headers = "From: $from\r\n" .
                   "Bcc: ".join(',', $emails)."\r\n" .
                   "MIME-Version: 1.0\r\n" .
                   "Content-Type: text/plain; charset=".CHARSET."; format=flowed\r\n" .
                   "Content-Transfer-Encoding: 8bit";

        // On development or integration environements, emails sent to
        // <login>@debug.log are rerouted to a simple file (for test suite)..
	$installation_environment = forge_get_config ('installation_environment') ;
	if ($installation_environment == 'development' || $installation_environment == 'integration') {
        	foreach ($emails as $to) {
        		if (preg_match('/^([a-z]+)\@debug.log$/', $to, $matches)) {
        			$fl = fopen(forge_get_config('data_path')."/logs/email-wiki.".$matches[1].".log", 'a');
        			fwrite($fl, $headers."\nSubject: [".WIKI_NAME."] $subject\n\n".$content);
        			fclose($fl);
        			return true;
        		}
        	}
        }

        $ok = mail(($to = array_shift($emails)),
                 "[".WIKI_NAME."] ".$subject, 
		   $subject."\n".$content,
		   $headers
		   );
	if (MAILER_LOG and is_writable(MAILER_LOG)) {
	    $f = fopen(MAILER_LOG, "a");
	    fwrite($f, "\n\nX-MailSentOK: " . $ok ? 'OK' : 'FAILED');
	    if (!$ok) {
		global $ErrorManager;
		// get last error message
		$last_err = $ErrorManager->_postponed_errors[count($ErrorHandler->_postponed_errors)-1];
		fwrite($f, "\nX-MailFailure: " . $last_err);
	    }
	    fwrite($f, "\nDate: " . CTime());
	    fwrite($f, "\nSubject: $subject");
	    fwrite($f, "\nFrom: $from");
	    fwrite($f, "\nTo: $to");
	    fwrite($f, "\nBcc: ".join(',', $emails));
	    fwrite($f, "\n\n". $content);
	    fclose($f);
	}
        if ($ok) {
            if (!$silent)
                trigger_error(sprintf($notice, $this->pagename)
                              . " "
                              . sprintf(_("sent to %s"), join(',',$this->userids)),
                              E_USER_NOTICE);
            return true;
        } else {
            trigger_error(sprintf($notice, $this->pagename)
                          . " "
                          . sprintf(_("Error: Couldn't send %s to %s"), 
                                   $subject."\n".$content, join(',',$this->userids)), 
                          E_USER_WARNING);
            return false;
        }
    }
    
    /**
     * Send udiff for a changed page to multiple users.
     * See rename and remove methods also
     */
    function sendPageChangeNotification(&$wikitext, $version, &$meta) {

        global $request;

        if (@is_array($request->_deferredPageChangeNotification)) {
            // collapse multiple changes (loaddir) into one email
            $request->_deferredPageChangeNotification[] = 
                array($this->pagename, $this->emails, $this->userids);
            return;
        }
        $backend = &$request->_dbi->_backend;
        $subject = _("Page change").' '.urlencode($this->pagename);
        $previous = $backend->get_previous_version($this->pagename, $version);
        if (!isset($meta['mtime'])) $meta['mtime'] = time();
        if ($previous) {
            $difflink = WikiURL($this->pagename, array('action'=>'diff'), true);
            $cache = &$request->_dbi->_cache;
            $this_content = explode("\n", $wikitext);
            $prevdata = $cache->get_versiondata($this->pagename, $previous, true);
            if (empty($prevdata['%content']))
                $prevdata = $backend->get_versiondata($this->pagename, $previous, true);
            $other_content = explode("\n", $prevdata['%content']);
            
            include_once("lib/difflib.php");
            $diff2 = new Diff($other_content, $this_content);
            //$context_lines = max(4, count($other_content) + 1,
            //                     count($this_content) + 1);
            $fmt = new UnifiedDiffFormatter(/*$context_lines*/);
            $content  = $this->pagename . " " . $previous . " " . 
                Iso8601DateTime($prevdata['mtime']) . "\n";
            $content .= $this->pagename . " " . $version . " " .  
                Iso8601DateTime($meta['mtime']) . "\n";
            $content .= $fmt->format($diff2);
            
        } else {
            $difflink = WikiURL($this->pagename,array(),true);
            $content = $this->pagename . " " . $version . " " .  
                Iso8601DateTime($meta['mtime']) . "\n";
            $content .= _("New page");
        }
        $editedby = sprintf(_("Edited by: %s"), $this->from);
        //$editedby = sprintf(_("Edited by: %s"), $meta['author']);
        $this->sendMail($subject, 
                        $editedby."\n".$difflink."\n\n".$content);
    }

    /** 
     * Support mass rename / remove (not yet tested)
     */
    function sendPageRenameNotification ($to, &$meta) {
        global $request;

        if (@is_array($request->_deferredPageRenameNotification)) {
            $request->_deferredPageRenameNotification[] = 
                array($this->pagename, $to, $meta, $this->emails, $this->userids);
        } else {
            $pagename = $this->pagename;
            //$editedby = sprintf(_("Edited by: %s"), $meta['author']) . ' ' . $meta['author_id'];
            $editedby = sprintf(_("Edited by: %s"), $this->from);
            $subject = sprintf(_("Page rename %s to %s"), urlencode($pagename), urlencode($to));
            $link = WikiURL($to, true);
            $this->sendMail($subject, 
                            $editedby."\n".$link."\n\n"."Renamed $pagename to $to");
        }
    }

    /**
     * The handlers:
     */
    function onChangePage (&$wikidb, &$wikitext, $version, &$meta) {
        $result = true;
	if (!isa($GLOBALS['request'],'MockRequest')) {
	    $notify = $wikidb->get('notify');
            /* Generate notification emails? */
	    if (!empty($notify) and is_array($notify)) {
                if (empty($this->pagename))
                    $this->pagename = $meta['pagename'];
		// TODO: Should be used for ModeratePage and RSS2 Cloud xml-rpc also.
                $this->getPageChangeEmails($notify);
                if (!empty($this->emails)) {
                    $result = $this->sendPageChangeNotification($wikitext, $version, $meta);
                }
	    }
	}
	return $result;
    }

    function onDeletePage (&$wikidb, $pagename) {
        $result = true;
	/* Generate notification emails? */
	if (! $wikidb->isWikiPage($pagename) and !isa($GLOBALS['request'],'MockRequest')) {
	    $notify = $wikidb->get('notify');
	    if (!empty($notify) and is_array($notify)) {
		//TODO: deferr it (quite a massive load if you remove some pages).
		$this->getPageChangeEmails($notify);
		if (!empty($this->emails)) {
		    $editedby = sprintf(_("Removed by: %s"), $this->from); // Todo: host_id
		    //$emails = join(',', $this->emails);
		    $subject = sprintf(_("Page removed %s"), urlencode($pagename));
		    $page = $wikidb->getPage($pagename);
		    $rev = $page->getCurrentRevision(true);
		    $content = $rev->getPackedContent();
                    $result = $this->sendMail($subject, 
                                              $editedby."\n"."Deleted $pagename"."\n\n".$content);
		}
	    }
	}
	//How to create a RecentChanges entry with explaining summary? Dynamically
	/*
	  $page = $this->getPage($pagename);
	  $current = $page->getCurrentRevision();
	  $meta = $current->_data;
	  $version = $current->getVersion();
	  $meta['summary'] = _("removed");
	  $page->save($current->getPackedContent(), $version + 1, $meta);
	*/
	return $result;
    }

    function onRenamePage (&$wikidb, $oldpage, $new_pagename) {
        $result = true;
	if (!isa($GLOBALS['request'], 'MockRequest')) {
	    $notify = $wikidb->get('notify');
	    if (!empty($notify) and is_array($notify)) {
		$this->getPageChangeEmails($notify);
		if (!empty($this->emails)) {
		    $newpage = $wikidb->getPage($new_pagename);
		    $current = $newpage->getCurrentRevision();
		    $meta = $current->_data;
                    $this->pagename = $oldpage;
		    $result = $this->sendPageRenameNotification($new_pagename, $meta);
		}
	    }
	}
    }

    /**
     * Send mail to user and store the cookie in the db
     * wikiurl?action=ConfirmEmail&id=bla
     */
    function sendEmailConfirmation ($email, $userid) {
        $id = rand_ascii_readable(16);
        $wikidb = $GLOBALS['request']->getDbh();
        $data = $wikidb->get('ConfirmEmail');
        while(!empty($data[$id])) { // id collision
            $id = rand_ascii_readable(16);
        }
        $subject = WIKI_NAME . " " . _("e-mail address confirmation");
        $ip = $request->get('REMOTE_HOST');
        $expire_date = time() + 7*86400;
        $content = fmt("Someone, probably you from IP address %s, has registered an
account \"%s\" with this e-mail address on %s.

To confirm that this account really does belong to you and activate
e-mail features on %s, open this link in your browser:

%s

If this is *not* you, don't follow the link. This confirmation code
will expire at %s.", 
                       $ip, $userid, WIKI_NAME, WIKI_NAME, 
                       WikiURL(HOME_PAGE, array('action' => 'ConfirmEmail',
                                                'id' => $id), 
                               true),
                       CTime($expire_date));
        $this->sendMail($subject, $content, "", true);
        $data[$id] = array('email' => $email,
                           'userid' => $userid,
                           'expire' => $expire_date);
        $wikidb->set('ConfirmEmail', $data);
        return '';
    }

    function checkEmailConfirmation () {
        global $request;
        $wikidb = $request->getDbh();
        $data = $wikidb->get('ConfirmEmail');
        $id = $request->getArg('id');
        if (empty($data[$id])) { // id not found
            return HTML(HTML::h1("Confirm E-mail address"),
                        HTML::h1("Sorry! Wrong URL"));
        }
        // upgrade the user
        $userid = $data['userid'];
        $email = $data['email'];
        $u = $request->getUser();
        if ($u->UserName() == $userid) { // lucky: current user (session)
            $prefs = $u->getPreferences();
            $request->_user->_level = WIKIAUTH_USER;
            $request->_prefs->set('emailVerified', true);
        } else {  // not current user
            if (ENABLE_USER_NEW) {
                $u = WikiUser($userid);
                $u->getPreferences();
                $prefs = &$u->_prefs;
            } else {
                $u = new WikiUser($request, $userid);
                $prefs = $u->getPreferences();
            }
            $u->_level = WIKIAUTH_USER;
            $request->setUser($u);
            $request->_prefs->set('emailVerified', true);
        }
        unset($data[$id]);
        $wikidb->set('ConfirmEmail', $data);
        return HTML(HTML::h1("Confirm E-mail address"),
                    HTML::p("Your e-mail address has now been confirmed."));
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
