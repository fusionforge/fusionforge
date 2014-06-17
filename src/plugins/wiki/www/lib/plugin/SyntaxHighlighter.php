<?php

/**
 * Copyright 2004 $ThePhpWikiProgrammingTeam
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

class WikiPlugin_SyntaxHighlighter
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Source code syntax highlighter (via http://highlightjs.org/).");
    }

    function managesValidators()
    {
        return true;
    }

    function getDefaultArguments()
    {
        return array(
            'syntax' => null, // required argument
            'style' => null, // optional argument ["ansi", "gnu", "kr", "java", "linux"]
            'color' => null, // optional, see highlight/themes
            'number' => 0,
            'wrap' => 0,
        );
    }

    function handle_plugin_args_cruft(&$argstr, &$args)
    {
        $this->source = $argstr;
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        extract($this->getArgs($argstr, $request));
        $source =& $this->source;
        if (empty($source)) {
            return HTML::div(array('class' => "error"),
                   "Please provide source code to SyntaxHighlighter plugin");
        }
        $html = HTML();
        $code = "\n<code>\n".htmlspecialchars($source)."\n</code>\n";
        $pre = HTML::pre(HTML::raw($code));
        $html->pushContent($pre);
        return HTML($html);
    }
}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
