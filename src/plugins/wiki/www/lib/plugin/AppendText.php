<?php // -*-php-*-
// rcs_id('$Id: AppendText.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright 2004,2007 $ThePhpWikiProgrammingTeam
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
 * Append text to an existing page.
 *
 * @Author: Pascal Giard <evilynux@gmail.com>
 *
 * See http://sourceforge.net/mailarchive/message.php?msg_id=10141823
 * why not to use "text" as parameter. Nasty mozilla bug with mult. radio rows.
 *
 * Todo: multiple pages. e.g. AppendText s=~[CategoryINtime~] page=<!plugin TitleSearch intime !>
 */
class WikiPlugin_AppendText
extends WikiPlugin
{
    function getName() {
        return _("AppendText");
    }

    function getDescription() {
        return _("Append text to any page in this wiki.");
    }

    function getDefaultArguments() {
        return array('page'     => '[pagename]',
                     'pages'    => false,
                     's'        => '',  // Text to append.
                     'before'   => '',  // Add before (ignores after if defined)
                     'after'    => '',  // Add after line beginning with this
                     'redirect' => false // Redirect to modified page
                     );
    }

    function _fallback($addtext, $oldtext, $notfound, &$message) {
        $message->pushContent(sprintf(_("%s not found"), $notfound).". ".
                              _("Appending at the end.")."\n");
        return $oldtext . "\n" . $addtext;
    }

    function run($dbi, $argstr, &$request, $basepage) {

        $args = $this->getArgs($argstr, $request);
        if (!$args['pages'] or !$request->isPost()) {
            return $this->_work($args['page'], $args, $dbi, $request);
        } else {
            $html = HTML();
            if ($args['page'] != $basepage)
                $html->pushContent("pages argument overrides page argument. ignored.",HTML::br());
            foreach ($args['pages'] as $pagename) {
                $html->pushContent($this->_work($pagename, $args, $dbi, $request));
            }
            return $html;
        }
    }

    function _work($pagename, $args, $dbi, &$request) {
        if (empty($args['s'])) {
            if ($request->isPost()) {
                if ($pagename != _("AppendText"))
                    return HTML($request->redirect(WikiURL($pagename, false, 'absurl'), false));
            }
            return '';
        }

        $page = $dbi->getPage($pagename);
        $message = HTML();

        if (!$page->exists()) { // We might want to create it?
            $message->pushContent(sprintf(_("Page could not be updated. %s doesn't exist!\n"),
                                            $pagename));
            return $message;
        }

        $current = $page->getCurrentRevision();
        $oldtext = $current->getPackedContent();
        $text = $args['s'];

        // If a "before" or "after" is specified but not found, we simply append text to the end.
        if (!empty($args['before'])) {
            $before = preg_quote($args['before'], "/");
            // Insert before
            $newtext = preg_match("/\n${before}/", $oldtext)
                ? preg_replace("/(\n${before})/",
                               "\n" .  preg_quote($text, "/") . "\\1",
                               $oldtext)
                : $this->_fallback($text, $oldtext, $args['before'], $message);
        } elseif (!empty($args['after'])) {
            // Insert after
            $after = preg_quote($args['after'], "/");
            $newtext = preg_match("/\n${after}/", $oldtext)
                ? preg_replace("/(\n${after})/",
                               "\\1\n" .  preg_quote($text, "/"),
                               $oldtext)
                : $this->_fallback($text, $oldtext, $args['after'], $message);
        } else {
            // Append at the end
            $newtext = $oldtext .
                "\n" . $text;
        }

        require_once("lib/loadsave.php");
        $meta = $current->_data;
        $meta['summary'] = sprintf(_("AppendText to %s"), $pagename);
        if ($page->save($newtext, $current->getVersion() + 1, $meta)) {
            $message->pushContent(_("Page successfully updated."), HTML::br());
        }

        // AppendText has been called from the same page that got modified
        // so we directly show the page.
        if ( $request->getArg($pagename) == $pagename ) {
            // TODO: Just invalidate the cache, if AppendText didn't
            // change anything before.
            //
            return $request->redirect(WikiURL($pagename, false, 'absurl'), false);

        // The user asked to be redirected to the modified page
        } elseif ($args['redirect']) {
            return $request->redirect(WikiURL($pagename, false, 'absurl'), false);

        } else {
            $link = HTML::em(WikiLink($pagename));
            $message->pushContent(HTML::Raw(sprintf(_("Go to %s."), $link->asXml())));
        }

        return $message;
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
