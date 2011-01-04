<?php // -*-php-*-
// $Id: Template.php 7806 2011-01-04 17:55:44Z vargenau $
/*
 * Copyright 2005,2007 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2011 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Template: Parametrized blocks.
 *    Include text from a wiki page and replace certain placeholders by parameters.
 *    Similiar to CreatePage with the template argument, but at run-time.
 *    Similiar to the mediawiki templates but not with the "|" parameter seperator.
 * Usage:   <<Template page=TemplateFilm vars="title=rurban&year=1999" >>
 * Author:  Reini Urban
 * See also: http://meta.wikimedia.org/wiki/Help:Template
 *
 * Parameter expansion:
 *   vars="var1=value1&var2=value2"
 * We only support named parameters, not numbered ones as in mediawiki, and
 * the placeholder is %%var%% and not {{{var}}} as in mediawiki.
 *
 * The following predefined uppercase variables are automatically expanded if existing:
 *   PAGENAME
 *   MTIME     - last modified date + time
 *   CTIME     - creation date + time
 *   AUTHOR    - last author
 *   OWNER
 *   CREATOR   - first author
 *   SERVER_URL, DATA_PATH, SCRIPT_NAME, PHPWIKI_BASE_URL and BASE_URL
 *
 * <noinclude> .. </noinclude>     is stripped from the template expansion.
 * <includeonly> .. </includeonly> is only expanded in pages using the template,
 *                                 not in the template itself.
 *
 *   We support a mediawiki-style syntax extension which maps
 *     {{TemplateFilm|title=Some Good Film|year=1999}}
 *   to
 *     <<Template page=TemplateFilm vars="title=Some Good Film&year=1999" >>
 */

