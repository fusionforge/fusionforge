<?php // -*-php-*-
// $Id: ListRelations.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2006 Reini Urban
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
 * Display the list of all relations and optionally attributes in the wiki.
 *
 * @author: Reini Urban
 */
class WikiPlugin_ListRelations
extends WikiPlugin
{
    function getName() {
        return _("ListRelations");
    }
    function getDescription() {
        return _("Display the list of all defined relations and optionnally attributes in this entire wiki");
    }
    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(), // paging and more.
             array(
                   'mode' => "relations" // or "attributes" or "all"
                   ));
    }
    function run ($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        $pagelist = new PageList($info, $exclude, $args);
        // should attributes be listed as pagename here?
        $pagelist->addPageList($dbi->listRelations($mode == 'all', $mode == 'attributes', !empty($sortby)));
        return $pagelist;
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
