<?php // -*-php-*-
// $Id: AllUsers.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2002,2004 $ThePhpWikiProgrammingTeam
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

require_once('lib/PageList.php');

/**
 * Based on AllPages and WikiGroup.
 *
 * We list all users,
 * either homepage users (prefs stored in a page),
 * users with db prefs and
 * externally authenticated users with a db users table, if auth_user_exists is defined.
 */
class WikiPlugin_AllUsers
extends WikiPlugin
{
    function getName () {
        return _("AllUsers");
    }

    function getDescription() {
        return _("List all once authenticated users.");
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array('noheader'      => false,
                   'include_empty' => true,
                   'debug'         => false
                   ));
    }
    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor,markup or all
    // exclude arg allows multiple pagenames exclude=WikiAdmin,.SecretUser
    //
    // include_empty shows also users which stored their preferences,
    // but never saved their homepage
    //
    // sortby: [+|-] pagename|mtime|hits

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);

        extract($args);
        if (defined('DEBUG') && DEBUG && $debug) {
            $timer = new DebugTimer;
        }

        $group = $request->getGroup();
        if (method_exists($group,'_allUsers')) {
            $allusers = $group->_allUsers();
        } else {
            $allusers = array();
        }
        $args['count'] = count($allusers);
        // deleted pages show up as version 0.
        $pagelist = new PageList($info, $exclude, $args);
        if (!$noheader)
            $pagelist->setCaption(_("Authenticated users on this wiki (%d total):"));
        if ($include_empty and empty($info))
            $pagelist->_addColumn('version');
        list($offset, $pagesize) = $pagelist->limit($args['limit']);
        if (!$pagesize) {
            $pagelist->addPageList($allusers);
        } else {
            for ($i=$offset; $i < $offset + $pagesize - 1; $i++) {
                if ($i >= $args['count']) break;
                $pagelist->addPage(trim($allusers[$i]));
            }
        }
        /*
        $page_iter = $dbi->getAllPages($include_empty, $sortby, $limit);
        while ($page = $page_iter->next()) {
            if ($page->isUserPage($include_empty))
                $pagelist->addPage($page);
        }
        */

        if (defined('DEBUG') && DEBUG and $debug) {
            return HTML($pagelist,
                        HTML::p(fmt("Elapsed time: %s s", $timer->getStats())));
        } else {
            return $pagelist;
        }
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
