<?php // -*-php-*-
// $Id: TranslateText.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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
 * TranslateText:  Translation helper
 * The (bogus) pagename is the text to be translated.
 * One required argument: lang
 * Requires that an action page with the <<TranslateText >> line exists.
 *
 * Usually called from <<_WikiTranslation >>
 * Contributed translation are stored in UsersPage/ContributedTranslations
 *
 * Examples:
 *    pagename="Some text in english" action=TranslateText lang=es
 *
 * @author:  Reini Urban
 */

require_once("lib/plugin/_WikiTranslation.php");

class WikiPlugin_TranslateText
extends WikiPlugin__WikiTranslation
{
    function getName() {
        return _("TranslateText");
    }

    function getDescription() {
        return _("Define a translation for a specified text");
    }

    function getDefaultArguments() {
        return
            array( 'lang'      => false,
                   'pagename'  => '[pagename]',
                   'translate' => false,
                 );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));
        if (!$lang)
            return $this->error(
                _("This internal action page cannot viewed.")."\n".
                _("You can only use it via the _WikiTranslation plugin."));

        $this->lang = $lang;
        //action=save
        if (!empty($translate) and isset($translate['submit']) and $request->isPost()) {
            $trans = $translate["content"];
            if (empty($trans) or $trans == $pagename) {
                $header = HTML(HTML::h2(_("Translation Error!")),
                               HTML::p(_("Your translated text is either empty or equal to the untranslated text. Please try again.")));
            } else {
                //save translation in a users subpage
                $user = $request->getUser();
                $homepage = $user->_HomePagehandle;
                $transpagename = $homepage->getName() . SUBPAGE_SEPARATOR . _("ContributedTranslations");

                $page    = $dbi->getPage($transpagename);
                $current = $page->getCurrentRevision();
                $version = $current->getVersion();
                if ($version) {
                    $text = $current->getPackedContent() . "\n";
                    $meta = $current->_data;
                } else {
                    $text = '';
                    $meta = array('markup' => 2.0,
                                  'author' => $user->getId());
                }
                $text .= $user->getId() . " " . Iso8601DateTime() . "\n" .
                         "* " . sprintf(_("Translate '%s' to '%s' in *%s*"),
                                        $pagename, $trans, $lang);
                $text .= "\n  <verbatim>locale/po/$lang.po:\n  msgid \"".$pagename."\"\n  msgstr \"".$trans."\"\n  </verbatim>";
                $meta['summary'] = sprintf(_("Translate %s to %s in %s"),
                                           substr($pagename,0,15),substr($trans,0,15),$lang);
                $page->save($text, $version + 1, $meta);
                // TODO: admin notification
                return HTML(HTML::h2(_("Thanks for adding this translation!")),
                            HTML::p(fmt("Your translated text doesn't yet appear in this %s, but the Administrator will pick it up and add to the installation.",
                                       WIKI_NAME)),
                            fmt("Your translation is stored in %s",WikiLink($transpagename)));
            }
        }
        $trans = $this->translate($pagename,$lang,'en');
        //Todo: google lookup or at least a google lookup button.
        if (isset($header))
            $header = HTML($header,fmt("From english to %s: ", HTML::strong($lang)));
        else
            $header = fmt("From english to %s: ", HTML::strong($lang));
        $button_label = _("Translate");

        $buttons = HTML::p(Button('submit:translate[submit]', $button_label, 'wikiadmin'),
                           Button('submit:translate[cancel]', _("Cancel"), 'button'));
        return HTML::form(array('action' => $request->getPostURL(),
                                'method' => 'post'),
                          $header,
                          HTML::textarea(array('class' => 'wikiedit',
                                               'name' => 'translate[content]',
                                               'id'   => 'translate[content]',
                                               'rows' => 4,
                                               'cols' => $request->getPref('editWidth')
                                               ),
                                         $trans),
                          HiddenInputs($request->getArgs(),
                                        false,
                                        array('translate')),
                          HiddenInputs(array('translate[action]' => $pagename,
                                             'require_authority_for_post' => WIKIAUTH_BOGO,
                                             )),
                          $buttons);
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
