<?php // -*-php-*-
rcs_id('$Id: BoxRight.php 6264 2008-09-16 18:39:14Z vargenau $');
/**
 Copyright 2006 $ThePhpWikiProgrammingTeam

 This file is part of PhpWiki.

 PhpWiki is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 PhpWiki is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with PhpWiki; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/**
 * A simple plugin for <div class="boxright"> with wikimarkup
 */
class WikiPlugin_BoxRight
extends WikiPlugin
{
    function getName () {
        return "BoxRight";
    }

    function getDescription () {
        return _("A simple plugin for <div class=boxright> with wikimarkup");
    }

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6264 $");
    }

    function getDefaultArguments() {
        return array();
    }

    function managesValidators() {
        // The plugin output will only change if the plugin
        // invocation (page text) changes --- so the necessary
        // validators have already been handled by displayPage.
        return true;
    }
    
    function run($dbi, $argstr, &$request, $basepage) {
        if (!$basepage) {
            return $this->error("$basepage unset?");
        }
        include_once("lib/BlockParser.php");
	$page = $request->getPage($basepage);
        return HTML::div(array('class'=>'boxright'), TransformText($argstr));
    }

}

// For emacs users
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
