<?php

/*
 * Copyright 2007 Reini Urban
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

/* Usage:
 *   <<PopularTags>>
 */

require_once 'lib/PageList.php';

class WikiPlugin_PopularTags
    extends WikiPlugin
{
    // get list of categories sorted by number of backlinks
    private function cmp_by_count($a, $b)
    {
        if ($a['count'] == $b['count']) return 0;
        return $a['count'] < $b['count'] ? 1 : -1;
    }

    function getDescription()
    {
        return _("List the most popular tags.");
    }

    function getDefaultArguments()
    {
        return array('pagename' => '[pagename]',
            'limit' => 10,
            'mincount' => 5,
            'noheader' => 0,
        );
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        $args = $this->getArgs($argstr, $request);
        extract($args);

        $maincat = $dbi->getPage(_("CategoryCategory"));
        $bi = $maincat->getBackLinks(false);
        $bl = array();
        while ($b = $bi->next()) {
            $name = $b->getName();
            if (preg_match("/^" . _("Template") . "/", $name)) continue;
            $pages = $b->getBackLinks(false);
            $bl[] = array('name' => $name,
                'count' => $pages->count());
        }

        usort($bl, array($this, 'cmp_by_count'));
        $html = HTML::ul();
        $i = 0;
        foreach ($bl as $b) {
            $i++;
            $name = $b['name'];
            $count = $b['count'];
            if ($count < $mincount) break;
            if ($i > $limit) break;
            $wo = preg_replace("/^(" . _("Category") . "|"
                . _("Topic") . ")/", "", $name);
            $wo = HTML(HTML::span($wo), HTML::raw("&nbsp;"), HTML::small("(" . $count . ")"));
            $link = WikiLink($name, 'auto', $wo);
            $html->pushContent(HTML::li($link));
        }
        return $html;
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
