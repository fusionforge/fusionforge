<?php // -*-php-*-
// $Id: GooglePlugin.php 7955 2011-03-03 16:41:35Z vargenau $
/**
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

require_once("lib/Google.php");

/**
 * This module is a wrapper for the Google Web APIs. It allows you to do Google searches,
 * retrieve pages from the Google cache, and ask Google for spelling suggestions.
 *
 * Note: You must first obtain a license key at http://www.google.com/apis/
 * Max 1000 queries per day.
 *
 * Other possible sample usages:
 *   Auto-monitor the web for new information on a subject
 *   Glean market research insights and trends over time
 *   Invent a catchy online game
 *   Create a novel UI for searching
 *   Add Google's spell-checking to an application
 */
class WikiPlugin_GooglePlugin
extends WikiPlugin
{
    function getName () {
        return _("GooglePlugin");
    }

    function getDescription () {
        return _("Make use of the Google API");
    }

    function getDefaultArguments() {
        return array('q'          => '',
                     'mode'       => 'search', // or 'cache' or 'spell'
                     'startIndex' => 1,
                     'maxResults' => 10, // fixed to 10 for now by google
                     'formsize'   => 30,
                     // 'language' => `??
                     //'license_key'  => false,
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        //        if (empty($args['s']))
        //    return '';
        $html = HTML();
        extract($args);
        // prevent from dump
        if ($q and $request->isPost()) {
            require_once("lib/Google.php");
            $google = new Google();
            if (!$google) return '';
            switch ($mode) {
                case 'search': $result = $google->doGoogleSearch($q); break;
                case 'cache':  $result = $google->doGetCachedPage($q); break;
                case 'spell':  $result = $google->doSpellingSuggestion($q); break;
                default:
                        trigger_error("Invalid mode");
            }
            if (isa($result,'HTML'))
                $html->pushContent($result);
            if (isa($result,'GoogleSearchResults')) {
                //TODO: result template
                if (!empty($result->resultElements)) {
                    $list = HTML::ol();
                    foreach ($result->resultElements as $res) {
                            $li = HTML::li(LinkURL($res['URL'],$res['directoryTitle']),HTML::br(),
                                           $res['directoryTitle'] ? HTML(HTML::raw('&nbsp;&nbsp;'),HTML::em($res['summary']),' -- ',LinkURL($res['URL'])) : '');
                        $list->pushContent($li);
                    }
                    $html->pushContent($list);
                }
                else
                    return _("Nothing found");
            }
            if (is_string($result)) {
                // cache content also?
                $html->pushContent(HTML::blockquote(HTML::raw($result)));
            }
        }
        if ($formsize < 1)  $formsize = 30;
        // todo: template
        $form = HTML::form(array('action' => $request->getPostURL(),
                                 'method' => 'post',
                                 //'class'  => 'class', //fixme
                                 'accept-charset' => $GLOBALS['charset']),
                           HiddenInputs(array('pagename' => $basepage,
                                              'mode' => $mode)));
        $form->pushContent(HTML::input(array('type' => 'text',
                                             'value' => $q,
                                             'name'  => 'q',
                                             'size'  => $formsize)));
        $form->pushContent(HTML::input(array('type' => 'submit',
                                             'class' => 'button',
                                             'value' => gettext($mode)
                                             )));
        return HTML($html,$form);
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
