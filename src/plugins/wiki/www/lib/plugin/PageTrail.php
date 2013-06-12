<?php

/**
 * Copyright 1999,2000,2001,2002,2005 $ThePhpWikiProgrammingTeam
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
 * A simple PageTrail WikiPlugin.
 * Put this at the begin/end of each page to store the trail,
 * or better in a template (body or bottom) to support it for all pages.
 * But Cache should be turned off then.
 *
 * Usage:
 * <<PageTrail>>
 * <<PageTrail numberlinks=5>>
 * <<PageTrail invisible=1>>
 */

if (!defined('PAGETRAIL_ARROW'))
    define('PAGETRAIL_ARROW', " => ");

class WikiPlugin_PageTrail
    extends WikiPlugin
{
    public $def_numberlinks = 5;

    function getDescription()
    {
        return _("Display PageTrail.");
    }

    // default values
    function getDefaultArguments()
    {
        return array('numberlinks' => $this->def_numberlinks,
            'invisible' => false,
            'duplicates' => false,
        );
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        extract($this->getArgs($argstr, $request));

        if ($numberlinks > 10 || $numberlinks < 0) {
            $numberlinks = $this->def_numberlinks;
        }

        // Get name of the current page we are on
        $thispage = $request->getArg('pagename');
        $Pages = $request->session->get("PageTrail");
        if (!is_array($Pages)) $Pages = array();

        if (!isset($Pages[0]) or ($duplicates || ($thispage != $Pages[0]))) {
            array_unshift($Pages, $thispage);
            $request->session->set("PageTrail", $Pages);
        }

        $numberlinks = min(count($Pages), $numberlinks);
        if (!$invisible and $numberlinks) {
            $html = HTML::div(array('class' => 'pagetrail'));
            $html->pushContent(WikiLink($Pages[$numberlinks - 1], 'auto'));
            for ($i = $numberlinks - 2; $i >= 0; $i--) {
                if (!empty($Pages[$i]))
                    $html->pushContent(PAGETRAIL_ARROW,
                        WikiLink($Pages[$i], 'auto'));
            }
            return $html;
        } else
            return HTML();
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
