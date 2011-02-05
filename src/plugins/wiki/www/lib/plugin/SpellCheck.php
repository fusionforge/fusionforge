<?php // -*-php-*-
// rcs_id('$Id: SpellCheck.php 7850 2011-01-21 09:41:05Z vargenau $');
/**
 * Copyright 2006,2007 $ThePhpWikiProgrammingTeam
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
 * SpellCheck is a plugin, used inside the editpage.tmpl (on save or preview)
 * This could be a userpref option always highlighting words in preview
 * or it could be an extra button in edit.
 *
 * The pspell extension is better, because it's easier to store corrections.
 * Enchant looks more promising, because it supports multiple speller backends.
 *
 * Currently we do use aspell (via pspell or cmdline) in ispell mode.
 * Maybe enchant later.
 * cmdline preparation:
  do autosplit wikiwords && sed s,^,\^^, $pagename | aspell --lang=$LANG -a
    or
  sed s,^,\^^, $pagename | aspell --lang=$LANG -a -C
=>
  & phpwiki 62 30: pipework, paprika, Popek, Phip, Pike, Viki, pike, ...
 */

// Those settings should really be defined in config.ini, not here.
if (!function_exists('pspell_new_config')) {
    // old pipe interface:
    if (!defined('ASPELL_EXE'))
        define('ASPELL_EXE','aspell');
    //define('ASPELL_EXE','/usr/local/bin/aspell');
    //define('ASPELL_EXE','/home/groups/p/ph/phpwiki/bin/aspell');
    if (!defined('ASPELL_DATA_DIR'))
        if (isWindows())
            define('ASPELL_DATA_DIR','c:\cygwin\usr\share\aspell');
        else
            define('ASPELL_DATA_DIR','/usr/share/aspell');
    //define('ASPELL_DATA_DIR','/home/groups/p/ph/phpwiki/share/highlight');
} else {
    // new library interface through the pspell extension:
    // "/var/dictionaries/custom.pws"
    if (!defined('PSPELL_PWL'))
        define('PSPELL_PWL', '');  // phpwiki-special wordlist
    // "/var/dictionaries/custom.repl"
    if (!defined('PSPELL_REPL'))
        define('PSPELL_REPL', ''); // phpwiki-special replacement list (persistent replacements)
}

class WikiPlugin_SpellCheck
extends WikiPlugin
{
    function getName () {
        return _("Spell Checker");
    }
    function getDescription () {
        return _("Check the spelling of a page and make suggestions");
    }
    function managesValidators() {
        return true;
    }
    function getDefaultArguments() {
        return array('pagename' => '[]', // button or preview highlight?
                     );
    }

    function pspell_check ($text, $lang=false) {
        global $charset;
        if ($lang) $lang = $GLOBALS['LANG'];
        $words = preg_split('/[\W]+?/', $text);

        $misspelled = $return = array();
        $pspell_config = pspell_config_create($lang, "", "", $charset,
                                              PSPELL_NORMAL|PSPELL_RUN_TOGETHER);
        //pspell_config_runtogether($pspell_config, true);
        if (PSPELL_PWL)
            pspell_config_personal($pspell_config, PSPELL_PWL);
        if (PSPELL_REPL)
            pspell_config_repl($pspell_config, PSPELL_REPL);
        $pspell = pspell_new_config($pspell_config);

        foreach ($words as $value) {
            // SplitPagename $value
            if (!pspell_check($pspell, $value)) {
                $misspelled[] = $value;
            }
        }
        foreach ($misspelled as $value) {
            $return[$value] = pspell_suggest($pspell, $value);
        }
        return $return;
    }

    function pspell_correct ($text, $corrections) {
        ;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));
        $page = $dbi->getPage($pagename);
        $current = $page->getCurrentRevision();
        $source = $current->getPackedContent();

        if (empty($source))
            return $this->error(fmt("empty source"));
        if ($basepage == _("SpellCheck"))
            return $this->error(fmt("Cannot SpellCheck myself"));
        $lang = $page->get('lang');
        if (empty($lang)) $lang = $GLOBALS['LANG'];
        $html = HTML();
        if (!function_exists('pspell_new_config')) {
            // use the aspell commandline interface
            include_once("lib/WikiPluginCached.php");
            $args = "";
            $source = preg_replace("/^/m", "^", $source);
            if (ASPELL_DATA_DIR)
                $args .= " --data-dir=" . ASPELL_DATA_DIR;
            // MAYBE TODO: do we have the language dictionary?
            $args .= " --lang=" . $lang;
            // use -C or autosplit wikiwords in the text
            $commandLine = ASPELL_EXE . " -a -C $args ";
            $cache = new WikiPluginCached;
            $code = $cache->filterThroughCmd($source, $commandLine);
            if (empty($code))
                return $this->error(fmt("Couldn't start commandline '%s'",$commandLine));
            $sugg = array();
            foreach (preg_split("/\n/", $code) as $line) {
                if (preg_match("/^& (\w+) \d+ \d+: (.+)$/", $line, $m)) {
                    $sugg[$m[1]] = preg_split("/, /", $m[2]);
                }
            }
            /*$pre = HTML::pre(HTML::raw($code));
            $html->pushContent($pre);*/
        } else {
            $sugg = pspell_check($source, $lang);
        }
        //$html->pushContent(HTML::hr(),HTML::h1(_("Spellcheck")));
        $page = $request->getPage();
        if ($version) {
            $revision = $page->getRevision($version);
            if (!$revision)
                NoSuchRevision($request, $page, $version);
        }
        else {
            $revision = $page->getCurrentRevision();
        }
        $GLOBALS['request']->setArg('suggestions', $sugg);
        include_once("lib/BlockParser.php");
        $ori_html = TransformText($revision, $revision->get('markup'), $page);
        $GLOBALS['request']->setArg('suggestions', false);

        $html->pushContent($ori_html, HTML::hr(), HTML::h1(_("SpellCheck result")));

        $list = HTML::ul();
        foreach ($sugg as $word => $suggs) {
            $w = HTML::span(array('class' => 'spell-wrong'), $word);
            // TODO: optional replace-link. jscript or request button with word replace.
            $r = HTML();
            foreach ($suggs as $s) {
                $r->pushContent(HTML::a(array('class' => 'spell-sugg',
                                              'href' => "javascript:do_replace('$word','$s')"),
                                        $s),", ");
            }
            $list->pushContent(HTML::li($w, _(": "), $r));
        }
        $html->pushContent($list);
        return $html;
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
