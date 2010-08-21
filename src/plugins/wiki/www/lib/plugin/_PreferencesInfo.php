<?php // -*-php-*-
// rcs_id('$Id: _PreferencesInfo.php 7638 2010-08-11 11:58:40Z vargenau $');
/**
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
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
 * Plugin to display the current preferences without auth check.
 */
class WikiPlugin__PreferencesInfo
extends WikiPlugin
{
    function getName () {
        return _("PreferencesInfo");
    }

    function getDescription () {
        return sprintf(_("Get preferences information for current user %s."),
                       '[userid]');
    }

    function getDefaultArguments() {
        return array('page' => '[pagename]',
                     'userid' => '[userid]');
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        // $user = &$request->getUser();
        return Template('userprefs', $args);
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
