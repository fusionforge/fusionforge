<?php // -*-php-*-
// rcs_id('$Id: SemanticSearchAdvanced.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright 2007 Reini Urban
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

require_once('lib/plugin/SemanticSearch.php');

/**
 * Advanced search for relations/attributes and its values.
 * Parse the query string, which can contain full mathematical expressions
 * and various logical and mathematical functions and operators.
 * Support subqueries (for _pagename ...) and temporary variables
 * starting with _
 *
 * Are multiple variables valid for one page only, or is the result
 * constructed as list of all matches? We'll stick with one page for now.
 * This the only way I can see semantic meaning for now.
 *
 * Simple queries, with no variables, only against the pagename (implicit):
 *    "is_a::city and (population < 1.000.000 or population > 10.000.000)"
 *    "(is_a::city or is_a::country) and population < 10.000.000"
 *
 * Subqueries, with variables bound to the matching pagename, with (for ...):
 *    "works_at::_organization
 *      and (for _organization located_in::_city
 *             and (for _city population>1000000))"
 *
 *    "works_at::_organization
 *       and (for _organization
 *             (located_in::_city
 *             and (for _city is_a::City
 *                   and population>1000000))
 *          or (located_in::_country
 *               and (for _country is_a::Country and population>5000000)))
 *
 * Relation links may contain wildcards. For relation and attribute names I'm not sure yet.
 *
 * @author: Reini Urban
 */

