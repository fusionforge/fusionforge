<?php // -*-php-*-
// $Id: PageInfo.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
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
 * An ActionPage plugin which returns extra information about a page.
 * This plugin just passes a page revision handle to the Template
 * 'info.tmpl', which does all the real work.
 */
class WikiPlugin_PageInfo
extends WikiPlugin
{
    function getName () {
        return _("PageInfo");
    }

    function getDescription () {
        return sprintf(_("Show extra page Info and statistics for %s."),
                       '[pagename]');
    }

    function getDefaultArguments() {
        return array('page' => '[pagename]',
                     'version' => '[version]');
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);

        $pagename = $page;
        $page = $request->getPage();
        $current = $page->getCurrentRevision();

        if ($current->getVersion() < 1)
            return fmt("I'm sorry, there is no such page as %s.",
                       WikiLink($pagename, 'unknown'));

        if (!empty($version)) {
            if (!($revision = $page->getRevision($version)))
                NoSuchRevision($request, $page, $version);
        }
        else {
            $revision = $current;
        }

        $template = new Template('info', $request,
                                 array('revision' => $revision));
        return $template;
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
