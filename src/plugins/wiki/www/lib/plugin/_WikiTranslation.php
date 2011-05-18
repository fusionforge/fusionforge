<?php // -*-php-*-
// $Id: _WikiTranslation.php 7842 2011-01-19 15:57:04Z vargenau $
/*
 * Copyright 2004,2005 $ThePhpWikiProgrammingTeam
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
 * _WikiTranslation:  Display pagenames and other strings in various languages.
 * Can also be used to let a favorite translation service translate a whole page.
 * Current favorite: translate.google.com if from_lang = en or fr
 *
 * Examples:
 *  <<_WikiTranslation page=HomePage languages=fr >>
 *     Translation service for HomePage into french (redirect to translate.google.com)
 *  <<_WikiTranslation what=pages >>
 *     Translation matrix of all pages with proper translations (all in pgsrc)
 *  <<_WikiTranslation what=wikiwords match="W*" limit=20 >>
 *     Translation matrix of the first 20 wikiwords matching "W*"
 *  <<_WikiTranslation string=HomePage languages=fr,de,sv >>
 *     Translation matrix for all given languages
 *  <<_WikiTranslation string=HomePage >>
 *     Translation matrix for all supported languages
 *  <<_WikiTranslation string=HomePage languages=fr >>
 *     Just return the translated string for this language.
 *
 * @author:  Reini Urban
 */

/* Container for untranslated pagenames. Needed to show up in locale/po/phpwiki.pot */
$pgsrc_container =
    _("AllPagesCreatedByMe")  .','.
    _("AllPagesLastEditedByMe")  .','.
    _("AllPagesOwnedByMe")  .','.
    _("BackLinks") .','.
    _("CategoryCategory")  .','.
    _("CategoryHomePages")  .','.
    _("DebugInfo") .','.
    _("EditMetaData") .','.
    _("FindPage") .','.
    _("FullRecentChanges") .','.
    _("Help/AddingPages")  .','.
    _("Help/AddCommentPlugin")  .','.
    _("Help/AuthorHistoryPlugin") .','.
    _("Help/CalendarListPlugin") .','.
    _("Help/CalendarPlugin") .','.
    _("Help/CommentPlugin")  .','.
    _("Help/CreateTocPlugin")  .','.
    _("Help/EditMetaDataPlugin") .','.
    _("Help/ExternalSearchPlugin") .','.
    _("Help/FoafViewerPlugin") .','.
    _("Help/FrameIncludePlugin") .','.
    _("Help/HelloWorldPlugin") .','.
    _("Help/IncludePagePlugin") .','.
    _("Help/LinkIcons") .','.
    _("Help/MagicPhpWikiURLs") .','.
    _("Help/MoreAboutMechanics") .','.
    _("Help/OldStyleTablePlugin") .','.
    _("Help/PhotoAlbumPlugin") .','.
    _("Help/PhpHighlightPlugin") .','.
    _("Help/PhpWeatherPlugin") .','.
    _("Help/PhpWiki") .','.
    _("Help/PloticusPlugin") .','.
    _("Help/RawHtmlPlugin") .','.
    _("Help/RedirectToPlugin") .','.
    _("Help/RichTablePlugin") .','.
    _("Help/SystemInfoPlugin") .','.
    _("Help/TranscludePlugin") .','.
    _("Help/UnfoldSubpagesPlugin") .','.
    _("Help/UpLoadPlugin") .','.
    _("Help/WabiSabi") .','.
    _("Help/WikiBlogPlugin") .','.
    _("Help/WikiPlugin") .','.
    _("Help/WikiWikiWeb");
    _("HomePageAlias") .','.
    _("InterWiki") .','.
//  _("PageDump") .','.
    _("PhpWikiAdministration/Chmod") .','.
    _("PhpWikiAdministration/Chown") .','.
    _("PhpWikiAdministration/Remove") .','.
    _("PhpWikiAdministration/Rename") .','.
    _("PhpWikiAdministration/Replace") .','.
    _("PhpWikiAdministration/SetAcl") .','.
    _("PhpWikiDocumentation") .','.
    _("PhpWikiPoll") .','.
    _("RecentVisitors") .','.
    _("ReleaseNotes") .','.