class WikiPlugin_SemanticSearchAdvanced
extends WikiPlugin_SemanticSearch
{
    function getName() {
        return _("SemanticSearchAdvanced");
    }
    function getDescription() {
        return _("Parse and execute a full query expression");
    }
    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),  // paging and more.
             array(
                   's'          => "",   // query expression
                   'page'       => "*",  // which pages (glob allowed), default: all
                   'case_exact' => false,
                   'regex'      => 'auto', // hmm
                   'noform'     => false, // don't show form with results.
                   'noheader'   => false  // no caption
                   ));
    }

    function showForm (&$dbi, &$request, $args, $allrelations) {
            global $WikiTheme;
        $action = $request->getPostURL();
        $hiddenfield = HiddenInputs($request->getArgs(),'',
                                    array('action','page','s'));
        $pagefilter = HTML::input(array('name' => 'page',
                                        'value' => $args['page'],
                                        'title' => _("Search only in these pages. With autocompletion."),
                                        'class' => 'dropdown',
                                        'acdropdown' => 'true',
                                        'autocomplete_complete' => 'true',
                                        'autocomplete_matchsubstring' => 'false',
                                        'autocomplete_list' => 'xmlrpc:wiki.titleSearch ^[S] 4'
                                        ), '');
        $help = Button('submit:semsearch[help]', "?", false);
        $svalues = empty($allrelations) ? "" : join("','", $allrelations);
        $reldef = JavaScript("var semsearch_relations = new Array('".$svalues."')");
        $querybox = HTML::textarea(array('name' => 's',
                                         'title' => _("Enter a valid query expression"),
                                         'rows' => 4,
                                         'acdropdown' => 'true',
                                         'autocomplete_complete' => 'true',
                                         'autocomplete_assoc' => 'false',
                                         'autocomplete_matchsubstring' => 'true',
                                         'autocomplete_list' => 'array:semsearch_relations'
                                      ), $args['s']);
        $submit = Button('submit:semsearch[relations]',  _("Search"), false,
                         array('title' => 'Move to help page. No seperate window'));
        $instructions = _("Search in all specified pages for the expression.");
        $form = HTML::form(array('action' => $action,
                                  'method' => 'post',
                                  'accept-charset' => $GLOBALS['charset']),
                           $reldef,
                           $hiddenfield, HiddenInputs(array('attribute'=>'')),
                           $instructions, HTML::br(),
                           HTML::table(array('border'=>'0','width' =>'100%'),
                                       HTML::tr(HTML::td(_("Pagename(s): "), $pagefilter),
                                                HTML::td(array('align' => 'right'),
                                                         $help)),
                                       HTML::tr(HTML::td(array('colspan' => 2),
                                                         $querybox))),
                           HTML::br(),
                           HTML::div(array('align'=>'center'),$submit));
        return $form;
    }

    function run ($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;

        $this->_supported_operators = array(':=','<','<=','>','>=','!=','==','=~');
        $args = $this->getArgs($argstr, $request);
        $posted = $request->getArg('semsearch');
        $request->setArg('semsearch', false);
        if ($request->isPost() and isset($posted['help'])) {
            $request->redirect(WikiURL(_("Help/SemanticSearchAdvancedPlugin"),
                                       array('redirectfrom' => $basepage), true));
        }
        $allrelations = $dbi->listRelations();
        $form = $this->showForm($dbi, $request, $args, $allrelations);
        if (isset($this->_norelations_warning))
            $form->pushContent(HTML::div(array('class' => 'warning'),
                                         _("Warning:").$this->_norelations_warning));
        extract($args);
        // For convenience, peace and harmony we allow GET requests also.
        if (!$args['s']) // check for good GET request
            return $form; // nobody called us, so just display our form

        // In reality we have to iterate over all found pages.
        // To makes things shorter extract the next AND required expr and
        // iterate only over this, then recurse into the next AND expr.
        // => Split into an AND and OR expression tree.

        $parsed_relations = $this->detectRelationsAndAttributes($args['s']);
        $regex = '';
        if ($parsed_relations)
            $regex = preg_grep("/[\*\?]/", $parsed_relations);
        // Check that all those do exist.
        else
            $this->error("Invalid query: No relations or attributes in the query $s found");
        $pagelist = new PageList($args['info'], $args['exclude'], $args);
        if (!$noheader) {
            $pagelist->setCaption
                (HTML($noform ? '' : HTML($form,HTML::hr()),
                      fmt("Semantic %s Search Result for \"%s\" in pages \"%s\"",
                          '',$s,$page)));
        }
        if (!$regex and $missing = array_diff($parsed_relations, $allrelations))
            return $pagelist;
        $relquery = new TextSearchQuery(join(" ",$parsed_relations));
        if (!$relquery->match(join(" ",$allrelations)))
            return $pagelist;
        $pagequery = new TextSearchQuery($page, $args['case_exact'], $args['regex']);
        // if we have only numeric or text ops we can optimize.
        //$parsed_attr_ops = $this->detectAttrOps($args['s']);

        //TODO: writeme
        $linkquery = new TextSearchQuery($s, $args['case_exact'], $args['regex']);
        $links = $dbi->linkSearch($pagequery, $linkquery, 'relation', $relquery);
        $pagelist->_links = array();
        while ($link = $links->next()) {
            $pagelist->addPage($link['pagename']);
            $pagelist->_links[] = $link;
        }
        $pagelist->addColumnObject
            (new _PageList_Column_SemanticSearch_relation('relation', _("Relation"), $pagelist));
        $pagelist->addColumnObject
            (new _PageList_Column_SemanticSearch_link('link', _("Link"), $pagelist));

        return $pagelist;
    }

    // ... (for _variable subquery) ...
    function bindSubquery($query) {
    }

    // is_a::city* and (population < 1.000.000 or population > 10.000.000)
    // => is_a population
    // Do we support wildcards in relation names also? is_*::city
    function detectRelationsAndAttributes($subquery) {
        $relations = array();
        // relations are easy
        //$reltoken = preg_grep("/::/", preg_split("/\s+/", $query));
        //$relations = array_map(create_function('$a','list($f,$b)=split("::",$a); return $f'),
        //                       $reltoken);
        foreach (preg_split("/\s+/", $query) as $whitetok) {
            if (preg_match("/^([\w\*\?]+)::/", $whitetok))
                $relations[] = $m[1];
        }
        return $relations;
        // for attributes we might use the tokenizer. All non-numerics excl. units and non-ops
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
