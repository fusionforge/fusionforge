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
 * A plugin that runs the highlight_string() function in PHP on its
 * arguments to pretty-print PHP code.
 */

class WikiPlugin_PhpHighlight
    extends WikiPlugin
{
    function getDescription()
    {
        return _("PHP syntax highlighting.");
    }

    // Establish default values for each of this plugin's arguments.
    function getDefaultArguments()
    {
        // TODO: results of ini_get() should be static for multiple
        // invocations of plugin on one WikiPage
        return array('wrap' => true,
            'string' => ini_get("highlight.string"), //'#00CC00',
            'comment' => ini_get("highlight.comment"), //'#FF9900',
            'keyword' => ini_get("highlight.keyword"), //'#006600',
            'bg' => (version_compare(PHP_VERSION, '5.4', '<')) ? ini_get("highlight.bg") : '#FFFFFF',
            'default' => ini_get("highlight.default"), //'#0000CC',
            'html' => ini_get("highlight.html") //'#000000'
        );
    }

    function run($dbi, $argstr, &$request, $basepage)
    {

        extract($this->getArgs($argstr, $request));
        $source =& $this->source;
        if (empty($source)) {
            return HTML::div(array('class' => "error"),
                   _("Please provide source code to PhpHighlight plugin"));
        }

        $this->sanify_colors($string, $comment, $keyword, $bg, $default, $html);
        $this->set_colors($string, $comment, $keyword, $bg, $default, $html);

        if ($wrap) {
            /* Wrap with "<?php\n" and "\n?>" required by highlight_string(): */
            $source = "<?php\n" . $source . "\n?>";
        } else {
            $source = str_replace(array('< ?php', '? >'),
                array('<?php', '?>'), $source);
        }

        $str = highlight_string($source, true);

        if ($wrap) {
            /* Remove "<?php\n" and "\n?>" again: */
            $str = str_replace(array('&lt;?php<br />', '?&gt;'), '', $str);
        }

        /* Remove empty span tags. */
        foreach (array($string, $comment, $keyword, $bg, $default, $html) as $color) {
            $search = "<span style=\"color: $color\"></span>";
            $str = str_replace($search, '', $str);
        }

        /* restore default colors in case of multiple invocations of this plugin on one page */
        $this->restore_colors();
        return new RawXml($str);
    }

    function handle_plugin_args_cruft(&$argstr, &$args)
    {
        $this->source = $argstr;
    }

    /**
     * Make sure color argument is valid
     * See http://www.w3.org/TR/REC-html40/types.html#h-6.5
     */
    function sanify_colors($string, $comment, $keyword, $bg, $default, $html)
    {
        static $html4colors = array("black", "silver", "gray", "white",
            "maroon", "red", "purple", "fuchsia",
            "green", "lime", "olive", "yellow",
            "navy", "blue", "teal", "aqua");
        /* max(strlen("fuchsia"), strlen("#00FF00"), ... ) = 7 */
        static $MAXLEN = 7;
        foreach (array($string, $comment, $keyword, $bg, $default, $html) as $color) {
            $length = strlen($color);
            //trigger_error(sprintf(_("DEBUG: color “%s” is length %d."), $color, $length), E_USER_NOTICE);
            if (($length == 7 || $length == 4) && substr($color, 0, 1) == "#"
                && "#" == preg_replace("/[a-fA-F0-9]/", "", $color)
            ) {
                //trigger_error(sprintf(_("DEBUG: color “%s” appears to be hex."), $color), E_USER_NOTICE);
                // stop checking, ok to go
            } elseif (($length < $MAXLEN + 1) && in_array($color, $html4colors)) {
                //trigger_error(sprintf(_("DEBUG color “%s” appears to be an HTML 4 color."), $color), E_USER_NOTICE);
                // stop checking, ok to go
            } else {
                trigger_error(sprintf(_("Invalid color: %s"),
                    $color), E_USER_NOTICE);
                // FIXME: also change color to something valid like "black" or ini_get("highlight.xxx")
            }
        }
    }

    function set_colors($string, $comment, $keyword, $bg, $default, $html)
    {
        // set highlight colors
        $this->oldstring = ini_set('highlight.string', $string);
        $this->oldcomment = ini_set('highlight.comment', $comment);
        $this->oldkeyword = ini_set('highlight.keyword', $keyword);
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->oldbg = ini_set('highlight.bg', $bg);
        }
        $this->olddefault = ini_set('highlight.default', $default);
        $this->oldhtml = ini_set('highlight.html', $html);
    }

    function restore_colors()
    {
        // restore previous default highlight colors
        ini_set('highlight.string', $this->oldstring);
        ini_set('highlight.comment', $this->oldcomment);
        ini_set('highlight.keyword', $this->oldkeyword);
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            ini_set('highlight.bg', $this->oldbg);
        }
        ini_set('highlight.default', $this->olddefault);
        ini_set('highlight.html', $this->oldhtml);
    }

}

// Local Variables:
// mode: php
// tab-width: 8
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
