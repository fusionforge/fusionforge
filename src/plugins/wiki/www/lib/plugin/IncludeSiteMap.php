<?php // -*-php-*-
// rcs_id('$Id: IncludeSiteMap.php 7638 2010-08-11 11:58:40Z vargenau $');
/**
 * Copyright 2003,2004 $ThePhpWikiProgrammingTeam
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
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * http://sourceforge.net/tracker/?func=detail&aid=537380&group_id=6121&atid=306121
 *
 * Submitted by: Cuthbert Cat (cuthbertcat)
 * Redesigned by Reini Urban
 *
 * This is a quick mod of BackLinks to do the job recursively. If your
 * site is categorized correctly, and all the categories are listed in
 * CategoryCategory, then a RecBackLinks there will produce one BIG(!)
 * contents page for the entire site.
 * The list is as deep as the recursion level ('reclimit').
 *
 * 'includepages': passed verbatim to the IncludePage plugin. Default: "words=50"
 *                 To disable words=50 use e.g. something like includepages="quiet=0"
 * 'reclimit':     Max Recursion depth. Default: 2
 * 'direction':    Get BackLinks or forward links (links listed on the page)
 * 'firstreversed': If true, get BackLinks for the first page and forward
 *                 links for the rest. Only applicable when direction = 'forward'.
 * 'excludeunknown': If true (default) then exclude any mentioned pages
 *                 which don't exist yet.  Only applicable when direction='forward'.
 */

require_once('lib/PageList.php');
require_once('lib/plugin/SiteMap.php');

class WikiPlugin_IncludeSiteMap
extends WikiPlugin_SiteMap
{
  function getName () {
    return _("IncludeSiteMap");
  }

  function getDescription () {
    return sprintf(_("Include recursively all linked pages starting at %s"),
                   $this->_pagename);
  }

  function getDefaultArguments() {
      return array('exclude'        => '',
                   'include_self'   => 0,
                   'noheader'       => 0,
                   'page'           => '[pagename]',
                   'description'    => $this->getDescription(),
                   'reclimit'       => 2,
                   'info'           => false,
                   'direction'      => 'back',
                   'firstreversed'  => false,
                   'excludeunknown' => true,
                   'includepages'   => 'words=50'
                   );
    }

    function run($dbi, $argstr, &$request, $basepage) {
      return WikiPlugin_SiteMap::run($dbi, $argstr, $request, $basepage);
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
