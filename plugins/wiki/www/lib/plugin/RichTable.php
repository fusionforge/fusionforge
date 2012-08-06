<?php // -*-php-*-
// $Id: RichTable.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright (C) 2003 Sameer D. Sahasrabuddhe
 * Copyright (C) 2005 $ThePhpWikiProgrammingTeam
 * Copyright (C) 2008-2009 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * RichTablePlugin
 * A PhpWiki plugin that allows insertion of tables using a richer syntax.
*/

class WikiPlugin_RichTable
extends WikiPlugin
{
    function getName() {
        return _("RichTable");
    }

    function getDescription() {
      return _("Layout tables using a very rich markup style.");
    }

    function getDefaultArguments() {
        return array();
    }

    function run($dbi, $argstr, &$request, $basepage) {
            global $WikiTheme;
        include_once("lib/BlockParser.php");
        // RichTablePlugin markup is new.
        $markup = 2.0;

        $lines = preg_split('/\n/', $argstr);
        $table = HTML::table();

        if ($lines[0][0] == '*') {
            $line = substr(array_shift($lines),1);
            $attrs = parse_attributes($line);
            foreach ($attrs as $key => $value) {
                if (in_array ($key, array("id", "class", "title", "style",
                                          "bgcolor", "frame", "rules", "border",
                                          "cellspacing", "cellpadding",
                                          "summary", "align", "width"))) {
                    $table->setAttr($key, $value);
                }
            }
        }

        foreach ($lines as $line){
            if (substr($line,0,1) == "-") {
                if (isset($row)) {
                    if (isset($cell)) {
                        if (isset($content)) {
                            if (is_numeric(trim($content))) {
                                $cell->pushContent(HTML::p(array('style' => "text-align:right"), trim($content)));
                            } else {
                                $cell->pushContent(TransformText($content, $markup, $basepage));
                            }
                            unset($content);
                        }
                        $row->pushContent($cell);
                        unset($cell);
                    }
                    $table->pushContent($row);
                }
                $row = HTML::tr();
                $attrs = parse_attributes(substr($line,1));
                foreach ($attrs as $key => $value) {
                    if (in_array ($key, array("id", "class", "title", "style",
                                              "bgcolor", "align", "valign"))) {
                        $row->setAttr($key, $value);
                    }
                }
                continue;
            }
            if (substr($line,0,1) == "|" and isset($row)) {
                if (isset($cell)) {
                    if (isset ($content)) {
                        if (is_numeric(trim($content))) {
                            $cell->pushContent(HTML::p(array('style' => "text-align:right"), trim($content)));
                        } else {
                            $cell->pushContent(TransformText($content, $markup, $basepage));
                        }
                        unset($content);
                    }
                    $row->pushContent($cell);
                }
                $cell = HTML::td();
                $line = substr($line, 1);
                if ($line[0] == "*" ) {
                    $attrs = parse_attributes(substr($line,1));
                    foreach ($attrs as $key => $value) {
                        if (in_array ($key, array("id", "class", "title", "style",
                                                  "colspan", "rowspan", "width", "height",
                                                  "bgcolor", "align", "valign"))) {
                            $cell->setAttr($key, $value);
                        }
                    }
                    continue;
                }
            }
            if (isset($row) and isset($cell)) {
                $line = str_replace("?\>", "?>", $line);
                $line = str_replace("\~", "~", $line);
                if (empty($content)) $content = '';
                $content .= $line . "\n";
            }
        }
        if (isset($row)) {
            if (isset($cell)) {
                if (isset($content)) {
                    if (is_numeric(trim($content))) {
                        $cell->pushContent(HTML::p(array('style' => "text-align:right"), trim($content)));
                    } else {
                        $cell->pushContent(TransformText($content, $markup, $basepage));
                    }
                }
                $row->pushContent($cell);
            }
            $table->pushContent($row);
        }
        return $table;
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
