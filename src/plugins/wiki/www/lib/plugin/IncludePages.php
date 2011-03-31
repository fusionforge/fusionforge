<?php // -*-php-*-
// $Id: IncludePages.php 7955 2011-03-03 16:41:35Z vargenau $
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * IncludePages: Include a list of multiple pages, based on IncludePage.
 * usage:   <<IncludePages pages=<!plugin-list BackLinks !> >>
 * author:  ReiniUrban
 */

include_once("lib/plugin/IncludePage.php");

class WikiPlugin_IncludePages
extends WikiPlugin_IncludePage
{
    function getName() {
        return _("IncludePages");
    }

    function getDescription() {
        return _("Include multiple pages.");
    }

    function getDefaultArguments() {
            return array_merge(
                           array( 'pages'   => false,  // the pages to include
                                  'exclude' => false), // the pages to exclude
                           WikiPlugin_IncludePage::getDefaultArguments()
                           );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
            $html = HTML();
        if (empty($args['pages']))
            return $html;
        $include = new WikiPlugin_IncludePage();

        if (is_string($args['exclude']) and !empty($args['exclude'])) {
            $args['exclude'] = explodePageList($args['exclude']);
            $argstr = preg_replace("/exclude=\S*\s/", "", $argstr);
        } elseif (is_array($args['exclude'])) {
            $argstr = preg_replace("/exclude=<\?plugin-list.*?\>/", "", $argstr);
        }
        if (is_string($args['pages']) and !empty($args['pages'])) {
            $args['pages'] = explodePageList($args['pages']);
            $argstr = preg_replace("/pages=\S*\s/", "", $argstr);
        } elseif (is_array($args['pages'])) {
            $argstr = preg_replace("/pages=<\?plugin-list.*?\>/", "", $argstr);
        }

            foreach ($args['pages'] as $page) {
            if (empty($args['exclude']) or !in_array($page, $args['exclude'])) {
                $html = HTML($html, $include->run($dbi, "page='$page' ".$argstr, $request, $basepage));
            }
            }
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
