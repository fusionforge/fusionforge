<?php // -*-php-*-
// rcs_id('$Id: RandomPage.php 7417 2010-05-19 12:57:42Z vargenau $');
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('lib/PageList.php');

/**
 * With 1.3.11 the "pages" argument was renamed to "numpages".
 * action=upgrade should deal with pages containing RandomPage modified earlier than 2005-01-24
 */
class WikiPlugin_RandomPage
extends WikiPlugin
{
    function getName () {
        return _("RandomPage");
    }

    function getDescription () {
        return _("Displays a list of randomly chosen pages or redirects to a random page.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array('numpages'     => 20,     // was pages
                   'pages'        => false, // deprecated
                   'redirect'     => false,
                   'hidename'     => false, // only for numpages=1
                   'exclude'      => $this->default_exclude(),
                   'info'         => ''));
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);

        // fix deprecated arg
        if (is_integer($pages)) {
            $numpages = $pages;
            $pages = false;
        // fix new pages handling in arg preprozessor.
        } elseif (is_array($pages)) {
            $numpages = (int)$pages[0];
            if ($numpages > 0 and !$dbi->isWikiPage($numpages)) $pages = false;
            else $numpages = 1;
        }

        $allpages = $dbi->getAllPages(false, $sortby, $limit, $exclude);
        $pagearray = $allpages->asArray();
        better_srand(); // Start with a good seed.

        if (($numpages == 1) && $pagearray) {
            $page = $pagearray[array_rand($pagearray)];
            $pagename = $page->getName();
            if ($redirect)
                $request->redirect(WikiURL($pagename, false, 'absurl')); // noreturn
            if ($hidename)
                return WikiLink($pagename, false, _("RandomPage"));
            else
                return WikiLink($pagename);
        }

        $numpages = min( max(1, (int) $numpages), 20, count($pagearray));
        $pagelist = new PageList($info, $exclude, $args);
        $shuffle = array_rand($pagearray, $numpages);
        if (is_array($shuffle)) {
            foreach ($shuffle as $i)
                if (isset($pagearray[$i])) $pagelist->addPage($pagearray[$i]);
        } else { // if $numpages = 1
             if (isset($pagearray[$shuffle]))
                 $pagelist->addPage($pagearray[$shuffle]);
        }
        return $pagelist;
    }

    function default_exclude() {
        // Some useful default pages to exclude.
        $default_exclude = 'RandomPage,HomePage,AllPages,RecentChanges,RecentEdits,FullRecentChanges';
        foreach (explode(",", $default_exclude) as $e) {
            $exclude[] = gettext($e);
        }
        return implode(",", $exclude);
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
