<?php

/**
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
 * You should have received a copy of the GNU General Public License along
 * with PhpWiki; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * OldStyleTable: Layout tables using the old table style.
 *
 * Usage:
 * <pre>
 *  <<OldStyleTable border||=0
 *  ||  __Name__               |v __Cost__   |v __Notes__
 *  | __First__   | __Last__
 *  |> Jeff       |< Dairiki   |^  Cheap     |< Not worth it
 *  |> Marco      |< Polo      | Cheaper     |< Not available
 *  >>
 * </pre>
 *
 * Note that multiple <code>|</code>'s lead to spanned columns,
 * and <code>v</code>'s can be used to span rows.  A <code>&gt;</code>
 * generates a right justified column, <code>&lt;</code> a left
 * justified column and <code>^</code> a centered column
 * (which is the default.)
 *
 * @author Geoffrey T. Dairiki
 */

class WikiPlugin_OldStyleTable
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Layout tables using the old markup style.");
    }

    function getDefaultArguments()
    {
        return array(
            'caption' => '',
            'cellpadding' => '1',
            'cellspacing' => '1',
            'border' => '1',
            'summary' => '',
        );
    }

    function handle_plugin_args_cruft($argstr, $args)
    {
        return;
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        include_once 'lib/InlineParser.php';

        $args = $this->getArgs($argstr, $request);
        $default = $this->getDefaultArguments();
        foreach (array('cellpadding', 'cellspacing', 'border') as $arg) {
            if (!is_numeric($args[$arg])) {
                $args[$arg] = $default[$arg];
            }
        }
        $lines = preg_split('/\s*?\n\s*/', $argstr);
        $table_args = array();
        $default_args = array_keys($default);
        foreach ($default_args as $arg) {
            if ($args[$arg] == '' and $default[$arg] == '')
                continue; // ignore '' arguments
            if ($arg == 'caption')
                $caption = $args[$arg];
            else
                $table_args[$arg] = $args[$arg];
        }
        $table = HTML::table($table_args);
        if (!empty($caption))
            $table->pushContent(HTML::caption($caption));
        if (preg_match("/^\s*(cellpadding|cellspacing|border|caption|summary)/", $lines[0]))
            $lines[0] = '';
        foreach ($lines as $line) {
            if (!$line)
                continue;
            if (strstr($line, "=")) {
                $tmp = explode("=", $line);
                if (in_array(trim($tmp[0]), $default_args))
                    continue;
            }
            if ($line[0] != '|') {
                // bogus error if argument
                trigger_error(sprintf(_("Line %s does not begin with a '|'."), $line), E_USER_WARNING);
            } else {
                $table->pushContent($this->parse_row($line, $basepage));
            }
        }

        return $table;
    }

    private function parse_row($line, $basepage)
    {
        $bracket_link = "\\[ .*? [^]\s] .*? \\]";
        $cell_content = "(?: [^[] | " . ESCAPE_CHAR . "\\[ | $bracket_link )*?";

        preg_match_all("/(\\|+) (v*) ([<>^]?) \s* ($cell_content) \s* (?=\\||\$)/x",
            $line, $matches, PREG_SET_ORDER);

        $row = HTML::tr();

        foreach ($matches as $m) {
            $attr = array();

            if (strlen($m[1]) > 1)
                $attr['colspan'] = strlen($m[1]);
            if (strlen($m[2]) > 0)
                $attr['rowspan'] = strlen($m[2]) + 1;

            if ($m[3] == '^')
                $attr['align'] = 'center';
            else if ($m[3] == '>')
                $attr['align'] = 'right';
            else
                $attr['align'] = 'left';

            $content = TransformInline($m[4], $basepage);

            $row->pushContent(HTML::td($attr, HTML::raw('&nbsp;'),
                $content, HTML::raw('&nbsp;')));
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