//    _("SpellCheck") .','.
    _("SteveWainstead") .','.
    _("TranslateText") .','.
    _("UpLoad") .','.

require_once('lib/PageList.php');

class WikiPlugin__WikiTranslation
extends WikiPlugin
{

    function getName() {
        return _("_WikiTranslation");
    }

    function getDescription() {
        return _("Show translations of various words or pages");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array( 'languages'  => '',  // comma delimited string of de,en,sv,...
                    'string'     => '',
                    'page'       => '',  // use a translation service
                    'what'       => 'pages', // or 'buttons', 'plugins' or 'wikiwords'

                    'match'         => '*',
                    'from_lang'     => false,
                    'include_empty' => false,
                    //'exclude'       => '',
                    //'sortby'        => '',
                    //'limit'         => 0,
                    'nolinks'       => false,  // don't display any links
                                                   // (for development only)
                    'noT'           => false   // don't display the T link
                                                  // (for development only)
                    ));
    }

    function init_locale($lang) {
        if ($lang != $this->lang)
            update_locale($lang);
        if ($lang == 'en') {
            // Hack alert! we need hash for stepping through it, even if it's
            // in the wrong language
            include (FindFile("locale/de/LC_MESSAGES/phpwiki.php", 0,'reinit'));
            foreach ($locale as $en => $de) {
                    $locale[$en] = $en;
            }
        // gettext module loaded: must load the LC_MESSAGES php hash
        } elseif (function_exists ('bindtextdomain')) {
            include (FindFile("locale/$lang/LC_MESSAGES/phpwiki.php", 0,'reinit'));
            //include (FindLocalizedFile("LC_MESSAGES/phpwiki.php", 0,'reinit'));
        // we already have a $locale, but maybe it's in the wrong language
        } elseif ($lang != $this->lang or empty($GLOBALS['locale'])) {
            include (FindFile("locale/$lang/LC_MESSAGES/phpwiki.php", 0,'reinit'));
        } else {
           $locale = & $GLOBALS['locale'];
        }
        $this->_locales[$lang] = $locale;
    }

    // reverse translation:
    function translate_to_en($text, $lang=false) {
        if (!$lang) $lang = $this->lang; // current locale
        if ($lang == 'en') return $text;

        $this->_locales = array();
        $this->_reverse_locales = array();

        if (!isset($this->_locales[$lang])) {
            $this->init_locale($lang);
        }
        assert(!empty($this->_locales[$lang]));
        if (!isset($this->_reverse_locales[$lang])) {
            // and now do a reverse lookup in the $locale hash
            $this->_reverse_locales[$lang] = array_flip($this->_locales[$lang]);
        }
        if (!empty($this->_reverse_locales[$lang][$text])) {
            return $this->_reverse_locales[$lang][$text];
        } else {
            return $text;
        }
    }

    /**
     * setlocale() switching with the gettext extension is by far too slow.
     * So use the hash regardless if gettext is loaded or not.
     */
    function fast_translate($text, $to_lang, $from_lang=false) {
        if (!$from_lang) $from_lang = $this->lang; // current locale
        if ($from_lang == $to_lang) return $text;
        // setup hash from en => to_lang
        if (!isset($this->_locales[$to_lang]))
            $this->init_locale($to_lang);
        if ($from_lang != 'en') {
            // get reverse gettext: translate to english
            $text = $this->translate_to_en($text, $from_lang);
        }
        return !empty($this->_locales[$to_lang][$text])
                 ? $this->_locales[$to_lang][$text]
                 : $text;
    }

    //FIXME! There's something wrong.
    function translate($text, $to_lang, $from_lang=false) {
        if (!$from_lang) $from_lang = $this->lang; // current locale
        if ($from_lang == $to_lang) return $text;
        // Speed up hash lookup. Not needed for gettext module
        if (!isset($this->_locales[$from_lang]) and !function_exists('bindtextdomain')) {
            $this->init_locale($from_lang);
        }
        if ($from_lang != 'en') {
            // get reverse gettext: translate to english
            $en = $this->translate_to_en($text, $from_lang);
            // and then to target
            update_locale($to_lang);
            $result = gettext($en);
            update_locale($from_lang);
        } else {
            // locale switching is very slow with the gettext extension.
            // better use fast_translate
            if ($from_lang != $to_lang) {
                update_locale($to_lang);
            }
            $result = gettext($text);
            if ($from_lang != $to_lang) {
                update_locale($from_lang);
            }
        }
        return $result;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $this->args = $this->getArgs($argstr, $request);
        extract($this->args);
        $this->request = &$request;
        if (!$from_lang) $from_lang = $request->getPref('lang');
        if (!$from_lang) $from_lang = $GLOBALS['LANG'];
        $this->lang = $from_lang;

        if (empty($languages)) {
            $available_languages = listAvailableLanguages();
            if ($from_lang == 'en') {
                // "en" is always the first.
                array_shift($available_languages);
            }
            // put from_lang to the very end.
            if (in_array($from_lang, $available_languages))
                $languages = $available_languages;
            else
                    $languages = array_merge($available_languages, array($from_lang));
        } elseif (strstr($languages,',')) {
            $languages = explode(',', $languages);
        } else {
            $languages = array($languages);
        }
        if (in_array('zh', $languages) or in_array('ja', $languages)) {

            // If the current charset != utf-8 the text will not be displayed correctly.
            // But here we cannot change the header anymore. So we can decide to ignore them,
            // or display them with all the errors.
            //FIXME: do iconv the ob
            if ($GLOBALS['charset'] != 'utf-8' and !defined('NEED_ICONV_TO')) {
                    define('NEED_ICONV_TO', 'utf-8');
                    //either the extension or external
                //$GLOBALS['charset'] = 'utf-8';
            }
        }
        $to_lang = $languages[0];
        if (!empty($string) and count($languages)==1) {
            return $this->translate($string, $to_lang, $from_lang);
        }
        if (!empty($page)) {
            $pagename = $page;
            if ($dbi->isWikiPage($pagename)) {
                    $url = '';
                    // google can only translate from english and french
                    if (in_array($from_lang, array('en', 'fr'))) {
                    $url = "http://translate.google.com/translate";
                    $url .= "?langpair=" . urlencode($from_lang."|".$to_lang);
                    $url .= "&u=" . urlencode(WikiURL($pagename, false, true));
                    }
                    // redirect or transclude?
                if ($url) {
                    return $request->redirect($url);
                }
                    return HTML(fmt("TODO: Google can only translate from english and french. Find a translation service for %s to language %s",
                                    WikiURL($pagename, false, true),
                                    $to_lang));
            } else {
                return $this->error(fmt("%s is empty",$pagename));
            }
        }

        $pagelist = new PageList('', $exclude, $this->args);
        $pagelist->_columns[0]->_heading = "$from_lang";
        foreach ($languages as $lang) {
            if ($lang == $from_lang) continue;
            $field = "custom:$lang";
            $pagelist->addColumnObject (
              new _PageList_Column_customlang($field, $from_lang, $this));
        }
        if (!empty($string)) {
            $pagelist->addPage( $string );
            return $pagelist;
        }
        switch ($what) {
        case 'allpages':
            $pagelist->addPages( $dbi->getAllPages($include_empty, $sortby,
                                                   $limit, $exclude) );
            break;
        case 'pages':
            // not all pages, only the pgsrc pages
            if (!is_array($exclude))
                $exclude = $pagelist->explodePageList($exclude, false, $sortby,
                                                      $limit, $exclude);
            $path = FindLocalizedFile(WIKI_PGSRC);
            $pgsrc = new fileSet($path);
            foreach ($pgsrc->getFiles($exclude, $sortby, $limit) as $pagename) {
                $pagename = urldecode($pagename);
                if (substr($pagename,-1,1) == '~') continue;
                if (in_array($pagename, $exclude))
                    continue;             // exclude page.
                if ($match != '*' and !glob_match($match, $pagename))
                    continue;
                $page_handle = $dbi->getPage($pagename);
                $pagelist->addPage( $page_handle );
            }
            break;
        case 'wikiwords':
            if (!isset($this->_locales[$from_lang])) {
                $this->init_locale($from_lang);
            }
            $locale = & $this->_locales[$from_lang];
            if (is_array($locale)) {
                $count = 0;
                foreach ($locale as $from => $to) {
                    if ($match != '*' and !glob_match($match, $from))
                        continue;
                    if (isWikiWord($from)) {
                        $count++;
                        $pagelist->addPage( $from );
                        if ($limit and $count > $limit) break;
                    }
                }
            }
            break;
        // all Button texts, which need a localized .png
        // where to get them from? templates/*.tmpl: Button()
        // and WikiLink(?,'button')
        // navbar links, actionpages, and admin requests
        case 'buttons':
            $buttons = $GLOBALS['AllActionPages'];
            $fileset = new FileSet(FindFile("themes/MacOSX/buttons/en"),
                                   "*.png");
            foreach ($fileset->getFiles() as $file) {
                $b = urldecode(substr($file, 0, -4));
                if (!in_array($b,$buttons))
                    $buttons[] = $b;
            }
            $count = 0;
            foreach ($buttons as $button) {
                $pagelist->addPage( $button );
                if ($limit and ++$count > $limit) break;
            }
            break;
        }
        return $pagelist;
    }
};

