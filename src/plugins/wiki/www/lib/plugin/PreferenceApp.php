<?php //-*-php-*-
// $Id: PreferenceApp.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright (C) 2004 $ThePhpWikiProgrammingTeam
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
 * PreferenceApp is used to analyze a category of items that a group
 * of people have rated.  A user is grouped to be analyzed in the group by
 * 1) having rated at least one item in the database and 2) matching the optional
 * criteria for declaring a budget on their homepage.
 *
 * An example of a budget decleration would be "TotalSoda: 50" on my homepage.
 *
 * PreferenceApp will output a matrix style table shows "how much" fractionally
 * a group of people prefer an item over other items.  For example, if my soda
 * budget is 100 then PreferenceApp might assign 20 units of my budget to Moutain Dew.
 *
 * Author: mcassano circa April 2004
 *
 * Usage:
 * <<PreferenceApp category="Soda" pageTextLabel="TotalSoda" roundCalc="true" >>
 */

require_once('lib/PageList.php');
require_once('lib/InlineParser.php');

require_once('lib/wikilens/Utils.php');
require_once('lib/WikiTheme.php');
require_once('lib/wikilens/Buddy.php');
require_once('lib/wikilens/RatingsDb.php');

class WikiPlugin_PreferenceApp
extends WikiPlugin
{
    function getName () {
        return _("PreferenceApp");
    }

    function getDescription () {
        return _("Analyzes preferences based on voting budget and ratings.");
    }

    function getDefaultArguments() {
        return array(
                     'category' => null,
                     'lockedBudget' => null,
                     'pageTextLabel' => null,
                     'group' => null,
                     'roundCalc' => "true",
                     'neutralRating' => "3",
                     'declareBudget' => "true");
    }
    // info arg allows multiple columns
    // info=mtime,hits,summary,version,author,locked,minor
    // exclude arg allows multiple pagenames exclude=HomePage,RecentChanges

    function run($dbi, $argstr, &$request, $basepage) {

        extract($this->getArgs($argstr, $request));
        if($pageTextLabel == null && $category != null && $group == null){
            $group = $category;
        }
        if($category == null || $pageTextLabel == null){
                return HTML::div(array('class' => "error"), "PreferencesApp Error: You must declare at least parameters category and pageTextLabel.");
        }

        $dbi = $request->getDbh();
        $rdbi = RatingsDb::getTheRatingsDb();

        $CATEGORY = $category;
        $PAGE_TEXT_LABEL = $pageTextLabel;
        $NEUTRAL_RATING = (int)$neutralRating;

        $active_user   = $request->getUser();
        $active_userid = $active_user->_userid;
        $html = HTML();
        $html->pushContent("");

        //Load participating Users
        $users_array = array();
        if($group != null){
            $users_array = getMembers($group, $rdbi);
        } else {
            $people_iter = $rdbi->sql_get_users_rated();
            while($people_array = $people_iter->next()){
                $users_array[] = $people_array['pagename'];
            }
        }
        $people = array();
        foreach($users_array as $person_indv){
            if($declareBudget == "true"){
                $get_array = getPageTextData($person_indv, $dbi, $PAGE_TEXT_LABEL, "cans");
                if(count($get_array) == 1){
                    $cans_text = $get_array[0];
                    if(is_numeric($cans_text) && $cans_text >= 0){
                        $canBudget[$person_indv] = $cans_text; //Load the persons budget
                    } else {
                        $canBudget[$person_indv] = 0;
                    }
                    $people[] = $person_indv;
                }
            } else {
                $canBudget[$person_indv] = $lockedBudget;
                $people[] = $person_indv;
            }
        }
        if(count($people) < 1){
            return fmt("Nobody has used %s on their homepage", $PAGE_TEXT_LABEL);
        }
        //Get all pages from Category
        $pageids = array();
        $category_page = $dbi->getPage($CATEGORY);
        $iter = $category_page->getLinks();
        while ($item = $iter->next()){
            array_push($pageids, $item->getName());
        }
        $ratingTotals = array();
        foreach ($people as $person){
                    $ratings_iter = $rdbi->sql_get_rating(0, $person, $pageids);
                    $ratingTotals[$person] = 0;
                while($ratings_array = $ratings_iter->next()){
                    $can_rating = $ratings_array['ratingvalue'];
                    if($can_rating >= $NEUTRAL_RATING){
                        $ratingTotals[$person] += $can_rating;
                    }
                }
        }

        //Generate numbers
        $canTotals = array();
        $peopleTotals = array();
        foreach($pageids as $soda){
            $canTotals[$soda] = 0;
        }
        foreach($people as $person){
            foreach($pageids as $soda){
                $peopleTotals[$person][$soda] = 0;
            }
        }
        foreach($people as $person){
            foreach($pageids as $page){
                $can_rating_iter = $rdbi->sql_get_rating(0, $person, $page);
                $can_rating_array = $can_rating_iter->next();
                $can_rating = $can_rating_array['ratingvalue'];
                if($can_rating >= $NEUTRAL_RATING){
                    $calc = (($can_rating / $ratingTotals[$person]) * $canBudget[$person]);
                    if($roundCalc == "true"){
                        $adjustedCans = round($calc);
                    } else {
                        $adjustedCans = round($calc, 2);
                    }
                    $peopleTotals[$person][$page] = $adjustedCans;

                    $canTotals[$page] = $canTotals[$page] + $adjustedCans;
                }
            }
        }
        $outputArray = array();
        foreach($people as $person){
            foreach($pageids as $page){
                $outputArray[$person][$page] = 0;
            }
        }

        $table = HTML::table(array('cellpadding' => '5', 'cellspacing' => '1', 'border' => '0'));
        $tr = HTML::tr();
        $td = HTML::td(array('bgcolor' => '#FFFFFF'));
        $td->pushContent(" ");
        $tr->pushContent($td);

        foreach($people as $person){
            $td = HTML::td(array('bgcolor' => '#FFFFFF'));
            $td->pushContent(HTML::a(array('href' => WikiURL($person),
                                           'class' => 'wiki'
                                           ),
                                     SplitPagename($person)));
            //$td->pushContent(WikiLink(" $person "));
            $tr->pushContent($td);
        }
        $td = HTML::td(array('bgcolor' => '#FFFFFF'));
        $td->pushContent(_("Total Units"));
        $tr->pushContent($td);
        $td = HTML::td(array('bgcolor' => '#FFFFFF'));
        $td->pushContent(_("Total Voters"));
        $tr->pushContent($td);
        $table->pushContent($tr);

        for($i = 0; $i < count($pageids); $i++){
            $total_cans = 0;
            for($j = 0; $j < count($people); $j++){
                $td = HTML::td(array('align' => 'right'));
                $cans_per_soda = $peopleTotals[$people[$j]][$pageids[$i]];
                $total_cans = $total_cans + $cans_per_soda;
                $outputArray[$people[$j]][$pageids[$i]] = $cans_per_soda;
            }
        }


        foreach($people as $person){
            $min_soda = "";
            $min_cans = 9999999; //9 million, serving as "infinity"
            $total_cans = 0;
            foreach($pageids as $page){
                $cur_soda_cans = $outputArray[$person][$page];
                if($cur_soda_cans < $min_cans && $cur_soda_cans > 0){
                    $min_cans = $cur_soda_cans;
                    $min_soda = $page;
                }
                $total_cans = $total_cans + $cur_soda_cans;
            }
            if($total_cans != $canBudget[$person] && $total_cans > 0){
                $diff = $canBudget[$person] - $total_cans;
                $outputArray[$person][$min_soda] = $outputArray[$person][$min_soda] + $diff;
            }
        }
        for($i = 0; $i < count($pageids); $i++){
            $tr = HTML::tr();
            $td = HTML::td(array('align' => 'left', 'bgcolor' => '#f7f7f7'));
            $td->pushContent(HTML::a(array('href' => WikiURL($pageids[$i]),
                                           'class' => 'wiki'
                                           ),
                                     SplitPagename($pageids[$i])));
            $tr->pushContent($td);
            $total_cans = 0;
            $total_voters = 0;
            for($j = 0; $j < count($people); $j++){
                $td = HTML::td(array('align' => 'right', 'bgcolor' => '#f7f7f7'));
                $output = $outputArray[$people[$j]][$pageids[$i]];
                $total_cans = $total_cans + $output;
                if($output == ""){
                    $output = "-";
                } else {
                    $total_voters++;
                }
                $td->pushContent($output);
                $tr->pushContent($td);
            }
            if($total_cans == ""){
                $total_cans = "-";
            }
            if($total_voters == ""){
                $total_voters = "-";
            }
            $td = HTML::td(array('align' => 'right'));
            $td->pushContent($total_cans);
            $tr->pushContent($td);
            $td = HTML::td(array('align' => 'right'));
            $td->pushContent($total_voters);
            $tr->pushContent($td);
            $table->pushContent($tr);
        }

        $tr = HTML::tr();
        $td = HTML::td(array('align' => 'left'));
        $td->pushContent(HTML::strong(_("Total Budget")));
        $tr->pushContent($td);
        $cans_total = 0;
        $total_voters = 0;
        for($i = 0; $i < count($people); $i++){
            $td = HTML::td(array('align' => 'right'));
            $cans_for_soda = 0;
            foreach($pageids as $page){
                $cans_for_soda = $cans_for_soda + $outputArray[$people[$i]][$page];
            }
            $cans = $cans_for_soda;
            $cans_total = $cans_total + $cans;
            if($cans == ""){
                $cans = "-";
            } else {
                $total_voters++;
            }
            $td->pushContent(HTML::strong($cans));
            $tr->pushContent($td);
        }
        $td = HTML::td(array('align' => 'right'));
        $td->pushContent(HTML::strong($cans_total));
        $tr->pushContent($td);
        $td = HTML::td(array('align' => 'right'));
        $td->pushContent(HTML::strong($total_voters));
        $tr->pushContent($td);
        $table->pushContent($tr);

        $table2 = HTML::table(array('bgcolor' => '#dedfdf'));
        $table2->pushContent(HTML::tr(HTML::td($table)));
        $html->pushContent($table2);

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
