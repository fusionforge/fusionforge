<?php // -*-php-*-
// rcs_id('$Id: DeadEndPages.php 7417 2010-05-19 12:57:42Z vargenau $');
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
 * Alias for OrphanedPages. Idea and name from Mediawiki.
 **/

require_once('lib/plugin/OrphanedPages.php');

class WikiPlugin_DeadEndPages
extends WikiPlugin_OrphanedPages
{
    function getName () {
        return _("DeadEndPages");
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
