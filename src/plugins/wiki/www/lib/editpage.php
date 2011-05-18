<?php
// $Id: editpage.php 7964 2011-03-05 17:05:30Z vargenau $

require_once('lib/Template.php');

class PageEditor
{
    function PageEditor (&$request) {
        $this->request = &$request;

        $this->user = $request->getUser();
        $this->page = $request->getPage();

        $this->current = $this->page->getCurrentRevision(false);

        // HACKish short circuit to browse on action=create
        if ($request->getArg('action') == 'create') {
            if (! $this->current->hasDefaultContents())
                $request->redirect(WikiURL($this->page->getName())); // noreturn
        }

        $this->meta = array('author' => $this->user->getId(),
                            'author_id' => $this->user->getAuthenticatedId(),
                            'mtime' => time());

        $this->tokens = array();

        if (ENABLE_WYSIWYG) {
            $backend = WYSIWYG_BACKEND;
            // TODO: error message
            require_once("lib/WysiwygEdit/$backend.php");
            $class = "WysiwygEdit_$backend";
            $this->WysiwygEdit = new $class();
        }
        if (ENABLE_CAPTCHA) {
            require_once('lib/Captcha.php');
            $this->Captcha = new Captcha($this->meta);
        }

        $version = $request->getArg('version');
        if ($version !== false) {
            $this->selected = $this->page->getRevision($version);
            $this->version = $version;
        }
        else {
            $this->version = $this->current->getVersion();
            $this->selected = $this->page->getRevision($this->version);
        }

        if ($this->_restoreState()) {
            $this->_initialEdit = false;
        }
        else {
            $this->_initializeState();
            $this->_initialEdit = true;

            // The edit request has specified some initial content from a template
            if (  ($template = $request->getArg('template'))
                   and $request->_dbi->isWikiPage($template))
            {
                $page = $request->_dbi->getPage($template);
                $current = $page->getCurrentRevision();
                $this->_content = $current->getPackedContent();
            } elseif ($initial_content = $request->getArg('initial_content')) {
                $this->_content = $initial_content;
                $this->_redirect_to = $request->getArg('save_and_redirect_to');
            }
        }
        if (!headers_sent())
            header("Content-Type: text/html; charset=" . $GLOBALS['charset']);
    }

