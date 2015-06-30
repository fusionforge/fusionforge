<?php

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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once 'lib/PageList.php';

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
    function getDescription()
    {
        return _("List all once authenticated users.");
    }

    function getDefaultArguments()
    {
        return array_merge
        (
            PageList::supportedArgs(),
            array('noheader' => false,
                'include_empty' => true
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

    /**
     * @param WikiDB $dbi
     * @param string $argstr
     * @param WikiRequest $request
     * @param string $basepage
     * @return mixed
     */
    function run($dbi, $argstr, &$request, $basepage)
    {
        $args = $this->getArgs($argstr, $request);

        if (isset($args['limit']) && !is_limit($args['limit'])) {
            return HTML::p(array('class' => "error"),
                           _("Illegal “limit” argument: must be an integer or two integers separated by comma"));
        }

        extract($args);

        $group = $request->getGroup();
        if (method_exists($group, '_allUsers')) {
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
            for ($i = $offset; $i < $offset + $pagesize - 1; $i++) {
                if ($i >= $args['count']) break;
                $pagelist->addPage(trim($allusers[$i]));
            }
        }
        return $pagelist;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
