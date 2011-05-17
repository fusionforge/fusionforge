<?php // -*-php-*-
// rcs_id('$Id: _Retransform.php 7638 2010-08-11 11:58:40Z vargenau $');
/**
 * Copyright 2007 $ThePhpWikiProgrammingTeam
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
 * Only useful for link and parser debugging purposes.
 */
class WikiPlugin__Retransform
extends WikiPlugin
{
    function getName () {
        return _("Retransform CachedMarkup");
    }

    function getDescription () {
        return sprintf(_("Show a markup retransformation of page %s."), '[pagename]');
    }

    function getDefaultArguments() {
        return array('page' => '[pagename]',
                     );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        if (empty($page))
            return '';

        $html = HTML(HTML::h3(fmt("Retransform page '%s'",
                                  $page)));

        // bypass WikiDB and cache, go directly through the backend.
        $backend = &$dbi->_backend;
        //$pagedata = $backend->get_pagedata($page);
        $version = $backend->get_latest_version($page);
        $vdata = $backend->get_versiondata($page, $version, true);

        include_once('lib/PageType.php');
        $formatted = new TransformedText($dbi->getPage($page), $vdata['%content'], $vdata);
        $content =& $formatted->_content;
        $html->pushContent($this->_DebugPrintArray($content));
        $links = $formatted->getWikiPageLinks();
        if (count($links) > 0) {
          $html->pushContent(HTML::h3("Links"));
          $html->pushContent($this->_DebugPrintArray($links));
        }
        return $html;
    }

    function _DebugPrintArray(&$array) {
            $html = HTML();
            foreach ($array as $line) {
            ob_start();
          print_r($line);
          $s = HTML::pre(ob_get_contents());
          ob_end_clean();
          $html->pushContent($s);
        }
        return $html;
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