    function editPage () {
        global $WikiTheme;
        $saveFailed = false;
        $tokens = &$this->tokens;
        $tokens['PAGE_LOCKED_MESSAGE'] = '';
        $tokens['LOCK_CHANGED_MSG'] = '';
        $tokens['CONCURRENT_UPDATE_MESSAGE'] = '';
        $r =& $this->request;

        if (isset($r->args['pref']['editWidth'])
            and ($r->getPref('editWidth') != $r->args['pref']['editWidth'])) {
            $r->_prefs->set('editWidth', $r->args['pref']['editWidth']);
        }
        if (isset($r->args['pref']['editHeight'])
            and ($r->getPref('editHeight') != $r->args['pref']['editHeight'])) {
            $r->_prefs->set('editHeight', $r->args['pref']['editHeight']);
        }

        if ($this->isModerated())
            $tokens['PAGE_LOCKED_MESSAGE'] = $this->getModeratedMessage();

        if (! $this->canEdit()) {
            if ($this->isInitialEdit())
                return $this->viewSource();
            $tokens['PAGE_LOCKED_MESSAGE'] = $this->getLockedMessage();
        }
        elseif ($r->getArg('save_and_redirect_to') != "") {
            if (ENABLE_CAPTCHA && $this->Captcha->Failed()) {
        $this->tokens['PAGE_LOCKED_MESSAGE'] =
                    HTML::p(HTML::h1($this->Captcha->failed_msg));
        }
            elseif ( $this->savePage()) {
                // noreturn
                $request->setArg('action', false);
                $r->redirect(WikiURL($r->getArg('save_and_redirect_to')));
                return true;    // Page saved.
            }
            $saveFailed = true;
        }
        elseif ($this->editaction == 'save') {
            if (ENABLE_CAPTCHA && $this->Captcha->Failed()) {
        $this->tokens['PAGE_LOCKED_MESSAGE'] =
                    HTML::p(HTML::h1($this->Captcha->failed_msg));
        }
            elseif ($this->savePage()) {
                return true;    // Page saved.
            }
            else {
                $saveFailed = true;
            }
        }
    // coming from loadfile conflicts
        elseif ($this->editaction == 'keep_old') {
        // keep old page and do nothing
            $this->_redirectToBrowsePage();
            //$r->redirect(WikiURL($r->getArg('save_and_redirect_to')));
        return true;
        }
        elseif ($this->editaction == 'overwrite') {
            // take the new content without diff
        $source = $this->request->getArg('loadfile');
        require_once('lib/loadsave.php');
        $this->request->setArg('loadfile', 1);
        $this->request->setArg('overwrite', 1);
        $this->request->setArg('merge', 0);
        LoadFileOrDir($this->request);
            $this->_redirectToBrowsePage();
            //$r->redirect(WikiURL($r->getArg('save_and_redirect_to')));
            return true;
        }
        elseif ($this->editaction == 'upload') {
            // run plugin UpLoad
            $plugin = WikiPluginLoader("UpLoad");
            $plugin->run();
            // add link to content
            ;
        }

        if ($saveFailed and $this->isConcurrentUpdate())
        {
            // Get the text of the original page, and the two conflicting edits
            // The diff3 class takes arrays as input.  So retrieve content as
            // an array, or convert it as necesary.
            $orig = $this->page->getRevision($this->_currentVersion);
            // FIXME: what if _currentVersion has be deleted?
            $orig_content = $orig->getContent();
            $this_content = explode("\n", $this->_content);
            $other_content = $this->current->getContent();
            require_once("lib/diff3.php");
            $diff = new diff3($orig_content, $this_content, $other_content);
            $output = $diff->merged_output(_("Your version"), _("Other version"));
            // Set the content of the textarea to the merged diff
            // output, and update the version
            $this->_content = implode ("\n", $output);
            $this->_currentVersion = $this->current->getVersion();
            $this->version = $this->_currentVersion;
            $unresolved = $diff->ConflictingBlocks;
            $tokens['CONCURRENT_UPDATE_MESSAGE']
                = $this->getConflictMessage($unresolved);
        } elseif ($saveFailed && !$this->_isSpam) {
            $tokens['CONCURRENT_UPDATE_MESSAGE'] =
                HTML(HTML::h2(_("Some internal editing error")),
                     HTML::p(_("Your are probably trying to edit/create an invalid version of this page.")),
                     HTML::p(HTML::em(_("&version=-1 might help."))));
        }

        if ($this->editaction == 'edit_convert')
            $tokens['PREVIEW_CONTENT'] = $this->getConvertedPreview();
        if ($this->editaction == 'preview')
            $tokens['PREVIEW_CONTENT'] = $this->getPreview(); // FIXME: convert to _MESSAGE?
        if ($this->editaction == 'diff')
            $tokens['PREVIEW_CONTENT'] = $this->getDiff();

        // FIXME: NOT_CURRENT_MESSAGE?
        $tokens = array_merge($tokens, $this->getFormElements());

        if (ENABLE_EDIT_TOOLBAR and !ENABLE_WYSIWYG) {
            require_once("lib/EditToolbar.php");
            $toolbar = new EditToolbar();
            $tokens = array_merge($tokens, $toolbar->getTokens());
        }

        return $this->output('editpage', _("Edit: %s"));
    }

    function output ($template, $title_fs) {
        global $WikiTheme;
        $selected = &$this->selected;
        $current = &$this->current;

        if ($selected && $selected->getVersion() != $current->getVersion()) {
            $rev = $selected;
            $pagelink = WikiLink($selected);
        }
        else {
            $rev = $current;
            $pagelink = WikiLink($this->page);
        }

        $title = new FormattedText ($title_fs, $pagelink);
        // not for dumphtml or viewsource
        if (ENABLE_WYSIWYG and $template == 'editpage') {
            $WikiTheme->addMoreHeaders($this->WysiwygEdit->Head());
            //$tokens['PAGE_SOURCE'] = $this->WysiwygEdit->ConvertBefore($this->_content);
        }
        $template = Template($template, $this->tokens);
    /* Tell google (and others) not to take notice of edit links */
    if (GOOGLE_LINKS_NOFOLLOW)
        $args = array('ROBOTS_META' => "noindex,nofollow");
        GeneratePage($template, $title, $rev);
        return true;
    }


    function viewSource () {
        assert($this->isInitialEdit());
        assert($this->selected);

        $this->tokens['PAGE_SOURCE'] = $this->_content;
        $this->tokens['HIDDEN_INPUTS'] = HiddenInputs($this->request->getArgs());
        return $this->output('viewsource', _("View Source: %s"));
    }

