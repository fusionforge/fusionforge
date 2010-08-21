<?php // -*-php-*-
// rcs_id('$Id: WikicreoleTable.php 7638 2010-08-11 11:58:40Z vargenau $');
/*
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
 * You should have received a copy of the GNU General Public License
 * along with PhpWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The WikicreoleTablePlugin ("Contribution") has not been tested and/or
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
 * WikicreoleTablePlugin
 * A PhpWiki plugin that allows insertion of tables using the Wikicreole
 * syntax.
 */

class WikiPlugin_WikicreoleTable
extends WikiPlugin
{
    function getName() {
        return _("WikicreoleTable");
    }

    function getDescription() {
      return _("Layout tables using the Wikicreole syntax.");
    }

    function getDefaultArguments() {
        return array();
    }

    function handle_plugin_args_cruft($argstr, $args) {
        return;
    }

    function run($dbi, $argstr, &$request, $basepage) {
        global $WikiTheme;
        include_once('lib/InlineParser.php');

        $table = array();

        $lines = preg_split('/\s*?\n\s*/', $argstr);

        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }
            $line = trim($line);
            // If line ends with a '|', remove it
            if ($line[strlen($line)-1] == '|') {
                $line = substr($line, 0, -1);
            }
            if ($line[0] != '|') {
                // trigger_error(sprintf(_("Line %s does not begin with a '|'."), $line), E_USER_WARNING);
            } else {
                $table[] = $this->_parse_row($line, $basepage);
            }
        }

        $nbrows = sizeof($table);
        // If table is empty, do not generate table markup
        if ($nbrows == 0) {
            return HTML::raw('');
        }
        $nbcols = sizeof($table[0]);

        for ($i=0; $i<$nbrows; $i++) {
            for ($j=0; $j<$nbcols; $j++) {
                if (preg_match('/@@/', $table[$i][$j])) {
                    $table[$i][$j] = compute_tablecell($table, $i, $j, $nbrows, $nbcols);
                }
            }
        }

        $htmltable = HTML::table(array('class' => "bordered"));
        foreach ($table as $row) {
            $htmlrow = HTML::tr();
            foreach ($row as $cell) {
                if ($cell && $cell[0] == '=') {
                    $cell = trim(substr($cell, 1));
                    $htmlrow->pushContent(HTML::th(TransformInline($cell, 2.0, $basepage)));
                } else {
                    if (is_numeric($cell)) {
                        $htmlrow->pushContent(HTML::td(array('style' => "text-align:right"), $cell));
                    } else {
                        $htmlrow->pushContent(HTML::td(TransformInline($cell, 2.0, $basepage)));
                    }
                }
            }
            $htmltable->pushContent($htmlrow);
        }
        return $htmltable;
    }

    function _parse_row ($line, $basepage) {
        $brkt_link = "\\[ .*? [^]\s] .*? \\]";
        $cell_content  = "(?: [^[] | ".ESCAPE_CHAR."\\[ | $brkt_link )*?";

        preg_match_all("/(\\|+) \s* ($cell_content) \s* (?=\\||\$)/x",
                       $line, $matches, PREG_SET_ORDER);

        $row = array();

        foreach ($matches as $m) {
            $cell = $m[2];
            $row[]= $cell;
        }
        return $row;
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
