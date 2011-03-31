<?php // -*-php-*-
// $Id: CreateToc.php 7955 2011-03-03 16:41:35Z vargenau $
/*
 * Copyright 2004,2005 $ThePhpWikiProgrammingTeam
 * Copyright 2008-2010 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * CreateToc:  Create a Table of Contents and automatically link to headers
 *
 * Usage:
 *  <<CreateToc arguments>>
 * @author:  Reini Urban, Marc-Etienne Vargenau
 *
 * Known problems:
 * - MacIE will not work with jshide.
 * - it will crash with old markup and Apache2 (?)
 * - Certain corner-edges will not work with TOC_FULL_SYNTAX.
 *   I believe I fixed all of them now, but who knows?
 * - bug #969495 "existing labels not honored" seems to be fixed.
 */

if (!defined('TOC_FULL_SYNTAX'))
    define('TOC_FULL_SYNTAX', 1);

class WikiPlugin_CreateToc
extends WikiPlugin
{
    function getName() {
        return _("CreateToc");
    }

    function getDescription() {
        return _("Create a Table of Contents and automatically link to headers");
    }

    function getDefaultArguments() {
        return array('extracollapse' => 1,            // provide an entry +/- link to collapse
                     'firstlevelstyle' => 'number',   // 'number', 'letter' or 'roman'
                     'headers'   =>  "1,2,3,4,5",     // "!!!"=>h2, "!!"=>h3, "!"=>h4
                                                      // "1"=>h2, "2"=>h3, "3"=>h4, "4"=>h5, "5"=>h6
                     'indentstr' => '&nbsp;&nbsp;',
                     'jshide'    => 0,                // collapsed TOC as DHTML button
                     'liststyle' => 'dl',             // 'dl' or 'ul' or 'ol'
                     'noheader'  => 0,                // omit "Table of Contents" header
                     'notoc'     => 0,                // do not display TOC, only number headers
                     'pagename'  => '[pagename]',     // TOC of another page here?
                     'position'  => 'full',           // full, right or left
                     'width'     => '200px',
                     'with_counter' => 0,
                     'with_toclink' => 0,             // link back to TOC
                     'version'   => false,
                    );
    }
    // Initialisation of toc counter
    function _initTocCounter() {
        $counter = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0);
        return $counter;
    }

    // Update toc counter with a new title
    function _tocCounter(&$counter, $level) {
        $counter[$level]++;
        for($i = $level+1; $i <= 5; $i++) {
            $counter[$i] = 0;
        }
    }

    function _roman_counter($number) {

        $n = intval($number);
        $result = '';

        $lookup = array('C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                        'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);

        foreach ($lookup as $roman => $value) {
            $matches = intval($n / $value);
            $result .= str_repeat($roman, $matches);
            $n = $n % $value;
        }
        return $result;
    }

    function _letter_counter($number) {
        if ($number <= 26) {
            return chr(ord("A") + $number - 1);
        } else {
            return chr(ord("A") + ($number/26) - 1) . chr(ord("A") + ($number%26));
        }
    }

    // Get string corresponding to the current title
    function _getCounter(&$counter, $level, $firstlevelstyle) {
        if ($firstlevelstyle == 'roman') {
            $str= $this->_roman_counter($counter[1]);
        } else if ($firstlevelstyle == 'letter') {
            $str= $this->_letter_counter($counter[1]);
        } else {
            $str=$counter[1];
        }
        for($i = 2; $i <= 5; $i++) {
            if($counter[$i] != 0)
                $str .= '.'.$counter[$i];
        }
        return $str;
    }

    // Get HTML header corresponding to current level (level is set of ! or =)
    function _getHeader($level) {

        $count = substr_count($level,'!');
        switch ($count) {
            case 3: return "h2";
            case 2: return "h3";
            case 1: return "h4";
        }
        $count = substr_count($level,'=');
        switch ($count) {
            case 2: return "h2";
            case 3: return "h3";
            case 4: return "h4";
            case 5: return "h5";
            case 6: return "h6";
        }
        return "";
    }

    function _quote($heading) {
        if (TOC_FULL_SYNTAX ) {
            $theading = TransformInline($heading);
            if ($theading)
                return preg_quote($theading->asXML(), "/");
            else
                return XmlContent::_quote(preg_quote($heading, "/"));
        } else {
            return XmlContent::_quote(preg_quote($heading, "/"));
        }
    }

    /*
     * @param $hstart id (in $content) of heading start
     * @param $hend   id (in $content) of heading end
     */
    function searchHeader ($content, $start_index, $heading,
                           $level, &$hstart, &$hend, $basepage=false) {
        $hstart = 0;
        $hend = 0;
        $h = $this->_getHeader($level);
        $qheading = $this->_quote($heading);
        for ($j=$start_index; $j < count($content); $j++) {
            if (is_string($content[$j])) {
                if (preg_match("/<$h>$qheading<\/$h>/",
                                   $content[$j]))
                    return $j;
            }
            elseif (isa($content[$j], 'cached_link'))
            {
                if (method_exists($content[$j],'asXML')) {
                    $content[$j]->_basepage = $basepage;
                    $content[$j] = $content[$j]->asXML();
                } else
                    $content[$j] = $content[$j]->asString();
                // shortcut for single wikiword or link headers
                if ($content[$j] == $heading
                    and substr($content[$j-1],-4,4) == "<$h>"
                    and substr($content[$j+1],0,5) == "</$h>")
                {
                    $hstart = $j-1;
                    $hend = $j+1;
                    return $j; // single wikiword
                }
                elseif (TOC_FULL_SYNTAX) {
                    //DONE: To allow "!! WikiWord link" or !! http://anylink/
                    // Check against joined content (after cached_plugininvocation).
                    // The first link is the anchor then.
                    if (preg_match("/<$h>(?!.*<\/$h>)/", $content[$j-1])) {
                        $hstart = $j-1;
                        $joined = '';
                        for ($k=max($j-1,$start_index);$k<count($content);$k++) {
                            if (is_string($content[$k]))
                                $joined .= $content[$k];
                            elseif (method_exists($content[$k],'asXML'))
                                $joined .= $content[$k]->asXML();
                            else
                                $joined .= $content[$k]->asString();
                            if (preg_match("/<$h>$qheading<\/$h>/",$joined)) {
                                $hend=$k;
                                return $k;
                            }
                        }
                    }
                }
            }
        }
        trigger_error("Heading <$h> $heading </$h> not found\n", E_USER_NOTICE);
        return 0;
    }

    /** prevent from duplicate anchors,
     *  beautify spaces: " " => "_" and not "x20."
     */
    function _nextAnchor($s) {
        static $anchors = array();

        $s = str_replace(' ','_',$s);
        $i = 1;
        $anchor = $s;
        while (!empty($anchors[$anchor])) {
            $anchor = sprintf("%s_%d",$s,$i++);
        }
        $anchors[$anchor] = $i;
        return $anchor;
    }

    // We have to find headers in both:
    // - classic Phpwiki syntax (lines starting with "!", "!!" or "!!!")
    // - Wikicreole syntax (lines starting with "==", "===", etc.)
    // We must omit lines starting with "!" if inside a Mediawiki table
    // (they represent a table header)
    // Feature request: proper nesting; multiple levels (e.g. 1,3)
    function extractHeaders (&$content, &$markup, $backlink=0,
                             $counter=0, $levels=false, $firstlevelstyle='number', $basepage='')
    {
        if (!$levels) $levels = array(1,2);
        $tocCounter = $this->_initTocCounter();
        reset($levels);
        sort($levels);
        $headers = array();
        $j = 0;
        $insidetable = false;
        $insideverbatim = false;
        for ($i=0; $i<count($content); $i++) {
            if (preg_match('/^\s*{\|/', $content[$i])) {
               $insidetable = true;
               continue;
            } else if (preg_match('/^\s*{{{/', $content[$i]) 
                    || preg_match('/^\s*<pre>/', $content[$i]) 
                    || preg_match('/^\s*<verbatim>/', $content[$i])) {
               $insideverbatim = true;
               continue;
            } else if (preg_match('/^\s*\|}/', $content[$i])) {
               $insidetable = false;
               continue;
            } else if (preg_match('/^\s*}}}/', $content[$i]) 
                    || preg_match('/^\s*<\/pre>/', $content[$i]) 
                    || preg_match('/^\s*<\/verbatim>/', $content[$i])) {
               $insideverbatim = false;
               continue;
            }
            if (($insidetable) || ($insideverbatim)) {
               continue;
            }
            foreach ($levels as $level) {
                if ($level < 1 or $level > 5) continue;
                $phpwikiclassiclevel = 4 -$level;
                $wikicreolelevel = $level + 1;
                $trim = trim($content[$i]);

                if ((((strpos($trim, '=') === 0))
                      && (preg_match('/^\s*(={'.$wikicreolelevel.','.$wikicreolelevel.'})([^=].*)$/', $content[$i], $match)))
                   or (((strpos($trim, '!') === 0))
                      && ((preg_match('/^\s*(!{'.$phpwikiclassiclevel.','.$phpwikiclassiclevel.'})([^!].*)$/', $content[$i], $match))))) {
                    $this->_tocCounter($tocCounter, $level);
                    if (!strstr($content[$i],'#[')) {
                        $s = trim($match[2]);
                        // If it is Wikicreole syntax, remove '='s at the end
                        if (string_starts_with($match[1], "=")) {
                            $s = trim($s, "=");
                            $s = trim($s);
                        }
                        $anchor = $this->_nextAnchor($s);
                        $manchor = MangleXmlIdentifier($anchor);
                        $texts = $s;
                        if($counter) {
                            $texts = $this->_getCounter($tocCounter, $level, $firstlevelstyle).' '.$s;
                        }
                        $headers[] = array('text' => $texts,
                                           'anchor' => $anchor,
                                           'level' => $level);
                        // Change original wikitext, but that is useless art...
                        $content[$i] = $match[1]." #[|$manchor][$s|#TOC]";
                        // And now change the to be printed markup (XmlTree):
                        // Search <hn>$s</hn> line in markup
                        /* Url for backlink */
                        $url = WikiURL(new WikiPageName($basepage,false,"TOC"));

                        $j = $this->searchHeader($markup->_content, $j, $s,
                                                 $match[1], $hstart, $hend,
                                                 $markup->_basepage);
                        if ($j and isset($markup->_content[$j])) {
                            $x = $markup->_content[$j];
                            $qheading = $this->_quote($s);
                            if ($counter)
                                 $counterString = $this->_getCounter($tocCounter, $level, $firstlevelstyle);
                            if (($hstart === 0) && is_string($markup->_content[$j])) {
                                if ($backlink) {
                                    if ($counter)
                                        $anchorString = "<a href=\"$url\" id=\"$manchor\">$counterString</a> - \$2";
                                    else
                                        $anchorString = "<a href=\"$url\" id=\"$manchor\">\$2</a>";
                                } else {
                                    $anchorString = "<a id=\"$manchor\"></a>";
                                    if ($counter)
                                        $anchorString .= "$counterString - ";
                                }
                                if ($x = preg_replace('/(<h\d>)('.$qheading.')(<\/h\d>)/',
                                                      "\$1$anchorString\$2\$3",$x,1)) {
                                    if ($backlink) {
                                        $x = preg_replace('/(<h\d>)('.$qheading.')(<\/h\d>)/',
                                                          "\$1$anchorString\$3",
                                                          $markup->_content[$j],1);
                                    }
                                    $markup->_content[$j] = $x;
                                }
                            } else {
                                $x = $markup->_content[$hstart];
                                $h = $this->_getHeader($match[1]);

                                if ($backlink) {
                                    if ($counter) {
                                        $anchorString = "\$1<a href=\"$url\" id=\"$manchor\">$counterString</a> - ";
                                    } else {
                                        /* Not possible to make a backlink on a
                                         * title with a WikiWord */
                                        $anchorString = "\$1<a id=\"$manchor\"></a>";
                                    }
                                }
                                else {
                                    $anchorString = "\$1<a id=\"$manchor\"></a>";
                                    if ($counter)
                                        $anchorString .= "$counterString - ";
                                }
                                $x = preg_replace("/(<$h>)(?!.*<\/$h>)/",
                                                  $anchorString, $x, 1);
                                if ($backlink) {
                                    $x =  preg_replace("/(<$h>)(?!.*<\/$h>)/",
                                                      $anchorString,
                                                      $markup->_content[$hstart],1);
                                }
                                $markup->_content[$hstart] = $x;
                            }
                        }
                    }
                }
            }
        }
        return $headers;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;
        extract($this->getArgs($argstr, $request));
        if ($pagename) {
            // Expand relative page names.
            $page = new WikiPageName($pagename, $basepage);
            $pagename = $page->name;
        }
        if (!$pagename) {
            return $this->error(_("No page specified."));
        }
        if (isBrowserIE() and browserDetect("Mac")) {
            $jshide = 0;
        }
        if (($notoc) or ($liststyle == 'ol')) {
            $with_counter = 1;
        }

        // Check if page exists.
        if (!($dbi->isWikiPage($pagename))) {
            return $this->error(sprintf(_("Page '%s' does not exist."), $pagename));
        }

        // Check if user is allowed to get the page.
        if (!mayAccessPage ('view', $pagename)) {
            return $this->error(sprintf(_("Illegal access to page %s: no read access"),
            $pagename));
        }

        $page = $dbi->getPage($pagename);

        if ($version) {
            $r = $page->getRevision($version);
            if ((!$r) || ($r->hasDefaultContents())) {
                return $this->error(sprintf(_("%s: no such revision %d."),
                                            $pagename, $version));
            }
        } else {
            $r = $page->getCurrentRevision();
        }

        $current = $page->getCurrentRevision();
        //FIXME: I suspect this only to crash with Apache2
        if (!$current->get('markup') or $current->get('markup') < 2) {
            if (in_array(php_sapi_name(),array('apache2handler','apache2filter'))) {
                return $this->error(_("CreateToc disabled for old markup."));
            }
        }

        $content = $r->getContent();

        $html = HTML::div(array('class' => 'toc', 'id'=> GenerateId("toc")));
        if ($notoc) {
            $html->setAttr('style','display:none;');
        }
        if (($position == "left") or ($position == "right")) {
            $html->setAttr('style','float:'.$position.'; width:'.$width.';');
        }
        $toclistid = GenerateId("toclist");
        $list = HTML::div(array('id'=>$toclistid, 'class'=>'toclist'));
        if (!strstr($headers,",")) {
            $headers = array($headers);
        } else {
            $headers = explode(",",$headers);
        }
        $levels = array();
        foreach ($headers as $h) {
            //replace !!! with level 1, ...
            if (strstr($h,"!")) {
                $hcount = substr_count($h,'!');
                $level = min(max(1, $hcount),3);
                $levels[] = $level;
            } else {
                $level = min(max(1, (int) $h), 5);
                $levels[] = $level;
            }
        }
        if (TOC_FULL_SYNTAX)
            require_once("lib/InlineParser.php");
        if ($headers = $this->extractHeaders($content, $dbi->_markup,
                                             $with_toclink, $with_counter,
                                             $levels, $firstlevelstyle, $basepage))
        {
            foreach ($headers as $h) {
                // proper heading indent
                $level = $h['level'];
                $indent = $level - 1;
                $link = new WikiPageName($pagename,$page,$h['anchor']);
                $li = WikiLink($link,'known',$h['text']);
                // Hack to suppress pagename before #
                // $li->_attr["href"] = strstr($li->_attr["href"], '#');
                $list->pushContent(HTML::p(HTML::raw
                       (str_repeat($indentstr,$indent)),$li));
            }
        }
        $list->setAttr('style','display:'.($jshide?'none;':'block;'));
        $open = DATA_PATH.'/'.$WikiTheme->_findFile("images/folderArrowOpen.png");
        $close = DATA_PATH.'/'.$WikiTheme->_findFile("images/folderArrowClosed.png");
      if ($noheader) {
      } else {
        $toctoggleid = GenerateId("toctoggle");
        if ($extracollapse)
            $toclink = HTML(_("Table of Contents"),
                            " ",
                            HTML::a(array('id'=>'TOC')),
                            HTML::img(array(
                                            'id'=>$toctoggleid,
                                            'class'=>'wikiaction',
                                            'title'=>_("Click to display to TOC"),
                                            'onclick'=>"toggletoc(this, '".$open."', '".$close."', '".$toclistid."')",
                                            'alt' => 'toctoggle',
                                            'src' => $jshide ? $close : $open )));
        else
            $toclink = HTML::a(array('id'=>'TOC',
                                     'class'=>'wikiaction',
                                     'title'=>_("Click to display"),
                                     'onclick'=>"toggletoc(this, '".$open."', '".$close."', '".$toclistid."')"),
                               _("Table of Contents"),
                               HTML::span(array('style'=>'display:none',
                                                'id'=>$toctoggleid)," "));
        $html->pushContent(HTML::p(array('class'=>'toctitle'), $toclink));
      }
      $html->pushContent($list);
      if (count($headers) == 0) {
          // Do not display an empty TOC
          $html->setAttr('style','display:none;');
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
