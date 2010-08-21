<?php // -*-php-*-
// rcs_id('$Id: SemanticSearch.php 7417 2010-05-19 12:57:42Z vargenau $');
/*
 * Copyright 2007 Reini Urban
 * Copyright 2009 Marc-Etienne Vargenau, Alcatel-Lucent
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

require_once('lib/PageList.php');
require_once('lib/TextSearchQuery.php');
require_once('lib/Units.php');
require_once("lib/SemanticWeb.php");

/**
 * Search for relations/attributes and its values.
 * page - relation::object. e.g list all cities: is_a::city => relation=is_a&s=city
 * We search for both a relation and if the search is valid for attributes also,
 * and OR combine the result.
 *
 * An attribute has just a value, which is a number, and which is for sure no pagename,
 * and its value goes through some units unification. (not yet)
 * We can also do numerical comparison and unit lifting with attributes.
 *   population > 1000000
 *   population > 1 million
 *
 * Limitation:
 * - The backends can already do simple AND/OR combination of multiple
 *   relations and attributes to search for. Just the UI not. TODO: implement the AND/OR buttons.
 *     population < 1 million AND area > 50 km2
 * - Due to attribute internals a relation search with matching attribute names will also
 *   find those attribute names, but not the values. You must explicitly search for attributes then.
 *
 * The Advanced query can do a freeform query expression with multiple comparison and nesting.
 *   "is_a::city and population > 1.000.000 and population < 10.000.000"
 *   "(is_a::city or is_a::country) and population < 10.000.000"
 *
 * @author: Reini Urban
 */
