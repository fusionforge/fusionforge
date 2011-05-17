<?php // -*-php-*-
// rcs_id('$Id: OrphanedPages.php 7417 2010-05-19 12:57:42Z vargenau $');
/**
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
 * A plugin which returns a list of pages which are not linked to by
 * any other page
 *
 * Initial version by Lawrence Akka
 *
 **/
require_once('lib/PageList.php');

class WikiPlugin_OrphanedPages
extends WikiPlugin
{
    function getName () {
        return _("OrphanedPages");
    }

    function getDescription () {
        return _("List pages which are not linked to by any other page.");
    }

    function getDefaultArguments() {
        return array('noheader'      => false,
                     'include_empty' => false,
                     'exclude'       => '',
                     'info'          => '',
                     'sortby'        => false,
                     'limit'         => 0,
                     'paging'        => 'auto',
                     );
    }
    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor,markup or all
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);

        // There's probably a more efficient way to do this (eg a
        // tailored SQL query via the backend, but this does the job

        $allpages_iter = $dbi->getAllPages($include_empty);
        $pages = array();
        while ($page = $allpages_iter->next()) {
            $links_iter = $page->getBackLinks();
            // Test for absence of backlinks. If a page is linked to
            // only by itself, it is still an orphan
            $parent = $links_iter->next();
            if (!$parent               // page has no parents
                or (($parent->getName() == $page->getName())
                    and !$links_iter->next())) // or page has only itself as a parent
            {
                $pages[] = $page;
            }
        }
        $args['count'] = count($pages);
        $pagelist = new PageList($info, $exclude, $args);
        if (!$noheader)
            $pagelist->setCaption(_("Orphaned Pages in this wiki (%d total):"));
        // deleted pages show up as version 0.
        if ($include_empty)
            $pagelist->_addColumn('version');
        list($offset,$pagesize) = $pagelist->limit($args['limit']);
        if (!$pagesize) $pagelist->addPageList($pages);
        else {
            for ($i=$offset; $i < $offset + $pagesize - 1; $i++) {
                    if ($i >= $args['count']) break;
                $pagelist->addPage($pages[$i]);
            }
        }
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