    function updateLock() {
        $changed = false;
        if (!ENABLE_PAGE_PUBLIC && !ENABLE_EXTERNAL_PAGES) {
            if ((bool)$this->page->get('locked') == (bool)$this->locked)
                return false;       // Not changed.
        }

        if (!$this->user->isAdmin()) {
            // FIXME: some sort of message
            return false;         // not allowed.
        }
        if ((bool)$this->page->get('locked') != (bool)$this->locked) {
            $this->page->set('locked', (bool)$this->locked);
            $this->tokens['LOCK_CHANGED_MSG']
                .= ($this->locked
                    ? _("Page now locked.")
                    : _("Page now unlocked.") . " ");
            $changed = true;
        }
        if (ENABLE_PAGE_PUBLIC and (bool)$this->page->get('public') != (bool)$this->public) {
            $this->page->set('public', (bool)$this->public);
            $this->tokens['LOCK_CHANGED_MSG']
                .= ($this->public
                ? _("Page now public.")
                : _("Page now not-public."));
            $changed = true;
        }

        if (ENABLE_EXTERNAL_PAGES) {
            if ((bool)$this->page->get('external') != (bool)$this->external) {
                $this->page->set('external', (bool)$this->external);
                $this->tokens['LOCK_CHANGED_MSG']
                    = ($this->external
                       ? _("Page now external.")
                       : _("Page now not-external.")) . " ";
                $changed = true;
            }
        }
        return $changed;            // lock changed.
    }

    function savePage () {
        $request = &$this->request;

        if ($this->isUnchanged()) {
            // Allow admin lock/unlock even if
            // no text changes were made.
            if ($this->updateLock()) {
                $dbi = $request->getDbh();
                $dbi->touch();
            }
            // Save failed. No changes made.
            $this->_redirectToBrowsePage();
            // user will probably not see the rest of this...
            require_once('lib/display.php');
            // force browse of current version:
            $request->setArg('action', false);
            $request->setArg('version', false);
            displayPage($request, 'nochanges');
            return true;
        }

        if (!$this->user->isAdmin() and $this->isSpam()) {
            $this->_isSpam = true;
            return false;
            /*
            // Save failed. No changes made.
            $this->_redirectToBrowsePage();
            // user will probably not see the rest of this...
            require_once('lib/display.php');
            // force browse of current version:
            $request->setArg('version', false);
            displayPage($request, 'nochanges');
            return true;
            */
        }

        $page = &$this->page;

        // Include any meta-data from original page version which
        // has not been explicitly updated.
        // (Except don't propagate pgsrc_version --- moot for now,
        //  because at present it never gets into the db...)
        $meta = $this->selected->getMetaData();
        unset($meta['pgsrc_version']);
        $meta = array_merge($meta, $this->meta);

        // Save new revision
        $this->_content = $this->getContent();
        $newrevision = $page->save($this->_content,
                       $this->version == -1
                                     ? -1
                                     : $this->_currentVersion + 1,
                                   // force new?
                       $meta);
        if (!isa($newrevision, 'WikiDB_PageRevision')) {
            // Save failed.  (Concurrent updates).
            return false;
        }

        // New contents successfully saved...
        $this->updateLock();

        // Clean out archived versions of this page.
        require_once('lib/ArchiveCleaner.php');
        $cleaner = new ArchiveCleaner($GLOBALS['ExpireParams']);
        $cleaner->cleanPageRevisions($page);

        /* generate notification emails done in WikiDB::save to catch
         all direct calls (admin plugins) */

        // look at the errorstack
        $errors   = $GLOBALS['ErrorManager']->_postponed_errors;
        $warnings = $GLOBALS['ErrorManager']->getPostponedErrorsAsHTML();
        $GLOBALS['ErrorManager']->_postponed_errors = $errors;

        $dbi = $request->getDbh();
        $dbi->touch();

        global $WikiTheme;
        if (empty($warnings->_content) && ! $WikiTheme->getImageURL('signature')) {
            // Do redirect to browse page if no signature has
            // been defined.  In this case, the user will most
            // likely not see the rest of the HTML we generate
            // (below).
            $request->setArg('action', false);
            $this->_redirectToBrowsePage();
        }

        // Force browse of current page version.
        $request->setArg('version', false);
        // testme: does preview and more need action=edit?
        $request->setArg('action', false);

        $template = Template('savepage', $this->tokens);
        $template->replace('CONTENT', $newrevision->getTransformedContent());
        if (!empty($warnings->_content)) {
            $template->replace('WARNINGS', $warnings);
            unset($GLOBALS['ErrorManager']->_postponed_errors);
        }

        $pagelink = WikiLink($page);

        GeneratePage($template, fmt("Saved: %s", $pagelink), $newrevision);
        return true;
    }

