<?php

/*
 * Copyright (C) 2004 $ThePhpWikiProgrammingTeam
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
 * This plugin allows user comments attached to a page, similar to WikiBlog.
 * Based on WikiBlog, no summary.
 *
 * TODO:
 * For admin user, put checkboxes beside comments to allow for bulk removal.
 *
 * @author: ReiniUrban
 */

include_once 'lib/plugin/WikiBlog.php';

class WikiPlugin_AddComment
    extends WikiPlugin_WikiBlog
{
    function getDescription()
    {
        return sprintf(_("Show and add comments for %s."), '[pagename]');
    }

    // Arguments:
    //
    //  page - page where the comment is attached at (default current page)
    //
    //  order - 'normal'  - place in chronological order
    //        - 'reverse' - place in reverse chronological order
    //
    //  mode - 'show'     - only show old comments
    //         'add'      - only show entry box for new comment
    //         'show,add' - show old comments, then entry box
    //         'add,show' - show entry box followed by list of comments
    //  jshide - boolean  - quick javascript expansion of the comments
    //                      and addcomment box

    function getDefaultArguments()
    {
        return array('pagename' => '[pagename]',
            'order' => 'normal',
            'mode' => 'add,show',
            'jshide' => '0',
            'noheader' => false,
            //'sortby'     => '-pagename' // oldest first. reverse by order=reverse
        );
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        $args = $this->getArgs($argstr, $request);
        if (!$args['pagename']) {
            return $this->error(sprintf(_("A required argument “%s” is missing."), 'pagename'));
        }

        // Get our form args.
        $comment = $request->getArg("comment");
        $request->setArg('comment', false);

        if ($request->isPost() and !empty($comment['addcomment'])) {
            $this->add($request, $comment, 'comment'); // noreturn
        }

        // Now we display previous comments and/or provide entry box
        // for new comments
        $html = HTML();
        if ($args['jshide']) {
            $div = HTML::div(array('id' => 'comments', 'style' => 'display:none;'));
            //$list->setAttr('style','display:none;');
            $div->pushContent(Javascript("
function togglecomments(a) {
  comments=document.getElementById('comments');
  if (comments.style.display=='none') {
    comments.style.display='block';
    a.title='" . _("Click to hide the comments") . "';
  } else {
    comments.style.display='none';
    a.title='" . _("Click to display all comments") . "';
  }
}"));
            $html->pushContent(HTML::h4(HTML::a(array('name' => 'comment-header',
                    'class' => 'wikiaction',
                    'title' => _("Click to display"),
                    'onclick' => "togglecomments(this)"),
                _("Comments"))));
        } else {
            $div = HTML::div(array('id' => 'comments'));
        }
        foreach (explode(',', $args['mode']) as $show) {
            if (!empty($seen[$show]))
                continue;
            $seen[$show] = 1;
            switch ($show) {
                case 'show':
                    $show = $this->showAll($request, $args, 'comment');
                    //if ($args['jshide']) $show->setAttr('style','display:none;');
                    $div->pushContent($show);
                    break;
                case 'add':
                    global $WikiTheme;
                    if (!$WikiTheme->DUMP_MODE) {
                        $add = $this->showForm($request, $args, 'addcomment');
                        //if ($args['jshide']) $add->setAttr('style','display:none;');
                        $div->pushContent($add);
                    }
                    break;
                default:
                    return $this->error(sprintf("Bad mode (“%s”)", $show));
            }
        }
        $html->pushContent($div);
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
