<?php // -*-php-*-
// rcs_id('$Id: LinkDatabase.php 7417 2010-05-19 12:57:42Z vargenau $');
/**
 * Copyright 2004,2007 $ThePhpWikiProgrammingTeam
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

require_once('lib/PageList.php');
require_once('lib/WikiPluginCached.php');

/**
 * - To be used by WikiBrowser at http://touchgraph.sourceforge.net/
 *   Only via a static text file yet. (format=text)
 * - Or the Hypergraph applet (format=xml)
 *   http://hypergraph.sourceforge.net/
 *   So far also only for a static xml file, but I'll fix the applet and test
 *   the RPC2 interface.
 *
 * TODO: Currently the meta-head tags disturb the touchgraph java browser a bit.
 * Maybe add a theme without that much header tags.
 * DONE: Convert " " to %20
 */
class WikiPlugin_LinkDatabase
extends WikiPluginCached
{
    function getName () {
        return _("LinkDatabase");
    }
    function getPluginType() {
        return PLUGIN_CACHED_HTML;
    }
    function getDescription () {
        return _("List all pages with all links in various formats for some Java Visualization tools");
    }
    function getExpire($dbi, $argarray, $request) {
        return '+900'; // 15 minutes
    }

    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),
             array(
                   'format'        => 'html', // 'html', 'text', 'xml'
                   'noheader'      => false,
                   'include_empty' => false,
                   'exclude_from'  => false,
                   'info'          => '',
                   ));
    }

    function getHtml($dbi, $argarray, $request, $basepage) {
        $this->run($dbi, WikiPluginCached::glueArgs($argarray), $request, $basepage);
    }

    function run($dbi, $argstr, $request, $basepage) {
        global $WikiTheme;
        $args = $this->getArgs($argstr, $request);

        $caption = _("All pages with all links in this wiki (%d total):");

        if ( !empty($args['owner']) ) {
            $pages = PageList::allPagesByOwner($args['owner'],$args['include_empty'],
                                               $args['sortby'],$args['limit']);
            if ($args['owner'])
                $caption = fmt("List of pages owned by [%s] (%d total):",
                               WikiLink($args['owner'], 'if_known'),
                               count($pages));
        } elseif ( !empty($args['author']) ) {
            $pages = PageList::allPagesByAuthor($args['author'],$args['include_empty'],
                                                $args['sortby'],$args['limit']);
            if ($args['author'])
                $caption = fmt("List of pages last edited by [%s] (%d total):",
                               WikiLink($args['author'], 'if_known'),
                               count($pages));
        } elseif ( !empty($args['creator']) ) {
            $pages = PageList::allPagesByCreator($args['creator'],$args['include_empty'],
                                                 $args['sortby'],$args['limit']);
            if ($args['creator'])
                $caption = fmt("List of pages created by [%s] (%d total):",
                               WikiLink($args['creator'], 'if_known'),
                               count($pages));
        } else {
            if (! $request->getArg('count'))
                $args['count'] = $dbi->numPages($args['include_empty'], $args['exclude_from']);
            else
                $args['count'] = $request->getArg('count');
            $pages = $dbi->getAllPages($args['include_empty'], $args['sortby'],
                                       $args['limit'], $args['exclude_from']);
        }
        if ($args['format'] == 'html') {
            $args['types']['links'] =
                new _PageList_Column_LinkDatabase_links('links', _("Links"), 'left');
            $pagelist = new PageList($args['info'], $args['exclude_from'], $args);
            //$pagelist->_addColumn("links");
            if (!$args['noheader']) $pagelist->setCaption($caption);
            $pagelist->addPages($pages);
            return $pagelist;
        } elseif ($args['format'] == 'text') {
            $request->discardOutput();
            $request->buffer_output(false);
            if (!headers_sent())
                header("Content-Type: text/plain");
            $request->checkValidators();
            while ($page = $pages->next()) {
                echo preg_replace("/ /","%20",$page->getName());
                $links = $page->getPageLinks(false, $args['sortby'], $args['limit'],
                                             $args['exclude']);
                while ($link = $links->next()) {
                    echo " ", preg_replace("/ /","%20",$link->getName());
                }
                echo "\n";
            }
            flush();
            if (empty($WikiTheme->DUMP_MODE))
                $request->finish();

        } elseif ($args['format'] == 'xml') {
            // For hypergraph.jar. Best dump it to a local sitemap.xml periodically
            global $WikiTheme, $charset;
            $currpage = $request->getArg('pagename');
            $request->discardOutput();
            $request->buffer_output(false);
            if (!headers_sent())
                header("Content-Type: text/xml");
            $request->checkValidators();
            echo "<?xml version=\"1.0\" encoding=\"$charset\"?>";
            // As applet it prefers only "GraphXML.dtd", but then we must copy it to the webroot.
            $dtd = $WikiTheme->_findData("GraphXML.dtd");
            echo "<!DOCTYPE GraphXML SYSTEM \"$dtd\">\n";
            echo "<GraphXML xmlns:xlink=\"http://www.w3.org/1999/xlink\">\n";
            echo "<graph id=\"",MangleXmlIdentifier(WIKI_NAME),"\">\n";
            echo '<style><line tag="node" class="main" colour="#ffffff"/><line tag="node" class="child" colour="blue"/><line tag="node" class="relation" colour="green"/></style>',"\n\n";
            while ($page = $pages->next()) {
                    $pageid = MangleXmlIdentifier($page->getName());
                    $pagename = $page->getName();
                echo "<node name=\"$pageid\"";
                if ($pagename == $currpage) echo " class=\"main\"";
                echo "><label>$pagename</label>";
                echo "<dataref><ref xlink:href=\"",WikiURL($pagename,'',true),"\"/></dataref></node>\n";
                $links = $page->getPageLinks(false, $args['sortby'], $args['limit'], $args['exclude']);
                while ($link = $links->next()) {
                    $edge = MangleXmlIdentifier($link->getName());
                    echo "<edge source=\"$pageid\" target=\"$edge\" />\n";
                }
                echo "\n";
            }
            echo "</graph>\n";
            echo "</GraphXML>\n";
            if (empty($WikiTheme->DUMP_MODE)) {
                unset($GLOBALS['ErrorManager']->_postponed_errors);
                $request->finish();
            }
        } else {
            return $this->error(fmt("Unsupported format argument %s", $args['format']));
        }
    }
};

class _PageList_Column_LinkDatabase_links extends _PageList_Column {
    function _getValue($page, &$revision_handle) {
        $out = HTML();
        $links = $page->getPageLinks();
        while ($link = $links->next()) {
            $out->pushContent(" ", WikiLink($link));
        }
        return $out;
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
