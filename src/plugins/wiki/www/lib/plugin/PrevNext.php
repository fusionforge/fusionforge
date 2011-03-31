<?php // -*-php-*-
// $Id: PrevNext.php 7955 2011-03-03 16:41:35Z vargenau $
/**
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
 * Copyright 2008 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * Usage: <<PrevNext prev=PrevLink next=NextLink >>
 * See also PageGroup which automatically tries to extract the various links
 *
 */
class WikiPlugin_PrevNext
extends WikiPlugin
{
    function getName() {
        return _("PrevNext");
    }

    function getDescription() {
        return sprintf(_("Easy navigation buttons for %s"),'[pagename]');
    }

    function getDefaultArguments() {
        return array(
                     'prev'    => '',
                     'next'    => '',
                     'contents' => '',
                     'index'   => '',
                     'up'      => '',
                     'first'   => '',
                     'last'    => '',
                     'order'   => '',
                     'style'   => 'button', // or 'text'
                     'align'   => 'left', // or 'right', or 'center'
                     'class'   => 'wikiaction'
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {

        $args = $this->getArgs($argstr, $request);
        extract($args);
        $directions = array ('first'    => _("First"),
                             'prev'     => _("Previous"),
                             'next'     => _("Next"),
                             'last'     => _("Last"),
                             'up'       => _("Up"),
                             'contents'  => _("Contents"),
                             'index'    => _("Index")
                             );
        if ($order) { // reorder the buttons: comma-delimited
            $new_directions = array();
            foreach (explode(',', $order) as $o) {
                $new_directions[$o] = $directions[$o];
            }
            $directions = $new_directions;
            unset ($new_directions); // free memory
        }

        global $WikiTheme;
        $sep = $WikiTheme->getButtonSeparator();
        if ($align == 'center') {
            $tr = HTML::tr();
            $links = HTML::table(array('cellpadding' => 0, 'cellspacing' => 0, 'width' => '100%'), $tr);
        } else if ($align == 'right') {
            $td = HTML::td(array('align' => $align));
            $links = HTML::table(array('cellpadding' => 0, 'cellspacing' => 0, 'width' => '100%'), HTML::tr($td));
        } else {
            $links = HTML();
        }

        if ($style == 'text') {
            if (!$sep) {
                $sep = " | "; // force some kind of separator
            }
            if ($align == 'center') {
                $tr->pushContent(HTML::td(array('align' => $align), " [ "));
            } else if ($align == 'right') {
                $td->pushcontent(" [ ");
            } else {
                $links->pushcontent(" [ ");
            }
        }
        $last_is_text = false;
        $this_is_first = true;
        foreach ($directions as $dir => $label) {
            // if ($last_is_text) $links->pushContent($sep);
            if (!empty($args[$dir])) {
                $url = $args[$dir];
                if ($style == 'button') {
                    // localized version: _("Previous").gif
                    if ($imgurl = $WikiTheme->getButtonURL($label)) {
                        if ($last_is_text) {
                            if ($align == 'center') {
                                $tr->pushContent(HTML::td(array('align' => $align), $sep));
                            } else if ($align == 'right') {
                                $td->pushcontent($sep);
                            } else {
                                $links->pushcontent($sep);
                            }
                        }
                        if ($align == 'center') {
                            $tr->pushContent(HTML::td(array('align' => $align), new ImageButton($label, $url, false, $imgurl)));
                        } else if ($align == 'right') {
                            $td->pushContent(new ImageButton($label, $url, false, $imgurl));
                        } else {
                            $links->pushcontent(new ImageButton($label, $url, false, $imgurl));
                        }
                        $last_is_text = false;
                        // generic version: prev.gif
                    } elseif ($imgurl = $WikiTheme->getButtonURL($dir)) {
                        if ($last_is_text) {
                            if ($align == 'center') {
                                $tr->pushContent(HTML::td(array('align' => $align), $sep));
                            } else if ($align == 'right') {
                                $td->pushcontent($sep);
                            } else {
                                $links->pushcontent($sep);
                            }
                        }
                        if ($align == 'center') {
                            $tr->pushContent(HTML::td(array('align' => $align), new ImageButton($label, $url, false, $imgurl)));
                        } else if ($align == 'right') {
                            $td->pushContent(new ImageButton($label, $url, false, $imgurl));
                        } else {
                            $links->pushcontent(new ImageButton($label, $url, false, $imgurl));
                        }
                        $last_is_text = false;
                    } else { // text only
                        if (! $this_is_first) {
                            if ($align == 'center') {
                                $tr->pushContent(HTML::td(array('align' => $align), $sep));
                            } else if ($align == 'right') {
                                $td->pushcontent($sep);
                            } else {
                                $links->pushcontent($sep);
                            }
                        }
                        if ($align == 'center') {
                            $tr->pushContent(HTML::td(array('align' => $align), new Button($label, $url, $class)));
                        } else if ($align == 'right') {
                            $td->pushContent(new Button($label, $url, $class));
                        } else {
                            $links->pushcontent(new Button($label, $url, $class));
                        }
                        $last_is_text = true;
                    }
                } else {
                    if (! $this_is_first) {
                        if ($align == 'center') {
                            $tr->pushContent(HTML::td(array('align' => $align), $sep));
                        } else if ($align == 'right') {
                            $td->pushcontent($sep);
                        } else {
                            $links->pushcontent($sep);
                        }
                    }
                    if ($align == 'center') {
                        $tr->pushContent(HTML::td(array('align' => $align), new Button($label, $url, $class)));
                    } else if ($align == 'right') {
                        $td->pushContent(new Button($label, $url, $class));
                    } else {
                        $links->pushcontent(new Button($label, $url, $class));
                    }
                    $last_is_text = true;
                }
                $this_is_first = false;
            }
        }
        if ($style == 'text') {
            if ($align == 'center') {
                $tr->pushContent(HTML::td(array('align' => $align), " ] "));
            } else if ($align == 'right') {
                $td->pushcontent(" ] ");
            } else {
                $links->pushcontent(" ] ");
            }
        }
        return $links;
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
