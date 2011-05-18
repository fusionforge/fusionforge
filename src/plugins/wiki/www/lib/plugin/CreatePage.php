<?php // -*-php-*-
// $Id: CreatePage.php 8071 2011-05-18 14:56:14Z vargenau $
/**
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
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * This allows you to create a page getting the new pagename from a
 * forms-based interface, and optionally with the initial content from
 * some template, plus expansion of some variables via %%variable%% statements
 * in the template.
 *
 * Put <?plugin-form CreatePage ?> at some page, browse this page,
 * enter the name of the page to create, then click the button.
 *
 * Usage: <?plugin-form CreatePage template=SomeTemplatePage vars="year=2004&name=None" ?>
 * @authors: Dan Frankowski, Reini Urban
 */

include_once("lib/plugin/Template.php");

class WikiPlugin_CreatePage
extends WikiPlugin_Template
{
    function getName() {
        return _("CreatePage");
    }

    function getDescription() {
        return _("Create a Wiki page by the provided name.");
    }

    function getDefaultArguments() {
        return array('s'            => false,
                     'initial_content' => '',
                     'template'     => false,
                     'vars'         => false,
                     'overwrite'    => false,
                     'verify'       => false, // true or a pagename
                     //'buttontext' => false,
                     //'method'     => 'POST'
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        extract($this->getArgs($argstr, $request));
        // Prevent spaces at the start and end of a page name
        $s = trim($s);
        if (!$s) {
            return $this->error(_("Cannot create page with empty name!"));
        }
        // TODO: javascript warning if "/" or SUBPAGE_SEPARATOR in s
        if ($verify) {
            $head = _("CreatePage failed");
            if ($dbi->isWikiPage($verify)) {
                $msg = _("Do you really want to create the page '%s'?");
            } else {
                $msg = _("Do you really want to create the page '%s'?");
            }
            if (isSubPage($s)) {
                $main = subPageSlice(0);
                if (!$dbi->isWikiPage(subPageSlice(0))) {
                    $msg .= "\n" . _("The new page you want to create will be a subpage.")
                         .  "\n" . _("Subpages cannot be created unless the parent page exists.");
                    return alert($head, $msg);
                } else {
                    $msg .= "\n" . _("The new page you want to create will be a subpage.");
                }
            }
            if (strpos($s, " \/")) {
                $msg .= "\n" . _("Subpages with ending space are not allowed as directory name on Windows.");
                return alert($head, $msg);
            }
        }

        $param = array('action' => 'edit');
        if ($template and $dbi->isWikiPage($template)) {
            $param['template'] = $template;
        } elseif (!empty($initial_content)) {
            // Warning! Potential URI overflow here on the GET redirect. Better use template.
            $param['initial_content'] = $initial_content;
        }
        // If the initial_content is too large, pre-save the content in the page
        // and redirect without that argument.
        // URI length limit:
        //   http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.2.1
        $url = WikiURL($s, $param, 'absurl');
        // FIXME: expand vars in templates here.
        if (strlen($url) > 255
            or ($param['template'])
            or preg_match('/%%\w+%%/', $initial_content)) // need variable expansion
        {
            unset($param['initial_content']);
            $url = WikiURL($s, $param, 'absurl');
            $page = $dbi->getPage($s);
            $current = $page->getCurrentRevision();
            $version = $current->getVersion();
            // overwrite empty (deleted) pages
            if ($version and !$current->hasDefaultContents() and !$overwrite) {
                return $this->error(fmt("%s already exists", WikiLink($s)));
            } else {
                $user = $request->getUser();
                $meta = array('markup' => 2.0,
                              'author' => $user->getId());
                if (!empty($param['template']) and !$initial_content) {
                    $tmplpage = $dbi->getPage($template);
                    $currenttmpl = $tmplpage->getCurrentRevision();
                    $initial_content = $currenttmpl->getPackedContent();
                    $meta['markup'] = $currenttmpl->_data['markup'];

                    if (preg_match('/<noinclude>.+<\/noinclude>/s', $initial_content)) {
                        $initial_content = preg_replace("/<noinclude>.+?<\/noinclude>/s", "",
                                                        $initial_content);
                    }
                }
                $meta['summary'] = _("Created by CreatePage");
                $content = $this->doVariableExpansion($initial_content, $vars, $s, $request);

                if ($content !== $initial_content) {
                    // need to destroy the template so that editpage doesn't overwrite it.
                    unset($param['template']);
                    $url = WikiURL($s, $param, 'absurl');
                }

                $page->save($content, $version+1, $meta);
            }
        }
        return HTML($request->redirect($url, true));
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
