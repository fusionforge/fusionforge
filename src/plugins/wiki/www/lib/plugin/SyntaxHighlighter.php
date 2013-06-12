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

/**
 * The SyntaxHighlighter plugin passes all its arguments through a C++
 * highlighter called "highlight" (available at http://www.andre-simon.de/).
 *
 * @author: alecthomas
 */
if (!defined('HIGHLIGHT_EXE'))
    define('HIGHLIGHT_EXE', 'highlight');
// highlight requires two subdirs themes and langDefs somewhere.
// Best by highlight.conf in $HOME, but the webserver user usually
// doesn't have a $HOME
if (!defined('HIGHLIGHT_DATA_DIR'))
    if (isWindows())
        define('HIGHLIGHT_DATA_DIR', 'f:\cygnus\usr\local\share\highlight');
    else
        define('HIGHLIGHT_DATA_DIR', '/usr/share/highlight');

class WikiPlugin_SyntaxHighlighter
    extends WikiPlugin
{
    function getDescription()
    {
        return _("Source code syntax highlighter (via http://www.andre-simon.de).");
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

    function newFilterThroughCmd($input, $commandLine)
    {
        $descriptorspec = array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w"), // stdout is a pipe that the child will write to
            2 => array("pipe", "w"), // stdout is a pipe that the child will write to
        );

        $process = proc_open("$commandLine", $descriptorspec, $pipes);
        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable  handle connected to child stdout
            // 2 => readable  handle connected to child stderr
            fwrite($pipes[0], $input);
            fclose($pipes[0]);
            $buf = "";
            while (!feof($pipes[1])) {
                $buf .= fgets($pipes[1], 1024);
            }
            fclose($pipes[1]);
            $stderr = '';
            while (!feof($pipes[2])) {
                $stderr .= fgets($pipes[2], 1024);
            }
            fclose($pipes[2]);
            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            proc_close($process);
            if (empty($buf)) {
                printXML($this->error($stderr));
            }
            return $buf;
        }
        return '';
    }

    function run($dbi, $argstr, &$request, $basepage)
    {
        extract($this->getArgs($argstr, $request));
        $source =& $this->source;
        if (empty($syntax)) {
            return $this->error(sprintf(_("A required argument “%s” is missing."), 'syntax'));
        }
        if (empty($source)) {
            return HTML::div(array('class' => "error"),
                   "Please provide source code to SyntaxHighlighter plugin");
        }
        $args = "";
        if (defined('HIGHLIGHT_DATA_DIR')) {
            $args .= " --data-dir " . HIGHLIGHT_DATA_DIR;
        }
        if ($number != 0) {
            $args .= " -l";
        }
        if ($wrap != 0) {
            $args .= " -V";
        }
        $html = HTML();
        if (!empty($color) and !preg_match('/^[\w-]+$/', $color)) {
            $html->pushContent($this->error(fmt("invalid %s ignored", 'color')));
            $color = false;
        }
        if (!empty($color)) {
            $args .= " --style $color --inline-css";
        }
        if (!empty($style)) {
            $args .= " -F $style";
        }
        $commandLine = HIGHLIGHT_EXE . "$args -q -X -f -S $syntax";
        $code = $this->newFilterThroughCmd($source, $commandLine);
        if (empty($code)) {
            return $this->error(fmt("Couldn't start commandline “%s”", $commandLine));
        }
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