    function isConcurrentUpdate () {
        assert($this->current->getVersion() >= $this->_currentVersion);
        return $this->current->getVersion() != $this->_currentVersion;
    }

    function canEdit () {
        return !$this->page->get('locked') || $this->user->isAdmin();
    }

    function isInitialEdit () {
        return $this->_initialEdit;
    }

    function isUnchanged () {
        $current = &$this->current;

        if ($this->meta['markup'] !=  $current->get('markup'))
            return false;

        return $this->_content == $current->getPackedContent();
    }

    /**
     * Handle AntiSpam here. How? http://wikiblacklist.blogspot.com/
     * Need to check dynamically some blacklist wikipage settings
     * (plugin WikiAccessRestrictions) and some static blacklist.
     * DONE:
     *   More than NUM_SPAM_LINKS (default: 20) new external links.
     *        Disabled if NUM_SPAM_LINKS is 0
     *   ENABLE_SPAMASSASSIN:  content patterns by babycart (only php >= 4.3 for now)
     *   ENABLE_SPAMBLOCKLIST: content domain blacklist
     */
    function isSpam () {
        $current = &$this->current;
        $request = &$this->request;

        $oldtext = $current->getPackedContent();
        $newtext =& $this->_content;
        $numlinks = $this->numLinks($newtext);
        $newlinks = $numlinks - $this->numLinks($oldtext);
        // FIXME: in longer texts the NUM_SPAM_LINKS number should be increased.
        //        better use a certain text : link ratio.

        // 1. Not more than NUM_SPAM_LINKS (default: 20) new external links
        if ((NUM_SPAM_LINKS > 0) and ($newlinks >= NUM_SPAM_LINKS))
        {
            // Allow strictly authenticated users?
            // TODO: mail the admin?
            $this->tokens['PAGE_LOCKED_MESSAGE'] =
                HTML($this->getSpamMessage(),
                     HTML::p(HTML::strong(_("Too many external links."))));
            return true;
        }
        // 2. external babycart (SpamAssassin) check
        // This will probably prevent from discussing sex or viagra related topics. So beware.
        if (ENABLE_SPAMASSASSIN) {
            require_once("lib/spam_babycart.php");
            if ($babycart = check_babycart($newtext, $request->get("REMOTE_ADDR"),
                                           $this->user->getId())) {
                // TODO: mail the admin
                if (is_array($babycart))
                    $this->tokens['PAGE_LOCKED_MESSAGE'] =
                        HTML($this->getSpamMessage(),
                             HTML::p(HTML::em(_("SpamAssassin reports: "),
                                                join("\n", $babycart))));
                return true;
            }
        }
        // 3. extract (new) links and check surbl for blocked domains
        if (ENABLE_SPAMBLOCKLIST and ($newlinks > 5)) {
            require_once("lib/SpamBlocklist.php");
            require_once("lib/InlineParser.php");
            $parsed = TransformLinks($newtext);
            $oldparsed = TransformLinks($oldtext);
            $oldlinks = array();
            foreach ($oldparsed->_content as $link) {
                if (isa($link, 'Cached_ExternalLink') and !isa($link, 'Cached_InterwikiLink')) {
                    $uri = $link->_getURL($this->page->getName());
                    $oldlinks[$uri]++;
                }
            }
            unset($oldparsed);
            foreach ($parsed->_content as $link) {
                if (isa($link, 'Cached_ExternalLink') and !isa($link, 'Cached_InterwikiLink')) {
                    $uri = $link->_getURL($this->page->getName());
                    // only check new links, so admins may add blocked links.
                    if (!array_key_exists($uri, $oldlinks) and ($res = IsBlackListed($uri))) {
                        // TODO: mail the admin
                        $this->tokens['PAGE_LOCKED_MESSAGE'] =
                            HTML($this->getSpamMessage(),
                                 HTML::p(HTML::strong(_("External links contain blocked domains:")),
                                         HTML::ul(HTML::li(sprintf(_("%s is listed at %s with %s"),
                                                                   $uri." [".$res[2]."]", $res[0], $res[1])))));
                        return true;
                    }
                }
            }
            unset($oldlinks);
            unset($parsed);
            unset($oldparsed);
        }

        return false;
    }

    /** Number of external links in the wikitext
     */
    function numLinks(&$text) {
        return substr_count($text, "http://") + substr_count($text, "https://");
    }

