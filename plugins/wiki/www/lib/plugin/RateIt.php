<?php // -*-php-*-
// $Id: RateIt.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright 2004,2007,2009 $ThePhpWikiProgrammingTeam
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
 * RateIt: A recommender system, based on MovieLens and "suggest".
 * Store user ratings per pagename. The wikilens theme displays a navbar image bar
 * with some nice javascript magic and this plugin shows various recommendations.
 *
 * There should be two methods to store ratings:
 * In a SQL database as in wikilens http://dickens.cs.umn.edu/dfrankow/wikilens
 *
 * The most important fact: A page has more than one rating. There can
 * be (and will be!) many ratings per page (ratee): different raters
 * (users), in different dimensions. Are those stored per page
 * (ratee)? Then what if I wish to access the ratings per rater
 * (user)?
 * wikilens plans several user-centered applications like:
 * a) show my ratings
 * b) show my buddies' ratings
 * c) show how my ratings are like my buddies'
 * d) show where I agree/disagree with my buddy
 * e) show what this group of people agree/disagree on
 *
 * If the ratings are stored in a real DB in a table, we can index the
 * ratings by rater and ratee, and be confident in
 * performance. Currently MovieLens has 80,000 users, 7,000 items,
 * 10,000,000 ratings. This is an average of 1400 ratings/page if each
 * page were rated equally. However, they're not: the most popular
 * things have tens of thousands of ratings (e.g., "Pulp Fiction" has
 * 42,000 ratings). If ratings are stored per page, you would have to
 * save/read huge page metadata every time someone submits a
 * rating. Finally, the movie domain has an unusually small number of
 * items-- I'd expect a lot more in music, for example.
 *
 * For a simple rating system one can also store the rating in the page
 * metadata (default).
 *
 * Recommender Engines:
 * Recommendation/Prediction is a special field of "Data Mining"
 * For a list of (also free) software see
 *  http://www.the-data-mine.com/bin/view/Software/WebIndex
 * - movielens: (Java Server) will be gpl'd in summer 2004 (weighted)
 * - suggest: is free for non-commercial use, available as compiled library
 *     (non-weighted)
 * - Autoclass: simple public domain C library
 * - MLC++: C++ library http://www.sgi.com/tech/mlc/
 *
 * Usage:    <<RateIt >>          just the widget without text
 *   Note: The wikilens theme or any derivate must be enabled, to enable this plugin!
 *           <<RateIt show=top >> text plus widget below
 *           <<RateIt show=ratings >> to show my ratings
 *   TODO:   <<RateIt show=buddies >> to show my buddies
 *           <<RateIt show=ratings dimension=1 >>
 *   TODO:   <<RateIt show=text >> just text, no widget, for dumps
 *
 * @author:  Dan Frankowski (wikilens author), Reini Urban (as plugin)
 *
 * TODO:
 * - finish mysuggest.c (external engine with data from mysql)
 */

require_once("lib/WikiPlugin.php");
require_once("lib/wikilens/RatingsDb.php");

