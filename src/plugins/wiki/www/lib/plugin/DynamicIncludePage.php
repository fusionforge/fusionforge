<?php

/*
 * Copyright 2009 $ThePhpWikiProgrammingTeam
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
 * DynamicIncludePage - Include wikipage asynchronously. Icon to show/hide.
 * Usage:   <<DynamicIncludePage page=OtherPage state=true ...>>
 * Author:  Reini Urban
 */

require_once 'lib/plugin/IncludePage.php';

class WikiPlugin_DynamicIncludePage
    extends WikiPlugin_IncludePage
{
    function getDescription()
    {
        return _("Dynamically include the content from another wiki page.");
    }

    function getDefaultArguments()
    {
        return array_merge
        (WikiPlugin_IncludePage::getDefaultArguments(),
            array(
                'state' => false, // initial state: false <=> [+], true <=> [-]
            ));
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        global $WikiTheme;
        $args = $this->getArgs($argstr, $request, false);
        $page =& $args['page'];
        if (ENABLE_AJAX) {
            if ($args['state'])
                $html = WikiPlugin_IncludePage::run($dbi, $argstr, $request, $basepage);
            else
                $html = HTML(HTML::p(array('class' => 'transclusion-title'),
                        fmt(" %s :", WikiLink($page))),
                    HTML::div(array('class' => 'transclusion'), ''));
            $ajaxuri = WikiURL($page, array('format' => 'xml'));
        } else {
            $html = WikiPlugin_IncludePage::run($dbi, $argstr, $request, $basepage);
        }
        $body = $html->_content[1];
        $id = 'DynInc-' . MangleXmlIdentifier($page);
        $body->setAttr('id', $id . '-body');
        $png = $WikiTheme->_findData('images/folderArrow' .
            ($args['state'] ? 'Open' : 'Closed') .
            '.png');
        $icon = HTML::img(array('id' => $id . '-img',
            'src' => $png,
            'onclick' => ENABLE_AJAX
                ? "showHideAsync('" . $ajaxuri . "','$id')"
                : "showHideFolder('$id')",
            'alt' => _("Click to hide/show"),
            'title' => _("Click to hide/show")));
        $header = HTML::p(array('class' => 'transclusion-title',
                'style' => "text-decoration: none;"),
            $icon,
            fmt(" %s :", WikiLink($page)));
        if ($args['state']) { // show base
            $body->setAttr('style', 'display:block');
            return HTML($header, $body);
        } else { // do not show base
            $body->setAttr('style', 'display:none');
            if (ENABLE_AJAX)
                return HTML($header, $body); // async (load in background and insert)
            else
                return HTML($header, $body); // sync (load but display:none)
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
