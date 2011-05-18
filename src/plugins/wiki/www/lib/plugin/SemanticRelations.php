<?php // -*-php-*-
// $Id: SemanticRelations.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2005 Reini Urban
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
 * SemanticRelations - Display the list of relations and attributes of given page(s).
 * Relations are stored in the link table.
 * Attributes as simple page meta-data.
 *
 * @author: Reini Urban
 * @see WikiPlugin_SemanticSearch
 */
class WikiPlugin_SemanticRelations
extends WikiPlugin
{
    function getName() {
        return _("SemanticRelations");
    }
    function getDescription() {
        return _("Display the list of relations and attributes on this page.");
    }
    function getDefaultArguments() {
        return array(
                     'page'       => "[pagename]", // which pages (glob allowed), default: current
                     'relations'  => '', // which relations. default all
                     'attributes' => '', // which attributes. default all
                     'units'      => '', // ?
                     'noheader'   => false,
                     'nohelp'     => false
                     );
    }
    function run ($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;
        $args = $this->getArgs($argstr, $request);
        extract($args);
        if (empty($page))
            $page = $request->getArg('pagename');
        $relhtml = HTML();
        if ($args['relations'] != '') {
            $relfilter = explode(",", $args['relations']);
        } else
            $relfilter = array();
        if ($args['attributes'] != '') {
            $attfilter = explode(",", $args['attributes']);
        } else
            $attfilter = array();
        foreach (explodePageList($page) as $pagename) {
            $p = $dbi->getPage($pagename);
            if ($args['relations'] != '0') {
              $links = $p->getRelations(); // iter of pagelinks
              // TODO: merge same relations together located_in::here, located_in::there
              while ($object = $links->next()) {
                if ($related = $object->get('linkrelation')) { // a page name
                    if ($relfilter and !in_array($related, $relfilter)) {
                             continue;
                    }
                    $rellink = WikiLink($related, false, $related);
                    $rellink->setAttr('class', $rellink->getAttr('class').' relation');
                    $relhtml->pushContent
                        ($pagename . " ",
                         // Link to a special "Relation:" InterWiki link?
                         $rellink,
                         HTML::span(array('class'=>'relation-symbol'), "::"), // use spaces?
                         WikiLink($object->_pagename),
                         " ",
                         // Link to SemanticSearch
                         $WikiTheme->makeActionButton(array('relation' => $related,
                                                            's'   => $object->_pagename),
                                                      '+',
                                                      _("SemanticSearch")),
                         (count($relfilter) > 3 ? HTML::br() : " "));
                }
              }
              if (!empty($relhtml->_content) and !$noheader)
                  $relhtml = HTML(HTML::hr(),
                                  HTML::h3(fmt("Semantic relations for %s", $pagename)),
                                  $relhtml);
            }
            $atthtml = HTML();
            if ($args['attributes'] != '0') {
              if ($attributes = $p->get('attributes')) { // a hash of unique pairs
                foreach ($attributes as $att => $val) {
                    if ($attfilter and !in_array($att, $attfilter)) continue;
                    $rellink = WikiLink($att, false, $att);
                    $rellink->setAttr('class', $rellink->getAttr('class').' relation');
                    $searchlink = $WikiTheme->makeActionButton
                        (array('attribute' => $att,
                               's'         => $val),
                         $val,
                         _("SemanticSearch"));
                    $searchlink->setAttr('class', $searchlink->getAttr('class').' attribute');
                    if (!$noheader)
                        $atthtml->pushContent("$pagename  ");
                    $atthtml->pushContent(HTML::span(array('class' => 'attribute '.$att),
                                                     $rellink,
                                                     HTML::span(array('class'=>'relation-symbol'),
                                                                ":="),
                                                     $searchlink),
                                          (count($attfilter) > 3 ? HTML::br() : " "));
                }
                if (!$noheader)
                    $relhtml = HTML($relhtml,
                                    HTML::hr(),
                                    HTML::h3(fmt("Attributes of %s", $pagename)),
                                    $atthtml);
                else
                    $relhtml = HTML($relhtml, $atthtml);
             }
           }
        }
        if ($nohelp) return $relhtml;
        return HTML($relhtml,
                    HTML::hr(),
                    WikiLink(_("Help/SemanticRelations"), false,
                             HTML::em(_("Help/SemanticRelations"))),
                    " - ",
                    HTML::em(_("Find out how to add relations and attributes to pages.")));
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