class _PageList_Column_customlang extends _PageList_Column {
    function _PageList_Column_customlang($field, $from_lang, $plugin) {
        $this->_field = $field;
        $this->_from_lang = $from_lang;
        $this->_plugin =& $plugin;
        $this->_what = $plugin->args['what'];
        $this->_noT = $plugin->args['noT'];
        $this->_nolinks = $plugin->args['nolinks'];
        $this->_iscustom = substr($field, 0, 7) == 'custom:';
        if ($this->_iscustom)
            $this->_field = substr($field, 7);
        //$heading = $field;
        $this->dbi = &$GLOBALS['request']->getDbh();
        $this->_PageList_Column_base($this->_field);
    }

    function _getValue($page, &$revision_handle) {
            if (is_object($page)) $text = $page->getName();
        else $text = $page;
            $trans = $this->_plugin->fast_translate($text, $this->_field,
                                                $this->_from_lang);
        // how to markup untranslated words and not existing pages?
        // untranslated: (TODO) link to translation editor
        if ($trans == $text or // untranslated
            (($this->_from_lang != 'en') and
             ($this->_field != 'en') and
             ($trans == $this->_plugin->fast_translate($text, 'en',
                                                       $this->_from_lang))
             ))
        {
            global $WikiTheme;
            $link = $WikiTheme->linkUnknownWikiWord($trans);
            if (!($this->_noT or $this->_nolinks)
                and $this->dbi->isWikiPage($trans))
            {
                $url = WikiURL($trans, array('action' => 'TranslateText',
                                             'lang' => $this->_field));
                $button = $WikiTheme->makeButton('T', $url);
                $button->addTooltip(sprintf(_("Define the translation for %s in %s"),
                                            $trans, $this->_field));
                $link = HTML::span($button);
                $link->setAttr('class', 'wikiunknown');
                $text = HTML::span($WikiTheme->maybeSplitWikiWord($trans));
                $text->setAttr('style', 'text-decoration:line-through');
                $link->pushContent($text);
                return $link;
            } elseif (is_object($page))
                return '';
            else                        // not existing: empty
                return '';
        } elseif (is_object($page)) {
            if (!$this->_nolinks)
                return WikiLink($trans, 'auto');
            else
                return $trans;
        } else {
            return $trans;
        }
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
