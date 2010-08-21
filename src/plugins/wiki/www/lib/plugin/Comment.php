<?php // -*-php-*-
// rcs_id('$Id: Comment.php 7638 2010-08-11 11:58:40Z vargenau $');

/*
 * Copyright (C) 2003 Martin Geisler
 * Copyright (C) 2003-2004 $ThePhpWikiProgrammingTeam
 * Copyright (C) 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * A WikiPlugin for putting comments in WikiPages
 *
 * Usage:
 * <<Comment
 *
 * == My Secret Text
 *
 * This is some WikiText that won't show up on the page.
 *
 * >>
 */

class WikiPlugin_Comment
extends WikiPlugin
{
    function getName() {
        return _("Comment");
    }

    function getDescription() {
        return _("Embed hidden comments in WikiPages.");
    }

    // No arguments here.
    function getDefaultArguments() {
        return array();
    }

    function run($dbi, $argstr, &$request, $basepage) {
        return HTML::raw('');
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
