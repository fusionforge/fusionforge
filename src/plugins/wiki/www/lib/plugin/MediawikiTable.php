<?php // -*-php-*-
rcs_id('$Id: MediawikiTable.php 6422 2009-01-20 14:30:22Z vargenau $');
/**
  MediawikiTablePlugin
  A PhpWiki plugin that allows insertion of tables using a Mediawiki-like
  syntax.
*/
/*
 * Copyright (C) 2003 Sameer D. Sahasrabuddhe
 * Copyright (C) 2005 $ThePhpWikiProgrammingTeam
 * Copyright (C) 2008-2009 Alcatel-Lucent
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

    function getVersion() {
        return preg_replace("/[Revision: $]/", '',
                            "\$Revision: 6422 $");
    }

    function run($dbi, $argstr, &$request, $basepage) {
    	global $WikiTheme;
        include_once("lib/BlockParser.php");
        // MediawikiTablePlugin markup is new.
        $markup = 2.0;

        // We allow the compact Mediawiki syntax with:
        // - multiple cells on the same line (separated by "||"),
        // - multiple header cells on the same line (separated by "!!").
        $argstr = str_replace("||", "\n|", $argstr);
        $argstr = str_replace("!!", "\n!", $argstr);

        $lines = preg_split('/\n/', $argstr);
        $table = HTML::table();

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

        foreach ($lines as $line){
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
                    if (isset($thead)) {
                    	$thead->pushContent($row);
                    	$table->pushContent($thead);
                    	unset($thead);
                    	$tbody = HTML::tbody();
                    } else {
                    	$tbody->pushContent($row);
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

                $caption = HTML::caption();
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

                $caption->pushContent(trim($line));
                $table->pushContent($caption);
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
                    $cell = HTML::th();   // Header
                    $thead = HTML::thead();
                } else { 
                    $cell = HTML::td();
                    if (!isset($tbody)) $tbody = HTML::tbody();
                }
                $line = substr($line, 1);

                // If there is a "|" in the line, the start of line
                // (before the "|") is made of attributes.
                // The end of the line (after the "|") is the cell content
                // This is not true if the pipe is inside []
                // | [foo|bar] 
                // The following cases must work:
                // | foo    
                // | [foo|bar]
                // | class="xxx" | foo
                // | class="xxx" | [foo|bar]
                $pospipe = strpos($line, "|");
                $posbracket = strpos($line, "[");
                if (($pospipe !== false) && (($posbracket === false) || ($posbracket > $pospipe))) {
                    $attrs = parse_attributes(substr($line, 0, $pospipe));
                    foreach ($attrs as $key => $value) {
                        if (in_array ($key, array("id", "class", "title", "style",
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
            $tbody->pushContent($row);
            $table->pushContent($tbody);
        }
        return $table;
    }
}

// For emacs users
// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
?>