class WikiPlugin_RateIt
extends WikiPlugin
{
    function getName() {
        return _("RateIt");
    }
    function getDescription() {
        return _("Rating system. Store user ratings per page");
    }

    function RatingWidgetJavascript() {
        global $WikiTheme;
        if (!empty($this->imgPrefix))
            $imgPrefix = $this->imgPrefix;
        elseif (defined("RATEIT_IMGPREFIX"))
            $imgPrefix = RATEIT_IMGPREFIX;
        else $imgPrefix = '';
        if ($imgPrefix and !$WikiTheme->_findData("images/RateIt".$imgPrefix."Nk0.png",1))
            $imgPrefix = '';
        $img   = substr($WikiTheme->_findData("images/RateIt".$imgPrefix."Nk0.png"),0,-7);
        $urlprefix = WikiURL("",0,1); // TODO: check actions USE_PATH_INFO=false
        $js_globals = "var rateit_imgsrc = '".$img."';
var rateit_action = '".urlencode("RateIt")."';
";
        $WikiTheme->addMoreHeaders
                (JavaScript('',
                            array('src' => $WikiTheme->_findData('themes/wikilens/wikilens.js'))));
        return JavaScript($js_globals);
    }

    function actionImgPath() {
        global $WikiTheme;
        return $WikiTheme->_findFile("images/RateItAction.png", 1);
    }

    /**
     * Take a string and quote it sufficiently to be passed as a Javascript
     * string between ''s
     */
    function _javascript_quote_string($s) {
        return str_replace("'", "\'", $s);
    }

    function getDefaultArguments() {
        return array( 'pagename'  => '[pagename]',
                      'version'   => false,
                      'id'        => 'rateit',
                      'imgPrefix' => '',      // '' or BStar or Star
                      'dimension' => false,
                      'small'     => false,
                      'show'      => false,
                      'mode'      => false,
                      );
    }

    function head() { // early side-effects (before body)
        global $WikiTheme;
        static $_already;
        if (!empty($_already)) return;
        $_already = 1;
        $WikiTheme->addMoreHeaders(JavaScript(
"var prediction = new Array; var rating = new Array;
var avg = new Array; var numusers = new Array;
var msg_rating_votes = '"._("Rating: %.1f (%d votes)")."';
var msg_curr_rating = '"._("Your current rating: ")."';
var msg_curr_prediction = '"._("Your current prediction: ")."';
var msg_chg_rating = '"._("Change your rating from ")."';
var msg_to = '"._(" to ")."';
var msg_add_rating = '"._("Add your rating: ")."';
var msg_thanks = '"._("Thanks!")."';
var msg_rating_deleted = '"._("Rating deleted!")."';
"));
        $WikiTheme->addMoreHeaders($this->RatingWidgetJavascript());
    }

    function displayActionImg ($mode) {
        global $WikiTheme, $request;
        if (!empty($request->_is_buffering_output))
            ob_end_clean();  // discard any previous output
        // delete the cache
        $page = $request->getPage();
        //$page->set('_cached_html', false);
        $request->cacheControl('MUST-REVALIDATE');
        $dbi = $request->getDbh();
        $dbi->touch();
        //fake validators without args
        $request->appendValidators(array('wikiname' => WIKI_NAME,
                                         'args'     => wikihash('')));
        $request->discardOutput();
        $actionImg = $WikiTheme->_path . $this->actionImgPath();
        if (file_exists($actionImg)) {
            header('Content-type: image/png');
            readfile($actionImg);
        } else {
            header('Content-type: image/png');
            echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACAQMAAABIeJ9nAAAAA1BMVEX///'
                     .'+nxBvIAAAAAXRSTlMAQObYZgAAABNJREFUeF4NwAEBAAAAgJD+r5YGAAQAAXHhfPAAAAAASUVORK5CYII=');
        }
        exit;
    }

    // Only for signed users done in template only yet.
    function run($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;
        //$this->_request = & $request;
        //$this->_dbi = & $dbi;
        $user = $request->getUser();
        //FIXME: fails on test with DumpHtml:RateIt
        if (!is_object($user)) {
            return HTML::raw('');
        }
        $this->userid = $user->getId();
        if (!$this->userid) {
            return HTML::raw('');
        }
        $args = $this->getArgs($argstr, $request);
        $this->dimension = $args['dimension'];
        $this->imgPrefix = $args['imgPrefix'];
        if ($this->dimension == '') {
            $this->dimension = 0;
            $args['dimension'] = 0;
        }
        if ($args['pagename']) {
            // Expand relative page names.
            $page = new WikiPageName($args['pagename'], $basepage);
            $args['pagename'] = $page->name;
        }
        if (empty($args['pagename'])) {
            return $this->error(_("no page specified"));
        }
        $this->pagename = $args['pagename'];

        $rdbi = RatingsDb::getTheRatingsDb();
        $this->_rdbi =& $rdbi;

        if ($args['mode'] === 'add') {
            //if (!$user->isSignedIn()) return $this->error(_("You must sign in"));
            $this->rating = $request->getArg('rating');
            $rdbi->addRating($this->rating, $this->userid, $this->pagename, $this->dimension);
            $this->displayActionImg('add');

        } elseif ($args['mode'] === 'delete') {
            //if (!$user->isSignedIn()) return $this->error(_("You must sign in"));
            $rdbi->deleteRating($this->userid, $this->pagename, $this->dimension);
            unset($this->rating);
            $this->displayActionImg('delete');
        } elseif (! $args['show'] ) {
            return $this->RatingWidgetHtml($args['pagename'], $args['version'], $args['imgPrefix'],
                                           $args['dimension'], $args['small']);
        } else {
            //if (!$user->isSignedIn()) return $this->error(_("You must sign in"));
            //extract($args);
            $this->rating   = $rdbi->getRating($this->userid, $this->pagename, $this->dimension);
            $this->avg      = $rdbi->getAvg($this->pagename, $this->dimension);
            $this->numusers = $rdbi->getNumUsers($this->pagename, $this->dimension);
            // Update this text on rateit in javascript. needed: NumUsers, Avg
            $html = HTML::div
                (
                 HTML::span(array('class' => 'rateit'),
                            sprintf(_("Rating: %.1f (%d votes)"),
                                    $this->avg, $this->numusers)));
            if ($args['show'] == 'top') {
                if (ENABLE_PAGE_PUBLIC) {
                    $page = $dbi->getPage($this->pagename);
                    if ($page->get('public'))
                        $html->setAttr('class', "public");
                }
                $html->setAttr('id', "rateit-widget-top");
                $html->pushContent(HTML::br(),
                                   $this->RatingWidgetHtml($args['pagename'], $args['version'],
                                                           $args['imgPrefix'],
                                                           $args['dimension'], $args['small']));
            } elseif ($args['show'] == 'text') {
                if (!$WikiTheme->DUMP_MODE)
                    $html->pushContent(HTML::br(),
                                       sprintf(_("Your rating was %.1f"),
                                               $this->rating));
            } elseif ($this->rating) {
                $html->pushContent(HTML::br(),
                                   sprintf(_("Your rating was %.1f"),
                                           $this->rating));
            } else {
                    $this->pred = $rdbi->getPrediction($this->userid, $this->pagename, $this->dimension);
                    if (is_string($this->pred))
                    $html->pushContent(HTML::br(),
                                       sprintf(_("Prediction: %s"),
                                               $this->pred));
                elseif ($this->pred)
                    $html->pushContent(HTML::br(),
                                       sprintf(_("Prediction: %.1f"),
                                               $this->pred));
            }
            //$html->pushContent(HTML::p());
            //$html->pushContent(HTML::em("(Experimental: This might be entirely bogus data)"));
            return $html;
        }
    }

    // box is used to display a fixed-width, narrow version with common header
    function box($args=false, $request=false, $basepage=false) {
        if (!$request) $request =& $GLOBALS['request'];
        if (!$request->_user->isSignedIn()) return;
        if (!isset($args)) $args = array();
        $args['small'] = 1;
        $argstr = '';
        foreach ($args as $key => $value)
            $argstr .= $key."=".$value;
        $widget = $this->run($request->_dbi, $argstr, $request, $basepage);

        return $this->makeBox(WikiLink(_("RateIt"),'',_("Rate It")),
                              $widget);
    }

    /**
     * HTML widget display
     *
     * This needs to be put in the <body> section of the page.
     *
     * @param pagename    Name of the page to rate
     * @param version     Version of the page to rate (may be "" for current)
     * @param imgPrefix   Prefix of the names of the images that display the rating
     *                    You can have two widgets for the same page displayed at
     *                    once iff the imgPrefix-s are different.
     * @param dimension   Id of the dimension to rate
     * @param small       Makes a smaller ratings widget if non-false
     *
     * Limitations: Currently this can only print the current users ratings.
     *              And only the widget, but no value (for buddies) also.
     */
    function RatingWidgetHtml($pagename, $version, $imgPrefix, $dimension, $small = false) {
        global $WikiTheme, $request;

        $dbi =& $request->_dbi;
        $version = $dbi->_backend->get_latest_version($pagename);
        $pageid = sprintf("%u",crc32($pagename)); // MangleXmlIdentifier($pagename)
        $imgId = 'RateIt' . $pageid;
        $actionImgName = 'RateIt'.$pageid.'Action';

        //$rdbi =& $this->_rdbi;
        $rdbi = RatingsDb::getTheRatingsDb();

        // check if the imgPrefix icons exist.
        if (! $WikiTheme->_findData("images/RateIt".$imgPrefix."Nk0.png", true))
            $imgPrefix = '';

        // Protect against \'s, though not \r or \n
        $reImgPrefix = $this->_javascript_quote_string($imgPrefix);
        $reImgId     = $this->_javascript_quote_string($imgId);
        $reActionImgName = $this->_javascript_quote_string($actionImgName);
        $rePagename      = $this->_javascript_quote_string($pagename);
        //$dimension = $args['pagename'] . "rat";

        $html = HTML::span(array("class" => "rateit-widget", "id" => $imgId));
        for ($i=0; $i < 2; $i++) {
            $ok[$i] = $WikiTheme->_findData("images/RateIt".$imgPrefix."Ok".$i.".png"); // empty
            $nk[$i] = $WikiTheme->_findData("images/RateIt".$imgPrefix."Nk".$i.".png"); // rated
            $rk[$i] = $WikiTheme->_findData("images/RateIt".$imgPrefix."Rk".$i.".png"); // pred
        }

        if (empty($this->userid)) {
            $user = $request->getUser();
            $this->userid = $user->getId();
        }
        if (empty($this->rating)) {
            $this->rating = $rdbi->getRating($this->userid, $pagename, $dimension);
            if (!$this->rating and empty($this->pred)) {
                $this->pred = $rdbi->getPrediction($this->userid, $pagename, $dimension);
            }
        }

        for ($i = 1; $i <= 10; $i++) {
            $j = $i / 2;
            $a1 = HTML::a(array('href' => "javascript:clickRating('$reImgPrefix','$rePagename','$version',"
                                ."'$reImgId','$dimension',$j)"));
            $img_attr = array();
            $img_attr['src'] = $nk[$i%2];
            if ($this->rating) {
                $img_attr['src'] = $ok[$i%2];
                $img_attr['onmouseover'] = "displayRating('$reImgId','$reImgPrefix',$j,0,1)";
                $img_attr['onmouseout']  = "displayRating('$reImgId','$reImgPrefix',$this->rating,0,1)";
            }
            else if (!$this->rating and $this->pred) {
                $img_attr['src'] = $rk[$i%2];
                $img_attr['onmouseover'] = "displayRating('$reImgId','$reImgPrefix',$j,1,1)";
                $img_attr['onmouseout']  = "displayRating('$reImgId','$reImgPrefix',$this->pred,1,1)";
            }
            else {
                $img_attr['onmouseover'] = "displayRating('$reImgId','$reImgPrefix',$j,0,1)";
                $img_attr['onmouseout']  = "displayRating('$reImgId','$reImgPrefix',0,0,1)";
            }
            //$imgName = 'RateIt'.$reImgId.$i;
            $img_attr['id'] = $imgId . $i;
            $img_attr['alt'] = $img_attr['id'];
            $a1->pushContent(HTML::img($img_attr));
            //$a1->addToolTip(_("Rate the topic of this page"));
            $html->pushContent($a1);

            //This adds a space between the rating smilies:
            //if (($i%2) == 0) $html->pushContent("\n");
        }
        $html->pushContent(HTML::Raw("&nbsp;"));

        $a0 = HTML::a(array('href' => "javascript:clickRating('$reImgPrefix','$rePagename','$version',"
                            ."'$reImgId','$dimension','X')"));
        $msg = _("Cancel your rating");
        $imgprops = array('src'   => $WikiTheme->getImageUrl("RateIt".$imgPrefix."Cancel"),
                          'id'    => $imgId.$imgPrefix.'Cancel',
                          'alt'   => $msg,
                          'title' => $msg);
        if (!$this->rating)
            $imgprops['style'] = 'display:none';
        $a0->pushContent(HTML::img($imgprops));
        $a0->addToolTip($msg);
        $html->pushContent($a0);

        /*} elseif ($pred) {
            $msg = _("No opinion");
            $html->pushContent(HTML::img(array('src' => $WikiTheme->getImageUrl("RateItCancelN"),
                                               'id'  => $imgPrefix.'Cancel',
                                               'alt' => $msg)));
            //$a0->addToolTip($msg);
            //$html->pushContent($a0);
        }*/
        $img_attr = array();
        $img_attr['src'] = $WikiTheme->_findData("images/spacer.png");
        $img_attr['id'] = $actionImgName;
        $img_attr['alt'] = $img_attr['id'];
        $img_attr['height'] = 15;
        $img_attr['width'] = 20;
        $html->pushContent(HTML::img($img_attr));

        // Display your current rating if there is one, or the current prediction
        // or the empty widget.
        $pred = empty($this->pred) ? 0 : $this->pred;
        $js = '';
        if (!empty($this->avg))
            $js .= "avg['$reImgId']=$this->avg; numusers['$reImgId']=$this->numusers;\n";
        if ($this->rating) {
            $js .= "rating['$reImgId']=$this->rating; prediction['$reImgId']=$pred;\n";
            $html->pushContent(JavaScript($js
                    ."displayRating('$reImgId','$reImgPrefix',$this->rating,0,1);"));
        } elseif (!empty($this->pred)) {
            $js .= "rating['$reImgId']=0; prediction['$reImgId']=$this->pred;\n";
            $html->pushContent(JavaScript($js
                    ."displayRating('$reImgId','$reImgPrefix',$this->pred,1,1);"));
        } else {
            $js .= "rating['$reImgId']=0; prediction['$reImgId']=0;\n";
            $html->pushContent(JavaScript($js
                    ."displayRating('$reImgId','$reImgPrefix',0,0,1);"));
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
