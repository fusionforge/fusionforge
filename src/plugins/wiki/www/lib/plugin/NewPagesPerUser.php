<?php // -*-php-*-
// $Id: NewPagesPerUser.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright (C) 2007 AVL
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
 * List all new pages per month per user.
 * March 2007
 *   BERTUZZI 20
 *   URBANR   15
 *   ...
 */

class WikiPlugin_NewPagesPerUser
extends WikiPlugin
{
    function getName () {
        return _("NewPagesPerUser");
    }

    function getDescription () {
        return _("List all new pages per month per user");
    }

    function getDefaultArguments() {
        return array('userid'   => '',
                     'month'    => 0,
                     'since'    => 0,
                     'until'    => 0,
                     'comments' => 0,
                     'links'    => 1,
                     'debug'    => 0,
                   );
    }

    function run($dbi, $argstr, &$request, $basepage) {
            global $WikiTheme;
            $args = $this->getArgs($argstr, $request);
        extract($args);
        if ($since)
            $since = strtotime($since);
        if ($month) {
            $since = strtotime($month);
            $since = mktime(0,0,0,date("m",$since),1,date("Y",$since));
            $until = mktime(23,59,59,date("m",$since)+1,0,date("Y",$since));
        } else
            $until = 0;

        $iter = $dbi->getAllPages(false,'-mtime');
        $pages = array();

        while ($page = $iter->next()) {
            $pagename = $page->getName();
            if (!$page->exists()) continue;
            $rev = $page->getRevision(1, false);
            $date = $rev->get('mtime');
            //$author = $rev->get('author_id');
            $author = $page->getOwner();
            if (defined('DEBUG') && DEBUG && $debug) {
                echo "<i>$pagename, ",strftime("%Y-%m-%d %h:%m:%s", $date),", $author</i><br />\n";
            }
            if ($userid and (!preg_match("/".$userid."/", $author))) continue;
            if ($since and $date < $since) continue;
            if ($until and $date > $until) continue;
            if (!$comments and preg_match("/\/Comment/", $pagename)) continue;
            $monthnum = strftime("%Y%m", $date);
            if (!isset($pages[$monthnum]))
                $pages[$monthnum] = array('author' => array(),
                                          'month' => strftime("%B, %Y", $date));
            if (!isset($pages[$monthnum]['author'][$author]))
                $pages[$monthnum]['author'][$author] = array('count' => 0,
                                                             'pages' => array());
            $pages[$monthnum]['author'][$author]['count']++;
            $pages[$monthnum]['author'][$author]['pages'][] = $pagename;
        }
        $iter->free();
        $html = HTML::table(HTML::col(array('span' => 2,'align'=> 'left')));
        $nbsp = HTML::raw('&nbsp;');
        krsort($pages);
        foreach ($pages as $monthname => $parr) {
           $html->pushContent(HTML::tr(HTML::td(array('colspan' => 2),
                                                HTML::strong($parr['month']))));
           uasort($parr['author'], 'cmp_by_count');
           foreach ($parr['author'] as $user => $authorarr) {
               $count = $authorarr['count'];
               $id = preg_replace("/ /","_",'pages-'.$monthname.'-'.$user);
               $html->pushContent
                   (HTML::tr(HTML::td($nbsp,$nbsp,
                                      HTML::img(array('id'  => "$id-img",
                                                      'src' => $WikiTheme->_findData("images/folderArrowClosed.png"),
                                                      'onclick'=> "showHideFolder('$id')",
                                                       'alt'  => _("Click to hide/show"),
                                                       'title'  => _("Click to hide/show"))),
                                      $nbsp,
                                      $user),
                             HTML::td($count)
                             ));
               if ($links) {
                   $pagelist = HTML();
                   foreach ($authorarr['pages'] as $p)
                       $pagelist->pushContent(WikiLink($p),', ');
               } else {
                   $pagelist = join(', ',$authorarr['pages']);
               }
               $html->pushContent
                   (HTML::tr(array('id' => $id.'-body',
                                   'style'=>'display:none; background-color: #eee;'),
                             HTML::td(array('colspan' => 2,
                                            'style' => 'font-size:smaller'),
                                      $pagelist
                                      )));
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
