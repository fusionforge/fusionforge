<?php // -*-php-*-
// $Id: MediawikiTable.php 8071 2011-05-18 14:56:14Z vargenau $
/*
 * Copyright (C) 2003 Sameer D. Sahasrabuddhe
 * Copyright (C) 2005 $ThePhpWikiProgrammingTeam
 * Copyright (C) 2008-2010 Marc-Etienne Vargenau, Alcatel-Lucent
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The MediawikiTablePlugin ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

/**
 * MediawikiTablePlugin
 * A PhpWiki plugin that allows insertion of tables using a Mediawiki-like
 * syntax.
*/
class WikiPlugin_MediawikiTable
extends WikiPlugin
{
    function getName() {
        return _("MediawikiTable");
    }

    function getDescription() {
      return _("Layout tables using a Mediawiki-like markup style.");
    }

    function getDefaultArguments() {
        return array();
    }

    function run($dbi, $argstr, &$request, $basepage) {
        include_once("lib/BlockParser.php");
        // MediawikiTablePlugin markup is new.
        $markup = 2.0;

        // We allow the compact Mediawiki syntax with:
        // - multiple cells on the same line (separated by "||"),
        // - multiple header cells on the same line (separated by "!!").
        $argstr = str_replace("||", "\n| ", $argstr);
        $argstr = str_replace("!!", "\n! ", $argstr);

        $lines = explode("\n", $argstr);

        $table = HTML::table();
        $caption = HTML::caption();
        $thead = HTML::thead();
        $tbody = HTML::tbody();

        // Do we need a <thead>?
        // 0 = unknown
        // 1 = inside (parsing cells)
        // 2 = false (no thead, only tbody)
        // 3 = true (there is a thead)
        $theadstatus = 0;

        // We always generate an Id for the table.
        // This is convenient for tables of class "sortable".
        // If user provides an Id, the generated Id will be overwritten below.
        $table->setAttr("id", GenerateId("MediawikiTable"));

        if (substr($lines[0],0,2) == "{|") {
            // Start of table
            $lines[0] = substr($lines[0],2);
        }
        if (($lines[0][0] != '|') and ($lines[0][0] != '!')) {
            $line = array_shift($lines);
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

        if (count($lines) == 1) { // empty table, we only have closing "|}" line
            return HTML::raw('');
        }

        foreach ($lines as $line) {
            if (substr($line,0,2) == "|}") {
                // End of table
                continue;
            }
            if (substr($line,0,2) == "|-") {
                if (isset($row)) {
                    if (isset($cell)) {
                        if (isset($content)) {
                            if (is_numeric(trim($content))) {
                                $cell->pushContent(HTML::p(array('style' => "text-align:right"), trim($content)));
                            } else {
                                $cell->pushContent(TransformText(trim($content), $markup, $basepage));
                            }
                            unset($content);
                        }
                        $row->pushContent($cell);
                        unset($cell);
                    }
                    if (!empty($row->_content)) {
                        if ($theadstatus == 1) { // inside
                            $theadstatus = 3; // true
                            $thead->pushContent($row);
                        } else {
                            $tbody->pushContent($row);
                        }
                    }
                }
                $row = HTML::tr();
                $attrs = parse_attributes(substr($line,2));
                foreach ($attrs as $key => $value) {
                    if (in_array ($key, array("id", "class", "title", "style",
                                              "bgcolor", "align", "valign"))) {
                        $row->setAttr($key, $value);
                    }
                }
                continue;
            }

            // Table summary
            if (substr($line,0,2) == "|=") {
                $line = substr($line,2);
                $table->setAttr("summary", trim($line));
            }

            // Table caption
            if (substr($line,0,2) == "|+") {

                $line = substr($line,2);
                $pospipe = strpos($line, "|");
                $posbracket = strpos($line, "[");
                if (($pospipe !== false) && (($posbracket === false) || ($posbracket > $pospipe))) {
                    $attrs = parse_attributes(substr($line, 0, $pospipe));
                    foreach ($attrs as $key => $value) {
                        if (in_array ($key, array("id", "class", "title", "style",
                                                  "align", "lang"))) {
                            $caption->setAttr($key, $value);
                        }
                    }
                    $line=substr($line, $pospipe+1);
                }

                $caption->setContent(TransformInline(trim($line)));
            }

            if (((substr($line,0,1) == "|") or (substr($line,0,1) == "!")) and isset($row)) {
                if (isset($cell)) {
                    if (isset ($content)) {
                        if (is_numeric(trim($content))) {
                            $cell->pushContent(HTML::p(array('style' => "text-align:right"), trim($content)));
                        } else {
                            $cell->pushContent(TransformText(trim($content), $markup, $basepage));
                        }
                        unset($content);
                    }
                    $row->pushContent($cell);
                }
                if (substr($line,0,1) == "!") {
                    if ($theadstatus == 0) { // unknown
                        $theadstatus = 1; // inside
                    }
                    $cell = HTML::th();   // Header
                } else {
                    if ($theadstatus == 1) { // inside
                        $theadstatus = 2; // false
                    }
                    $cell = HTML::td();
                }
                $line = substr($line, 1);

                // If there is a "|" in the line, the start of line
                // (before the "|") is made of attributes.
                // The end of the line (after the "|") is the cell content
                // This is not true if the pipe is inside [], {{}} or {{{}}}
                // | [foo|bar]
                // The following cases must work:
                // | foo
                // | [foo|bar]
                // | class="xxx" | foo
                // | class="xxx" | [foo|bar]
                // | {{tmpl|arg=val}}
                // | {{image.png|alt}}
                // | {{{ xxx | yyy }}}
                $pospipe = strpos($line, "|");
                $posbracket = strpos($line, "[");
                $poscurly = strpos($line, "{");
                if (($pospipe !== false) && (($posbracket === false) || ($posbracket > $pospipe)) && (($poscurly === false) || ($poscurly > $pospipe))) {
                    $attrs = parse_attributes(substr($line, 0, $pospipe));
                    foreach ($attrs as $key => $value) {
                        if (in_array ($key, array("id", "class", "title", "style", "scope",
                                                  "colspan", "rowspan", "width", "height",
                                                  "bgcolor", "align", "valign"))) {
                            $cell->setAttr($key, $value);
                        }
                    }
                    $line=substr($line, $pospipe+1);
                    if (is_numeric(trim($line))) {
                        $cell->pushContent(HTML::p(array('style' => "text-align:right"), trim($line)));
                    } else {
                        $cell->pushContent(TransformText(trim($line), $markup, $basepage));
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
                        $cell->pushContent(TransformText(trim($content), $markup, $basepage));
                    }

                }
                $row->pushContent($cell);
            }
            // If user put and extra "|-" without cells just before "|}"
            // we ignore it to get valid XHTML code
            if (!empty($row->_content)) {
                $tbody->pushContent($row);
            }
        }
        if (!empty($caption->_content)) {
            $table->pushContent($caption);
        }
        if (!empty($thead->_content)) {
            $table->pushContent($thead);
        }
        if (!empty($tbody->_content)) {
            $table->pushContent($tbody);
        }
        if (!empty($table->_content)) {
            return $table;
        } else {
            return HTML::raw('');
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
?>
