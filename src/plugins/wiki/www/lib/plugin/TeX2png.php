<?php

/*
 * Copyright (C) Copyright 2004 Pierrick Meignen
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
 * This is a simple version of the original TexToPng plugin which uses
 * the powerful plugincached mechanism.
 * TeX2png uses its own much simplier static cache in images/tex.
 *
 * @author: Pierrick Meignen
 * TODO: use url helpers, windows fixes
 *       use a better imagepath
 */

// needs latex
// LaTeX2HTML ftp://ftp.dante.de/tex-archive/support/latex2html

class WikiPlugin_TeX2png
    extends WikiPlugin
{
    public $imagepath = 'images/tex';
    public $latexbin = '/usr/bin/latex';
    public $dvipsbin = '/usr/bin/dvips';
    public $pstoimgbin = '/usr/bin/pstoimg';

    function getDescription()
    {
        return _("Convert Tex mathematicals expressions to cached PNG files. This is for small text.");
    }

    function getDefaultArguments()
    {
        return array('text' => "$$(a + b)^2 = a^2 + 2 ab + b^2$$");
    }

    function parseArgStr($argstr)
    {
        // modified from WikiPlugin.php
        $arg_p = '\w+';
        $op_p = '(?:\|\|)?=';
        $word_p = '\S+';
        $opt_ws = '\s*';
        $qq_p = '" ( (?:[^"\\\\]|\\\\.)* ) "';
        //"<--kludge for brain-dead syntax coloring
        $q_p = "' ( (?:[^'\\\\]|\\\\.)* ) '";
        $gt_p = "_\\( $opt_ws $qq_p $opt_ws \\)";
        $argspec_p = "($arg_p) $opt_ws ($op_p) $opt_ws (?: $qq_p|$q_p|$gt_p|($word_p))";

        $args = array();
        $defaults = array();

        while (preg_match("/^$opt_ws $argspec_p $opt_ws/x", $argstr, $m)) {
            @ list(, $arg, $op, $qq_val, $q_val, $gt_val, $word_val) = $m;
            $argstr = substr($argstr, strlen($m[0]));

            // Remove quotes from string values.
            if ($qq_val)
                // we don't remove backslashes in tex formulas
                // $val = stripslashes($qq_val);
                $val = $qq_val;
            elseif ($q_val)
                $val = stripslashes($q_val); elseif ($gt_val)
                $val = _(stripslashes($gt_val)); else
                $val = $word_val;

            if ($op == '=') {
                $args[$arg] = $val;
            } else {
                // NOTE: This does work for multiple args. Use the
                // separator character defined in your webserver
                // configuration, usually & or &amp; (See
                // http://www.htmlhelp.com/faq/cgifaq.4.html)
                // e.g. <plugin RecentChanges days||=1 show_all||=0 show_minor||=0>
                // url: RecentChanges?days=1&show_all=1&show_minor=0
                assert($op == '||=');
                $defaults[$arg] = $val;
            }
        }

        if ($argstr) {
            $this->handle_plugin_args_cruft($argstr, $args);
        }

        return array($args, $defaults);
    }

    function createTexFile($texfile, $text)
    {
        // this is the small latex file
        // which contains only the mathematical
        // expression
        $fp = fopen($texfile, 'w');
        $str = "\documentclass{article}\n";
        $str .= "\usepackage{amsfonts}\n";
        $str .= "\usepackage{amssymb}\n";
        // Here tou can add some package in order
        // to produce more sophisticated output
        $str .= "\pagestyle{empty}\n";
        $str .= "\begin{document}\n";
        $str .= $text . "\n";
        $str .= "\end{document}";
        fwrite($fp, $str);
        fclose($fp);
        return 0;
    }

    function createPngFile($imagepath, $imagename)
    {
        // to create dvi file from the latex file
        $commandes = $this->latexbin . " temp.tex;";
        exec("cd $imagepath;$commandes");
        // to create png file from the dvi file
        // there is no option but it is possible
        // to add one (scale for example)
        if (file_exists("$imagepath/temp.dvi")) {
            $commandes = $this->dvipsbin . " temp.dvi -o temp.ps;";
            $commandes .= $this->pstoimgbin . " -type png -margins 0,0 ";
            $commandes .= "-crop a -geometry 600x300 ";
            $commandes .= "-aaliastext -color 1 -scale 1.5 ";
            $commandes .= "temp.ps -o " . $imagename;
            exec("cd $imagepath;$commandes");
            unlink("$imagepath/temp.dvi");
            unlink("$imagepath/temp.ps");
        } else
            echo _(" (syntax error for latex) ");
        // to clean the directory
        unlink("$imagepath/temp.tex");
        unlink("$imagepath/temp.aux");
        unlink("$imagepath/temp.log");
        return 0;
    }

    function isMathExp(&$text)
    {
        // this function returns
        // 0 : text is too long or not a mathematical expression
        // 1 : text is $xxxxxx$ hence in line
        // 2 : text is $$xxxx$$ hence centered
        $last = strlen($text) - 1;
        if ($last >= 250) {
            $text = "Too long !";
            return 0;
        } elseif ($last <= 1 || strpos($text, '$') != 0) {
            return 0;
        } elseif (strpos($text, '$', 1) == $last)
            return 1; elseif ($last > 3 &&
            strpos($text, '$', 1) == 1 &&
            strpos($text, '$', 2) == $last - 1
        )
            return 2;
        return 0;
    }

    function tex2png($text)
    {
        // the name of the png cached file
        $imagename = md5($text) . ".png";
        $url = $this->imagepath . "/$imagename";

        if (!file_exists($url)) {
            if (is_writable($this->imagepath)) {
                $texfile = $this->imagepath . "/temp.tex";
                $this->createTexFile($texfile, $text);
                $this->createPngFile($this->imagepath, $imagename);
            } else {
                $error_html = _("TeX imagepath not writable.");
                trigger_error($error_html, E_USER_NOTICE);
            }
        }

        // there is always something in the html page
        // even if the tex directory doesn't exist
        // or mathematical expression is wrong
        switch ($this->isMathExp($text)) {
            case 0: // not a mathematical expression
                $html = HTML::tt(array('class' => 'tex',
                    'style' => 'color:red;'), $text);
                break;
            case 1: // an inlined mathematical expression
                $html = HTML::img(array('class' => 'tex',
                    'src' => $url,
                    'alt' => $text));
                break;
            case 2: // mathematical expression on separate line
                $html = HTML::img(array('class' => 'tex',
                    'src' => $url,
                    'alt' => $text));
                $html = HTML::div(array('align' => 'center'), $html);
                break;
            default:
                break;
        }

        return $html;
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        // from text2png.php
        if ((function_exists('ImageTypes') and (ImageTypes() & IMG_PNG))
            or function_exists("ImagePNG")
        ) {
            // we have gd & png so go ahead.
            extract($this->getArgs($argstr, $request));
            return $this->tex2png($text);
        } else {
            // we don't have png and/or gd.
            $error_html = _("Sorry, this version of PHP cannot create PNG image files.");
            $link = "http://www.php.net/manual/pl/ref.image.php";
            $error_html .= sprintf(_("See %s"), $link) . ".";
            trigger_error($error_html, E_USER_NOTICE);
            return HTML::p($error_html);
        }
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