class WikiPlugin_Template
extends WikiPlugin
{
    function getName() {
        return _("Template");
    }

    function getDescription() {
        return _("Parametrized page inclusion.");
    }

    function getDefaultArguments() {
        return array(
                     'page'    => false, // the page to include
                     'vars'    => false, // TODO: get rid of this, all remaining args should be vars
                     'rev'     => false, // the revision (defaults to most recent)
                     'version' => false, // same as "rev"
                     'section' => false, // just include a named section
                     'sectionhead' => false // when including a named section show the heading
                     );
    }
    function allow_undeclared_arg($name, $value) {
            // either just allow it or you can store it here away also.
            $this->vars[$name] = $value;
            return $name != 'action';
    }

    // TODO: check if page can really be pulled from the args, or if it is just the basepage.
    function getWikiPageLinks($argstr, $basepage) {
        $args = $this->getArgs($argstr);
        $page = @$args['page'];
        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
        }
        if (!$page or !$page->name)
            return false;
        return array(array('linkto' => $page->name, 'relation' => 0));
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $this->vars = array();
        $args = $this->getArgs($argstr, $request);
        $vars = $args['vars'] ? $args['vars'] : $this->vars;
        $page = $args['page'];

        if ($args['version'] && $args['rev']) {
            return $this->error(_("Choose only one of 'version' or 'rev' parameters."));
        } elseif ($args['version']) {
            $args['rev'] = $args['version'];
        }

        if ($page) {
            // Expand relative page names.
            $page = new WikiPageName($page, $basepage);
            $page = $page->name;
        }
        if (!$page) {
            return $this->error(_("No page specified."));
        }

        // If "Template:$page" exists, use it
        // else if "Template/$page" exists, use it
        // else use "$page"
        if ($dbi->isWikiPage("Template:" . $page)) {
            $page = "Template:" . $page;
        } elseif ($dbi->isWikiPage("Template/" . $page)) {
            $page = "Template/" . $page;
        }

        // Protect from recursive inclusion. A page can include itself once
        static $included_pages = array();
        if (in_array($page, $included_pages)) {
            return $this->error(sprintf(_("Recursive inclusion of page %s"),
                                        $page));
        }

        // Check if page exists
        if (!($dbi->isWikiPage($page))) {
            return $this->error(sprintf(_("Page '%s' does not exist."), $page));
        }

        // Check if user is allowed to get the Page.
        if (!mayAccessPage ('view', $page)) {
                return $this->error(sprintf(_("Illegal inclusion of page %s: no read access."),
                                        $page));
        }

        $p = $dbi->getPage($page);
        if ($args['rev']) {
            $r = $p->getRevision($args['rev']);
            if ((!$r) || ($r->hasDefaultContents())) {
                return $this->error(sprintf(_("%s: no such revision %d."),
                                            $page, $args['rev']));
            }
        } else {
            $r = $p->getCurrentRevision();
        }
        $initial_content = $r->getPackedContent();

        $content = $r->getContent();
        // follow redirects
        if ((preg_match('/<'.'\?plugin\s+RedirectTo\s+page=(\S+)\s*\?'.'>/', implode("\n", $content), $m))
          or (preg_match('/<'.'\?plugin\s+RedirectTo\s+page=(.*?)\s*\?'.'>/', implode("\n", $content), $m))
          or (preg_match('/<<\s*RedirectTo\s+page=(\S+)\s*>>/', implode("\n", $content), $m))
          or (preg_match('/<<\s*RedirectTo\s+page="(.*?)"\s*>>/', implode("\n", $content), $m)))
        {
            // Strip quotes (simple or double) from page name if any
            if ((string_starts_with($m[1], "'"))
              or (string_starts_with($m[1], "\""))) {
                $m[1] = substr($m[1], 1, -1);
            }
            // trap recursive redirects
            if (in_array($m[1], $included_pages)) {
                return $this->error(sprintf(_("Recursive inclusion of page %s ignored"),
                                                $page.' => '.$m[1]));
            }
            $page = $m[1];
            $p = $dbi->getPage($page);
            $r = $p->getCurrentRevision();
            $initial_content = $r->getPackedContent();
        }

        if ($args['section']) {
            $c = explode("\n", $initial_content);
            $c = extractSection($args['section'], $c, $page, $quiet, $args['sectionhead']);
            $initial_content = implode("\n", $c);
        }
        // exclude from expansion
        if (preg_match('/<noinclude>.+<\/noinclude>/s', $initial_content)) {
            $initial_content = preg_replace("/<noinclude>.+?<\/noinclude>/s", "",
                                            $initial_content);
        }
        // only in expansion
        $initial_content = preg_replace("/<includeonly>(.+)<\/includeonly>/s", "\\1",
                                        $initial_content);
        $this->doVariableExpansion($initial_content, $vars, $basepage, $request);

        array_push($included_pages, $page);

        // If content is single-line, call TransformInline, else call TransformText
        $initial_content = trim($initial_content, "\n");
        if (preg_match("/\n/", $initial_content)) {
            include_once('lib/BlockParser.php');
            $content = TransformText($initial_content, $r->get('markup'), $page);
        } else {
            include_once('lib/InlineParser.php');
            $content = TransformInline($initial_content, $r->get('markup'), $page);
        }

        return $content;
    }

    /**
     * Expand template variables. Used by the TemplatePlugin and the CreatePagePlugin
     */
    function doVariableExpansion(&$content, $vars, $basepage, &$request) {
        if (preg_match('/%%\w+%%/', $content)) // need variable expansion
        {
            $dbi =& $request->_dbi;
            $var = array();
            if (is_string($vars) and !empty($vars)) {
                foreach (explode("&", $vars) as $pair) {
                    list($key,$val) = explode("=", $pair);
                    $var[$key] = $val;
                }
            } elseif (is_array($vars)) {
                $var =& $vars;
            }
            $thispage = $dbi->getPage($basepage);
            // pagename and userid are not overridable
            $var['PAGENAME'] = $thispage->getName();
            if (preg_match('/%%USERID%%/', $content))
                $var['USERID'] = $request->_user->getId();
            if (empty($var['MTIME']) and preg_match('/%%MTIME%%/', $content)) {
                $thisrev  = $thispage->getCurrentRevision(false);
                $var['MTIME'] = $GLOBALS['WikiTheme']->formatDateTime($thisrev->get('mtime'));
            }
            if (empty($var['CTIME']) and preg_match('/%%CTIME%%/', $content)) {
                if ($first = $thispage->getRevision(1,false))
                    $var['CTIME'] = $GLOBALS['WikiTheme']->formatDateTime($first->get('mtime'));
            }
            if (empty($var['AUTHOR']) and preg_match('/%%AUTHOR%%/', $content))
                $var['AUTHOR'] = $thispage->getAuthor();
            if (empty($var['OWNER']) and preg_match('/%%OWNER%%/', $content))
                $var['OWNER'] = $thispage->getOwner();
            if (empty($var['CREATOR']) and preg_match('/%%CREATOR%%/', $content))
                $var['CREATOR'] = $thispage->getCreator();
            foreach (array("SERVER_URL", "DATA_PATH", "SCRIPT_NAME", "PHPWIKI_BASE_URL") as $c) {
                // constants are not overridable
                if (preg_match('/%%'.$c.'%%/', $content))
                    $var[$c] = constant($c);
            }
            if (preg_match('/%%BASE_URL%%/', $content))
                $var['BASE_URL'] = PHPWIKI_BASE_URL;

            foreach ($var as $key => $val) {
                // We have to decode the double quotes that have been encoded
                // in inline or block parser.
                $content = str_replace("%%".$key."%%", htmlspecialchars_decode($val), $content);
            }
        }
        return $content;
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
