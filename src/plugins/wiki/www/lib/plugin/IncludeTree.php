<?php

/**
 * Copyright 2003,2004,2009 $ThePhpWikiProgrammingTeam
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
 * Dynamic version of the IncludeSiteMap by Cuthbert Cat (cuthbertcat)
 * with a category filter.
 *
 * Display an initially closed tree of all pages within certain categories. dhtml.
 * On [+] open the subtree, on leaves (how to decide?) transclude parts of the page.
 * Leave detection: more content than just plugins.
 */

require_once 'lib/PageList.php';
require_once 'lib/plugin/SiteMap.php';

class WikiPlugin_IncludeTree
    extends WikiPlugin_SiteMap
{
    function getDescription()
    {
        return _("Display Dynamic Category Tree.");
    }

    function getDefaultArguments()
    {
        return array('exclude' => '',
            'include_self' => 0,
            'noheader' => 0,
            'page' => '[pagename]',
            'description' => $this->getDescription(),
            'reclimit' => 2,
            'info' => false,
            'direction' => 'back',
            'firstreversed' => false,
            'excludeunknown' => true,
            'includepages' => 'words=100',
            'category' => '',
            'dtree' => true,
        );
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        return WikiPlugin_SiteMap::run($dbi, $argstr, $request, $basepage);
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
