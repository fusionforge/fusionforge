<?php
/**
 * Copyright © 2008-2009,2011 Marc-Etienne Vargenau, Alcatel-Lucent
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
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

require_once 'lib/Template.php';

/**
 * @param WikiRequest $request
 */
function PurgePage(&$request)
{
    global $WikiTheme;

    $page = $request->getPage();
    $pagelink = WikiLink($page);

    if ($request->getArg('cancel')) {
        $request->redirect(WikiURL($page,
            array('warningmsg' => _('Purge cancelled'))));
        // noreturn
    }

    $current = $page->getCurrentRevision();

    if (!$current or !($version = $current->getVersion())) {
        $html = HTML::p(array('class' => 'error'), _("Sorry, this page does not exist."));
    } elseif (!$request->isPost() || !$request->getArg('verify')) {

        $purgeB = Button('submit:verify', _("Purge Page"), 'wikiadmin');
        $cancelB = Button('submit:cancel', _("Cancel"), 'button'); // use generic wiki button look

        $fieldset = HTML::fieldset(HTML::legend(_('Confirm purge')),
            HTML::p(fmt("You are about to purge “%s”!", $pagelink)),
            HTML::form(array('method' => 'post',
                    'action' => $request->getPostURL()),
                HiddenInputs(array('currentversion' => $version,
                    'pagename' => $page->getName(),
                    'action' => 'purge')),
                HTML::div(array('class' => 'toolbar'),
                    $purgeB,
                    $WikiTheme->getButtonSeparator(),
                    $cancelB))
        );
        $sample = HTML::div(array('class' => 'transclusion'));
        // simple and fast preview expanding only newlines
        foreach (explode("\n", firstNWordsOfContent(100, $current->getPackedContent())) as $s) {
            $sample->pushContent($s, HTML::br());
        }
        $html = HTML($fieldset, HTML::div(array('class' => 'wikitext'), $sample));
    } elseif ($request->getArg('currentversion') != $version) {
        $html = HTML(HTML::p(array('class' => 'error'), (_("Someone has edited the page!"))),
            HTML::p(fmt("Since you started the purge process, someone has saved a new version of %s.  Please check to make sure you still want to permanently purge the page from the database.", $pagelink)));
    } else {
        // Real purge.
        $pagename = $page->getName();
        $dbi = $request->getDbh();
        $dbi->purgePage($pagename);
        $dbi->touch();
        $html = HTML::p(array('class' => 'feedback'), fmt("Purged page “%s” successfully.", $pagename));
    }

    GeneratePage($html, _("Purge Page"));
}
