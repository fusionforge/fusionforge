<?php
/**
 * Copyright © 2004 $ThePhpWikiProgrammingTeam
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
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 *
 */

/**
 * @author: Charles Corrigan
 */

class WikiPlugin_DebugGroupInfo
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Show Group Information.");
    }

    function getDefaultArguments()
    {
        return array();
    }

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
        extract($args);

        $output = HTML();

        $group = WikiGroup::getGroup();
        $allGroups = $group->getAllGroupsIn();

        foreach ($allGroups as $g) {
            $members = $group->getMembersOf($g);
            $output->pushContent(HTML::h2($g . " - members: " .
                    sizeof($members) . " - isMember: " . ($group->isMember($g) ? "yes" : "no")
            ));
            $list = HTML::ul();
            foreach ($members as $m) {
                $list->pushContent(HTML::li($m));
            }
            $output->pushContent($list);
        }
        $output->pushContent(HTML::p("--- the end ---"));

        return $output;
    }
}