class WikiPlugin_SemanticSearch
extends WikiPlugin
{
    function getName() {
        return _("SemanticSearch");
    }
    function getDescription() {
        return _("Search relations and attributes");
    }
    function getDefaultArguments() {
        return array_merge
            (
             PageList::supportedArgs(),  // paging and more.
             array(
                   's'          => "*",  // linkvalue query string
                   'page'       => "*",  // which pages (glob allowed), default: all
                   'relation'   => '',   // linkname. which relations. default all
                   'attribute'  => '',   // linkname. which attributes. default all
                   'attr_op'    => ':=', // a funny written way for equality for pure aesthetic pleasure
                                            // "All attributes which have this value set"
                   'units'      => '',   // ?
                   'case_exact' => true,
                   'regex'      => 'auto',// is different here.
                    // no word splitting, if no regex op is present, defaults to exact match
                   'noform'     => false, // don't show form with results.
                   'noheader'   => false, // no caption
                   'info'       => false  // valid: pagename,relation,linkto,attribute,value and all other pagelist columns
                   ));
    }

    function showForm (&$dbi, &$request, $args) {
            global $WikiTheme;
        $action = $request->getPostURL();
        $hiddenfield = HiddenInputs($request->getArgs(),'',
                                    array('action','page','s','semsearch',
                                          'relation','attribute'));
        $pagefilter = HTML::input(array('name' => 'page',
                                        'value' => $args['page'],
                                        'title' => _("Search only in these pages. With autocompletion."),
                                        'class' => 'dropdown',
                                        'acdropdown' => 'true',
                                        'autocomplete_complete' => 'true',
                                        'autocomplete_matchsubstring' => 'false',
                                        'autocomplete_list' => 'xmlrpc:wiki.titleSearch ^[S] 4'
                                        ), '');
        $allrelations = $dbi->listRelations(false,false,true);
        $svalues = empty($allrelations) ? "" : join("','", $allrelations);
        $reldef = JavaScript("var semsearch_relations = new Array('".$svalues."')");
        $relation = HTML::input(array('name' => 'relation',
                                      'value' => $args['relation'],
                                      'title' => _("Filter by this relation. With autocompletion."),
                                      'class' => 'dropdown',
                                      'style' => 'width:10em',
                                      'acdropdown' => 'true',
                                      'autocomplete_assoc' => 'false',
                                      'autocomplete_complete' => 'true',
                                      'autocomplete_matchsubstring' => 'true',
                                      'autocomplete_list' => 'array:semsearch_relations'
                                      ), '');
        $queryrel = HTML::input(array('name' => 's',
                                      'value' => $args['s'],
                                      'title' => _("Filter by this link. These are pagenames. With autocompletion."),
                                      'class' => 'dropdown',
                                      'acdropdown' => 'true',
                                      'autocomplete_complete' => 'true',
                                      'autocomplete_matchsubstring' => 'true',
                                      'autocomplete_list' => 'xmlrpc:wiki.titleSearch ^[S] 4'
                                      ), '');
        $relsubmit = Button('submit:semsearch[relations]',  _("Relations"), false);
        // just testing some dhtml... not yet done
        $enhancements = HTML();
        $nbsp = HTML::raw('&nbsp;');
        $this_uri = $_SERVER['REQUEST_URI'].'#';
        $andbutton = new Button(_("AND"),$this_uri,'wikiaction',
                                array(
                                      'onclick' => "addquery('rel', 'and')",
                                      'title' => _("Add an AND query")));
        $orbutton = new Button(_("OR"),$this_uri,'wikiaction',
                                array(
                                      'onclick' => "addquery('rel', 'or')",
                                      'title' => _("Add an OR query")));
        if (DEBUG)
            $enhancements = HTML::span($andbutton, $nbsp, $orbutton);
        $instructions = _("Search in pages for a relation with that value (a pagename).");
        $form1 = HTML::form(array('action' => $action,
                                  'method' => 'post',
                                  'accept-charset' => $GLOBALS['charset']),
                            $reldef,
                            $hiddenfield, HiddenInputs(array('attribute'=>'')),
                            $instructions, HTML::br(),
                            HTML::table
                            (array('border' => 0,'cellspacing' => 2),
                             HTML::colgroup(array('span' => 6)),
                             HTML::thead
                             (HTML::tr(
                                       HTML::th('Pagefilter'),
                                       HTML::th('Relation'),
                                       HTML::th(),
                                       HTML::th('Links'),
                                       HTML::th()
                                      )),
                             HTML::tbody
                             (HTML::tr(
                                       HTML::td($pagefilter, ": "),
                                       HTML::td($relation),
                                       HTML::td(HTML::strong(HTML::tt('  ::  '))),
                                       HTML::td($queryrel),
                                       HTML::td($nbsp, $relsubmit, $nbsp, $enhancements)))));

        $allattrs = $dbi->listRelations(false,true,true);
        if (empty($allrelations) and empty($allattrs)) // be nice to the dummy.
            $this->_norelations_warning = 1;
        $svalues = empty($allattrs) ? "" : join("','", $allattrs);
        $attdef = JavaScript("var semsearch_attributes = new Array('".$svalues."')\n"
                            ."var semsearch_op = new Array('"
                                  .join("','", $this->_supported_operators)
                                  ."')");
        // TODO: We want some more tricks: Autofill the base unit of the selected
        // attribute into the s area.
        $attribute = HTML::input(array('name' => 'attribute',
                                       'value' => $args['attribute'],
                                       'title' => _("Filter by this attribute name. With autocompletion."),
                                       'class' => 'dropdown',
                                       'style' => 'width:10em',
                                       'acdropdown' => 'true',
                                       'autocomplete_complete' => 'true',
                                       'autocomplete_matchsubstring' => 'true',
                                       'autocomplete_assoc' => 'false',
                                       'autocomplete_list' => 'array:semsearch_attributes'
                                       /* 'autocomplete_onselect' => 'check_unit' */
                                      ), '');
        $attr_op = HTML::input(array('name' => 'attr_op',
                                        'value' => $args['attr_op'],
                                        'title' => _("Comparison operator. With autocompletion."),
                                        'class' => 'dropdown',
                                        'style' => 'width:2em',
                                        'acdropdown' => 'true',
                                        'autocomplete_complete' => 'true',
                                        'autocomplete_matchsubstring' => 'true',
                                        'autocomplete_assoc' => 'false',
                                        'autocomplete_list' => 'array:semsearch_op'
                                      ), '');
        $queryatt = HTML::input(array('name' => 's',
                                      'value' => $args['s'],
                                      'title' => _("Filter by this numeric attribute value. With autocompletion."), //?
                                      'class' => 'dropdown',
                                      'acdropdown' => 'false',
                                      'autocomplete_complete' => 'true',
                                      'autocomplete_matchsubstring' => 'false',
                                      'autocomplete_assoc' => 'false',
                                      'autocomplete_list' => 'plugin:SemanticSearch page='.$args['page'].' attribute=^[S] attr_op==~'
                                      ), '');
        $andbutton = new Button(_("AND"),$this_uri,'wikiaction',
                                array(
                                      'onclick' => "addquery('attr', 'and')",
                                      'title' => _("Add an AND query")));
        $orbutton = new Button(_("OR"),$this_uri,'wikiaction',
                                array(
                                      'onclick' => "addquery('attr', 'or')",
                                      'title' => _("Add an OR query")));
        if (DEBUG)
            $enhancements = HTML::span($andbutton, $nbsp, $orbutton);
        $attsubmit = Button('submit:semsearch[attributes]', _("Attributes"), false);
        $instructions = HTML::span(_("Search in pages for an attribute with that numeric value."),"\n");
        if (DEBUG)
            $instructions->pushContent
                (HTML(" ", new Button(_("Advanced..."),_("SemanticSearchAdvanced"))));
        $form2 = HTML::form(array('action' => $action,
                                  'method' => 'post',
                                  'accept-charset' => $GLOBALS['charset']),
                            $attdef,
                            $hiddenfield, HiddenInputs(array('relation'=>'')),
                            $instructions, HTML::br(),
                            HTML::table
                            (array('border' => 0,'cellspacing' => 2),
                             HTML::colgroup(array('span' => 6)),
                             HTML::thead
                             (HTML::tr(
                                       HTML::th('Pagefilter'),
                                       HTML::th('Attribute'),
                                       HTML::th('Op'),
                                       HTML::th('Value'),
                                       HTML::th()
                                      )),
                             HTML::tbody
                             (HTML::tr(
                                       HTML::td($pagefilter, ": "),
                                       HTML::td($attribute),
                                       HTML::td($attr_op),
                                       HTML::td($queryatt),
                                       HTML::td($nbsp, $attsubmit, $nbsp, $enhancements)))));

        return HTML($form1, $form2);
    }

    function regex_query ($string, $case_exact, $regex) {
            if ($string != '*' and $regex == 'auto') {
            if (strcspn($string, ".+*?^$\"") == strlen($string)) {
                    // performance hack: construct an exact query w/o parsing. pcre is fastest.
                $q = new TextSearchQuery($string, $case_exact, 'pcre');
                // and now override the fields
                unset ($q->_stoplist);
                $q->_regex = TSQ_REGEX_NONE;
                if ($case_exact)
                    $q->_tree = new TextSearchQuery_node_exact($string); // hardcode this string
                else
                    $q->_tree = new TextSearchQuery_node_word($string);
                return $q;
                //$string = "\"" . $string ."\"";
                //$regex = 'none'; // EXACT or WORD match
            }
        }
        return new TextSearchQuery($string, $case_exact, $regex);
    }

    function run ($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;

        $this->_supported_operators = array(':=','<','<=','>','>=','!=','==','=~');
        $this->_text_operators = array(':=','==','=~','!=');
        $args = $this->getArgs($argstr, $request);
        if (empty($args['page']))
            $args['page'] = "*";
        if (!isset($args['s'])) // it might be (integer) 0
            $args['s'] = "*";
        $posted = $request->getArg("semsearch");
        $form = $this->showForm($dbi, $request, $args);
        if (isset($this->_norelations_warning))
            $form->pushContent
                (HTML::div(array('class' => 'warning'),
                           _("Warning:"),HTML::br(),
                           _("No relations nor attributes in the whole wikidb defined!")
                           ,"\n"
                           ,fmt("See %s",WikiLink(_("Help:SemanticRelations")))));
        extract($args);
        // for convenience and harmony we allow GET requests also.
        if (!$request->isPost()) {
            if ($relation or $attribute) // check for good GET request
                ;
            else
                return $form; // nobody called us, so just display our supadupa form
        }
        $pagequery = $this->regex_query($page, $args['case_exact'], $args['regex']);
        // we might want to check for semsearch['relations'] and semsearch['attributes'] also
        if (empty($relation) and empty($attribute)) {
            // so we just clicked without selecting any relation.
            // hmm. check which button we clicked, before we do the massive alltogether search.
            if (isset($posted['relations']) and $posted['relations'])
                $relation = '*';
            elseif (isset($posted['attributes']) and $posted['attributes']) {
                $attribute = '*';
                // here we have to check for invalid text operators. ignore it then
                if (!in_array($attr_op, $this->_text_operators))
                    $attribute = '';
            }
        }
        $searchtype = "Text";
        if (!empty($relation)) {
            $querydesc = $relation."::".$s;
            $linkquery =  $this->regex_query($s, $args['case_exact'], $args['regex']);
            $relquery = $this->regex_query($relation, $args['case_exact'], $args['regex']);
            $links = $dbi->linkSearch($pagequery, $linkquery, 'relation', $relquery);
            $pagelist = new PageList($info, $exclude, $args);
            $pagelist->_links = array();
            while ($link = $links->next()) {
                $pagelist->addPage($link['pagename']);
                $pagelist->_links[] = $link;
            }
            // default (=empty info) wants all three. but we want to be able to override this.
            // $pagelist->_columns_seen is the exploded info
            if (!$info or ($info and isset($pagelist->_columns_seen['relation'])))
                $pagelist->addColumnObject
                    (new _PageList_Column_SemanticSearch_relation('relation', _("Relation"), $pagelist));
            if (!$args['info'] or ($args['info'] and isset($pagelist->_columns_seen['linkto'])))
                $pagelist->addColumnObject
                    (new _PageList_Column_SemanticSearch_link('linkto', _("Link"), $pagelist));
        }
        // can we merge two different pagelist?
        if (!empty($attribute)) {
            $relquery =  $this->regex_query($attribute, $args['case_exact'], $args['regex']);
            if (!in_array($attr_op, $this->_supported_operators)) {
                return HTML($form, $this->error(fmt("Illegal operator: %s",
                                                    HTML::tt($attr_op))));
            }
            $s_base = preg_replace("/,/","", $s);
            $units = new Units();
            if (!is_numeric($s_base)) {
                $s_base = $units->basevalue($s_base);
                $is_numeric = is_numeric($s_base);
            } else {
                $is_numeric = true;
            }
            // check which type to search with:
            // at first check if forced text matcher
            if ($attr_op == '=~') {
                if ($s == '*') $s = '.*'; // help the poor user. we need pcre syntax.
                $linkquery = new TextSearchQuery("$s", $args['case_exact'], 'pcre');
                $querydesc = "$attribute $attr_op $s";
            } elseif ($is_numeric) { // do comparison with numbers
                /* We want to search for multiple attributes also. linkSearch can do this.
                 * But we have to construct the query somehow. (that's why we try the AND OR dhtml)
                 *     population < 1 million AND area > 50 km2
                 * Here we check only for one attribute per page.
                 * See SemanticSearchAdvanced for the full expression.
                 */
                // it might not be the best idea to use '*' as variable to expand. hmm.
                if ($attribute == '*') $attribute = '_star_';
                $searchtype = "Numeric";
                $query = $attribute." ".$attr_op." ".$s_base;
                $linkquery = new SemanticAttributeSearchQuery($query, $attribute,
                                                              $units->baseunit($s));
                if ($attribute == '_star_') $attribute = '*';
                $querydesc = $attribute." ".$attr_op." ".$s;

            // no number or unit: check other text matchers or '*' MATCH_ALL
            } elseif (in_array($attr_op, $this->_text_operators)) {
                if ($attr_op == '=~') {
                    if ($s == '*') $s = '.*'; // help the poor user. we need pcre syntax.
                    $linkquery = new TextSearchQuery("$s", $args['case_exact'], 'pcre');
                }
                else
                    $linkquery =  $this->regex_query($s, $args['case_exact'], $args['regex']);
                $querydesc = "$attribute $attr_op $s";

            // should we fail or skip when the user clicks on Relations?
            } elseif (isset($posted['relations']) and $posted['relations'])  {
                $linkquery = false; // skip
            } else {
                $querydesc = $attribute." ".$attr_op." ".$s;
                return HTML($form, $this->error(fmt("Only text operators can be used with strings: %s",
                                                    HTML::tt($querydesc))));

            }
            if ($linkquery) {
                $links = $dbi->linkSearch($pagequery, $linkquery, 'attribute', $relquery);
                if (empty($relation)) {
                    $pagelist = new PageList($args['info'], $args['exclude'], $args);
                    $pagelist->_links = array();
                }
                while ($link = $links->next()) {
                    $pagelist->addPage($link['pagename']);
                    $pagelist->_links[] = $link;
                }
                // default (=empty info) wants all three. but we want to override this.
                if (!$args['info'] or
                    ($args['info'] and isset($pagelist->_columns_seen['attribute'])))
                    $pagelist->addColumnObject
                        (new _PageList_Column_SemanticSearch_relation('attribute',
                                _("Attribute"), $pagelist));
                if (!$args['info'] or
                    ($args['info'] and isset($pagelist->_columns_seen['value'])))
                    $pagelist->addColumnObject
                        (new _PageList_Column_SemanticSearch_link('value',
                                _("Value"), $pagelist));
            }
        }
        if (!isset($pagelist)) {
            $querydesc = _("<empty>");
            $pagelist = new PageList();
        }
        if (!$noheader) {
        // We put the form into the caption just to be able to return one pagelist object,
        // and to still have the convenience form at the top. we could workaround this by
        // putting the form as WikiFormRich into the actionpage. but thid doesnt look as
        // nice as this here.
            $pagelist->setCaption
            (   // on mozilla the form doesn't fit into the caption very well.
                HTML($noform ? '' : HTML($form,HTML::hr()),
                     fmt("Semantic %s Search Result for \"%s\" in pages \"%s\"",
                              $searchtype, $querydesc, $page)));
        }
        return $pagelist;
    }
};

class _PageList_Column_SemanticSearch_relation
extends _PageList_Column
{
    function _PageList_Column_SemanticSearch_relation ($field, $heading, &$pagelist) {
        $this->_field = $field;
        $this->_heading = $heading;
        $this->_need_rev = false;
        $this->_iscustom = true;
        $this->_pagelist =& $pagelist;
    }
    function _getValue(&$page, $revision_handle) {
        if (is_object($page)) $text = $page->getName();
        else $text = $page;
        $link = $this->_pagelist->_links[$this->current_row];
        return WikiLink($link['linkname'],'if_known');
    }
}
class _PageList_Column_SemanticSearch_link
extends _PageList_Column_SemanticSearch_relation
{
    function _getValue(&$page, $revision_handle) {
        if (is_object($page)) $text = $page->getName();
        else $text = $page;
        $link = $this->_pagelist->_links[$this->current_row];
        if ($this->_field != 'value')
            return WikiLink($link['linkvalue'],'if_known');
        else
            return $link['linkvalue'];
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