    /** Header of the Anti Spam message
     */
    function getSpamMessage () {
        return
            HTML(HTML::h2(_("Spam Prevention")),
                 HTML::p(_("This page edit seems to contain spam and was therefore not saved."),
                         HTML::br(),
                         _("Sorry for the inconvenience.")),
                 HTML::p(""));
    }

    function getPreview () {
        require_once('lib/PageType.php');
        $this->_content = $this->getContent();
    return new TransformedText($this->page, $this->_content, $this->meta);
    }

    function getConvertedPreview () {
        require_once('lib/PageType.php');
        $this->_content = $this->getContent();
        $this->meta['markup'] = 2.0;
        $this->_content = ConvertOldMarkup($this->_content);
    return new TransformedText($this->page, $this->_content, $this->meta);
    }

    function getDiff () {
        require_once('lib/diff.php');
    $html = HTML();

    $diff = new Diff($this->current->getContent(), explode("\n", $this->getContent()));
    if ($diff->isEmpty()) {
        $html->pushContent(HTML::hr(),
                   HTML::p('[', _("Versions are identical"),
                       ']'));
    }
    else {
        // New CSS formatted unified diffs
        $fmt = new HtmlUnifiedDiffFormatter;
        $html->pushContent($fmt->format($diff));
    }
        return $html;
    }

    // possibly convert HTMLAREA content back to Wiki markup
    function getContent () {
        if (ENABLE_WYSIWYG) {
            // don't store everything as html
            if (!WYSIWYG_DEFAULT_PAGETYPE_HTML) {
                // Wikiwyg shortcut to avoid the InlineTransformer:
                if (WYSIWYG_BACKEND == "Wikiwyg") return $this->_content;
                $xml_output = $this->WysiwygEdit->ConvertAfter($this->_content);
                $this->_content = join("", $xml_output->_content);
            } else {
                $this->meta['pagetype'] = 'html';
            }
            return $this->_content;
        } else {
            return $this->_content;
        }
    }

    function getLockedMessage () {
        return
            HTML(HTML::h2(_("Page Locked")),
                 HTML::p(_("This page has been locked by the administrator so your changes can not be saved.")),
                 HTML::p(_("(Copy your changes to the clipboard. You can try editing a different page or save your text in a text editor.)")),
                 HTML::p(_("Sorry for the inconvenience.")));
    }

    function isModerated() {
        return $this->page->get('moderation');
    }
    function getModeratedMessage() {
        return
            HTML(HTML::h2(WikiLink(_("ModeratedPage"))),
                 HTML::p(fmt("You can edit away, but your changes will have to be approved by the defined moderators at the definition in %s", WikiLink(_("ModeratedPage")))),
                 HTML::p(fmt("The approval has a grace period of 5 days. If you have your E-Mail defined in your %s, you will get a notification of approval or rejection.",
                         WikiLink(_("UserPreferences")))));
    }
    function getConflictMessage ($unresolved = false) {
        /*
         xgettext only knows about c/c++ line-continuation strings
         it does not know about php's dot operator.
         We want to translate this entire paragraph as one string, of course.
         */

        //$re_edit_link = Button('edit', _("Edit the new version"), $this->page);

        if ($unresolved)
            $message =  HTML::p(fmt("Some of the changes could not automatically be combined.  Please look for sections beginning with '%s', and ending with '%s'.  You will need to edit those sections by hand before you click Save.",
                                "<<<<<<< ". _("Your version"),
                                ">>>>>>> ". _("Other version")));
        else
            $message = HTML::p(_("Please check it through before saving."));



        /*$steps = HTML::ol(HTML::li(_("Copy your changes to the clipboard or to another temporary place (e.g. text editor).")),
          HTML::li(fmt("%s of the page. You should now see the most current version of the page. Your changes are no longer there.",
                       $re_edit_link)),
          HTML::li(_("Make changes to the file again. Paste your additions from the clipboard (or text editor).")),
          HTML::li(_("Save your updated changes.")));
        */
        return
            HTML(HTML::h2(_("Conflicting Edits!")),
                 HTML::p(_("In the time since you started editing this page, another user has saved a new version of it.")),
                 HTML::p(_("Your changes can not be saved as they are, since doing so would overwrite the other author's changes. So, your changes and those of the other author have been combined. The result is shown below.")),
                 $message);
    }


