<?php // -*-php-*-
// $Id: FrameInclude.php 8071 2011-05-18 14:56:14Z vargenau $
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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * FrameInclude:  Displays a url or page in a seperate frame inside our body.
 *
 * Usage:
 *  <<FrameInclude src=http://www.internet-technology.de/fourwins_de.htm >>
 *  <<FrameInclude page=OtherPage >>
 *  at the VERY BEGINNING in the content!
 *
 * Author:  Reini Urban <rurban@x-ray.at>, rewrite by Jeff Dairiki <dairiki@dairiki.org>
 *
 * KNOWN ISSUES:
 *
 * This is a dirty hack into the whole system. To display the page as
 * frameset we:
 *
 *  1. Discard any output buffered so far.
 *  2. Recursively call displayPage with magic arguments to generate
 *     the frameset (or individual frame contents.)
 *  3. Exit early.  (So this plugin is usually a no-return.)
 *
 *  In any cases we can now serve only specific templates with the new
 *  frame argument. The whole page is now ?frame=html (before it was
 *  named "top") For the Sidebar theme (or derived from it) we provide
 *  a left frame also, otherwise only top, content and bottom.
 */
class WikiPlugin_FrameInclude
extends WikiPlugin
{
    function getName() {
        return _("FrameInclude");
    }

    function getDescription() {
        return _("Displays a url in a seperate frame inside our body. Only one frame allowed.");
    }

    function getDefaultArguments() {
        return array( 'src'         => false,       // the src url to include
                      'page'        => false,
                      'name'        => 'content',   // name of our frame
                      'title'       => false,
                      'rows'        => '18%,*,15%', // names: top, $name, bottom
                      'cols'        => '20%,*',     // names: left, $name
                                                    // only useful on WikiTheme "Sidebar"
                      'frameborder' => 1,
                      'marginwidth'  => false,
                      'marginheight' => false,
                      'noresize'    => false,
                      'scrolling'   => 'auto',  // '[ yes | no | auto ]'
                    );
    }

    function run($dbi, $argstr, &$request, $basepage) {

        $args = ($this->getArgs($argstr, $request));
        extract($args);

        if ($request->getArg('action') != 'browse') {
            return $this->disabled(_("Plugin not run: not in browse mode"));
        }
        if (! $request->isGetOrHead()) {
            return $this->disabled("(method != 'GET')");
        }

        if (!$src and $page) {
            if ($page == $request->get('pagename')) {
                return $this->error(sprintf(_("Recursive inclusion of page %s"),
                                            $page));
            }
            $src = WikiURL($page);
        }
        if (!$src) {
            return $this->error(sprintf(_("%s or %s parameter missing"),
                                        'src', 'page'));
        }

        // FIXME: How to normalize url's to compare against recursion?
        if ($src == $request->getURLtoSelf() ) {
            return $this->error(sprintf(_("Recursive inclusion of url %s"),
                                        $src));
        }

        static $noframes = false;
        if ($noframes) {
            // Content for noframes version of page.
            return HTML::p(fmt("See %s",
                               HTML::a(array('href' => $src), $src)));
        }
        $noframes = true;

        if (($which = $request->getArg('frame'))) {
            // Generate specialized frame output (header, footer, etc...)
            $request->discardOutput();
            displayPage($request, new Template("frame-$which", $request));
            $request->finish(); //noreturn
        }

        // Generate the outer frameset
        $frame = HTML::frame(array('name' => $name,
                                   'src' => $src,
                                   'title' => $title,
                                   'frameborder' => (int)$frameborder,
                                   'scrolling' => (string)$scrolling,
                                   'noresize' => (bool)$noresize,
                                   ));

        if ($marginwidth)
            $frame->setArg('marginwidth', $marginwidth);
        if ($marginheight)
            $frame->setArg('marginheight', $marginheight);

        $tokens = array('CONTENT_FRAME' => $frame,
                        'ROWS' => $rows,
                        'COLS' => $cols,
                        'FRAMEARGS' => sprintf('frameborder="%d"', $frameborder),
                        );

        // Produce the frameset.
        $request->discardOutput();
        displayPage($request, new Template('frameset', $request, $tokens));
        $request->finish(); //noreturn
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
