<?php // -*-php-*-
// $Id: FuzzyPages.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 1999, 2000, 2001, 2002 $ThePhpWikiProgrammingTeam
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

/**
 * FuzzyPages is plugin which searches for similar page titles.
 *
 * Pages are considered similar by averaging the similarity scores of
 * the spelling comparison and the metaphone comparison for each page
 * title in the database (php's metaphone() is an improved soundex
 * function).
 *
 * http://www.php.net/manual/en/function.similar-text.php
 * http://www.php.net/manual/en/function.metaphone.php
 */
class WikiPlugin_FuzzyPages
extends WikiPlugin
{
    function getName() {
        return _("FuzzyPages");
    }

    function getDescription() {
        return sprintf(_("Search for page titles similar to %s."),
                       '[pagename]');
    }

    function getDefaultArguments() {
        return array('s'     => false,
                     'debug' => false);
    }

    function spelling_similarity($subject) {
        $spelling_similarity_score = 0;
        similar_text($subject, $this->_searchterm,
                     $spelling_similarity_score);
        return $spelling_similarity_score;
    }

    function sound_similarity($subject) {
        $sound_similarity_score = 0;
        similar_text(metaphone($subject), $this->_searchterm_metaphone,
                     $sound_similarity_score);
        return $sound_similarity_score;
    }

    function averageSimilarities($subject) {
        return ($this->spelling_similarity($subject)
                + $this->sound_similarity($subject)) / 2;
    }

    function collectSimilarPages(&$list, &$dbi) {
        if (! defined('MIN_SCORE_CUTOFF'))
            define('MIN_SCORE_CUTOFF', 33);

        $this->_searchterm_metaphone = metaphone($this->_searchterm);

        $allPages = $dbi->getAllPages();

        while ($pagehandle = $allPages->next()) {
            $pagename = $pagehandle->getName();
            $similarity_score = $this->averageSimilarities($pagename);
            if ($similarity_score > MIN_SCORE_CUTOFF)
                $list[$pagename] = $similarity_score;
        }
    }

    function sortCollectedPages(&$list) {
        arsort($list, SORT_NUMERIC);
    }

    function addTableCaption(&$table, &$dbi) {
        if ($dbi->isWikiPage($this->_searchterm))
            $link = WikiLink($this->_searchterm, 'auto');
        else
            $link = $this->_searchterm;
        $caption = fmt("These page titles match fuzzy with '%s'", $link);
        $table->pushContent(HTML::caption(array('align'=>'top'), $caption));
    }

    function addTableHead(&$table) {
        $row = HTML::tr(HTML::th(_("Name")),
                        HTML::th(array('align' => 'right'), _("Score")));

        if (defined('DEBUG') && DEBUG && $this->debug) {
            $this->_pushDebugHeadingTDinto($row);
        }

        $table->pushContent(HTML::thead($row));
    }

    function addTableBody(&$list, &$table) {
        if (! defined('HIGHLIGHT_ROWS_CUTOFF_SCORE'))
            define('HIGHLIGHT_ROWS_CUTOFF_SCORE', 60);

        $tbody = HTML::tbody();
        foreach ($list as $found_pagename => $score) {
            $row = HTML::tr(array('class' =>
                                  $score > HIGHLIGHT_ROWS_CUTOFF_SCORE
                                  ? 'evenrow' : 'oddrow'),
                            HTML::td(WikiLink($found_pagename)),
                            HTML::td(array('align' => 'right'),
                                     round($score)));

            if (defined('DEBUG') && DEBUG && $this->debug) {
                $this->_pushDebugTDinto($row, $found_pagename);
            }

            $tbody->pushContent($row);
        }
        $table->pushContent($tbody);
    }

    function formatTable(&$list, &$dbi) {

        if (empty($list)) {
           return HTML::p(fmt("No fuzzy matches with '%s'", $this->_searchterm));
        }
        $table = HTML::table(array('cellpadding' => 2,
                                   'cellspacing' => 1,
                                   'border'      => 0,
                                   'class' => 'pagelist'));
        $this->addTableCaption($table, $dbi);
        $this->addTableHead($table);
        $this->addTableBody($list, $table);
        return $table;
    }


    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        if (empty($s)) {
            return HTML();
        }

        if (defined('DEBUG') && DEBUG) {
            $this->debug = $debug;
        }

        $this->_searchterm = $s;
        $this->_list = array();

        $this->collectSimilarPages($this->_list, $dbi);
        $this->sortCollectedPages($this->_list);
        return $this->formatTable($this->_list, $dbi);
    }

    function _pushDebugHeadingTDinto(&$row) {
        $row->pushContent(HTML::td(_("Spelling Score")),
                          HTML::td(_("Sound Score")),
                          HTML::td('Metaphones'));
    }

    function _pushDebugTDinto(&$row, $pagename) {
        // This actually calculates everything a second time for each pagename
        // so the individual scores can be displayed separately for debugging.
        $debug_spelling = round($this->spelling_similarity($pagename), 1);
        $debug_sound = round($this->sound_similarity($pagename), 1);
        $debug_metaphone = sprintf("(%s, %s)", metaphone($pagename),
                                   $this->_searchterm_metaphone);

        $row->pushcontent(HTML::td(array('align' => 'center'), $debug_spelling),
                          HTML::td(array('align' => 'center'), $debug_sound),
                          HTML::td($debug_metaphone));
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