    function getTextArea () {
        $request = &$this->request;

        $readonly = ! $this->canEdit(); // || $this->isConcurrentUpdate();

        // WYSIWYG will need two pagetypes: raw wikitest and converted html
        if (ENABLE_WYSIWYG) {
            $this->_wikicontent = $this->_content;
            $this->_content = $this->WysiwygEdit->ConvertBefore($this->_content);
            //                $this->getPreview();
            //$this->_htmlcontent = $this->_content->asXML();
        }

        $textarea = HTML::textarea(array('class'=> 'wikiedit',
                                         'name' => 'edit[content]',
                                         'id'   => 'edit-content',
                                         'rows' => $request->getPref('editHeight'),
                                         'cols' => $request->getPref('editWidth'),
                                         'readonly' => (bool) $readonly),
                                   $this->_content);
        if (ENABLE_WYSIWYG) {
            return $this->WysiwygEdit->Textarea($textarea, $this->_wikicontent,
                                                $textarea->getAttr('name'));
        } else
            return $textarea;
    }

    function getFormElements () {
        global $WikiTheme;
        $request = &$this->request;
        $page = &$this->page;

        $h = array('action'   => 'edit',
                   'pagename' => $page->getName(),
                   'version'  => $this->version,
                   'edit[pagetype]' => $this->meta['pagetype'],
                   'edit[current_version]' => $this->_currentVersion);

        $el['HIDDEN_INPUTS'] = HiddenInputs($h);
        $el['EDIT_TEXTAREA'] = $this->getTextArea();
        if ( ENABLE_CAPTCHA ) {
            $el = array_merge($el, $this->Captcha->getFormElements());
        }
        $el['SUMMARY_INPUT']
            = HTML::input(array('type'  => 'text',
                                'class' => 'wikitext',
                                'id' => 'edit-summary',
                                'name'  => 'edit[summary]',
                                'size'  => 50,
                                'maxlength' => 256,
                                'value' => $this->meta['summary']));
        $el['MINOR_EDIT_CB']
            = HTML::input(array('type' => 'checkbox',
                                'name'  => 'edit[minor_edit]',
                                'id' => 'edit-minor_edit',
                                'checked' => (bool) $this->meta['is_minor_edit']));
        $el['OLD_MARKUP_CB']
            = HTML::input(array('type' => 'checkbox',
                                'name' => 'edit[markup]',
                                'value' => 'old',
                                'checked' => $this->meta['markup'] < 2.0,
                                'id' => 'useOldMarkup',
                                'onclick' => 'showOldMarkupRules(this.checked)'));
        $el['OLD_MARKUP_CONVERT'] = ($this->meta['markup'] < 2.0)
            ? Button('submit:edit[edit_convert]', _("Convert"), 'wikiaction') : '';
        $el['LOCKED_CB']
            = HTML::input(array('type' => 'checkbox',
                                'name' => 'edit[locked]',
                                'id'   => 'edit-locked',
                                'disabled' => (bool) !$this->user->isAdmin(),
                                'checked'  => (bool) $this->locked));
        if (ENABLE_PAGE_PUBLIC) {
            $el['PUBLIC_CB']
            = HTML::input(array('type' => 'checkbox',
                                'name' => 'edit[public]',
                                'id'   => 'edit-public',
                                'disabled' => (bool) !$this->user->isAdmin(),
                                'checked'  => (bool) $this->page->get('public')));
        }
        if (ENABLE_EXTERNAL_PAGES) {
            $el['EXTERNAL_CB']
            = HTML::input(array('type' => 'checkbox',
                                'name' => 'edit[external]',
                                'id'   => 'edit-external',
                                'disabled' => (bool) !$this->user->isAdmin(),
                                'checked'  => (bool) $this->page->get('external')));
        }
        if (ENABLE_WYSIWYG) {
        if (($this->version == 0) and ($request->getArg('mode') != 'wysiwyg')) {
        $el['WYSIWYG_B'] = Button(array("action" => "edit", "mode" => "wysiwyg"), "Wysiwyg Editor");
        }
    }

        $el['PREVIEW_B'] = Button('submit:edit[preview]', _("Preview"),
                                  'wikiaction',
                                  array('accesskey'=> 'p',
                                     'title' => 'Preview the current content [alt-p]'));

        //if (!$this->isConcurrentUpdate() && $this->canEdit())
        $el['SAVE_B'] = Button('submit:edit[save]',
                               _("Save"), 'wikiaction',
                               array('accesskey'=> 's',
                                     'title' => 'Save the current content as wikipage [alt-s]'));
        $el['CHANGES_B'] = Button('submit:edit[diff]',
                               _("Changes"), 'wikiaction',
                               array('accesskey'=> 'c',
                                     'title' => 'Preview the current changes as diff [alt-c]'));
        $el['UPLOAD_B'] = Button('submit:edit[upload]',
                               _("Upload"), 'wikiaction',
                                array('title' => 'Select a local file and press Upload to attach into this page'));
        $el['SPELLCHECK_B'] = Button('submit:edit[SpellCheck]',
                               _("Spell Check"), 'wikiaction',
                                array('title' => 'Check the spelling'));
        $el['IS_CURRENT'] = $this->version == $this->current->getVersion();

        $el['WIDTH_PREF']
            = HTML::input(array('type'     => 'text',
                                'size'     => 3,
                                'maxlength'=> 4,
                                'class'    => "numeric",
                                'name'     => 'pref[editWidth]',
                                'id'       => 'pref-editWidth',
                                'value'    => $request->getPref('editWidth'),
                                'onchange' => 'this.form.submit();'));
        $el['HEIGHT_PREF']
            = HTML::input(array('type'     => 'text',
                                'size'     => 3,
                                'maxlength'=> 4,
                                'class'    => "numeric",
                                'name'     => 'pref[editHeight]',
                                'id'       => 'pref-editHeight',
                                'value'    => $request->getPref('editHeight'),
                                'onchange' => 'this.form.submit();'));
        $el['SEP'] = $WikiTheme->getButtonSeparator();
        $el['AUTHOR_MESSAGE'] = fmt("Author will be logged as %s.",
                                    HTML::em($this->user->getId()));

        return $el;
    }

