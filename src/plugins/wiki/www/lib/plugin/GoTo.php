<?php // -*-php-*-
// rcs_id('$Id: GoTo.php 7447 2010-05-31 11:29:39Z vargenau $');
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 *  Display a form with text entry box and 'Go' button.
 *  The user enters a page name... if it exists, browse
 *  that page; if not, edit (create) that page.
 *  Note: pagenames are absolute, not relative to the actual subpage.
 *
 *  Usage: <<GoTo >>
 *  @author: Michael van Dam
 */

class WikiPlugin_GoTo
extends WikiPlugin
{
    function getName () {
        return _("GoTo");
    }

    function getDescription () {
        return _("Go to or create page.");
    }

    function getDefaultArguments() {
        return array('size' => 32);
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $request->setArg('action',false);
        $args = $this->getArgs($argstr, $request);
        extract($args);

        if ($goto = $request->getArg('goto')) {
            // The user has pressed 'Go'; process request
            $request->setArg('goto', false);
            $target = $goto['target'];
            if ($dbi->isWikiPage($target))
                $url = WikiURL($target,0,1);
            else
                $url = WikiURL($target, array('action'=>'edit'),1);

            $request->redirect($url);
            // User should see nothing after redirect
            return '';
        }

        $action = $request->getURLtoSelf();
        $form = HTML::form(array('action'=>$action,
                                 'method'=>'post'
                          ));

        $form->pushContent(HiddenInputs($request->getArgs()));

        $textfield = HTML::input(array('type' => 'text',
                                       'size' => $size,
                                       'name' => 'goto[target]'));

        $button = Button('submit:goto[go]', _("Go"), false);

        $form->pushContent($textfield, $button);

        return $form;

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
