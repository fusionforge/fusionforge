<?php // -*-php-*-
// rcs_id('$Id: RedirectTo.php 7653 2010-08-30 10:41:28Z vargenau $');
/*
 * Copyright 2002 $ThePhpWikiProgrammingTeam
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
 * Redirect to another page or external uri. Kind of PageAlias.
 * Usage:
 * <<RedirectTo href="http://www.internet-technology.de/fourwins_de.htm" >>
 *      or  <<RedirectTo page=AnotherPage >>
 *
 * Author:  Reini Urban <rurban@x-ray.at>
 *
 * BUGS/COMMENTS:
 * Todo: fix with USE_PATH_INFO = false
 *
 * This plugin could probably result in a lot of confusion, especially when
 * redirecting to external sites.  (Perhaps it can even be used for dastardly
 * purposes?)  Maybe it should be disabled by default.
 */

class WikiPlugin_RedirectTo
extends WikiPlugin
{
    function getName() {
        return _("RedirectTo");
    }

    function getDescription() {
        return _("Redirects to another URL or page.");
    }

    function getDefaultArguments() {
        return array( 'href' => '',
                      'page' => false,
                      );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = ($this->getArgs($argstr, $request));

        $href = $args['href'];
        $page = $args['page'];
        if ($href) {
            // If URL is urlencoded, decode it.
            if (strpos('%', $href) !== false) {
                $href = urldecode($href);
            }
            $url = strip_tags($href);
            if ($url != $href) { // URL contains tags
                return $this->disabled(_("Illegal characters in external URL."));
            }
            $thispage = $request->getPage();
            if (! $thispage->get('locked')) {
                return $this->disabled(_("Redirect to an external URL is only allowed in locked pages."));
            }
        }
        else if ($page) {
            $url = WikiURL($page,
                           array('redirectfrom' => $request->getArg('pagename')),
                           'abs_path');
        }
        else {
            return $this->error(_("'href' or 'page' parameter missing."));
        }

        if ($page == $request->getArg('pagename')) {
            return $this->error(fmt("Recursive redirect to self: '%s'", $url));
        }

        if ($request->getArg('action') != 'browse')
            return $this->disabled("(action != 'browse')");

        $redirectfrom = $request->getArg('redirectfrom');
        if ($redirectfrom !== false) {
            if ($redirectfrom)
                return $this->disabled(_("Double redirect not allowed."));
            else {
                // Got here by following the "Redirected from ..." link
                // on a browse page.
                return $this->disabled(_("Viewing redirecting page."));
            }
        }

        return $request->redirect($url);
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
