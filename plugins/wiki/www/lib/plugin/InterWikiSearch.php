<?php // -*-php-*-
// $Id: InterWikiSearch.php 8071 2011-05-18 14:56:14Z vargenau $
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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
/**
 * @description
 */
require_once('lib/PageType.php');

class WikiPlugin_InterWikiSearch
extends WikiPlugin
{
    function getName() {
        return _("InterWikiSearch");
    }

    function getDescription() {
        return _("Perform searches on InterWiki sites listed in InterWikiMap.");
    }

    function getDefaultArguments() {
        return array('s' => '',
                     'formsize' => 30,
                    );
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);

        if (defined('DEBUG') && !DEBUG)
            return $this->disabled("Sorry, this plugin is currently out of order.");

        $page = $dbi->getPage($request->getArg('pagename'));
        return new TransformedText($page,_('InterWikiMap'),array('markup' => 2),
                                   'searchableInterWikiMap');
        /*
        return new PageType($pagerevisionhandle,
                            $pagename = _('InterWikiMap'),
                            $markup = 2,
                            $overridePageType = 'PageType_searchableInterWikiMap');
        */
    }
};


/**
 * @desc
 */
if (defined('DEBUG') && DEBUG) {
class PageFormatter_searchableInterWikiMap
extends PageFormatter_interwikimap {}

class PageType_searchableInterWikiMap
extends PageType_interwikimap
{
    function format($text) {
        return HTML::div(array('class' => 'wikitext'),
                         $this->_transform($this->_getHeader($text)),
                         $this->_formatMap(),
                         $this->_transform($this->_getFooter($text)));
    }

    function _formatMap() {
        return $this->_arrayToTable ($this->_getMap(), $GLOBALS['request']);
    }

    function _arrayToTable ($array, &$request) {
        $thead = HTML::thead();
        $label[0] = _("Wiki Name");
        $label[1] = _("Search");
        $thead->pushContent(HTML::tr(HTML::th($label[0]),
                                     HTML::th($label[1])));

        $tbody = HTML::tbody();
        $dbi = $request->getDbh();
        if ($array) {
            foreach ($array as $moniker => $interurl) {
                $monikertd = HTML::td(array('class' => 'interwiki-moniker'),
                                      $dbi->isWikiPage($moniker)
                                      ? WikiLink($moniker)
                                      : $moniker);

                $w = new WikiPluginLoader;
                $p = $w->getPlugin('ExternalSearch');
                $argstr = sprintf('url="%s"', addslashes($interurl));
                $searchtd = HTML::td($p->run($dbi, $argstr, $request, $basepage));

                $tbody->pushContent(HTML::tr($monikertd, $searchtd));
            }
        }
        $table = HTML::table();
        $table->setAttr('class', 'interwiki-map');
        $table->pushContent($thead);
        $table->pushContent($tbody);

        return $table;
    }
};
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