    function _redirectToBrowsePage() {
        $this->request->redirect(WikiURL($this->page, false, 'absolute_url'));
    }

    function _restoreState () {
        $request = &$this->request;

        $posted = $request->getArg('edit');
        $request->setArg('edit', false);

        if (!$posted
        || !$request->isPost()
            || !in_array($request->getArg('action'),array('edit','loadfile')))
            return false;

        if (!isset($posted['content']) || !is_string($posted['content']))
            return false;
        $this->_content = preg_replace('/[ \t\r]+\n/', "\n",
                                        rtrim($posted['content']));
        $this->_content = $this->getContent();

        $this->_currentVersion = (int) $posted['current_version'];

        if ($this->_currentVersion < 0)
            return false;
        if ($this->_currentVersion > $this->current->getVersion())
            return false;       // FIXME: some kind of warning?

        $is_old_markup = !empty($posted['markup']) && $posted['markup'] == 'old';
        $meta['markup'] = $is_old_markup ? false : 2.0;
        $meta['summary'] = trim(substr($posted['summary'], 0, 256));
        $meta['is_minor_edit'] = !empty($posted['minor_edit']);
        $meta['pagetype'] = !empty($posted['pagetype']) ? $posted['pagetype'] : false;
        if ( ENABLE_CAPTCHA )
        $meta['captcha_input'] = !empty($posted['captcha_input']) ?
        $posted['captcha_input'] : '';

        $this->meta = array_merge($this->meta, $meta);
        $this->locked = !empty($posted['locked']);
        if (ENABLE_PAGE_PUBLIC)
            $this->public = !empty($posted['public']);
        if (ENABLE_EXTERNAL_PAGES)
            $this->external = !empty($posted['external']);

    foreach (array('preview','save','edit_convert',
               'keep_old','overwrite','diff','upload') as $o)
    {
        if (!empty($posted[$o]))
        $this->editaction = $o;
    }
        if (empty($this->editaction))
            $this->editaction = 'edit';

        return true;
    }

    function _initializeState () {
        $request = &$this->request;
        $current = &$this->current;
        $selected = &$this->selected;
        $user = &$this->user;

        if (!$selected)
            NoSuchRevision($request, $this->page, $this->version); // noreturn

        $this->_currentVersion = $current->getVersion();
        $this->_content = $selected->getPackedContent();

        $this->locked = $this->page->get('locked');

        // If author same as previous author, default minor_edit to on.
        $age = $this->meta['mtime'] - $current->get('mtime');
        $this->meta['is_minor_edit'] = ( $age < MINOR_EDIT_TIMEOUT
                                         && $current->get('author') == $user->getId()
                                         );

        // Default for new pages is new-style markup.
        if ($selected->hasDefaultContents())
            $is_new_markup = true;
        else
            $is_new_markup = $selected->get('markup') >= 2.0;

        $this->meta['markup'] = $is_new_markup ? 2.0: false;
        $this->meta['pagetype'] = $selected->get('pagetype');
        if ($this->meta['pagetype'] == 'wikiblog')
            $this->meta['summary'] = $selected->get('summary'); // keep blog title
        else
            $this->meta['summary'] = '';
        $this->editaction = 'edit';
    }
}

