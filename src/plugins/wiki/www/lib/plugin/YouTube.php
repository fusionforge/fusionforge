<?php // -*-php-*-
// rcs_id('$Id: YouTube.php 7638 2010-08-11 11:58:40Z vargenau $');
/*
 * Copyright 2007 Reini Urban
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * Embed YouTube videos
 *
 * Browse: Daily pick, Most Recent, Most Viewed, Top Rated, Most Discussed, Top Favorites, Most Linked,
 *         Recently Featured, Most Responded, Watch on Mobile
 * Time:   Today, This Week, This Month, All Time
 * Category: All, Autos & Vehicles, Comedy, Entertainment, Film & Animation,
 *         Gadgets & Games, Howto & DIY, Music, News & Politics, People & Blogs, Pets & Animals,
 *         Sports, Travel & Places
 * Language: All, English, Spanish, Japanese, German, Chinese, French
 * @author: Reini Urban
 */

class WikiPlugin_YouTube
extends WikiPlugin
{
    function getName () {
        return _("YouTube");
    }

    function getDescription () {
        return _("Embed YouTube videos");
    }

    function getDefaultArguments() {
        return array('v' => "",
                     'browse' => '',      // see above
                     'time'   => '',      // only if browse
                     'category' => '',    // only if browse
                     'language' => '',    // only if browse
                     'index'    => 0,     // only if browse
                     'style' => 'inline', // or link. link links to youtube.
                     'size'  => 'medium', // or large, medium or small
                     'autoplay' => 0,
                     'width' => "425",
                     'height' => "350");
    }

    function run($dbi, $argstr, &$request, $basepage) {
        $args = $this->getArgs($argstr, $request);
        extract($args);
        if (empty($args['v'])) {
            if (empty($args['browse']))
                return $this->error(fmt("Required argument %s missing", "v"));
            $this->_browse = array("Most Recent"   => "mr",
                                       "Most Viewed"   => "mp",
                                       "Top Rated"     => "tr",
                                       "Most Discussed"=> "md",
                                   "Top Favorites" => "mf",
                                   "Most Linked"   => "mrd",
                                   "Recently Featured"=> "rf",
                                   "Most Responded"   => "ms",
                                   "Watch on Mobile"  => "mv");
            $this->browse   = array_keys($this->_browse);
            array_unshift($this->browse, "Daily Pick");
            $this->_time    = array("Today" => "t",
                                    "This Week" => "w",
                                    "This Month" => "m",
                                    "All Time" => "a");
            $this->_category = array("All"                 => "0",
                                     "Autos & Vehicles" => "2",
                                     "Comedy"                 => "23",
                                     "Entertainment"         => "24",
                                     "Film & Animation" => "1",
                                     "Gadgets & Games"         => "20",
                                     "Howto & DIY"         => "26",
                                     "Music"                 => "10",
                                     "News & Politics"         => "25",
                                     "People & Blogs"         => "22",
                                     "Pets & Animals"         => "15",
                                     "Sports"                 => "17",
                                     "Travel & Places"         => "19");
            $this->_language = array("All"     => "",
                                     "English" => "EN",
                                     "Spanish" => "ES",
                                     "Japanese"=> "JA",
                                     "German"  => "DE",
                                     "Chinese" => "CN",
                                     "French"  => "FR");
            if (!in_array($browse,$this->browse))
                return $this->error(fmt("Invalid argument %s", "browse"));
            if ($time and !in_array($time,array_keys($this->_time)))
                return $this->error(fmt("Invalid argument %s", "time"));
            if ($category and !in_array($category,$this->category))
                return $this->error(fmt("Invalid argument %s", "category"));
            if ($language and !in_array($language,$this->language))
                return $this->error(fmt("Invalid argument %s", "language"));
            if ($browse == "Daily Pick")
                $v = $this->Daily_pick();
            else {
                $s = $this->_browse[$browse];
                $t = $time ? $this->_time[$time] : 't';
                $c = $category ? $this->_category[$category] : '0';
                $l = $language ? $this->_language[$language] : '';
                    $url = "http://www.youtube.com/browse?s=$s&t=$t&c=$c&l=$l";
                $m = array('','');
                if ($xml = url_get_contents($url)) {
                    if ($index) {
                        if (preg_match_all('/<div class="vtitle">.*?\n.*?<a href="\/watch\?v=(\w+)" onclick=/s', $xml, $m))
                            $v = $m[1][$index];
                    }
                    else {
                        if (preg_match('/<div class="vtitle">.*?\n.*?<a href="\/watch\?v=(\w+)" onclick=/s', $xml, $m))
                            $v = $m[1];
                    }
                }
            }
        }
        // sanify check
        if (strlen($v) < 10 or strlen($v) > 12)
            return $this->error(fmt("Invalid argument %s", "v"));
        if (strcspn($v,"-_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"))
            return $this->error(fmt("Invalid argument %s", "v"));
        $url = "http://www.youtube.com/v/" . $v;
        if ($autoplay)
            $url .= "?autoplay=1";
        if ($size != 'medium') {
            if ($size == 'large') { $width = 640; $height = 526; }
            elseif ($size == 'small') { $width = 240; $height = 200; }
        }
        unset($args['size']);
        unset($args['style']);
        $args['src'] = $v;
        unset($args['v']);
        if ($style == 'link') {
            if ($size == 'medium') { $width = 130; $height = 97; }
            elseif ($size == 'large') { $width = 320; $height = 240; }
            elseif ($size == 'small') { $width = 90; $height = 60; }
            // img: http://img.youtube.com/vi/KKTDRqQtPO8/2.jpg or 0.jpg
            $link = HTML::a(array('href' => $url),
                            HTML::img(array('src' => "http://img.youtube.com/vi/".
                                            $v."/".(($size == 'large')?"0":"2").".jpg",
                                            'width' => $width,
                                            'height' => $height,
                                            'alt' => "YouTube video $v")));
            return $link;
        }
        $object = HTML::object(array('class' => 'inlineobject',
                                     'width' => $width,
                                     'height' => $height,
                                     ));
        $attrs = array('data' => $url,
                       'type' => 'application/x-shockwave-flash',
                       'width' => $width,
                       'height' => $height);
        if (isBrowserSafari()) {
            return HTML::object($attrs);
        }
        $object->pushContent(HTML::param(array('name' => 'movie', 'value' => $url)));
        $object->pushContent(HTML::param(array('name' => 'wmode', 'value' => 'transparent')));
        $object->pushContent(HTML::object($attrs));
        return $object;
    }

    function Daily_pick() {
        if ($xml = url_get_contents("http://www.youtube.com/categories")) {
            if (preg_match('/<div class="heading"><b>Pick of The Day<\/b><\/div>.*?<a href="\/watch\?v=(\w+)">/s', $xml, $m))
                return $m[1];
        }
        return '';
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
