<?php
// $Id: removepage.php 8065 2011-05-04 10:27:44Z vargenau $
require_once('lib/Template.php');

function RemovePage (&$request) {
    global $WikiTheme;

    $page = $request->getPage();
    $pagelink = WikiLink($page);

    if ($request->getArg('cancel')) {
        $request->redirect(WikiURL($page)); // noreturn
    }

    $current = $page->getCurrentRevision();

    if (!$current or !($version = $current->getVersion())) {
        $html = HTML::p(array('class' => 'error'), _("Sorry, this page does not exist."));
    }
    elseif (!$request->isPost() || !$request->getArg('verify')) {

        $removeB = Button('submit:verify', _("Remove Page"), 'wikiadmin');
        $cancelB = Button('submit:cancel', _("Cancel"), 'button'); // use generic wiki button look

        $fieldset = HTML::fieldset(HTML::p(fmt("You are about to remove '%s'!", $pagelink)),
                     HTML::form(array('method' => 'post',
                                      'action' => $request->getPostURL()),
                                HiddenInputs(array('currentversion' => $version,
                                                   'pagename' => $page->getName(),
                                                   'action' => 'remove')),
                                HTML::div(array('class' => 'toolbar'),
                                          $removeB,
                                          $WikiTheme->getButtonSeparator(),
                                          $cancelB))
                     );
        $sample = HTML::div(array('class' => 'transclusion'));
        // simple and fast preview expanding only newlines
        foreach (explode("\n", firstNWordsOfContent(100, $current->getPackedContent())) as $s) {
            $sample->pushContent($s, HTML::br());
        }
        $html = HTML($fieldset, HTML::div(array('class' => 'wikitext'), $sample));
    }
    elseif ($request->getArg('currentversion') != $version) {
        $html = HTML(HTML::p(array('class' => 'error'), (_("Someone has edited the page!"))),
                     HTML::p(fmt("Since you started the deletion process, someone has saved a new version of %s.  Please check to make sure you still want to permanently remove the page from the database.", $pagelink)));
    }
    else {
        // Real delete.
        $pagename = $page->getName();
        $dbi = $request->getDbh();
        $dbi->deletePage($pagename);
        $dbi->touch();
        $html = HTML::p(array('class' => 'feedback'), fmt("Removed page '%s' successfully.", $pagename));
    }

    GeneratePage($html, _("Remove Page"));
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