class LoadFileConflictPageEditor
extends PageEditor
{
    function editPage ($saveFailed = true) {
        $tokens = &$this->tokens;

        if (!$this->canEdit()) {
            if ($this->isInitialEdit()) {
                return $this->viewSource();
        }
            $tokens['PAGE_LOCKED_MESSAGE'] = $this->getLockedMessage();
        }
        elseif ($this->editaction == 'save') {
            if ($this->savePage()) {
                return true;    // Page saved.
        }
            $saveFailed = true;
        }

        if ($saveFailed || $this->isConcurrentUpdate())
        {
            // Get the text of the original page, and the two conflicting edits
            // The diff class takes arrays as input.  So retrieve content as
            // an array, or convert it as necesary.
            $orig = $this->page->getRevision($this->_currentVersion);
            $this_content = explode("\n", $this->_content);
            $other_content = $this->current->getContent();
            require_once("lib/diff.php");
            $diff2 = new Diff($other_content, $this_content);
            $context_lines = max(4, count($other_content) + 1,
                                 count($this_content) + 1);
            $fmt = new BlockDiffFormatter($context_lines);

            $this->_content = $fmt->format($diff2);
            // FIXME: integrate this into class BlockDiffFormatter
            $this->_content = str_replace(">>>>>>>\n<<<<<<<\n", "=======\n",
                                          $this->_content);
            $this->_content = str_replace("<<<<<<<\n>>>>>>>\n", "=======\n",
                                          $this->_content);

            $this->_currentVersion = $this->current->getVersion();
            $this->version = $this->_currentVersion;
            $tokens['CONCURRENT_UPDATE_MESSAGE'] = $this->getConflictMessage();
        }

        if ($this->editaction == 'edit_convert')
            $tokens['PREVIEW_CONTENT'] = $this->getConvertedPreview();
        if ($this->editaction == 'preview')
            $tokens['PREVIEW_CONTENT'] = $this->getPreview(); // FIXME: convert to _MESSAGE?

        // FIXME: NOT_CURRENT_MESSAGE?
        $tokens = array_merge($tokens, $this->getFormElements());
    // we need all GET params for loadfile overwrite
    if ($this->request->getArg('action') == 'loadfile') {

        $this->tokens['HIDDEN_INPUTS'] =
        HTML(HiddenInputs
            (array('source' => $this->request->getArg('source'),
                   'merge'  => 1)),
             $this->tokens['HIDDEN_INPUTS']);
        // add two conflict resolution buttons before preview and save.
        $tokens['PREVIEW_B'] = HTML(
                    Button('submit:edit[keep_old]',
                       _("Keep old"), 'wikiaction'),
                    $tokens['SEP'],
                    Button('submit:edit[overwrite]',
                       _("Overwrite with new"), 'wikiaction'),
                    $tokens['SEP'],
                    $tokens['PREVIEW_B']);
    }
    if (ENABLE_EDIT_TOOLBAR and !ENABLE_WYSIWYG) {
            include_once("lib/EditToolbar.php");
            $toolbar = new EditToolbar();
            $tokens = array_merge($tokens, $toolbar->getTokens());
    }

        return $this->output('editpage', _("Merge and Edit: %s"));
    }

    function output ($template, $title_fs) {
        $selected = &$this->selected;
        $current = &$this->current;

        if ($selected && $selected->getVersion() != $current->getVersion()) {
            $rev = $selected;
            $pagelink = WikiLink($selected);
        }
        else {
            $rev = $current;
            $pagelink = WikiLink($this->page);
        }

        $title = new FormattedText ($title_fs, $pagelink);
    $this->tokens['HEADER'] = $title;
    //hack! there's no TITLE in editpage, but in the previous top template
    if (empty($this->tokens['PAGE_LOCKED_MESSAGE']))
        $this->tokens['PAGE_LOCKED_MESSAGE'] = HTML::h3($title);
    else
        $this->tokens['PAGE_LOCKED_MESSAGE'] = HTML(HTML::h3($title),
                            $this->tokens['PAGE_LOCKED_MESSAGE']);
        $template = Template($template, $this->tokens);

        //GeneratePage($template, $title, $rev);
        PrintXML($template);
        return true;
    }

    function getConflictMessage () {
        $message = HTML(HTML::p(fmt("Some of the changes could not automatically be combined.  Please look for sections beginning with '%s', and ending with '%s'.  You will need to edit those sections by hand before you click Save.",
                                    "<<<<<<<",
                                    "======="),
                                HTML::p(_("Please check it through before saving."))));
        return $message;
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
